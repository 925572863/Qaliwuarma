"""
Modelo de aprendizaje automatico (Random Forest) para predecir la demanda
diaria de raciones del servicio alimentario escolar.

Implementa el algoritmo descrito en la tesis "Modelo de aprendizaje automatico
para optimizar la gestion del servicio alimentario escolar en una institucion
educativa de Piura, 2026": Random Forest (Breiman, 2001), evaluado mediante
VALIDACION TEMPORAL CON ORIGEN MOVIL (rolling-origin / walk-forward): se
entrena inicialmente con un tramo base del historico y luego se reentrena al
final de cada semana con todos los datos anteriores, prediciendo unicamente
los dias de la semana siguiente (nunca datos futuros a los que el modelo no
tendria acceso en produccion). Se reporta MAE, RMSE, MAPE y R^2 sobre todas
las predicciones fuera de muestra concatenadas.

Este script es invocado por Laravel (PrediccionIAService) via linea de
comandos y se comunica exclusivamente por JSON (stdin -> stdout), sin
dependencias de framework web.

Uso:
    python prediccion_raciones.py entrenar   --nivel inicial --datos ruta.json --modelo ruta.joblib
    python prediccion_raciones.py predecir    --nivel inicial --datos ruta.json --modelo ruta.joblib --dias 5
"""

import argparse
import json
import sys
import time
from datetime import datetime, timedelta

import joblib
import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.base import clone
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score

MIN_MUESTRAS = 10
N_ARBOLES = 300
PROFUNDIDAD_MAX = 8
RANDOM_STATE = 42

# Validacion temporal con origen movil: proporcion del historico que se usa
# como entrenamiento inicial (equivalente a "marzo-junio" en la tesis, ~40%
# de los dias lectivos) y tamano del paso semanal de reentrenamiento (5 dias
# lectivos = 1 semana escolar).
PROPORCION_ENTRENO_INICIAL = 0.4
PASO_SEMANAL = 5


def cargar_historico(ruta_datos: str) -> pd.DataFrame:
    """Carga el historico diario {fecha, raciones} exportado por Laravel."""
    with open(ruta_datos, "r", encoding="utf-8") as f:
        registros = json.load(f)

    df = pd.DataFrame(registros)
    if df.empty:
        return df

    df["fecha"] = pd.to_datetime(df["fecha"])
    df = df.sort_values("fecha").reset_index(drop=True)
    return df


def construir_features(df: pd.DataFrame) -> tuple[pd.DataFrame, dict]:
    """
    Preprocesamiento de datos (dimension 1 de la VI): construye variables de
    calendario y promedios moviles (MA3, MA7) a partir del historico diario.

    Devuelve el dataframe depurado junto con las metricas de la Ficha 1:
    porcentaje de registros depurados correctamente y porcentaje de datos
    completos disponibles para el entrenamiento.
    """
    df = df.copy()
    registros_totales = len(df)

    # Completitud: filas sin valores nulos en las columnas fuente antes de derivar features
    completos = int(df[["fecha", "raciones"]].dropna().shape[0])
    pct_completos = round((completos / registros_totales) * 100, 2) if registros_totales else 0.0

    df["dia_semana"] = df["fecha"].dt.dayofweek
    df["dia_mes"] = df["fecha"].dt.day
    df["mes"] = df["fecha"].dt.month
    df["indice"] = np.arange(len(df))
    df["ma3"] = df["raciones"].rolling(window=3, min_periods=1).mean().shift(1)
    df["ma7"] = df["raciones"].rolling(window=7, min_periods=1).mean().shift(1)

    # Variables de contexto del marco teorico (clima, eventos especiales del calendario).
    # Si no vienen en los datos exportados (compatibilidad con historico antiguo), se asume neutro (0).
    df["clima_lluvioso"] = df["clima_lluvioso"].fillna(0) if "clima_lluvioso" in df.columns else 0
    df["evento_especial"] = df["evento_especial"].fillna(0) if "evento_especial" in df.columns else 0

    # Los primeros registros no tienen historico previo suficiente: se depuran (se descartan)
    df_depurado = df.dropna(subset=["ma3", "ma7"]).reset_index(drop=True)
    registros_depurados = len(df_depurado)
    pct_depurados = round((registros_depurados / registros_totales) * 100, 2) if registros_totales else 0.0

    metricas_preprocesamiento = {
        "registros_totales": registros_totales,
        "registros_depurados": registros_depurados,
        "pct_depurados": pct_depurados,
        "pct_completos": pct_completos,
    }

    return df_depurado, metricas_preprocesamiento


