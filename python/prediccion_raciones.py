"""
Modelo de aprendizaje automatico (Random Forest) para predecir la asistencia
diaria y las raciones a preparar del servicio alimentario escolar.

Implementa el modelo principal evaluado en la tesis "Modelo de aprendizaje
automatico para optimizar la gestion del servicio alimentario escolar en una
institucion educativa de Piura, 2026" (capitulo II), con el mismo codigo base
del paquete de reproduccion depositado en Zenodo (01_modelos_principal.py):

- Un unico Random Forest agrupado para todas las secciones, que predice la
  asistencia de cada seccion y la suma por nivel.
- Los 13 predictores de la tesis, calculados solo con informacion disponible
  hasta el dia anterior (matriculados, asist_lag1, tasa_lag1, ma5, seas,
  dia_sem, semana_escolar, dias_desde_pausa, dias_hasta_pausa,
  post_vacaciones, es_inicial, grado_num, seccion_cod). No usa el mes ni el
  clima, que la tesis excluyo.
- Hiperparametros fijos elegidos en la tesis: 300 arboles, profundidad sin
  limite y minimo de 10 registros por hoja.
- Evaluacion fuera de muestra con origen movil y ventana expansiva:
  reentrenamiento al cierre de cada semana con todos los datos anteriores.
- Raciones a preparar = ceil(prediccion del nivel + cuantil 95 de los propios
  errores A - y_hat), con errores iniciales de una validacion temporal de
  cuatro particiones sobre el tramo de entrenamiento inicial (Ecuacion 4).

Diferencias inevitables con la evaluacion retrospectiva de la tesis, propias
de un uso en produccion:
- Para los dias futuros, la asistencia de los dias intermedios aun no existe,
  por lo que los rezagos del segundo dia en adelante usan la asistencia
  predicha (el primer dia coincide con el pronostico a un dia de la tesis).
- El calendario escolar posterior al ultimo registro no se conoce: los dias
  futuros se asumen lectivos de lunes a viernes y dias_hasta_pausa se calcula
  con la misma regla de la tesis sobre el calendario conocido.
- Con historial de varios anos, semana_escolar se cuenta desde el primer dia
  de clases de cada ano (en la tesis, con un solo ano, es lo mismo).

Este script es invocado por Laravel (PrediccionIAService) via linea de
comandos y se comunica exclusivamente por JSON (archivo -> stdout).

Uso:
    python prediccion_raciones.py entrenar --nivel inicial --datos ruta.json --modelo ruta.joblib
    python prediccion_raciones.py predecir --nivel inicial --datos ruta.json --modelo ruta.joblib --dias 5
"""

import argparse
import json
import math
import sys
import time
from datetime import timedelta

import joblib
import numpy as np
import pandas as pd
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import mean_absolute_error, mean_squared_error, r2_score
from sklearn.model_selection import TimeSeriesSplit

SEED = 42
# Hiperparametros elegidos por la busqueda en rejilla de la tesis (capitulo III).
RF_PARAMS = {"n_estimators": 300, "max_depth": None, "min_samples_leaf": 10}
NIVEL_SERVICIO = 0.95
# Pausa = intervalo de mas de tres dias sin clases.
DIAS_PAUSA = 3
# Proporcion de dias lectivos para el entrenamiento inicial (en la tesis,
# marzo a junio: 68 de 173 dias, alrededor del 40 %).
PROPORCION_ENTRENO_INICIAL = 0.4
PARTICIONES_OOF = 4
MIN_DIAS = 10

NUM = [
    "matriculados", "asist_lag1", "tasa_lag1", "ma5", "seas",
    "dias_hasta_pausa", "dias_desde_pausa", "post_vacaciones",
    "semana_escolar", "es_inicial", "grado_num",
]
XT = NUM + ["dia_sem", "seccion_cod"]
Y = "asistentes"


# ── Carga y variables ───────────────────────────────────────────────────

def cargar_registros(ruta_datos: str) -> pd.DataFrame:
    """Carga los registros por seccion y dia exportados por Laravel."""
    with open(ruta_datos, "r", encoding="utf-8") as f:
        registros = json.load(f)

    df = pd.DataFrame(registros)
    if df.empty:
        return df

    df["fecha"] = pd.to_datetime(df["fecha"])
    df["nivel"] = df["nivel"].str.lower()
    df["key"] = df["nivel"] + "|" + df["grado"] + "|" + df["seccion"]
    return df.sort_values(["key", "fecha"]).reset_index(drop=True)


