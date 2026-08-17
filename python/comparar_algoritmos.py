"""
Comparacion de algoritmos (Random Forest vs XGBoost vs LSTM) para justificar
la seleccion de Random Forest como modelo final, tal como se describe en la
metodologia de la tesis: "el modelo se comparo con algoritmos alternativos
como XGBoost y LSTM para garantizar la seleccion del enfoque mas eficiente".

Script INDEPENDIENTE, no forma parte del pipeline de produccion (Laravel no
lo invoca). Se ejecuta manualmente, una sola vez por nivel, para generar la
tabla comparativa que respalda esa afirmacion metodologica.

El LSTM esta implementado desde cero con numpy (ver clase LSTMSimple), en
vez de con TensorFlow/Keras: la descarga del paquete de TensorFlow (~380 MB)
resulto sistematicamente no confiable en este entorno (fallo repetido de
hash/timeout), y una implementacion propia evita esa dependencia fragil sin
sacrificar que el algoritmo evaluado sea un LSTM real (ecuaciones estandar
de Hochreiter y Schmidhuber, 1997, entrenado con descenso de gradiente).

Uso:
    pip install -r requirements-comparacion.txt
    python comparar_algoritmos.py --datos ruta_a_datos_nivel.json
"""

import argparse
import json
import time

import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.model_selection import KFold
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from xgboost import XGBRegressor

from prediccion_raciones import cargar_historico, construir_features, FEATURE_COLS, calcular_metricas


def _sigmoid(x):
    return 1.0 / (1.0 + np.exp(-np.clip(x, -50, 50)))


