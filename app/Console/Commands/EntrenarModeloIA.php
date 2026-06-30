<?php

namespace App\Console\Commands;

use App\Services\PrediccionIAService;
use Illuminate\Console\Command;

class EntrenarModeloIA extends Command
{
    protected $signature = 'ia:entrenar {nivel? : inicial o primaria, omite para entrenar ambos}';

    protected $description = 'Entrena el modelo predictivo (Rubix ML / GradientBoost) con el histórico de asistencia';

    public function handle(PrediccionIAService $service): int
    {
        $niveles = $this->argument('nivel') ? [$this->argument('nivel')] : ['inicial', 'primaria'];

        foreach ($niveles as $nivel) {
            if (!in_array($nivel, ['inicial', 'primaria'])) {
                $this->error("Nivel inválido: {$nivel}");
                continue;
            }

            $this->info("Entrenando modelo para nivel: {$nivel}...");
            $resultado = $service->entrenar($nivel);

            if ($resultado === null) {
                $this->warn("  Sin suficientes datos históricos para entrenar (mínimo 20 días con histórico). Se sigue usando el modelo estadístico.");
                continue;
            }

            $this->info("  Entrenado con {$resultado['muestras']} muestras. Error promedio (MAE): {$resultado['mae']} raciones.");
        }

        return self::SUCCESS;
    }
}
