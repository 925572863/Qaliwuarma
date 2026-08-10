<?php

namespace App\Console\Commands;

use App\Services\PrediccionIAService;
use Illuminate\Console\Command;

class EntrenarModeloIA extends Command
{
    protected $signature = 'ia:entrenar {nivel? : inicial o primaria, omite para entrenar ambos} {--por-grado : Además del modelo general, entrena un modelo independiente por cada grado}';

    protected $description = 'Entrena el modelo predictivo (Random Forest / scikit-learn, Python) con el histórico de asistencia';

    public function handle(PrediccionIAService $service): int
    {
        $niveles = $this->argument('nivel') ? [$this->argument('nivel')] : ['inicial', 'primaria'];

        foreach ($niveles as $nivel) {
            if (!in_array($nivel, ['inicial', 'primaria'])) {
                $this->error("Nivel inválido: {$nivel}");
                continue;
            }

            $this->info("Entrenando modelo general para nivel: {$nivel}...");
            $this->entrenarYReportar($service, $nivel, null);

            if ($this->option('por-grado')) {
                $grados = $service->gradosDisponibles($nivel);
                foreach ($grados as $grado) {
                    $this->info("Entrenando modelo para {$nivel} / grado \"{$grado}\"...");
                    $this->entrenarYReportar($service, $nivel, $grado);
                }
            }
        }

        return self::SUCCESS;
    }

    private function entrenarYReportar(PrediccionIAService $service, string $nivel, ?string $grado): void
    {
        $resultado = $service->entrenar($nivel, $grado);

        if ($resultado === null) {
            $this->warn('  Sin suficientes datos históricos para entrenar (mínimo 10 días con histórico) o falló el script Python.');
            return;
        }

        $this->info("  Entrenado con {$resultado['muestras']} muestras ({$resultado['n_arboles']} árboles, profundidad {$resultado['profundidad']}, {$resultado['k_folds']}-fold CV).");
        $this->info("  MAE: {$resultado['mae']} | RMSE: {$resultado['rmse']} | MAPE: {$resultado['mape']}% | R²: {$resultado['r2']}");
    }
}
