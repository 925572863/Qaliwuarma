"""
Tests unitarios para el modulo de prediccion (Random Forest, fichas 1-2-3 de la tesis).

Ejecutar con:
    python -m pytest python/tests -v
"""

import json
import sys
from datetime import date, timedelta
from pathlib import Path

import numpy as np
import pandas as pd
import pytest

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

import prediccion_raciones as pr


# ── Fixtures ──────────────────────────────────────────────────────────────

def generar_historico(dias: int, base: int = 100, variacion: int = 10, con_contexto: bool = False):
    """Genera una lista de registros diarios (solo dias habiles) para pruebas."""
    registros = []
    fecha = date(2026, 1, 5)  # lunes
    agregados = 0
    while agregados < dias:
        if fecha.weekday() < 5:
            registro = {
                "fecha": fecha.isoformat(),
                "raciones": base + (agregados % variacion),
            }
            if con_contexto:
                registro["clima_lluvioso"] = 1 if agregados % 4 == 0 else 0
                registro["evento_especial"] = 1 if agregados % 7 == 0 else 0
            registros.append(registro)
            agregados += 1
        fecha += timedelta(days=1)
    return registros


@pytest.fixture
def ruta_datos_valida(tmp_path):
    registros = generar_historico(20)
    ruta = tmp_path / "datos.json"
    ruta.write_text(json.dumps(registros), encoding="utf-8")
    return str(ruta)


@pytest.fixture
def ruta_datos_insuficientes(tmp_path):
    registros = generar_historico(5)
    ruta = tmp_path / "datos_pocos.json"
    ruta.write_text(json.dumps(registros), encoding="utf-8")
    return str(ruta)


@pytest.fixture
def ruta_modelo(tmp_path):
    return str(tmp_path / "modelo.joblib")


# ── cargar_historico ──────────────────────────────────────────────────────

class TestCargarHistorico:
    def test_carga_y_ordena_por_fecha(self, tmp_path):
        registros = [
            {"fecha": "2026-01-10", "raciones": 5},
            {"fecha": "2026-01-05", "raciones": 3},
        ]
        ruta = tmp_path / "datos.json"
        ruta.write_text(json.dumps(registros), encoding="utf-8")

        df = pr.cargar_historico(str(ruta))

        assert list(df["raciones"]) == [3, 5]
        assert pd.api.types.is_datetime64_any_dtype(df["fecha"])

    def test_datos_vacios_devuelve_dataframe_vacio(self, tmp_path):
        ruta = tmp_path / "vacio.json"
        ruta.write_text("[]", encoding="utf-8")

        df = pr.cargar_historico(str(ruta))

        assert df.empty


# ── construir_features (Ficha 1 - Preprocesamiento) ──────────────────────

class TestConstruirFeatures:
    def test_depura_los_primeros_registros_sin_historico_suficiente(self):
        df = pd.DataFrame(generar_historico(10))
        df["fecha"] = pd.to_datetime(df["fecha"])

        df_depurado, metricas = pr.construir_features(df)

        # ma3/ma7 requieren shift(1): la primera fila siempre se depura
        assert len(df_depurado) < len(df)
        assert metricas["registros_totales"] == 10
        assert metricas["registros_depurados"] == len(df_depurado)

    def test_pct_depurados_y_completos_se_calculan_correctamente(self):
        df = pd.DataFrame(generar_historico(10))
        df["fecha"] = pd.to_datetime(df["fecha"])

        _, metricas = pr.construir_features(df)

        esperado = round((metricas["registros_depurados"] / 10) * 100, 2)
        assert metricas["pct_depurados"] == esperado
        assert metricas["pct_completos"] == 100.0  # sin nulos en los datos generados

    def test_variables_de_contexto_ausentes_se_asumen_neutras(self):
        """Compatibilidad con historico exportado antes de agregar clima/evento_especial."""
        df = pd.DataFrame(generar_historico(10, con_contexto=False))
        df["fecha"] = pd.to_datetime(df["fecha"])

        df_depurado, _ = pr.construir_features(df)

        assert (df_depurado["clima_lluvioso"] == 0).all()
        assert (df_depurado["evento_especial"] == 0).all()

    def test_variables_de_contexto_presentes_se_preservan(self):
        df = pd.DataFrame(generar_historico(10, con_contexto=True))
        df["fecha"] = pd.to_datetime(df["fecha"])

        df_depurado, _ = pr.construir_features(df)

        assert df_depurado["clima_lluvioso"].isin([0, 1]).all()
        assert df_depurado["evento_especial"].isin([0, 1]).all()

    def test_todas_las_feature_cols_estan_presentes(self):
        df = pd.DataFrame(generar_historico(10))
        df["fecha"] = pd.to_datetime(df["fecha"])

        df_depurado, _ = pr.construir_features(df)

        for col in pr.FEATURE_COLS:
            assert col in df_depurado.columns


# ── calcular_metricas (Ficha 2 - Entrenamiento y validacion) ─────────────