FEATURE_COLS = ["dia_semana", "dia_mes", "mes", "indice", "ma3", "ma7", "clima_lluvioso", "evento_especial"]


def calcular_metricas(y_true, y_pred) -> dict:
    """Metricas de desempeno predictivo (MAE, RMSE, MAPE, R^2) segun la tesis."""
    mae = mean_absolute_error(y_true, y_pred)
    rmse = float(np.sqrt(mean_squared_error(y_true, y_pred)))
    y_true_arr = np.array(y_true, dtype=float)
    y_pred_arr = np.array(y_pred, dtype=float)
    mask = y_true_arr != 0
    mape = (
        float(np.mean(np.abs((y_true_arr[mask] - y_pred_arr[mask]) / y_true_arr[mask])) * 100)
        if mask.any()
        else None
    )
    r2 = float(r2_score(y_true, y_pred)) if len(y_true) > 1 else None

    return {
        "mae": round(float(mae), 3),
        "rmse": round(rmse, 3),
        "mape": round(mape, 3) if mape is not None else None,
        "r2": round(r2, 4) if r2 is not None else None,
    }


def nuevo_modelo() -> RandomForestRegressor:
    return RandomForestRegressor(
        n_estimators=N_ARBOLES,
        max_depth=PROFUNDIDAD_MAX,
        random_state=RANDOM_STATE,
        n_jobs=-1,
    )


def validar_temporal(X: np.ndarray, y: np.ndarray, fechas: pd.Series) -> dict:
    """
    Validacion temporal con origen movil (rolling-origin / walk-forward):
    entrena con el tramo inicial del historico y, desde ahi, avanza semana
    por semana reentrenando SOLO con datos ya conocidos hasta ese punto y
    prediciendo unicamente los dias de la semana siguiente. A diferencia de
    K-Fold, ningun dato "futuro" participa jamas del entrenamiento de una
    prediccion pasada, tal como ocurriria en produccion.
    """
    n = len(X)
    corte_inicial = max(MIN_MUESTRAS, int(n * PROPORCION_ENTRENO_INICIAL))

    if corte_inicial >= n:
        return {
            "metricas": {"mae": None, "rmse": None, "mape": None, "r2": None},
            "margen_seguridad_p95": None,
            "semanas_evaluadas": 0,
            "muestras_entreno_inicial": int(corte_inicial),
            "semanas_detalle": [],
        }

    y_true_oof = []
    y_pred_oof = []
    semanas_detalle = []

    origen = corte_inicial
    semana = 0
    while origen < n:
        fin_ventana = min(origen + PASO_SEMANAL, n)

        estimador = clone(nuevo_modelo())
        estimador.fit(X[:origen], y[:origen])
        pred_semana = estimador.predict(X[origen:fin_ventana])

        semana += 1
        m_semana = calcular_metricas(y[origen:fin_ventana], pred_semana)
        semanas_detalle.append({
            "semana": semana,
            "desde": fechas.iloc[origen].strftime("%Y-%m-%d"),
            "hasta": fechas.iloc[fin_ventana - 1].strftime("%Y-%m-%d"),
            "muestras_entreno": int(origen),
            **m_semana,
        })

        y_true_oof.extend(y[origen:fin_ventana].tolist())
        y_pred_oof.extend(pred_semana.tolist())

        origen = fin_ventana

    metricas = calcular_metricas(y_true_oof, y_pred_oof)
    errores_abs = np.abs(np.array(y_true_oof) - np.array(y_pred_oof))
    margen_seguridad = float(np.percentile(errores_abs, 95)) if len(errores_abs) else None

    return {
        "metricas": metricas,
        "margen_seguridad_p95": round(margen_seguridad, 2) if margen_seguridad is not None else None,
        "semanas_evaluadas": semana,
        "muestras_entreno_inicial": int(corte_inicial),
        "semanas_detalle": semanas_detalle,
    }


