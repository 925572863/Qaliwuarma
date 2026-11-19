"""
Tests unitarios para el modelo de la tesis (Random Forest agrupado por seccion,
13 predictores, origen movil y margen del cuantil 95).

Ejecutar con:
    python -m pytest python/tests -v
"""

import json
import math
import sys
from datetime import date, timedelta
from pathlib import Path

import joblib
import numpy as np
import pandas as pd
import pytest

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

import prediccion_raciones as pr


# ── Fixtures ──────────────────────────────────────────────────────────────

SECCIONES = [
    ("inicial", "3 Años", "A", 20),
    ("inicial", "4 Años", "A", 22),
    ("primaria", "1.º grado", "A", 30),
    ("primaria", "2.º grado", "B", 28),
]


def generar_registros(dias: int, inicio: date = date(2026, 3, 2), pausa_tras: int | None = None):
    """
    Registros por seccion y dia habil. Si pausa_tras se indica, deja una
    semana sin clases despues de ese numero de dias lectivos.
    """
    registros = []
    fecha = inicio
    agregados = 0
    while agregados < dias:
        if pausa_tras is not None and agregados == pausa_tras:
            fecha += timedelta(days=7)
            pausa_tras = None
        if fecha.weekday() < 5:
            for i, (nivel, grado, seccion, matriculados) in enumerate(SECCIONES):
                registros.append({
                    "fecha": fecha.isoformat(),
                    "nivel": nivel,
                    "grado": grado,
                    "seccion": seccion,
                    "matriculados": matriculados,
                    "asistentes": matriculados - 2 - ((agregados + i) % 4),
                })
            agregados += 1
        fecha += timedelta(days=1)
    return registros


def escribir(tmp_path, registros, nombre="datos.json"):
    ruta = tmp_path / nombre
    ruta.write_text(json.dumps(registros, ensure_ascii=False), encoding="utf-8")
    return str(ruta)


@pytest.fixture
def ruta_datos_valida(tmp_path):
    return escribir(tmp_path, generar_registros(40, pausa_tras=20))


@pytest.fixture
def ruta_modelo(tmp_path):
    return str(tmp_path / "modelo.joblib")


def features(registros):
    df = pd.DataFrame(registros)
    df["fecha"] = pd.to_datetime(df["fecha"])
    df["key"] = df["nivel"] + "|" + df["grado"] + "|" + df["seccion"]
    df = df.sort_values(["key", "fecha"]).reset_index(drop=True)
    return pr.construir_features(df, pr.codigos_seccion(df))


# ── cargar_registros ──────────────────────────────────────────────────────

class TestCargarRegistros:
    def test_ordena_por_seccion_y_fecha(self, tmp_path):
        registros = generar_registros(3)
        ruta = escribir(tmp_path, list(reversed(registros)))

        df = pr.cargar_registros(ruta)

        for _, d in df.groupby("key"):
            assert d.fecha.is_monotonic_increasing

    def test_normaliza_el_nivel_a_minusculas(self, tmp_path):
        registros = generar_registros(2)
        for r in registros:
            r["nivel"] = r["nivel"].capitalize()

        df = pr.cargar_registros(escribir(tmp_path, registros))

        assert set(df.nivel) == {"inicial", "primaria"}

    def test_datos_vacios_devuelve_dataframe_vacio(self, tmp_path):
        assert pr.cargar_registros(escribir(tmp_path, [])).empty


# ── calendario ────────────────────────────────────────────────────────────

class TestCalendario:
    def test_detecta_la_pausa_y_marca_los_cinco_dias_posteriores(self):
        dias = list(pd.to_datetime(sorted({r["fecha"] for r in generar_registros(30, pausa_tras=15)})))

        cal = pr.calendario(dias)

        assert cal.post_vacaciones.iloc[15:20].tolist() == [1] * 5
        assert cal.post_vacaciones.iloc[20] == 0
        assert cal.dias_desde_pausa.iloc[15] == 0
        assert cal.dias_hasta_pausa.iloc[14] == 0

    def test_semana_escolar_se_reinicia_cada_anio(self):
        dias = list(pd.to_datetime(["2025-12-15", "2025-12-22", "2026-03-02", "2026-03-09"]))

        cal = pr.calendario(dias)

        assert cal.semana_escolar.tolist() == [1, 2, 1, 2]