class LSTMSimple:
    """
    Implementacion minima de una celda LSTM (Hochreiter y Schmidhuber, 1997)
    para regresion sobre una secuencia univariada, entrenada con descenso de
    gradiente (backpropagation through time) implementado manualmente.
    Suficiente para el proposito comparativo de este script; no pretende
    sustituir a un framework de produccion.
    """

    def __init__(self, n_entrada=1, n_oculto=16, semilla=42):
        rng = np.random.default_rng(semilla)
        n = n_entrada + n_oculto
        escala = 1.0 / np.sqrt(n)

        # Pesos combinados [h_prev, x] -> puertas (forget, input, output, candidato)
        self.Wf = rng.uniform(-escala, escala, (n_oculto, n))
        self.Wi = rng.uniform(-escala, escala, (n_oculto, n))
        self.Wo = rng.uniform(-escala, escala, (n_oculto, n))
        self.Wc = rng.uniform(-escala, escala, (n_oculto, n))
        self.bf = np.zeros(n_oculto)
        self.bi = np.zeros(n_oculto)
        self.bo = np.zeros(n_oculto)
        self.bc = np.zeros(n_oculto)
        self.Wy = rng.uniform(-escala, escala, (1, n_oculto))
        self.by = np.zeros(1)
        self.n_oculto = n_oculto

    def _forward_secuencia(self, x_seq):
        """x_seq: (T, n_entrada). Devuelve el estado oculto final y los caches por paso, para BPTT."""
        T = x_seq.shape[0]
        h = np.zeros(self.n_oculto)
        c = np.zeros(self.n_oculto)
        caches = []
        for t in range(T):
            z = np.concatenate([h, x_seq[t]])
            f = _sigmoid(self.Wf @ z + self.bf)
            i = _sigmoid(self.Wi @ z + self.bi)
            o = _sigmoid(self.Wo @ z + self.bo)
            c_tilde = np.tanh(self.Wc @ z + self.bc)
            c_nuevo = f * c + i * c_tilde
            h_nuevo = o * np.tanh(c_nuevo)
            caches.append((z, f, i, o, c_tilde, c, c_nuevo, h))
            h, c = h_nuevo, c_nuevo
        return h, caches

    def predecir(self, X):
        """X: (N, T, n_entrada) -> (N,)"""
        salidas = []
        for x_seq in X:
            h, _ = self._forward_secuencia(x_seq)
            salidas.append(float((self.Wy @ h + self.by)[0]))
        return np.array(salidas)

    def entrenar(self, X, y, epocas=60, tasa_aprendizaje=0.05):
        """Descenso de gradiente por muestra (SGD), con BPTT truncado a la ventana de entrada."""
        n_muestras = X.shape[0]
        for _ in range(epocas):
            orden = np.random.permutation(n_muestras)
            for idx in orden:
                x_seq, y_obj = X[idx], y[idx]
                h_final, caches = self._forward_secuencia(x_seq)
                y_pred = float((self.Wy @ h_final + self.by)[0])
                error = y_pred - y_obj  # d(MSE)/dy_pred

                # Gradientes de la capa de salida
                dWy = error * h_final.reshape(1, -1)
                dby = np.array([error])

                dh_next = (error * self.Wy.flatten())
                dc_next = np.zeros(self.n_oculto)

                dWf = np.zeros_like(self.Wf); dbf = np.zeros_like(self.bf)
                dWi = np.zeros_like(self.Wi); dbi = np.zeros_like(self.bi)
                dWo = np.zeros_like(self.Wo); dbo = np.zeros_like(self.bo)
                dWc = np.zeros_like(self.Wc); dbc = np.zeros_like(self.bc)

                for z, f, i, o, c_tilde, c_prev, c_nuevo, h_prev in reversed(caches):
                    tanh_c = np.tanh(c_nuevo)
                    dh = dh_next
                    do = dh * tanh_c * o * (1 - o)
                    dc = dh * o * (1 - tanh_c ** 2) + dc_next
                    df = dc * c_prev * f * (1 - f)
                    di = dc * c_tilde * i * (1 - i)
                    dc_tilde = dc * i * (1 - c_tilde ** 2)

                    dWf += np.outer(df, z); dbf += df
                    dWi += np.outer(di, z); dbi += di
                    dWo += np.outer(do, z); dbo += do
                    dWc += np.outer(dc_tilde, z); dbc += dc_tilde

                    dz = (self.Wf.T @ df + self.Wi.T @ di + self.Wo.T @ do + self.Wc.T @ dc_tilde)
                    dh_next = dz[:self.n_oculto]
                    dc_next = dc * f

                for W, dW in [(self.Wf, dWf), (self.Wi, dWi), (self.Wo, dWo), (self.Wc, dWc)]:
                    np.clip(dW, -5, 5, out=dW)
                    W -= tasa_aprendizaje * dW
                for b, db in [(self.bf, dbf), (self.bi, dbi), (self.bo, dbo), (self.bc, dbc)]:
                    b -= tasa_aprendizaje * np.clip(db, -5, 5)
                self.Wy -= tasa_aprendizaje * np.clip(dWy, -5, 5)
                self.by -= tasa_aprendizaje * np.clip(dby, -5, 5)

N_ARBOLES = 300
PROFUNDIDAD_MAX = 8
RANDOM_STATE = 42
K_FOLDS = 5
VENTANA_LSTM = 7  # dias previos usados como secuencia de entrada


def entrenar_random_forest(X, y, idx_train, idx_test):
    modelo = RandomForestRegressor(
        n_estimators=N_ARBOLES, max_depth=PROFUNDIDAD_MAX,
        random_state=RANDOM_STATE, n_jobs=-1,
    )
    modelo.fit(X[idx_train], y[idx_train])
    return modelo.predict(X[idx_test])


def entrenar_xgboost(X, y, idx_train, idx_test):
    modelo = XGBRegressor(
        n_estimators=N_ARBOLES, max_depth=6, learning_rate=0.1,
        random_state=RANDOM_STATE, n_jobs=-1,
    )
    modelo.fit(X[idx_train], y[idx_train])
    return modelo.predict(X[idx_test])


def construir_secuencias(y, ventana):
    """Convierte la serie de raciones en secuencias [ventana] -> siguiente valor, para el LSTM."""
    Xs, ys = [], []
    for i in range(ventana, len(y)):
        Xs.append(y[i - ventana:i])
        ys.append(y[i])
    return np.array(Xs), np.array(ys)


