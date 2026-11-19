<?php

namespace App\Services;

use App\Models\IaEntrenamiento;
use App\Models\IaModeloBinario;
use App\Models\RegistroAsistencia;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Modelo de aprendizaje automático (Random Forest) evaluado en la tesis
 * "Modelo de aprendizaje automático para optimizar la gestión del servicio
 * alimentario escolar en una institución educativa de Piura, 2026".
 *
 * El entrenamiento y la predicción se delegan a python/prediccion_raciones.py
 * (un único Random Forest agrupado que predice la asistencia de cada sección
 * con los 13 predictores de la tesis, evaluación con origen móvil y raciones
 * = predicción del nivel + cuantil 95 de sus propios errores). Este servicio
 * solo exporta los registros por sección y día, invoca el script y decodifica
 * su salida JSON.
 *
 * Con $grado = null el modelo se entrena con todas las secciones de ambos
 * niveles, como en la tesis, y se informa el nivel pedido. Con $grado se
 * entrena solo con las secciones de ese grado.
 */
class PrediccionIAService
{
    private const MIN_DIAS = 10;

    /**
     * Convierte un grado en un slug ASCII seguro, tanto para nombres de
     * archivo como para pasarlo como argumento de línea de comandos al
     * script Python: en Windows, el subproceso de Python puede devolver
     * caracteres acentuados (ej. "Años") en una codificación que no es
     * UTF-8 válido, lo que rompe json_decode() en PHP y hace que
     * entrenar()/predecir() fallen en silencio. Evitamos el problema de raíz
     * no enviándole nunca acentos/ñ al proceso hijo.
     */
    private static function slugGrado(?string $grado): string
    {
        if ($grado === null || $grado === '') {
            return '';
        }
        $slug = mb_strtolower(trim($grado));
        $slug = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }

    public static function rutaModelo(string $nivel, ?string $grado = null): string
    {
        $sufijo = self::slugGrado($grado);
        return storage_path("app/ia_modelos/modelo_{$nivel}" . ($sufijo ? "_{$sufijo}" : '') . ".joblib");
    }

    private static function rutaDatos(string $nivel, ?string $grado = null): string
    {
        $sufijo = self::slugGrado($grado);
        return storage_path("app/ia_modelos/datos_{$nivel}" . ($sufijo ? "_{$sufijo}" : '') . ".json");
    }

    /** Etiqueta ASCII segura para pasar como --nivel al script Python (ver slugGrado). */
    private static function etiquetaProceso(string $nivel, ?string $grado): string
    {
        $sufijo = self::slugGrado($grado);
        return $sufijo ? "{$nivel}:{$sufijo}" : $nivel;
    }

    private static function rutaScript(): string
    {
        return base_path('python/prediccion_raciones.py');
    }

    /** Clave única (nivel + slug del grado) usada para guardar/restaurar el modelo en BD. */
    private static function claveModelo(string $nivel, ?string $grado): string
    {
        $sufijo = self::slugGrado($grado);
        return $sufijo ? "{$nivel}:{$sufijo}" : $nivel;
    }

    /**
     * Guarda una copia del modelo recién entrenado en la base de datos.
     * Necesario porque el filesystem de hosts como Render no es persistente
     * entre deploys: la BD sí lo es, así que sirve de respaldo para poder
     * restaurar el .joblib a disco cuando el contenedor se reinicie.
     */
    private static function guardarModeloEnBD(string $nivel, ?string $grado): void
    {
        $ruta = self::rutaModelo($nivel, $grado);
        if (!file_exists($ruta)) {
            return;
        }

        IaModeloBinario::updateOrCreate(
            ['clave' => self::claveModelo($nivel, $grado)],
            [
                'nivel'     => $nivel,
                'grado'     => $grado,
                'contenido' => base64_encode(file_get_contents($ruta)),
            ]
        );
    }

    /**
     * Si el .joblib no está en disco (ej. tras un deploy nuevo sin
     * filesystem persistente) pero sí hay una copia en BD, la restaura a
     * disco. Devuelve true si el archivo existe en disco al terminar.
     */
    private static function restaurarModeloSiHaceFalta(string $nivel, ?string $grado): bool
    {
        $ruta = self::rutaModelo($nivel, $grado);
        if (file_exists($ruta)) {
            return true;
        }

        $registro = IaModeloBinario::where('clave', self::claveModelo($nivel, $grado))->first();
        if (!$registro) {
            return false;
        }

        if (!is_dir(dirname($ruta))) {
            mkdir(dirname($ruta), 0775, true);
        }
        file_put_contents($ruta, base64_decode($registro->contenido));

        return file_exists($ruta);
    }