# ── construir_features ────────────────────────────────────────────────────

class TestConstruirFeatures:
    def test_genera_los_trece_predictores_de_la_tesis(self):
        X = features(generar_registros(10))

        assert len(pr.XT) == 13
        for col in pr.XT:
            assert col in X.columns
        assert "mes" not in pr.XT

    def test_los_rezagos_solo_usan_dias_anteriores(self):
        X = features(generar_registros(10))
        s = X[X.key == "primaria|1.º grado|A"].reset_index(drop=True)

        assert s.asist_lag1.iloc[1] == s.asistentes.iloc[0]
        assert s.ma5.iloc[5] == pytest.approx(s.asistentes.iloc[0:5].mean())
        assert s.seas.iloc[5] == s.asistentes.iloc[0]

    def test_grado_num_es_negativo_en_inicial(self):
        X = features(generar_registros(2))

        assert set(X[X.nivel == "inicial"].grado_num) == {-3, -2}
        assert set(X[X.nivel == "primaria"].grado_num) == {1, 2}

    def test_depurar_descarta_las_filas_sin_historia_suficiente(self):
        X = pr.depurar(features(generar_registros(10)))

        # ma5 necesita al menos tres dias previos.
        assert X.groupby("key").size().tolist() == [7] * len(SECCIONES)
        assert X[pr.XT].notna().all().all()


# ── metricas ──────────────────────────────────────────────────────────────

class TestCalcularMetricas:
    def test_prediccion_perfecta_da_error_cero_y_r2_uno(self):
        m = pr.calcular_metricas([10, 20, 30, 40], [10, 20, 30, 40])

        assert m["mae"] == 0.0
        assert m["rmse"] == 0.0
        assert m["mape"] == 0.0
        assert m["r2"] == 1.0

    def test_mae_se_calcula_correctamente(self):
        m = pr.calcular_metricas([10, 20, 30], [12, 18, 33])

        assert m["mae"] == pytest.approx(7 / 3, abs=0.001)

    def test_r2_es_none_con_una_sola_muestra(self):
        assert pr.calcular_metricas([10], [12])["r2"] is None


class TestMetricasDecision:
    def test_exceso_faltante_y_pinball(self):
        # Dia 1: exceso 2. Dia 2: faltante 1.
        m = pr.metricas_decision(A=[10, 10], R=[12, 9])

        assert m["exceso_medio"] == 1.0
        assert m["dias_con_faltante"] == 1
        assert m["cobertura_ciclo"] == 50.0
        assert m["cobertura_demanda"] == 95.0
        assert m["pinball"] == pytest.approx((0.05 * 2 + 0.95 * 1) / 2, abs=0.001)


# ── entrenar ──────────────────────────────────────────────────────────────

class TestEntrenar:
    def test_dias_insuficientes_devuelve_error_sin_generar_modelo(self, tmp_path, ruta_modelo):
        ruta = escribir(tmp_path, generar_registros(5))

        resultado = pr.entrenar("inicial", ruta, ruta_modelo)

        assert resultado["ok"] is False
        assert resultado["error"] == "muestras_insuficientes"
        assert not Path(ruta_modelo).exists()

    def test_sin_datos_devuelve_error(self, tmp_path, ruta_modelo):
        resultado = pr.entrenar("inicial", escribir(tmp_path, []), ruta_modelo)

        assert resultado == {"ok": False, "error": "sin_datos"}

    def test_usa_los_hiperparametros_de_la_tesis(self, ruta_datos_valida, ruta_modelo):
        resultado = pr.entrenar("primaria", ruta_datos_valida, ruta_modelo)

        assert resultado["ok"] is True
        assert resultado["n_estimators"] == 300
        assert resultado["max_depth"] is None
        assert resultado["min_samples_leaf"] == 10
        assert resultado["predictores"] == pr.XT

    def test_evalua_con_origen_movil_y_reporta_metricas(self, ruta_datos_valida, ruta_modelo):
        resultado = pr.entrenar("primaria", ruta_datos_valida, ruta_modelo)

        assert resultado["muestras"] == 40
        assert resultado["semanas_evaluadas"] > 0
        for semana in resultado["semanas_detalle"]:
            assert semana["desde"] <= semana["hasta"]
        for clave in ("mae", "rmse", "mape", "r2"):
            assert clave in resultado["metricas"]
        assert "cobertura_ciclo" in resultado["decision"]

    def test_persiste_el_modelo_con_los_codigos_de_seccion(self, ruta_datos_valida, ruta_modelo):
        pr.entrenar("inicial", ruta_datos_valida, ruta_modelo)

        paquete = joblib.load(ruta_modelo)
        assert paquete["nivel"] == "inicial"
        assert len(paquete["codigos_seccion"]) == len(SECCIONES)
        assert paquete["margen_seguridad_p95"] == pytest.approx(np.quantile(paquete["errores"], 0.95))

    def test_funciona_con_una_sola_seccion(self, tmp_path, ruta_modelo):
        registros = [r for r in generar_registros(15) if r["grado"] == "1.º grado"]

        resultado = pr.entrenar("primaria", escribir(tmp_path, registros), ruta_modelo)

        assert resultado["ok"] is True

    def test_acepta_la_etiqueta_de_grado(self, ruta_datos_valida, ruta_modelo):
        assert pr.entrenar("inicial:3-anos", ruta_datos_valida, ruta_modelo)["nivel"] == "inicial"


