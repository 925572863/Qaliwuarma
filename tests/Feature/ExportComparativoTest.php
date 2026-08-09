<?php

namespace Tests\Feature;

use App\Models\ControlDistribucion;
use App\Models\ControlNutricional;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportComparativoTest extends TestCase
{
    use RefreshDatabase;

    private function contenidoCsv($response): string
    {
        ob_start();
        $response->baseResponse->sendContent();
        return ob_get_clean();
    }

    private function filasCsv($response): array
    {
        $contenido = $this->contenidoCsv($response);
        $contenido = preg_replace('/^\xEF\xBB\xBF/', '', $contenido); // quitar BOM
        $lineas = array_filter(explode("\n", trim($contenido)));
        return array_map(fn ($l) => str_getcsv(trim($l), ';'), $lineas);
    }

    // ── Ficha 4 → H1: error de estimación de raciones ──────────────────

    public function test_comparativo_raciones_pares_pretest_y_postest_por_desviacion_absoluta(): void
    {
        $user = User::factory()->investigador()->create();

        RegistroAsistencia::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'fase' => 'pretest',
            'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 20, 'raciones_planificadas' => 30, // desv = 10
        ]);
        RegistroAsistencia::create([
            'fecha' => '2026-06-05', 'nivel' => 'primaria', 'fase' => 'postest',
            'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 27, 'raciones_planificadas' => 30, // desv = 3
        ]);

        $response = $this->actingAs($user)->get(route('exportar.comparativo.raciones'));
        $response->assertOk();

        $filas = $this->filasCsv($response);
        $this->assertSame(['error_estimacion_pretest', 'error_estimacion_postest'], $filas[0]);
        $this->assertSame(['10', '3'], $filas[1]);
    }

    public function test_comparativo_raciones_ignora_registros_sin_planificacion(): void
    {
        $user = User::factory()->investigador()->create();

        RegistroAsistencia::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'fase' => 'pretest',
            'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 20, 'raciones_planificadas' => null,
        ]);

        $response = $this->actingAs($user)->get(route('exportar.comparativo.raciones'));

        $filas = $this->filasCsv($response);
        $this->assertCount(1, $filas); // solo el encabezado, sin filas de datos
    }

    public function test_comparativo_raciones_rellena_con_vacio_cuando_los_grupos_tienen_distinto_n(): void
    {
        $user = User::factory()->investigador()->create();

        RegistroAsistencia::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'fase' => 'pretest',
            'grado' => '1er grado', 'seccion' => 'A', 'total_alumnos' => 30, 'presentes' => 28,
            'raciones' => 20, 'raciones_planificadas' => 30,
        ]);
        RegistroAsistencia::create([
            'fecha' => '2026-01-06', 'nivel' => 'primaria', 'fase' => 'pretest',
            'grado' => '1er grado', 'seccion' => 'B', 'total_alumnos' => 30, 'presentes' => 28,
            'raciones' => 22, 'raciones_planificadas' => 30,
        ]);
        RegistroAsistencia::create([
            'fecha' => '2026-06-05', 'nivel' => 'primaria', 'fase' => 'postest',
            'grado' => '1er grado', 'seccion' => 'A', 'total_alumnos' => 30, 'presentes' => 28,
            'raciones' => 29, 'raciones_planificadas' => 30,
        ]);

        $response = $this->actingAs($user)->get(route('exportar.comparativo.raciones'));

        $filas = $this->filasCsv($response);
        $this->assertCount(3, $filas); // encabezado + 2 filas (n = max(2,1))
        $this->assertSame(['10', '1'], $filas[1]);
        $this->assertSame(['8', ''], $filas[2]);
    }

    // ── Ficha 5 → H3: precisión nutricional ─────────────────────────────

    public function test_comparativo_nutricional_usa_error_absoluto_en_gramos(): void
    {
        $user = User::factory()->investigador()->create();

        ControlNutricional::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'fase' => 'pretest',
            'menu_dia' => 'A', 'gramos_planificados' => 300, 'gramos_servidos' => 250, // 50
            'cumple_requerimiento' => false,
        ]);
        ControlNutricional::create([
            'fecha' => '2026-06-05', 'nivel' => 'primaria', 'fase' => 'postest',
            'menu_dia' => 'A', 'gramos_planificados' => 300, 'gramos_servidos' => 295, // 5
            'cumple_requerimiento' => true,
        ]);

        $response = $this->actingAs($user)->get(route('exportar.comparativo.nutricional'));

        $filas = $this->filasCsv($response);
        $this->assertSame(['error_gramos_pretest', 'error_gramos_postest'], $filas[0]);
        $this->assertSame(['50', '5'], $filas[1]);
    }

    // ── Ficha 6 → H4: mermas y tiempo de distribución ───────────────────

    public function test_comparativo_mermas_calcula_el_indice_por_registro(): void
    {
        $user = User::factory()->investigador()->create();

        ControlDistribucion::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'fase' => 'pretest',
            'kg_desperdiciados' => 10, 'kg_distribuidos' => 50, 'tiempo_distribucion_min' => 30, // 20%
        ]);
        ControlDistribucion::create([
            'fecha' => '2026-06-05', 'nivel' => 'primaria', 'fase' => 'postest',
            'kg_desperdiciados' => 2, 'kg_distribuidos' => 50, 'tiempo_distribucion_min' => 15, // 4%
        ]);

        $response = $this->actingAs($user)->get(route('exportar.comparativo.mermas'));

        $filas = $this->filasCsv($response);
        $this->assertSame(['indice_mermas_pretest', 'indice_mermas_postest'], $filas[0]);
        $this->assertSame(['20', '4'], $filas[1]);
    }

    public function test_comparativo_tiempo_distribucion_pares_los_minutos_registrados(): void
    {
        $user = User::factory()->investigador()->create();

        ControlDistribucion::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'fase' => 'pretest',
            'kg_desperdiciados' => 10, 'kg_distribuidos' => 50, 'tiempo_distribucion_min' => 30,
        ]);
        ControlDistribucion::create([
            'fecha' => '2026-06-05', 'nivel' => 'primaria', 'fase' => 'postest',
            'kg_desperdiciados' => 2, 'kg_distribuidos' => 50, 'tiempo_distribucion_min' => 15,
        ]);

        $response = $this->actingAs($user)->get(route('exportar.comparativo.tiempo-distribucion'));

        $filas = $this->filasCsv($response);
        $this->assertSame(['tiempo_min_pretest', 'tiempo_min_postest'], $filas[0]);
        $this->assertSame(['30', '15'], $filas[1]);
    }

    public function test_comparativos_requieren_autenticacion(): void
    {
        $this->get(route('exportar.comparativo.raciones'))->assertRedirect(route('login'));
        $this->get(route('exportar.comparativo.nutricional'))->assertRedirect(route('login'));
        $this->get(route('exportar.comparativo.mermas'))->assertRedirect(route('login'));
        $this->get(route('exportar.comparativo.tiempo-distribucion'))->assertRedirect(route('login'));
    }
}