    private static function python(): string
    {
        return env('PYTHON_BIN', 'python');
    }

    /**
     * Devuelve los grados distintos con historial de asistencia para un
     * nivel (ej. ["3 Años", "4 Años", "5 Años"] en inicial), para poder
     * entrenar un modelo independiente por cada uno.
     */
    public function gradosDisponibles(string $nivel): array
    {
        return RegistroAsistencia::where('nivel', $nivel)
            ->select('grado')
            ->distinct()
            ->orderBy('grado')
            ->pluck('grado')
            ->all();
    }

    /**
     * Registros de asistencia por sección y día (ficha de registro de la
     * tesis): matriculados y asistentes. Sin $grado incluye ambos niveles,
     * porque el modelo de la tesis es uno solo para todas las secciones.
     */
    private function registrosPorSeccion(string $nivel, ?string $grado = null): array
    {
        return RegistroAsistencia::query()
            ->when($grado !== null, fn ($q) => $q->where('nivel', $nivel)->where('grado', $grado))
            ->orderBy('fecha')
            ->get(['fecha', 'nivel', 'grado', 'seccion', 'total_alumnos', 'presentes'])
            ->map(fn ($r) => [
                'fecha'        => $r->fecha->toDateString(),
                'nivel'        => $r->nivel,
                'grado'        => $r->grado,
                'seccion'      => $r->seccion,
                'matriculados' => (int) $r->total_alumnos,
                'asistentes'   => (int) $r->presentes,
            ])
            ->all();
    }

    private function diasConRegistro(string $nivel, ?string $grado = null): int
    {
        return RegistroAsistencia::where('nivel', $nivel)
            ->when($grado !== null, fn ($q) => $q->where('grado', $grado))
            ->distinct()
            ->count('fecha');
    }

    private function exportarDatos(string $nivel, ?string $grado = null): string
    {
        $historico = $this->registrosPorSeccion($nivel, $grado);
        $ruta = self::rutaDatos($nivel, $grado);

        if (!is_dir(dirname($ruta))) {
            mkdir(dirname($ruta), 0775, true);
        }

        file_put_contents($ruta, json_encode($historico));

        return $ruta;
    }

