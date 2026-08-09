<?php

namespace Tests\Feature;

use App\Models\ControlDistribucion;
use App\Models\ControlNutricional;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalisisContextoTest extends TestCase
{
    use RefreshDatabase;

    public function test_requiere_autenticacion(): void
    {
        $this->get(route('analisis-contexto.index'))->assertRedirect(route('login'));
    }

    public function test_visible_para_cualquier_usuario_autenticado_sin_restriccion_de_rol(): void
    {
        $personal = User::factory()->create(); // role por defecto: personal

        $response = $this->actingAs($personal)->get(route('analisis-contexto.index'));

        $response->assertOk();
    }

    public function test_agrega_las_raciones_por_dia_sumando_todas_las_secciones(): void
    {
        $user = User::factory()->create();

        // Mismo día, dos secciones distintas, mismo clima -> deben sumarse en un solo "día"
        RegistroAsistencia::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 20, 'condicion_climatica' => 'lluvioso',
        ]);
        RegistroAsistencia::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'grado' => '1er grado', 'seccion' => 'B',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 15, 'condicion_climatica' => 'lluvioso',
        ]);
        RegistroAsistencia::create([
            'fecha' => '2026-01-06', 'nivel' => 'primaria', 'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 28, 'condicion_climatica' => 'soleado',
        ]);

        $response = $this->actingAs($user)->get(route('analisis-contexto.index'));

        $response->assertOk();
        $resultado = $response->viewData('resultadoClima');
        $lluvioso = collect($resultado['primaria']['categorias'])->firstWhere('categoria', 'lluvioso');
        $soleado = collect($resultado['primaria']['categorias'])->firstWhere('categoria', 'soleado');

        $this->assertSame(35.0, $lluvioso['promedio']); // 20 + 15, un solo día
        $this->assertSame(1, $lluvioso['n']);
        $this->assertSame(28.0, $soleado['promedio']);
    }

    public function test_marca_el_dia_como_lluvioso_si_al_menos_una_seccion_lo_reporto(): void
    {
        $user = User::factory()->create();

        RegistroAsistencia::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 20, 'condicion_climatica' => 'lluvioso',
        ]);
        RegistroAsistencia::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'grado' => '1er grado', 'seccion' => 'B',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 15, 'condicion_climatica' => 'soleado',
        ]);

        $response = $this->actingAs($user)->get(route('analisis-contexto.index'));

        $resultado = $response->viewData('resultadoClima');
        $lluvioso = collect($resultado['primaria']['categorias'])->firstWhere('categoria', 'lluvioso');
        $this->assertNotNull($lluvioso);
        $this->assertSame(1, $lluvioso['n']);
    }

    public function test_calcula_significancia_estadistica_entre_soleado_y_lluvioso(): void
    {
        $user = User::factory()->create();
        $fecha = \Carbon\Carbon::parse('2026-01-05');

        // 5 días soleados con ~30 raciones, 5 días lluviosos con ~10 raciones: diferencia grande y consistente
        foreach (range(0, 4) as $i) {
            RegistroAsistencia::create([
                'fecha' => $fecha->copy()->addDays($i)->toDateString(), 'nivel' => 'primaria',
                'grado' => '1er grado', 'seccion' => 'A', 'total_alumnos' => 30, 'presentes' => 28,
                'raciones' => 29 + $i % 2, 'condicion_climatica' => 'soleado',
            ]);
            RegistroAsistencia::create([
                'fecha' => $fecha->copy()->addDays(10 + $i)->toDateString(), 'nivel' => 'primaria',
                'grado' => '1er grado', 'seccion' => 'A', 'total_alumnos' => 30, 'presentes' => 28,
                'raciones' => 9 + $i % 2, 'condicion_climatica' => 'lluvioso',
            ]);
        }

        $response = $this->actingAs($user)->get(route('analisis-contexto.index'));

        $resultado = $response->viewData('resultadoClima');
        $this->assertNotNull($resultado['primaria']['test']);
        $this->assertTrue($resultado['primaria']['test']['significativo']);
        $this->assertLessThan(0.05, $resultado['primaria']['test']['p']);
    }

    public function test_cruza_clima_con_indice_de_mermas_de_ficha_6(): void
    {
        $user = User::factory()->create();

        RegistroAsistencia::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 20, 'condicion_climatica' => 'lluvioso',
        ]);
        ControlDistribucion::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'fase' => 'pretest',
            'kg_desperdiciados' => 10, 'kg_distribuidos' => 50, 'tiempo_distribucion_min' => 20, // 20%
        ]);

        $response = $this->actingAs($user)->get(route('analisis-contexto.index'));

        $cruce = $response->viewData('cruceMermas');
        $filaLluvioso = collect($cruce['primaria'])->firstWhere('categoria', 'lluvioso');
        $this->assertNotNull($filaLluvioso);
        $this->assertSame(20.0, $filaLluvioso['promedio']);
    }

    public function test_cruza_clima_con_cumplimiento_nutricional_de_ficha_5(): void
    {
        $user = User::factory()->create();

        RegistroAsistencia::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 20, 'condicion_climatica' => 'soleado',
        ]);
        ControlNutricional::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'fase' => 'pretest',
            'menu_dia' => 'A', 'gramos_planificados' => 300, 'gramos_servidos' => 295,
            'cumple_requerimiento' => true,
        ]);

        $response = $this->actingAs($user)->get(route('analisis-contexto.index'));

        $cruce = $response->viewData('cruceNutricion');
        $filaSoleado = collect($cruce['primaria'])->firstWhere('categoria', 'soleado');
        $this->assertNotNull($filaSoleado);
        $this->assertSame(100.0, $filaSoleado['pct_cumple']);
    }

    public function test_reporta_cobertura_del_dato_de_clima_por_nivel(): void
    {
        $user = User::factory()->create();

        RegistroAsistencia::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 20, 'condicion_climatica' => 'soleado',
        ]);
        RegistroAsistencia::create([
            'fecha' => '2026-01-06', 'nivel' => 'primaria', 'grado' => '1er grado', 'seccion' => 'B',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 20, 'condicion_climatica' => null,
        ]);

        $response = $this->actingAs($user)->get(route('analisis-contexto.index'));

        $cobertura = $response->viewData('coberturaPorNivel');
        $this->assertSame(1, $cobertura['primaria']['con_clima']);
        $this->assertSame(2, $cobertura['primaria']['total']);
    }

    public function test_sin_registros_muestra_la_vista_sin_errores(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('analisis-contexto.index'));

        $response->assertOk();
        $response->assertSee('Sin datos de clima capturados para este nivel.', false);
    }

    // ── Exportador CSV ────────────────────────────────────────────────

    public function test_exportar_contexto_requiere_rol_investigacion(): void
    {
        $personal = User::factory()->create();

        $this->actingAs($personal)->get(route('exportar.contexto'))->assertForbidden();
    }

    public function test_exportar_contexto_devuelve_csv_para_investigador(): void
    {
        $investigador = User::factory()->investigador()->create();

        RegistroAsistencia::create([
            'fecha' => '2026-01-05', 'nivel' => 'primaria', 'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 20, 'condicion_climatica' => 'lluvioso',
        ]);

        $response = $this->actingAs($investigador)->get(route('exportar.contexto'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
