<?php

namespace App\Services;

use App\Models\RegistroAsistencia;
use Illuminate\Support\Facades\Log;
use Rubix\ML\Datasets\Labeled;
use Rubix\ML\PersistentModel;
use Rubix\ML\Persisters\Filesystem;
use Rubix\ML\Regressors\GradientBoost;

class PrediccionIAService
{
    private const MIN_MUESTRAS = 20;

    public static function rutaModelo(string $nivel): string
    {
        return storage_path("app/ia_modelos/modelo_{$nivel}.rbx");
    }

    /**
     * Construye el histórico diario (fecha + raciones) agrupado, igual que PrediccionController.
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
            ])
            ->values()
            ->sortBy('fecha')
            ->values()
            ->toArray();
    }

    /**
     * Genera [features, label] por cada día con suficiente histórico previo (lags).
     */
    private function construirMuestras(array $historico): array
    {
        $samples = [];
        $labels  = [];

        foreach ($historico as $i => $dia) {
            if ($i < 3) continue; // necesita al menos 3 días previos para los promedios móviles

            $fecha = \Carbon\Carbon::parse($dia['fecha']);
            $anteriores = array_slice($historico, max(0, $i - 7), min($i, 7));
            $valoresAnt = array_column($anteriores, 'raciones');

            $ma3 = array_sum(array_slice($valoresAnt, -3)) / min(3, count($valoresAnt));
            $ma7 = array_sum($valoresAnt) / count($valoresAnt);

            $samples[] = [
                (float) $fecha->dayOfWeek,
                (float) $fecha->day,
                (float) $fecha->month,
                (float) $i,
                $ma3,
                $ma7,
            ];
            $labels[] = $dia['raciones'];
        }

        return [$samples, $labels];
    }

    /**
     * Entrena y persiste el modelo para un nivel. Devuelve estadísticas o null si faltan datos.
     */
    public function entrenar(string $nivel): ?array
    {
        $historico = $this->historicoDiario($nivel);
        [$samples, $labels] = $this->construirMuestras($historico);

        if (count($samples) < self::MIN_MUESTRAS) {
            return null;
        }

        $dataset = new Labeled($samples, $labels);

        $estimator = new GradientBoost(
            booster: null,
            rate: 0.1,
            ratio: 0.5,
            epochs: 300,
            minChange: 1e-4,
            window: 5,
            holdOut: 0.1,
        );

        $estimator->train($dataset);

        $ruta = self::rutaModelo($nivel);
        if (!is_dir(dirname($ruta))) {
            mkdir(dirname($ruta), 0775, true);
        }

        $persistente = new PersistentModel($estimator, new Filesystem($ruta));
        $persistente->save();

        // Error de entrenamiento (MAE sobre el propio set, solo referencial)
        $predicciones = $estimator->predict($dataset);
        $errores = array_map(fn ($real, $pred) => abs($real - $pred), $labels, $predicciones);
        $mae = round(array_sum($errores) / count($errores), 2);

        return [
            'muestras' => count($samples),
            'mae'      => $mae,
        ];
    }

    public function modeloExiste(string $nivel): bool
    {
        return file_exists(self::rutaModelo($nivel));
    }

    /**
     * Predice 'cantidad' días hábiles futuros usando el modelo entrenado.
     * Devuelve null si no hay modelo guardado.
     */
    public function predecir(string $nivel, int $cantidad = 5): ?array
    {
        if (!$this->modeloExiste($nivel)) {
            return null;
        }

        try {
            $estimator = PersistentModel::load(new Filesystem(self::rutaModelo($nivel)));
        } catch (\Throwable $e) {
            Log::error("PrediccionIAService: no se pudo cargar modelo {$nivel}: " . $e->getMessage());
            return null;
        }

        $historico = $this->historicoDiario($nivel);
        if (empty($historico)) {
            return null;
        }

        $serie = array_column($historico, 'raciones'); // se irá extendiendo con las predicciones
        $n     = count($historico);

        $predicciones = [];
        for ($i = 0; $i <= 14 && count($predicciones) < $cantidad; $i++) {
            $fecha = now()->addDays($i);
            if (in_array($fecha->dayOfWeek, [0, 6])) continue;

            $idx = $n + count($predicciones);
            $ultimos7 = array_slice($serie, -7);
            $ma3 = array_sum(array_slice($serie, -3)) / min(3, count($serie));
            $ma7 = array_sum($ultimos7) / count($ultimos7);

            $features = [
                (float) $fecha->dayOfWeek,
                (float) $fecha->day,
                (float) $fecha->month,
                (float) $idx,
                $ma3,
                $ma7,
            ];

            $pred = max(0, round($estimator->predict(new \Rubix\ML\Datasets\Unlabeled([$features]))[0]));

            $serie[] = $pred; // autoregresivo: la predicción alimenta el siguiente cálculo de MA

            $predicciones[] = [
                'fecha'              => $fecha->format('Y-m-d'),
                'fecha_legible'      => ucfirst($fecha->locale('es')->isoFormat('dddd D/MM')),
                'raciones_predichas' => (int) $pred,
            ];
        }

        return $predicciones;
    }
}