    /**
     * Ejecuta el script Python y decodifica su salida JSON.
     */
    private function ejecutar(array $argumentos, int $timeout = 120): ?array
    {
        $process = new Process(array_merge([self::python(), self::rutaScript()], $argumentos));
        $process->setTimeout($timeout);
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
     * Entrena el modelo Random Forest y lo evalúa para un nivel (o un grado
     * específico dentro de ese nivel, si se indica $grado). Devuelve
     * estadísticas (días, hiperparámetros y métricas MAE/RMSE/MAPE/R² del
     * pronóstico del nivel) o null si faltan datos suficientes.
     */
    public function entrenar(string $nivel, ?string $grado = null): ?array
    {
        if ($this->diasConRegistro($nivel, $grado) < self::MIN_DIAS) {
            return null;
        }

        $rutaDatos = $this->exportarDatos($nivel, $grado);
        $rutaModelo = self::rutaModelo($nivel, $grado);

        if (!is_dir(dirname($rutaModelo))) {
            mkdir(dirname($rutaModelo), 0775, true);
        }

        $resultado = $this->ejecutar([
            'entrenar',
            '--nivel', self::etiquetaProceso($nivel, $grado),
            '--datos', $rutaDatos,
            '--modelo', $rutaModelo,
        ], 900);

        if (!$resultado || !($resultado['ok'] ?? false)) {
            return null;
        }

        self::guardarModeloEnBD($nivel, $grado);

        $preprocesamiento = $resultado['preprocesamiento'] ?? [];

        // Persiste las fichas 1, 2 y 3 (VI) en un único registro histórico.
        IaEntrenamiento::create([
            'nivel'                    => $nivel,
            'grado'                    => $grado,
            'registros_totales'        => $preprocesamiento['registros_totales'] ?? 0,
            'registros_depurados'      => $preprocesamiento['registros_depurados'] ?? 0,
            'pct_depurados'            => $preprocesamiento['pct_depurados'] ?? 0,
            'pct_completos'            => $preprocesamiento['pct_completos'] ?? 0,
            // 'k_folds' y 'folds_detalle' se reutilizan para la validacion temporal
            // con origen movil (cantidad de semanas evaluadas y su detalle por
            // semana), que reemplazo a la validacion cruzada k-fold original.
            'k_folds'                  => $resultado['semanas_evaluadas'] ?? 0,
            'mae'                      => $resultado['metricas']['mae'] ?? null,
            'rmse'                     => $resultado['metricas']['rmse'] ?? null,
            'mape'                     => $resultado['metricas']['mape'] ?? null,
            'r2'                       => $resultado['metricas']['r2'] ?? null,
            'folds_detalle'            => $resultado['semanas_detalle'] ?? [],
            'margen_seguridad_p95'     => $resultado['margen_seguridad_p95'] ?? null,
            'muestras_entreno_inicial' => $resultado['muestras_entreno_inicial'] ?? null,
            'n_estimators'             => $resultado['n_estimators'] ?? null,
            'max_depth'                => $resultado['max_depth'] ?? null,
            'tiempo_entrenamiento_seg' => $resultado['tiempo_entrenamiento_seg'] ?? null,
        ]);

        return [
            'muestras'             => $resultado['muestras'] ?? null,
            'n_arboles'            => $resultado['n_estimators'] ?? null,
            'profundidad'          => $resultado['max_depth'] ?? null,
            'semanas_evaluadas'    => $resultado['semanas_evaluadas'] ?? null,
            'margen_seguridad_p95' => $resultado['margen_seguridad_p95'] ?? null,
            'mae'                  => $resultado['metricas']['mae'] ?? null,
            'rmse'                 => $resultado['metricas']['rmse'] ?? null,
            'mape'                 => $resultado['metricas']['mape'] ?? null,
            'r2'                   => $resultado['metricas']['r2'] ?? null,
        ];
    }

    /**
     * Entrena un modelo independiente para cada grado con historial
     * suficiente dentro del nivel indicado. Devuelve un array
     * [grado => resultado|null] (null si a ese grado le faltan muestras).
     */
    public function entrenarPorGrado(string $nivel): array
    {
        $resultados = [];
        foreach ($this->gradosDisponibles($nivel) as $grado) {
            $resultados[$grado] = $this->entrenar($nivel, $grado);
        }
        return $resultados;
    }

    public function modeloExiste(string $nivel, ?string $grado = null): bool
    {
        return self::restaurarModeloSiHaceFalta($nivel, $grado);
    }

    /**
     * Predice 'cantidad' días hábiles futuros usando el modelo entrenado
     * (del nivel completo, o de un grado específico si se indica $grado).
     * Devuelve null si no hay modelo guardado o falla la predicción.
     */
    public function predecir(string $nivel, int $cantidad = 5, ?string $grado = null): ?array
    {
        if (!$this->modeloExiste($nivel, $grado)) {
            return null;
        }

        // Ejecutar el script Python (carga scikit-learn/pandas + el modelo)
        // es lento — varios segundos en el servidor gratuito. Como la
        // predicción solo cambia cuando hay un registro de asistencia nuevo
        // para este nivel/grado, se cachea en BD (persistente entre
        // requests y deploys) y se recalcula solo cuando el hash cambia.
        $ultimoRegistro = RegistroAsistencia::where('nivel', $nivel)
            ->when($grado, fn($q) => $q->where('grado', $grado))
            ->max('updated_at');
        $totalRegistros = RegistroAsistencia::where('nivel', $nivel)
            ->when($grado, fn($q) => $q->where('grado', $grado))
            ->count();
        // La fecha del archivo del modelo invalida el caché al reentrenar.
        $versionModelo = @filemtime(self::rutaModelo($nivel, $grado)) ?: 0;
        $hash = md5($ultimoRegistro . '|' . $totalRegistros . '|' . $cantidad . '|' . $versionModelo);
        $claveCache = 'prediccion_ia_' . self::claveModelo($nivel, $grado) . '_' . $hash;

        $cacheado = Cache::get($claveCache);
        if ($cacheado !== null) {
            return $cacheado;
        }

        $rutaDatos = $this->exportarDatos($nivel, $grado);

        $resultado = $this->ejecutar([
            'predecir',
            '--nivel', self::etiquetaProceso($nivel, $grado),
            '--datos', $rutaDatos,
            '--modelo', self::rutaModelo($nivel, $grado),
            '--dias', (string) $cantidad,
        ]);

        if (!$resultado || !($resultado['ok'] ?? false)) {
            // No se cachea un fallo: se reintenta en el siguiente request.
            return null;
        }

        $predicciones = $resultado['predicciones'] ?? [];
        Cache::put($claveCache, $predicciones, now()->addDays(7));

        return $predicciones;
    }
}
