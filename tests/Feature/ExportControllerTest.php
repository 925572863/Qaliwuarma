<?php

namespace Tests\Feature;

use App\Models\ControlDistribucion;
use App\Models\ControlNutricional;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_exportar_raciones_devuelve_csv_descargable(): void
    {
        $user = User::factory()->investigador()->create();
        RegistroAsistencia::create([
            'fecha' => now()->toDateString(), 'nivel' => 'primaria',
            'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 20, 'presentes' => 18, 'raciones' => 18,
            'raciones_planificadas' => 20,
        ]);

        $response = $this->actingAs($user)->get(route('exportar.raciones'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_exportar_nutricional_devuelve_csv_descargable(): void
    {
        $user = User::factory()->investigador()->create();
        ControlNutricional::create([
            'fecha' => now()->toDateString(), 'nivel' => 'primaria', 'fase' => 'pretest',
            'menu_dia' => 'A', 'gramos_planificados' => 300, 'gramos_servidos' => 290,
            'cumple_requerimiento' => true,
        ]);

        $response = $this->actingAs($user)->get(route('exportar.nutricional'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_exportar_distribucion_devuelve_csv_descargable(): void
    {
        $user = User::factory()->investigador()->create();
        ControlDistribucion::create([
            'fecha' => now()->toDateString(), 'nivel' => 'primaria', 'fase' => 'pretest',
            'kg_desperdiciados' => 2, 'kg_distribuidos' => 40, 'tiempo_distribucion_min' => 15,
        ]);

        $response = $this->actingAs($user)->get(route('exportar.distribucion'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_exportadores_requieren_autenticacion(): void
    {
        $this->get(route('exportar.raciones'))->assertRedirect(route('login'));
        $this->get(route('exportar.nutricional'))->assertRedirect(route('login'));
        $this->get(route('exportar.distribucion'))->assertRedirect(route('login'));
        $this->get(route('exportar.ia-entrenamientos'))->assertRedirect(route('login'));
    }
}
