<?php

namespace Tests\Feature;

use App\Models\RegistroAsistencia;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegistroAsistenciaTest extends TestCase
{
    use RefreshDatabase;

    public function test_desviacion_raciones_es_null_si_no_se_registro_planificacion(): void
    {
        $registro = new RegistroAsistencia([
            'raciones' => 100,
            'raciones_planificadas' => null,
        ]);

        $this->assertNull($registro->desviacion_raciones);
    }

    public function test_desviacion_raciones_calcula_planificadas_menos_consumidas(): void
    {
        $registro = new RegistroAsistencia([
            'raciones' => 95,
            'raciones_planificadas' => 110,
        ]);

        $this->assertSame(15, $registro->desviacion_raciones);
    }

    public function test_fase_por_defecto_es_pretest(): void
    {
        $registro = RegistroAsistencia::create([
            'fecha' => now()->toDateString(),
            'nivel' => 'primaria',
            'grado' => '1er grado',
            'seccion' => 'A',
            'total_alumnos' => 20,
            'presentes' => 18,
            'raciones' => 18,
        ]);

        $this->assertSame('pretest', $registro->fresh()->fase);
    }
}
