<?php

namespace Tests\Feature;

use App\Models\ControlDistribucion;
use App\Models\ControlNutricional;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifica que los módulos de investigación (fichas 4/5/6, entrenamiento IA
 * y exportadores) estén restringidos a los roles admin/investigador, y que
 * un usuario con rol 'personal' (por defecto) solo pueda ver, no modificar.
 */
class RoleAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_personal_no_puede_registrar_control_nutricional(): void
    {
        $personal = User::factory()->create(); // role por defecto: personal

        $response = $this->actingAs($personal)->post(route('control-nutricional.store'), [
            'fecha' => now()->toDateString(), 'nivel' => 'primaria', 'fase' => 'pretest',
            'menu_dia' => 'A', 'gramos_planificados' => 300, 'gramos_servidos' => 290,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('controles_nutricionales', 0);
    }

    public function test_usuario_personal_no_puede_eliminar_control_nutricional(): void
    {
        $personal = User::factory()->create();
        $control = ControlNutricional::create([
            'fecha' => now()->toDateString(), 'nivel' => 'primaria', 'fase' => 'pretest',
            'menu_dia' => 'A', 'gramos_planificados' => 300, 'gramos_servidos' => 290,
            'cumple_requerimiento' => true,
        ]);

        $response = $this->actingAs($personal)->delete(route('control-nutricional.destroy', $control));

        $response->assertForbidden();
        $this->assertDatabaseHas('controles_nutricionales', ['id' => $control->id]);
    }

    public function test_usuario_personal_no_puede_registrar_control_distribucion(): void
    {
        $personal = User::factory()->create();

        $response = $this->actingAs($personal)->post(route('control-distribucion.store'), [
            'fecha' => now()->toDateString(), 'nivel' => 'primaria', 'fase' => 'pretest',
            'kg_desperdiciados' => 1, 'kg_distribuidos' => 20, 'tiempo_distribucion_min' => 10,
        ]);

        $response->assertForbidden();
    }

    public function test_usuario_personal_no_puede_eliminar_control_distribucion(): void
    {
        $personal = User::factory()->create();
        $control = ControlDistribucion::create([
            'fecha' => now()->toDateString(), 'nivel' => 'primaria', 'fase' => 'pretest',
            'kg_desperdiciados' => 1, 'kg_distribuidos' => 20, 'tiempo_distribucion_min' => 10,
        ]);

        $response = $this->actingAs($personal)->delete(route('control-distribucion.destroy', $control));

        $response->assertForbidden();
    }

    public function test_usuario_personal_no_puede_borrar_registro_de_asistencia(): void
    {
        $personal = User::factory()->create();
        $registro = RegistroAsistencia::create([
            'fecha' => now()->toDateString(), 'nivel' => 'primaria',
            'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 20, 'presentes' => 18, 'raciones' => 18,
        ]);

        $response = $this->actingAs($personal)->delete(route('prediccion.destroy', $registro));

        $response->assertForbidden();
    }

    public function test_usuario_personal_si_puede_registrar_asistencia_diaria(): void
    {
        // La captura diaria de asistencia es una tarea operativa, no de investigación:
        // el personal de cocina debe poder seguir haciéndola.
        $personal = User::factory()->create();

        $response = $this->actingAs($personal)->post(route('prediccion.store'), [
            'fecha' => now()->toDateString(), 'nivel' => 'primaria',
            'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 20, 'presentes' => 18, 'raciones' => 18,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('registros_asistencia', ['grado' => '1er grado']);
    }

    public function test_usuario_personal_no_puede_importar_historico_de_asistencia(): void
    {
        $personal = User::factory()->create();
        $archivo = \Illuminate\Http\UploadedFile::fake()->createWithContent(
            'historico.csv',
            "fecha,presentes\n2026-03-02,18\n"
        );

        $response = $this->actingAs($personal)->post(route('prediccion.importar'), [
            'archivo' => $archivo, 'nivel' => 'primaria',
        ]);

        $response->assertForbidden();
    }

    public function test_usuario_personal_no_puede_entrenar_el_modelo(): void
    {
        $personal = User::factory()->create();

        $response = $this->actingAs($personal)->post(route('prediccion.entrenar-ia'), ['nivel' => 'primaria']);

        $response->assertForbidden();
    }

    public function test_usuario_personal_no_puede_exportar_datos(): void
    {
        $personal = User::factory()->create();

        $this->actingAs($personal)->get(route('exportar.raciones'))->assertForbidden();
        $this->actingAs($personal)->get(route('exportar.comparativo.raciones'))->assertForbidden();
    }

    public function test_usuario_personal_si_puede_ver_los_indices_de_los_modulos(): void
    {
        // Ver está permitido para todos los autenticados; solo modificar está restringido.
        $personal = User::factory()->create();

        $this->actingAs($personal)->get(route('control-nutricional.index'))->assertOk();
        $this->actingAs($personal)->get(route('control-distribucion.index'))->assertOk();
        $this->actingAs($personal)->get(route('ia-entrenamientos.index'))->assertOk();
    }

    public function test_investigador_si_puede_gestionar_los_modulos_de_investigacion(): void
    {
        $investigador = User::factory()->investigador()->create();

        $response = $this->actingAs($investigador)->post(route('control-nutricional.store'), [
            'fecha' => now()->toDateString(), 'nivel' => 'primaria', 'fase' => 'pretest',
            'menu_dia' => 'A', 'gramos_planificados' => 300, 'gramos_servidos' => 290,
        ]);

        $response->assertRedirect(route('control-nutricional.index'));
        $this->assertDatabaseCount('controles_nutricionales', 1);
    }

    public function test_admin_si_puede_gestionar_los_modulos_de_investigacion(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('exportar.raciones'));

        $response->assertOk();
    }

    public function test_puede_gestionar_investigacion_es_true_solo_para_admin_e_investigador(): void
    {
        $this->assertTrue(User::factory()->admin()->make()->puedeGestionarInvestigacion());
        $this->assertTrue(User::factory()->investigador()->make()->puedeGestionarInvestigacion());
        $this->assertFalse(User::factory()->make()->puedeGestionarInvestigacion()); // personal
    }
}