class TestCalcularMetricas:
    def test_prediccion_perfecta_da_error_cero_y_r2_uno(self):
        y_true = [10, 20, 30, 40]
        y_pred = [10, 20, 30, 40]

        m = pr.calcular_metricas(y_true, y_pred)

        assert m["mae"] == 0.0
        assert m["rmse"] == 0.0
        assert m["mape"] == 0.0
        assert m["r2"] == 1.0

    def test_mae_se_calcula_correctamente(self):
        y_true = [10, 20, 30]
        y_pred = [12, 18, 33]  # errores: 2, 2, 3 -> MAE = 7/3

        m = pr.calcular_metricas(y_true, y_pred)

        assert m["mae"] == pytest.approx(7 / 3, abs=0.001)

    def test_mape_ignora_valores_reales_iguales_a_cero(self):
        y_true = [0, 10, 20]
        y_pred = [5, 10, 22]

        m = pr.calcular_metricas(y_true, y_pred)

        # Solo se evaluan los indices donde y_true != 0
        esperado = np.mean([abs((10 - 10) / 10), abs((20 - 22) / 20)]) * 100
        assert m["mape"] == pytest.approx(round(esperado, 3), abs=0.01)

    def test_r2_es_none_con_una_sola_muestra(self):
        m = pr.calcular_metricas([10], [12])

        assert m["r2"] is None


# ── entrenar (Fichas 1, 2 y 3 integradas) ────────────────────────────────

class TestEntrenar:
    def test_muestras_insuficientes_devuelve_error_sin_generar_modelo(self, ruta_datos_insuficientes, ruta_modelo):
        resultado = pr.entrenar("primaria", ruta_datos_insuficientes, ruta_modelo)

        assert resultado["ok"] is False
        assert resultado["error"] == "muestras_insuficientes"
        assert not Path(ruta_modelo).exists()

    def test_sin_datos_devuelve_error(self, tmp_path, ruta_modelo):
        ruta_vacia = tmp_path / "vacio.json"
        ruta_vacia.write_text("[]", encoding="utf-8")

        resultado = pr.entrenar("primaria", str(ruta_vacia), ruta_modelo)

        assert resultado["ok"] is False
        assert resultado["error"] == "sin_datos"

    def test_entrenamiento_exitoso_devuelve_las_tres_fichas(self, ruta_datos_valida, ruta_modelo):
        resultado = pr.entrenar("primaria", ruta_datos_valida, ruta_modelo, k_folds=5)

        assert resultado["ok"] is True
        # Ficha 1
        assert "preprocesamiento" in resultado
        assert resultado["preprocesamiento"]["pct_depurados"] > 0
        # Ficha 2
        assert resultado["metricas"]["mae"] is not None
        assert len(resultado["folds_detalle"]) == resultado["k_folds"]
        for fold in resultado["folds_detalle"]:
            assert set(fold.keys()) >= {"fold", "mae", "rmse", "mape", "r2"}
        # Ficha 3
        assert resultado["n_estimators"] == pr.N_ARBOLES
        assert resultado["max_depth"] == pr.PROFUNDIDAD_MAX
        assert resultado["tiempo_entrenamiento_seg"] >= 0

    def test_entrenamiento_exitoso_persiste_el_modelo_en_disco(self, ruta_datos_valida, ruta_modelo):
        pr.entrenar("primaria", ruta_datos_valida, ruta_modelo)

        assert Path(ruta_modelo).exists()

    def test_k_folds_se_ajusta_si_hay_menos_muestras_que_folds(self, tmp_path, ruta_modelo):
        # 11 registros -> tras depuracion, menos que 5*2, pero >= MIN_MUESTRAS
        registros = generar_historico(11)
        ruta = tmp_path / "datos_pocos_folds.json"
        ruta.write_text(json.dumps(registros), encoding="utf-8")

        resultado = pr.entrenar("primaria", str(ruta), ruta_modelo, k_folds=5)

        assert resultado["ok"] is True
        assert resultado["k_folds"] <= 5


# ── predecir ──────────────────────────────────────────────────────────────

class TestPredecir:
    def test_modelo_no_entrenado_devuelve_error(self, ruta_datos_valida, tmp_path):
        ruta_inexistente = str(tmp_path / "no_existe.joblib")

        resultado = pr.predecir("primaria", ruta_datos_valida, ruta_inexistente)

        assert resultado["ok"] is False
        assert resultado["error"] == "modelo_no_entrenado"

    def test_prediccion_exitosa_devuelve_solo_dias_habiles(self, ruta_datos_valida, ruta_modelo):
        pr.entrenar("primaria", ruta_datos_valida, ruta_modelo)

        resultado = pr.predecir("primaria", ruta_datos_valida, ruta_modelo, dias=5)

        assert resultado["ok"] is True
        assert len(resultado["predicciones"]) == 5
        for p in resultado["predicciones"]:
            fecha = pd.Timestamp(p["fecha"])
            assert fecha.dayofweek < 5  # nunca sabado (5) ni domingo (6)
            assert p["raciones_predichas"] >= 0

    def test_predicciones_son_autoregresivas_y_deterministas(self, ruta_datos_valida, ruta_modelo):
        pr.entrenar("primaria", ruta_datos_valida, ruta_modelo)

        r1 = pr.predecir("primaria", ruta_datos_valida, ruta_modelo, dias=3)
        r2 = pr.predecir("primaria", ruta_datos_valida, ruta_modelo, dias=3)

        # Mismo modelo + mismos datos -> misma prediccion (RandomForest con random_state fijo)
        assert r1["predicciones"] == r2["predicciones"]
