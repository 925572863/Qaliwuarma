<?php

namespace Tests\Feature;

use App\Models\IaEntrenamiento;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IaEntrenamientoTest extends TestCase
{
    use RefreshDatabase;

    public function test_folds_detalle_se_castea_como_array(): void
    {
        $entrenamiento = IaEntrenamiento::create([
            'nivel' => 'primaria',
            'fase' => 'postest',
            'registros_totales' => 15,
            'registros_depurados' => 14,
            'pct_depurados' => 93.33,
            'pct_completos' => 100,
            'k_folds' => 5,
            'mae' => 2.7,
            'rmse' => 3.4,
            'mape' => 2.1,
            'r2' => 0.3,
            'folds_detalle' => [
                ['fold' => 1, 'mae' => 2.1, 'rmse' => 2.4, 'r2' => -0.06],
            ],
            'n_estimators' => 300,
            'max_depth' => 8,
            'tiempo_entrenamiento_seg' => 1.2,
        ]);

        $entrenamiento->refresh();

        $this->assertIsArray($entrenamiento->folds_detalle);
        $this->assertSame(1, $entrenamiento->folds_detalle[0]['fold']);
    }

    public function test_index_requiere_autenticacion(): void
    {
        $this->get(route('ia-entrenamientos.index'))->assertRedirect(route('login'));
    }

    public function test_index_muestra_el_ultimo_entrenamiento_por_nivel(): void
    {
        $user = User::factory()->create();

        IaEntrenamiento::create([
            'nivel' => 'inicial', 'fase' => 'postest',
            'registros_totales' => 10, 'registros_depurados' => 10,
            'pct_depurados' => 100, 'pct_completos' => 100,
            'k_folds' => 5, 'mae' => 1, 'rmse' => 1, 'mape' => 1, 'r2' => 0.5,
            'n_estimators' => 300, 'max_depth' => 8, 'tiempo_entrenamiento_seg' => 1,
        ]);

        $response = $this->actingAs($user)->get(route('ia-entrenamientos.index'));

        $response->assertOk();
        $response->assertViewHas('ultimoPorNivel', function ($ultimo) {
            return $ultimo['inicial'] !== null && $ultimo['primaria'] === null;
        });
    }
}