def entrenar(nivel: str, ruta_datos: str, ruta_modelo: str, k_folds: int = 5) -> dict:
    df = cargar_historico(ruta_datos)
    if df.empty:
        return {"ok": False, "error": "sin_datos"}

    df, preprocesamiento = construir_features(df)
    if len(df) < MIN_MUESTRAS:
        return {"ok": False, "error": "muestras_insuficientes", "muestras": len(df)}

    X = df[FEATURE_COLS].values
    y = df["raciones"].values

    inicio = time.perf_counter()

    # Entrenamiento y validacion (dimension 2 de la VI): validacion temporal
    # con origen movil, tal como exige el diseno metodologico de la tesis
    # (entrenamiento inicial + reentrenamiento semanal solo con datos pasados).
    resultado_validacion = validar_temporal(X, y, df["fecha"])
    metricas = resultado_validacion["metricas"]

    # Modelo final entrenado con el 100% de los datos disponibles, para las
    # predicciones futuras reales (no para la metrica reportada arriba).
    modelo = nuevo_modelo()
    modelo.fit(X, y)
    tiempo_entrenamiento = round(time.perf_counter() - inicio, 2)

    joblib.dump({
        "modelo": modelo,
        "nivel": nivel,
        "margen_seguridad_p95": resultado_validacion.get("margen_seguridad_p95"),
    }, ruta_modelo)

    return {
        "ok": True,
        "nivel": nivel,
        "muestras": int(len(df)),
        "n_estimators": N_ARBOLES,
        "max_depth": PROFUNDIDAD_MAX,
        "validacion": "temporal_origen_movil",
        "semanas_evaluadas": resultado_validacion["semanas_evaluadas"],
        "muestras_entreno_inicial": resultado_validacion.get("muestras_entreno_inicial"),
        "margen_seguridad_p95": resultado_validacion.get("margen_seguridad_p95"),
        "tiempo_entrenamiento_seg": tiempo_entrenamiento,
        "preprocesamiento": preprocesamiento,
        "semanas_detalle": resultado_validacion["semanas_detalle"],
        "metricas": metricas,
    }


def predecir(nivel: str, ruta_datos: str, ruta_modelo: str, dias: int = 5) -> dict:
    try:
        paquete = joblib.load(ruta_modelo)
    except FileNotFoundError:
        return {"ok": False, "error": "modelo_no_entrenado"}

    modelo = paquete["modelo"]
    margen_seguridad = paquete.get("margen_seguridad_p95") or 0

    df = cargar_historico(ruta_datos)
    if df.empty:
        return {"ok": False, "error": "sin_datos"}

    serie = df["raciones"].tolist()
    n = len(df)
    fecha_actual = df["fecha"].max()

    predicciones = []
    dias_agregados = 0
    offset = 1
    dias_es = [
        "lunes", "martes", "miercoles", "jueves", "viernes", "sabado", "domingo",
    ]

    while dias_agregados < dias and offset <= 30:
        fecha = fecha_actual + timedelta(days=offset)
        offset += 1
        if fecha.weekday() >= 5:  # sabado=5, domingo=6: dias no lectivos
            continue

        idx = n + dias_agregados
        ma3 = float(np.mean(serie[-3:])) if len(serie) >= 1 else 0.0
        ma7 = float(np.mean(serie[-7:])) if len(serie) >= 1 else 0.0

        # Clima y eventos especiales futuros son desconocidos al momento de predecir;
        # se asumen neutros (0) salvo que se integre un pronostico o calendario externo.
        X_pred = np.array(
            [[fecha.weekday(), fecha.day, fecha.month, idx, ma3, ma7, 0, 0]]
        )
        asistencia_estimada = max(0, round(float(modelo.predict(X_pred)[0])))
        # La cantidad de raciones a preparar suma el margen de seguridad
        # (percentil 95 del error del modelo en validacion temporal) a la
        # asistencia estimada, tal como define la tesis.
        raciones_sugeridas = int(asistencia_estimada + round(margen_seguridad))
        serie.append(asistencia_estimada)

        predicciones.append({
            "fecha": fecha.strftime("%Y-%m-%d"),
            "fecha_legible": f"{dias_es[fecha.weekday()].capitalize()} {fecha.day}/{fecha.month:02d}",
            "asistencia_estimada": int(asistencia_estimada),
            "margen_seguridad": round(margen_seguridad),
            "raciones_predichas": raciones_sugeridas,
        })
        dias_agregados += 1

    return {"ok": True, "nivel": nivel, "predicciones": predicciones}


def main():
    parser = argparse.ArgumentParser(description="Modelo Random Forest de prediccion de raciones")
    parser.add_argument("accion", choices=["entrenar", "predecir"])
    parser.add_argument("--nivel", required=True)
    parser.add_argument("--datos", required=True, help="Ruta al JSON con el historico diario")
    parser.add_argument("--modelo", required=True, help="Ruta del archivo .joblib del modelo")
    parser.add_argument("--dias", type=int, default=5)
    parser.add_argument("--k-folds", type=int, default=5)
    args = parser.parse_args()

    if args.accion == "entrenar":
        resultado = entrenar(args.nivel, args.datos, args.modelo, args.k_folds)
    else:
        resultado = predecir(args.nivel, args.datos, args.modelo, args.dias)

    print(json.dumps(resultado, ensure_ascii=False))
    sys.exit(0 if resultado.get("ok") else 1)


if __name__ == "__main__":
    main()