# ── predecir ──────────────────────────────────────────────────────────────

class TestPredecir:
    def test_modelo_no_entrenado_devuelve_error(self, ruta_datos_valida, tmp_path):
        resultado = pr.predecir("inicial", ruta_datos_valida, str(tmp_path / "no_existe.joblib"))

        assert resultado == {"ok": False, "error": "modelo_no_entrenado"}

    def test_modelo_del_formato_anterior_pide_reentrenar(self, ruta_datos_valida, ruta_modelo):
        joblib.dump({"modelo": None, "nivel": "primaria", "margen_seguridad_p95": 5}, ruta_modelo)

        resultado = pr.predecir("primaria", ruta_datos_valida, ruta_modelo)

        assert resultado == {"ok": False, "error": "modelo_desactualizado"}

    def test_devuelve_solo_dias_habiles_posteriores_al_ultimo_registro(self, ruta_datos_valida, ruta_modelo):
        pr.entrenar("primaria", ruta_datos_valida, ruta_modelo)

        resultado = pr.predecir("primaria", ruta_datos_valida, ruta_modelo, dias=5)

        ultima = max(r["fecha"] for r in json.loads(Path(ruta_datos_valida).read_text(encoding="utf-8")))
        fechas = [p["fecha"] for p in resultado["predicciones"]]
        assert len(fechas) == 5
        assert all(f > ultima for f in fechas)
        assert all(date.fromisoformat(f).weekday() < 5 for f in fechas)

    def test_raciones_son_la_asistencia_mas_el_margen_redondeado_hacia_arriba(self, ruta_datos_valida, ruta_modelo):
        pr.entrenar("primaria", ruta_datos_valida, ruta_modelo)
        margen = joblib.load(ruta_modelo)["margen_seguridad_p95"]

        for p in pr.predecir("primaria", ruta_datos_valida, ruta_modelo)["predicciones"]:
            assert p["raciones_predichas"] >= p["asistencia_estimada"]
            assert p["raciones_predichas"] - math.ceil(p["asistencia_estimada"] + margen) in (-1, 0)

    def test_la_asistencia_estimada_es_la_suma_de_las_secciones_del_nivel(self, ruta_datos_valida, ruta_modelo):
        pr.entrenar("inicial", ruta_datos_valida, ruta_modelo)

        p = pr.predecir("inicial", ruta_datos_valida, ruta_modelo, dias=1)["predicciones"][0]

        # Dos secciones de inicial con 20 y 22 matriculados.
        assert 30 <= p["asistencia_estimada"] <= 42

    def test_es_determinista(self, ruta_datos_valida, ruta_modelo):
        pr.entrenar("primaria", ruta_datos_valida, ruta_modelo)

        a = pr.predecir("primaria", ruta_datos_valida, ruta_modelo)
        b = pr.predecir("primaria", ruta_datos_valida, ruta_modelo)

        assert a == b