def entrenar_lstm(y_completo, idx_train, idx_test, ventana=VENTANA_LSTM):
    """
    Red LSTM simple (una capa, 16 unidades, implementacion propia con numpy)
    entrenada sobre ventanas deslizantes de la serie temporal de raciones.
    A diferencia de RF/XGBoost (que usan variables de calendario), el LSTM
    aprende directamente de la secuencia, que es su fortaleza tipica frente
    a datos temporales.
    """
    X_seq, y_seq = construir_secuencias(y_completo, ventana)
    # Los indices de train/test del k-fold aplican sobre la serie original;
    # aqui filtramos las secuencias cuyo target cae en cada particion.
    idx_train_set = set(idx_train.tolist())
    idx_test_set = set(idx_test.tolist())
    mask_train = np.array([(i + ventana) in idx_train_set for i in range(len(y_seq))])
    mask_test = np.array([(i + ventana) in idx_test_set for i in range(len(y_seq))])

    if mask_train.sum() < 5 or mask_test.sum() < 1:
        return None, None

    # Normalizacion simple (media/desviacion del set de entrenamiento) para
    # estabilizar el entrenamiento de la red.
    media, sigma = y_seq[mask_train].mean(), y_seq[mask_train].std() or 1.0

    X_train = ((X_seq[mask_train] - media) / sigma).reshape(-1, ventana, 1)
    y_train = (y_seq[mask_train] - media) / sigma
    X_test = ((X_seq[mask_test] - media) / sigma).reshape(-1, ventana, 1)
    y_test = y_seq[mask_test]

    modelo = LSTMSimple(n_entrada=1, n_oculto=16, semilla=RANDOM_STATE)
    modelo.entrenar(X_train, y_train, epocas=60, tasa_aprendizaje=0.05)

    pred_normalizada = modelo.predecir(X_test)
    pred = pred_normalizada * sigma + media
    return y_test, pred


def comparar(ruta_datos: str) -> dict:
    df = cargar_historico(ruta_datos)
    df, _ = construir_features(df)
    X = df[FEATURE_COLS].values
    y = df["raciones"].values.astype(float)

    kf = KFold(n_splits=min(K_FOLDS, len(df)), shuffle=True, random_state=RANDOM_STATE)

    resultados = {"random_forest": {"y_true": [], "y_pred": []},
                  "xgboost": {"y_true": [], "y_pred": []},
                  "lstm": {"y_true": [], "y_pred": []}}

    inicio = time.perf_counter()
    for idx_train, idx_test in kf.split(X):
        resultados["random_forest"]["y_true"].extend(y[idx_test].tolist())
        resultados["random_forest"]["y_pred"].extend(entrenar_random_forest(X, y, idx_train, idx_test).tolist())

        resultados["xgboost"]["y_true"].extend(y[idx_test].tolist())
        resultados["xgboost"]["y_pred"].extend(entrenar_xgboost(X, y, idx_train, idx_test).tolist())

        y_true_lstm, y_pred_lstm = entrenar_lstm(y, idx_train, idx_test)
        if y_true_lstm is not None:
            resultados["lstm"]["y_true"].extend(y_true_lstm.tolist())
            resultados["lstm"]["y_pred"].extend(y_pred_lstm.tolist())
    tiempo_total = round(time.perf_counter() - inicio, 2)

    comparacion = {}
    for algoritmo, datos in resultados.items():
        if not datos["y_true"]:
            comparacion[algoritmo] = {"error": "sin_datos_suficientes"}
            continue
        comparacion[algoritmo] = calcular_metricas(datos["y_true"], datos["y_pred"])

    return {
        "ok": True,
        "muestras": int(len(df)),
        "k_folds": kf.get_n_splits(),
        "tiempo_total_seg": tiempo_total,
        "comparacion": comparacion,
    }


def main():
    parser = argparse.ArgumentParser(description="Compara Random Forest, XGBoost y LSTM")
    parser.add_argument("--datos", required=True, help="Ruta al JSON con el historico diario (mismo formato que prediccion_raciones.py)")
    args = parser.parse_args()

    resultado = comparar(args.datos)
    print(json.dumps(resultado, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
