<?php

namespace Tests\Feature;

use App\Models\IaEntrenamiento;
use App\Models\RegistroAsistencia;
use App\Services\PrediccionIAService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Pruebas de integración reales: invocan el script Python de verdad
 * (scikit-learn), igual que en producción, en vez de mockear el proceso.
 * Esto es intencional: lo que queremos garantizar es que Laravel y Python
 * se entienden correctamente vía JSON, que es el punto más frágil del
 * pipeline (Ficha 1-2-3 de la tesis).
 *
 * Los artefactos de storage/app/ia_modelos/ se respaldan antes de cada test
 * y se restauran después, para no perder los modelos ya entrenados del
 * proyecto real.
 */
class PrediccionIAServiceTest extends TestCase
{
    use RefreshDatabase;

    private string $dirModelos;
    private string $dirBackup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dirModelos = storage_path('app/ia_modelos');
        $this->dirBackup  = storage_path('app/ia_modelos_backup_test');

        if (File::isDirectory($this->dirModelos)) {
            File::copyDirectory($this->dirModelos, $this->dirBackup);
        }
    }

    protected function tearDown(): void
    {
        if (File::isDirectory($this->dirModelos)) {
            File::deleteDirectory($this->dirModelos);
        }
        if (File::isDirectory($this->dirBackup)) {
            File::moveDirectory($this->dirBackup, $this->dirModelos);
        }

        parent::tearDown();
    }

    /** Crea $dias registros de asistencia consecutivos (días hábiles) para 'primaria'. */
    private function sembrarHistorico(int $dias): void
    {
        $fecha = now()->subWeekdays($dias);
        for ($i = 0; $i < $dias; $i++) {
            while ($fecha->isWeekend()) {
                $fecha->addDay();
            }
            RegistroAsistencia::create([
                'fecha'         => $fecha->toDateString(),
                'nivel'         => 'primaria',
                'grado'         => '1er grado',
                'seccion'       => 'A',
                'total_alumnos' => 30,
                'presentes'     => 28,
                'raciones'      => 25 + ($i % 5),
            ]);
            $fecha->addDay();
        }
    }

    public function test_entrenar_devuelve_null_si_no_hay_muestras_suficientes(): void
    {
        $this->sembrarHistorico(3); // menos del mínimo (10)

        $resultado = (new PrediccionIAService())->entrenar('primaria');

        $this->assertNull($resultado);
        $this->assertDatabaseCount('ia_entrenamientos', 0);
    }

    public function test_entrenar_persiste_las_fichas_1_2_y_3_en_ia_entrenamientos(): void
    {
        $this->sembrarHistorico(15);

        $resultado = (new PrediccionIAService())->entrenar('primaria');

        $this->assertNotNull($resultado);
        $this->assertArrayHasKey('mae', $resultado);
        $this->assertArrayHasKey('rmse', $resultado);
        $this->assertArrayHasKey('mape', $resultado);
        $this->assertArrayHasKey('r2', $resultado);

        $this->assertDatabaseCount('ia_entrenamientos', 1);

        $entrenamiento = IaEntrenamiento::first();
        $this->assertSame('primaria', $entrenamiento->nivel);
        $this->assertSame(300, $entrenamiento->n_estimators);
        $this->assertSame(8, $entrenamiento->max_depth);
        $this->assertIsArray($entrenamiento->folds_detalle);
        $this->assertNotEmpty($entrenamiento->folds_detalle);
        $this->assertGreaterThan(0, $entrenamiento->pct_depurados);
    }

    public function test_entrenar_genera_el_archivo_del_modelo_en_disco(): void
    {
        $this->sembrarHistorico(15);

        (new PrediccionIAService())->entrenar('primaria');

        $this->assertFileExists(PrediccionIAService::rutaModelo('primaria'));
    }

    public function test_modelo_existe_es_false_antes_de_entrenar(): void
    {
        // Nivel sin datos ni modelo previo respaldado (aislado por el backup/restore)
        if (File::exists(PrediccionIAService::rutaModelo('inicial'))) {
            File::delete(PrediccionIAService::rutaModelo('inicial'));
        }

        $this->assertFalse((new PrediccionIAService())->modeloExiste('inicial'));
    }

    public function test_predecir_devuelve_null_si_no_existe_modelo_entrenado(): void
    {
        if (File::exists(PrediccionIAService::rutaModelo('inicial'))) {
            File::delete(PrediccionIAService::rutaModelo('inicial'));
        }

        $resultado = (new PrediccionIAService())->predecir('inicial', 5);

        $this->assertNull($resultado);
    }

    public function test_predecir_devuelve_dias_habiles_con_la_estructura_esperada(): void
    {
        $this->sembrarHistorico(15);
        $service = new PrediccionIAService();
        $service->entrenar('primaria');

        $predicciones = $service->predecir('primaria', 3);

        $this->assertNotNull($predicciones);
        $this->assertCount(3, $predicciones);
        foreach ($predicciones as $prediccion) {
            $this->assertArrayHasKey('fecha', $prediccion);
            $this->assertArrayHasKey('fecha_legible', $prediccion);
            $this->assertArrayHasKey('raciones_predichas', $prediccion);
            $this->assertGreaterThanOrEqual(0, $prediccion['raciones_predichas']);

            // Nunca debe caer sábado o domingo (solo días hábiles)
            $diaSemana = \Carbon\Carbon::parse($prediccion['fecha'])->dayOfWeek;
            $this->assertNotContains($diaSemana, [\Carbon\Carbon::SATURDAY, \Carbon\Carbon::SUNDAY]);
        }
    }

    /**
     * Reproduce el escenario de un host sin filesystem persistente (ej.
     * Render en plan free): el .joblib entrenado se pierde en cada deploy,
     * pero la base de datos sí persiste. modeloExiste()/predecir() deben
     * restaurar el archivo a disco automáticamente desde la copia guardada
     * en ia_modelos_binarios.
     */
    public function test_modelo_se_restaura_desde_bd_si_falta_en_disco(): void
    {
        $this->sembrarHistorico(15);
        $service = new PrediccionIAService();
        $service->entrenar('primaria');

        $this->assertDatabaseCount('ia_modelos_binarios', 1);
        $this->assertDatabaseHas('ia_modelos_binarios', ['clave' => 'primaria']);

        // Simula la pérdida del filesystem entre deploys
        File::delete(PrediccionIAService::rutaModelo('primaria'));
        $this->assertFileDoesNotExist(PrediccionIAService::rutaModelo('primaria'));

        $this->assertTrue($service->modeloExiste('primaria'));
        $this->assertFileExists(PrediccionIAService::rutaModelo('primaria'));

        $predicciones = $service->predecir('primaria', 2);
        $this->assertNotNull($predicciones);
        $this->assertCount(2, $predicciones);
    }
}
