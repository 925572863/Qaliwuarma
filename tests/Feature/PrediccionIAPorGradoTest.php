<?php

namespace Tests\Feature;

use App\Models\IaEntrenamiento;
use App\Models\RegistroAsistencia;
use App\Services\PrediccionIAService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Prueba de integración real (invoca Python de verdad): entrenamiento y
 * predicción por grado dentro de un nivel, incluyendo el caso que rompía
 * silenciosamente por codificación cuando el grado tenía tildes/ñ
 * (ej. "3 Años") — ver PrediccionIAService::slugGrado().
 */
class PrediccionIAPorGradoTest extends TestCase
{
    use RefreshDatabase;

    private string $dirModelos;
    private string $dirBackup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dirModelos = storage_path('app/ia_modelos');
        $this->dirBackup  = storage_path('app/ia_modelos_backup_test_grado');

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

    private function sembrarHistorico(string $grado, int $dias): void
    {
        $fecha = now()->subWeekdays($dias);
        for ($i = 0; $i < $dias; $i++) {
            while ($fecha->isWeekend()) {
                $fecha->addDay();
            }
            RegistroAsistencia::create([
                'fecha'         => $fecha->toDateString(),
                'nivel'         => 'inicial',
                'grado'         => $grado,
                'seccion'       => 'A',
                'total_alumnos' => 20,
                'presentes'     => 18,
                'raciones'      => 15 + ($i % 5),
            ]);
            $fecha->addDay();
        }
    }

    public function test_gradosDisponibles_devuelve_los_grados_distintos_del_nivel(): void
    {
        $this->sembrarHistorico('3 Años', 12);
        $this->sembrarHistorico('4 Años', 12);

        $grados = (new PrediccionIAService())->gradosDisponibles('inicial');

        $this->assertContains('3 Años', $grados);
        $this->assertContains('4 Años', $grados);
    }

    public function test_entrenar_con_grado_acentuado_no_falla_por_codificacion(): void
    {
        // Este es el caso que rompía en Windows: "3 Años" contiene "ñ", y el
        // subproceso de Python devolvía bytes que json_decode() no aceptaba,
        // haciendo que entrenar() devolviera null en silencio.
        $this->sembrarHistorico('3 Años', 12);

        $resultado = (new PrediccionIAService())->entrenar('inicial', '3 Años');

        $this->assertNotNull($resultado, 'entrenar() no debería devolver null por un grado con tildes/ñ');
        $this->assertArrayHasKey('mae', $resultado);
    }

    public function test_entrenar_por_grado_persiste_el_grado_en_ia_entrenamientos(): void
    {
        $this->sembrarHistorico('4 Años', 12);

        (new PrediccionIAService())->entrenar('inicial', '4 Años');

        $this->assertDatabaseHas('ia_entrenamientos', [
            'nivel' => 'inicial',
            'grado' => '4 Años',
        ]);
    }

    public function test_entrenar_sin_grado_persiste_grado_null(): void
    {
        $this->sembrarHistorico('5 Años', 12);

        (new PrediccionIAService())->entrenar('inicial'); // sin grado = modelo general

        $entrenamiento = IaEntrenamiento::where('nivel', 'inicial')->first();
        $this->assertNull($entrenamiento->grado);
    }

    public function test_modelos_de_distintos_grados_usan_archivos_separados(): void
    {
        $this->sembrarHistorico('3 Años', 12);
        $this->sembrarHistorico('4 Años', 12);

        $rutaGeneral = PrediccionIAService::rutaModelo('inicial');
        $ruta3anos   = PrediccionIAService::rutaModelo('inicial', '3 Años');
        $ruta4anos   = PrediccionIAService::rutaModelo('inicial', '4 Años');

        $this->assertNotSame($rutaGeneral, $ruta3anos);
        $this->assertNotSame($ruta3anos, $ruta4anos);
    }

    public function test_entrenar_por_grado_entrena_cada_grado_con_datos_suficientes(): void
    {
        $this->sembrarHistorico('3 Años', 12);
        $this->sembrarHistorico('4 Años', 3); // insuficiente

        $resultados = (new PrediccionIAService())->entrenarPorGrado('inicial');

        $this->assertNotNull($resultados['3 Años']);
        $this->assertNull($resultados['4 Años']);
    }

    public function test_predecir_por_grado_devuelve_predicciones_independientes(): void
    {
        $service = new PrediccionIAService();
        $this->sembrarHistorico('3 Años', 15);
        $service->entrenar('inicial', '3 Años');

        $predicciones = $service->predecir('inicial', 3, '3 Años');

        $this->assertNotNull($predicciones);
        $this->assertCount(3, $predicciones);
    }

    public function test_predecir_por_grado_sin_modelo_entrenado_devuelve_null(): void
    {
        if (File::exists(PrediccionIAService::rutaModelo('inicial', '5 Años'))) {
            File::delete(PrediccionIAService::rutaModelo('inicial', '5 Años'));
        }

        $resultado = (new PrediccionIAService())->predecir('inicial', 5, '5 Años');

        $this->assertNull($resultado);
    }
}
