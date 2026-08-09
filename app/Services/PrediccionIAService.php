<?php

namespace App\Services;

use App\Models\IaEntrenamiento;
use App\Models\RegistroAsistencia;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Modelo de aprendizaje automático (Random Forest) para predecir la demanda
 * diaria de raciones, tal como se sustenta en la tesis "Modelo de aprendizaje
 * automático para optimizar la gestión del servicio alimentario escolar en
 * una institución educativa de Piura, 2026".
 *
 * El entrenamiento y la predicción se delegan a un script Python
 * (scikit-learn RandomForestRegressor, validación cruzada k-fold, métricas
 * MAE/RMSE/MAPE/R²) invocado como proceso externo. Este servicio solo se
 * encarga de exportar el histórico, invocar el script y decodificar su
 * salida JSON.
 */
class PrediccionIAService
{
    private const MIN_MUESTRAS = 10;

    public static function rutaModelo(string $nivel): string
    {
        return storage_path("app/ia_modelos/modelo_{$nivel}.joblib");
    }

    private static function rutaDatos(string $nivel): string
    {
        return storage_path("app/ia_modelos/datos_{$nivel}.json");
    }

    private static function rutaScript(): string
    {
        return base_path('python/prediccion_raciones.py');
    }

    private static function python(): string
    {
        return env('PYTHON_BIN', 'python');
    }

    /**
     * Construye el histórico diario (fecha + raciones) agrupado por día,
     * incluyendo las variables de contexto del marco teórico (clima, eventos
     * especiales del calendario) cuando están disponibles.
     */
    private function historicoDiario(string $nivel): array
    {
        return RegistroAsistencia::where('nivel', $nivel)
            ->orderBy('fecha')
            ->get()
            ->groupBy(fn ($r) => $r->fecha->toDateString())
            ->map(fn ($grupo, $fecha) => [
                'fecha'    => $fecha,
                'raciones' => (float) $grupo->sum('raciones'),
                // Variable de contexto: lluvia registrada en alguna sección ese día
                'clima_lluvioso' => $grupo->contains(fn ($r) => $r->condicion_climatica === 'lluvioso') ? 1 : 0,
                // Variable de contexto: feriado próximo/actividad especial reportada ese día
                'evento_especial' => $grupo->contains(fn ($r) => (bool) $r->evento_especial) ? 1 : 0,
            ])
            ->values()
            ->sortBy('fecha')
            ->values()
            ->toArray();
    }

    private function exportarDatos(string $nivel): string
    {
        $historico = $this->historicoDiario($nivel);
        $ruta = self::rutaDatos($nivel);

        if (!is_dir(dirname($ruta))) {
            mkdir(dirname($ruta), 0775, true);
        }

        file_put_contents($ruta, json_encode($historico));

        return $ruta;
    }

    /**
     * Ejecuta el script Python y decodifica su salida JSON.
     */
    private function ejecutar(array $argumentos): ?array
    {
        $process = new Process(array_merge([self::python(), self::rutaScript()], $argumentos));
        $process->setTimeout(120);
        $process->run();

        $salida = trim($process->getOutput());

        if ($salida === '') {
            Log::error('PrediccionIAService: el script Python no devolvió salida. ' . $process->getErrorOutput());
            return null;
        }

        $resultado = json_decode($salida, true);

        if (!is_array($resultado)) {
            Log::error('PrediccionIAService: salida no interpretable como JSON: ' . $salida);
            return null;
        }

        if (!$process->isSuccessful() && !($resultado['ok'] ?? false)) {
            Log::warning('PrediccionIAService: proceso Python finalizó con error: ' . ($resultado['error'] ?? 'desconocido'));
        }

        return $resultado;
    }

    /**
     * Entrena el modelo Random Forest para un nivel. Devuelve estadísticas
     * (muestras, hiperparámetros y métricas MAE/RMSE/MAPE/R²) o null si
     * faltan datos suficientes.
     */
    public function entrenar(string $nivel): ?array
    {
        $historico = $this->historicoDiario($nivel);

        if (count($historico) < self::MIN_MUESTRAS) {
            return null;
        }

        $rutaDatos = $this->exportarDatos($nivel);
        $rutaModelo = self::rutaModelo($nivel);

        if (!is_dir(dirname($rutaModelo))) {
            mkdir(dirname($rutaModelo), 0775, true);
        }

        $resultado = $this->ejecutar([
            'entrenar',
            '--nivel', $nivel,
            '--datos', $rutaDatos,
            '--modelo', $rutaModelo,
        ]);

        if (!$resultado || !($resultado['ok'] ?? false)) {
            return null;
        }

        $preprocesamiento = $resultado['preprocesamiento'] ?? [];

        // Persiste las fichas 1, 2 y 3 (VI) en un único registro histórico.
        IaEntrenamiento::create([
            'nivel'                    => $nivel,
            'registros_totales'        => $preprocesamiento['registros_totales'] ?? 0,
            'registros_depurados'      => $preprocesamiento['registros_depurados'] ?? 0,
            'pct_depurados'            => $preprocesamiento['pct_depurados'] ?? 0,
            'pct_completos'            => $preprocesamiento['pct_completos'] ?? 0,
            'k_folds'                  => $resultado['k_folds'] ?? 0,
            'mae'                      => $resultado['metricas']['mae'] ?? null,
            'rmse'                     => $resultado['metricas']['rmse'] ?? null,
            'mape'                     => $resultado['metricas']['mape'] ?? null,
            'r2'                       => $resultado['metricas']['r2'] ?? null,
            'folds_detalle'            => $resultado['folds_detalle'] ?? [],
            'n_estimators'             => $resultado['n_estimators'] ?? null,
            'max_depth'                => $resultado['max_depth'] ?? null,
            'tiempo_entrenamiento_seg' => $resultado['tiempo_entrenamiento_seg'] ?? null,
        ]);

        return [
            'muestras'    => $resultado['muestras'] ?? null,
            'n_arboles'   => $resultado['n_estimators'] ?? null,
            'profundidad' => $resultado['max_depth'] ?? null,
            'k_folds'     => $resultado['k_folds'] ?? null,
            'mae'         => $resultado['metricas']['mae'] ?? null,
            'rmse'        => $resultado['metricas']['rmse'] ?? null,
            'mape'        => $resultado['metricas']['mape'] ?? null,
            'r2'          => $resultado['metricas']['r2'] ?? null,
        ];
    }

    public function modeloExiste(string $nivel): bool
    {
        return file_exists(self::rutaModelo($nivel));
    }

    /**
     * Predice 'cantidad' días hábiles futuros usando el modelo entrenado.
     * Devuelve null si no hay modelo guardado o falla la predicción.
     */
    public function predecir(string $nivel, int $cantidad = 5): ?array
    {
        if (!$this->modeloExiste($nivel)) {
            return null;
        }

        $rutaDatos = $this->exportarDatos($nivel);

        $resultado = $this->ejecutar([
            'predecir',
            '--nivel', $nivel,
            '--datos', $rutaDatos,
            '--modelo', self::rutaModelo($nivel),
            '--dias', (string) $cantidad,
        ]);

        if (!$resultado || !($resultado['ok'] ?? false)) {
            return null;
        }

        return $resultado['predicciones'] ?? [];
    }
}