def calendario(dias: list) -> pd.DataFrame:
    """Variables de calendario de la tesis para una lista ordenada de dias lectivos."""
    didx = {d: i for i, d in enumerate(dias)}
    gaps = [(dias[i - 1], dias[i]) for i in range(1, len(dias)) if (dias[i] - dias[i - 1]).days > DIAS_PAUSA]
    bs = [g[0] for g in gaps]
    be = [g[1] for g in gaps]
    primer_dia_anio = {}
    for d in dias:
        primer_dia_anio.setdefault(d.year, d)

    cal = pd.DataFrame({"fecha": dias})
    cal["dias_hasta_pausa"] = [min([didx[b] - didx[d] for b in bs if b >= d], default=len(dias) - 1 - didx[d]) for d in dias]
    cal["dias_desde_pausa"] = [min([didx[d] - didx[b] for b in be if b <= d], default=didx[d]) for d in dias]
    cal["post_vacaciones"] = (cal.dias_desde_pausa < 5).astype(int)
    cal["semana_escolar"] = [(d - primer_dia_anio[d.year]).days // 7 + 1 for d in dias]
    cal["dia_sem"] = cal.fecha.dt.dayofweek
    return cal


def codigos_seccion(df: pd.DataFrame) -> dict:
    """Codigo numerico de cada seccion (equivale a cat.codes sobre las claves ordenadas)."""
    return {k: i for i, k in enumerate(sorted(df["key"].unique()))}


def construir_features(df: pd.DataFrame, codigos: dict) -> pd.DataFrame:
    """
    Calcula los 13 predictores de la tesis. No descarta filas: las que no
    tienen historia suficiente quedan con asist_lag1 o ma5 nulos.
    """
    df = df.drop(columns=[c for c in ["dias_hasta_pausa", "dias_desde_pausa", "post_vacaciones", "semana_escolar", "dia_sem"] if c in df.columns])
    dias = list(pd.to_datetime(sorted(df.fecha.unique())))
    df = df.merge(calendario(dias), on="fecha").sort_values(["key", "fecha"]).reset_index(drop=True)

    g = df.groupby("key")
    df["asist_lag1"] = g.asistentes.shift(1)
    df["tasa_lag1"] = df.asist_lag1 / g.matriculados.shift(1)
    df["ma5"] = g.asistentes.transform(lambda s: s.shift(1).rolling(5, min_periods=3).mean())

    # Ingenuo estacional: ultimo dia previo con el mismo dia de la semana.
    seas = pd.Series(np.nan, index=df.index)
    for _, s in g:
        last = {}
        for i, v, w in zip(s.index, s.asistentes, s.dia_sem):
            seas[i] = last.get(w, np.nan)
            last[w] = v
    df["seas"] = seas
    df["es_inicial"] = (df.nivel == "inicial").astype(int)
    gr = df.grado.str.extract(r"(\d)")[0].astype(float).fillna(0).astype(int)
    df["grado_num"] = np.where(df.es_inicial == 1, gr - 6, gr)
    siguiente = max(codigos.values(), default=-1) + 1
    df["seccion_cod"] = df.key.map(codigos).fillna(siguiente).astype(int)
    df["seas"] = df.seas.fillna(df.asist_lag1)
    return df


def depurar(df: pd.DataFrame) -> pd.DataFrame:
    return df.dropna(subset=["asist_lag1", "ma5"]).reset_index(drop=True)


def nuevo_modelo() -> RandomForestRegressor:
    return RandomForestRegressor(random_state=SEED, n_jobs=-1, **RF_PARAMS)


def agregar_por_nivel(t: pd.DataFrame) -> pd.DataFrame:
    return t.groupby(["fecha", "nivel"]).agg(
        A=("asistentes", "sum"), padron=("matriculados", "sum"), p=("p", "sum")
    ).reset_index()


# ── Metricas ────────────────────────────────────────────────────────────

def calcular_metricas(y_true, y_pred) -> dict:
    """Metricas del pronostico de la asistencia del nivel (MAE, RMSE, MAPE, R^2)."""
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


def metricas_decision(A, R, tau: float = NIVEL_SERVICIO) -> dict:
    """Exceso, faltante, perdida pinball y niveles de servicio (Ecuaciones 5 a 9)."""
    A = np.array(A, dtype=float)
    R = np.array(R, dtype=float)
    exceso = np.maximum(R - A, 0)
    faltante = np.maximum(A - R, 0)
    n = len(A)
    return {
        "exceso_medio": round(float(exceso.mean()), 2) if n else None,
        "dias_con_faltante": int((faltante > 0).sum()),
        "cobertura_ciclo": round(float(100 * (R >= A).mean()), 2) if n else None,
        "cobertura_demanda": round(float(100 * (1 - faltante.sum() / A.sum())), 2) if n and A.sum() else None,
        "pinball": round(float(np.mean(tau * faltante + (1 - tau) * exceso)), 3) if n else None,
    }


# ── Evaluacion con origen movil ─────────────────────────────────────────

def evaluar_origen_movil(df: pd.DataFrame, inicio_evaluacion=None) -> tuple[pd.DataFrame, dict]:
    """
    Reproduce la evaluacion de la tesis: errores iniciales fuera de muestra con
    TimeSeriesSplit(4) sobre el tramo inicial y, desde inicio_evaluacion,
    reentrenamiento semanal con ventana expansiva. Devuelve los resultados
    diarios por nivel y los errores acumulados (A - y_hat) de cada nivel.
    """
    dias = list(pd.to_datetime(sorted(df.fecha.unique())))
    if inicio_evaluacion is None:
        inicio_evaluacion = dias[min(len(dias) - 1, max(1, int(len(dias) * PROPORCION_ENTRENO_INICIAL)))]
    inicio_evaluacion = pd.Timestamp(inicio_evaluacion)

    errores = {n: [] for n in df.nivel.unique()}
    tr0 = df[df.fecha < inicio_evaluacion].sort_values("fecha")

    if len(tr0) > PARTICIONES_OOF:
        oof = []
        for a, b in TimeSeriesSplit(n_splits=PARTICIONES_OOF).split(tr0):
            t = tr0.iloc[b].copy()
            t["p"] = nuevo_modelo().fit(tr0.iloc[a][XT], tr0.iloc[a][Y]).predict(t[XT])
            oof.append(t)
        for n, d in agregar_por_nivel(pd.concat(oof)).groupby("nivel"):
            errores[n] += list((d.A - d.p).values)

    dias_eval = [d for d in dias if d >= inicio_evaluacion]
    semanas = pd.Series(dias_eval, dtype="datetime64[ns]").dt.to_period("W").values
    filas = []
    for semana in pd.unique(semanas):
        wd = [d for d, w in zip(dias_eval, semanas) if w == semana]
        tr = df[df.fecha < wd[0]]
        te = df[df.fecha.isin(wd)].copy()
        if tr.empty or te.empty:
            continue
        te["p"] = nuevo_modelo().fit(tr[XT], tr[Y]).predict(te[XT])
        A = agregar_por_nivel(te)
        A["q"] = A.nivel.map(lambda n: float(np.quantile(errores[n], NIVEL_SERVICIO)) if errores[n] else 0.0)
        A["R"] = np.ceil(A.p + A.q)
        A["semana"] = str(semana)
        filas.append(A)
        for n, d in A.groupby("nivel"):
            errores[n] += list((d.A - d.p).values)

    resultados = pd.concat(filas) if filas else pd.DataFrame(columns=["fecha", "nivel", "A", "padron", "p", "q", "R", "semana"])
    return resultados, errores


# ── Entrenar y predecir ─────────────────────────────────────────────────

def entrenar(nivel_etiqueta: str, ruta_datos: str, ruta_modelo: str, inicio_evaluacion=None) -> dict:
    nivel = nivel_etiqueta.split(":")[0]
    df = cargar_registros(ruta_datos)
    if df.empty:
        return {"ok": False, "error": "sin_datos"}

    dias_nivel = df[df.nivel == nivel].fecha.nunique()
    if dias_nivel < MIN_DIAS:
        return {"ok": False, "error": "muestras_insuficientes", "muestras": int(dias_nivel)}

    registros_totales = len(df)
    pct_completos = round(100 * len(df[["matriculados", "asistentes"]].dropna()) / registros_totales, 2)
    codigos = codigos_seccion(df)
    df = depurar(construir_features(df, codigos))
    preprocesamiento = {
        "registros_totales": registros_totales,
        "registros_depurados": len(df),
        "pct_depurados": round(100 * len(df) / registros_totales, 2),
        "pct_completos": pct_completos,
    }

    inicio = time.perf_counter()
    resultados, errores = evaluar_origen_movil(df, inicio_evaluacion)
    r = resultados[resultados.nivel == nivel].sort_values("fecha")

    semanas_detalle = []
    for i, (semana, d) in enumerate(r.groupby("semana", sort=False), start=1):
        semanas_detalle.append({
            "semana": i,
            "desde": d.fecha.min().strftime("%Y-%m-%d"),
            "hasta": d.fecha.max().strftime("%Y-%m-%d"),
            "muestras_entreno": int((df.fecha < d.fecha.min()).sum()),
            **calcular_metricas(d.A, d.p),
        })

    if len(r):
        metricas = calcular_metricas(r.A, r.p)
        decision = metricas_decision(r.A, r.R)
    else:
        metricas = {"mae": None, "rmse": None, "mape": None, "r2": None}
        decision = metricas_decision([], [])

    # Modelo final con todos los datos, para las predicciones reales.
    modelo = nuevo_modelo().fit(df[XT], df[Y])
    margen = float(np.quantile(errores[nivel], NIVEL_SERVICIO)) if errores.get(nivel) else 0.0
    tiempo_entrenamiento = round(time.perf_counter() - inicio, 2)

    joblib.dump({
        "modelo": modelo,
        "nivel": nivel,
        "codigos_seccion": codigos,
        "margen_seguridad_p95": margen,
        "errores": errores.get(nivel, []),
    }, ruta_modelo)

    return {
        "ok": True,
        "nivel": nivel,
        "muestras": int(dias_nivel),
        "n_estimators": RF_PARAMS["n_estimators"],
        "max_depth": RF_PARAMS["max_depth"],
        "min_samples_leaf": RF_PARAMS["min_samples_leaf"],
        "predictores": XT,
        "validacion": "temporal_origen_movil",
        "semanas_evaluadas": len(semanas_detalle),
        "muestras_entreno_inicial": int((df.fecha < r.fecha.min()).sum()) if len(r) else int(len(df)),
        "margen_seguridad_p95": round(margen, 2),
        "tiempo_entrenamiento_seg": tiempo_entrenamiento,
        "preprocesamiento": preprocesamiento,
        "semanas_detalle": semanas_detalle,
        "metricas": metricas,
        "decision": decision,
    }


def dias_habiles_siguientes(ultima_fecha: pd.Timestamp, dias: int) -> list:
    fechas = []
    fecha = ultima_fecha
    while len(fechas) < dias:
        fecha = fecha + timedelta(days=1)
        if fecha.weekday() < 5:  # sabado=5, domingo=6: dias no lectivos
            fechas.append(fecha)
    return fechas


def predecir(nivel_etiqueta: str, ruta_datos: str, ruta_modelo: str, dias: int = 5) -> dict:
    nivel = nivel_etiqueta.split(":")[0]
    try:
        paquete = joblib.load(ruta_modelo)
    except FileNotFoundError:
        return {"ok": False, "error": "modelo_no_entrenado"}

    # Un modelo guardado con la version anterior (historico diario agregado)
    # no tiene codigos de seccion: hay que reentrenarlo.
    if "codigos_seccion" not in paquete:
        return {"ok": False, "error": "modelo_desactualizado"}

    modelo = paquete["modelo"]
    codigos = paquete["codigos_seccion"]
    margen = paquete.get("margen_seguridad_p95") or 0.0

    df = cargar_registros(ruta_datos)
    df = df[df.nivel == nivel]
    if df.empty:
        return {"ok": False, "error": "sin_datos"}

    # Secciones activas: las que tienen registro el ultimo dia, con su padron vigente.
    ultima_fecha = df.fecha.max()
    activas = df[df.fecha == ultima_fecha][["nivel", "grado", "seccion", "key", "matriculados"]]
    dias_es = ["lunes", "martes", "miercoles", "jueves", "viernes", "sabado", "domingo"]

    predicciones = []
    for fecha in dias_habiles_siguientes(ultima_fecha, dias):
        futuro = activas.assign(fecha=fecha, asistentes=np.nan)
        df = pd.concat([df, futuro], ignore_index=True)
        X = construir_features(df, codigos)
        filas = X[X.fecha == fecha]
        pred = modelo.predict(filas[XT])
        # Los dias siguientes usan la asistencia predicha como rezago.
        df.loc[df.fecha == fecha, "asistentes"] = df.loc[df.fecha == fecha, "key"].map(dict(zip(filas.key, pred))).values

        asistencia = float(pred.sum())
        predicciones.append({
            "fecha": fecha.strftime("%Y-%m-%d"),
            "fecha_legible": f"{dias_es[fecha.weekday()].capitalize()} {fecha.day}/{fecha.month:02d}",
            "asistencia_estimada": int(round(asistencia)),
            "margen_seguridad": int(round(margen)),
            # Ecuacion 4 de la tesis: R = ceil(y_hat + q_0,95(e)).
            "raciones_predichas": int(math.ceil(asistencia + margen)),
        })

    return {"ok": True, "nivel": nivel, "predicciones": predicciones}


def main():
    parser = argparse.ArgumentParser(description="Modelo Random Forest de prediccion de raciones")
    parser.add_argument("accion", choices=["entrenar", "predecir"])
    parser.add_argument("--nivel", required=True)
    parser.add_argument("--datos", required=True, help="Ruta al JSON con los registros por seccion y dia")
    parser.add_argument("--modelo", required=True, help="Ruta del archivo .joblib del modelo")
    parser.add_argument("--dias", type=int, default=5)
    parser.add_argument("--inicio-evaluacion", default=None, help="Primer dia del periodo de evaluacion (AAAA-MM-DD)")
    args = parser.parse_args()

    if args.accion == "entrenar":
        resultado = entrenar(args.nivel, args.datos, args.modelo, args.inicio_evaluacion)
    else:
        resultado = predecir(args.nivel, args.datos, args.modelo, args.dias)

    print(json.dumps(resultado, ensure_ascii=False))
    sys.exit(0 if resultado.get("ok") else 1)


if __name__ == "__main__":
    main()
