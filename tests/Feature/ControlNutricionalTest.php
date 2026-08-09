<?php

namespace Tests\Feature;

use App\Models\ControlNutricional;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlNutricionalTest extends TestCase
{
    use RefreshDatabase;

    public function test_cumple_requerimiento_es_true_dentro_de_la_tolerancia_del_10_por_ciento(): void
    {
        $user = User::factory()->investigador()->create();

        $response = $this->actingAs($user)->post(route('control-nutricional.store'), [
            'fecha' => now()->toDateString(),
            'nivel' => 'primaria',
            'fase' => 'pretest',
            'menu_dia' => 'Arroz con pollo',
            'gramos_planificados' => 300,
            'gramos_servidos' => 285, // 5% de diferencia, dentro del 10% de tolerancia
        ]);

        $response->assertRedirect(route('control-nutricional.index'));
        $this->assertDatabaseHas('controles_nutricionales', [
            'menu_dia' => 'Arroz con pollo',
            'cumple_requerimiento' => true,
        ]);
    }

    public function test_cumple_requerimiento_es_false_fuera_de_la_tolerancia(): void
    {
        $user = User::factory()->investigador()->create();

        $this->actingAs($user)->post(route('control-nutricional.store'), [
            'fecha' => now()->toDateString(),
            'nivel' => 'primaria',
            'fase' => 'pretest',
            'menu_dia' => 'Menestra con pescado',
            'gramos_planificados' => 300,
            'gramos_servidos' => 200, // 33% de diferencia
        ]);

        $this->assertDatabaseHas('controles_nutricionales', [
            'menu_dia' => 'Menestra con pescado',
            'cumple_requerimiento' => false,
        ]);
    }

    public function test_atributo_diferencia_calcula_gramos_planificados_menos_servidos(): void
    {
        $control = ControlNutricional::create([
            'fecha' => now()->toDateString(),
            'nivel' => 'inicial',
            'fase' => 'postest',
            'menu_dia' => 'Puré con pollo',
            'gramos_planificados' => 250,
            'gramos_servidos' => 230,
            'cumple_requerimiento' => true,
        ]);

        $this->assertSame(20.0, $control->diferencia);
    }

    public function test_index_requiere_autenticacion(): void
    {
        $this->get(route('control-nutricional.index'))->assertRedirect(route('login'));
    }

    public function test_index_muestra_comparativo_pretest_postest(): void
    {
        $user = User::factory()->investigador()->create();

        ControlNutricional::create([
            'fecha' => now()->toDateString(), 'nivel' => 'primaria', 'fase' => 'pretest',
            'menu_dia' => 'A', 'gramos_planificados' => 300, 'gramos_servidos' => 300,
            'cumple_requerimiento' => true,
        ]);
        ControlNutricional::create([
            'fecha' => now()->toDateString(), 'nivel' => 'primaria', 'fase' => 'postest',
            'menu_dia' => 'B', 'gramos_planificados' => 300, 'gramos_servidos' => 295,
            'cumple_requerimiento' => true,
        ]);

        $response = $this->actingAs($user)->get(route('control-nutricional.index'));

        $response->assertOk();
        $response->assertViewHas('comparativo', function ($comparativo) {
            return $comparativo['pretest']['total'] === 1 && $comparativo['postest']['total'] === 1;
        });
    }

    public function test_destroy_elimina_el_registro(): void
    {
        $user = User::factory()->investigador()->create();
        $control = ControlNutricional::create([
            'fecha' => now()->toDateString(), 'nivel' => 'inicial', 'fase' => 'pretest',
            'menu_dia' => 'A', 'gramos_planificados' => 100, 'gramos_servidos' => 100,
            'cumple_requerimiento' => true,
        ]);

        $this->actingAs($user)->delete(route('control-nutricional.destroy', $control));

        $this->assertDatabaseMissing('controles_nutricionales', ['id' => $control->id]);
    }
}
