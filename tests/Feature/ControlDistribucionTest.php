<?php

namespace Tests\Feature;

use App\Models\ControlDistribucion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlDistribucionTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_crea_un_registro_de_distribucion(): void
    {
        $user = User::factory()->investigador()->create();

        $response = $this->actingAs($user)->post(route('control-distribucion.store'), [
            'fecha' => now()->toDateString(),
            'nivel' => 'primaria',
            'fase' => 'pretest',
            'kg_desperdiciados' => 3,
            'kg_distribuidos' => 60,
            'tiempo_distribucion_min' => 30,
        ]);

        $response->assertRedirect(route('control-distribucion.index'));
        $this->assertDatabaseHas('controles_distribucion', [
            'kg_desperdiciados' => 3,
            'kg_distribuidos' => 60,
        ]);
    }

    public function test_atributo_indice_mermas_calcula_el_porcentaje_correcto(): void
    {
        $control = ControlDistribucion::create([
            'fecha' => now()->toDateString(),
            'nivel' => 'inicial',
            'fase' => 'postest',
            'kg_desperdiciados' => 5,
            'kg_distribuidos' => 50,
            'tiempo_distribucion_min' => 20,
        ]);

        $this->assertSame(10.0, $control->indice_mermas);
    }

    public function test_atributo_indice_mermas_no_divide_por_cero(): void
    {
        $control = new ControlDistribucion(['kg_desperdiciados' => 0, 'kg_distribuidos' => 0]);

        $this->assertSame(0.0, $control->indice_mermas);
    }

    public function test_validacion_rechaza_kg_distribuidos_cero_o_negativo(): void
    {
        $user = User::factory()->investigador()->create();

        $response = $this->actingAs($user)->post(route('control-distribucion.store'), [
            'fecha' => now()->toDateString(),
            'nivel' => 'primaria',
            'fase' => 'pretest',
            'kg_desperdiciados' => 1,
            'kg_distribuidos' => 0,
            'tiempo_distribucion_min' => 10,
        ]);

        $response->assertSessionHasErrors('kg_distribuidos');
    }

    public function test_destroy_elimina_el_registro(): void
    {
        $user = User::factory()->investigador()->create();
        $control = ControlDistribucion::create([
            'fecha' => now()->toDateString(), 'nivel' => 'primaria', 'fase' => 'pretest',
            'kg_desperdiciados' => 1, 'kg_distribuidos' => 10, 'tiempo_distribucion_min' => 5,
        ]);

        $this->actingAs($user)->delete(route('control-distribucion.destroy', $control));

        $this->assertDatabaseMissing('controles_distribucion', ['id' => $control->id]);
    }
}
