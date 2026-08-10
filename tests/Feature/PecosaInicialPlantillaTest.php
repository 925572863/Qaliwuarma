<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PecosaInicialPlantillaTest extends TestCase
{
    use RefreshDatabase;

    public function test_plantilla_requiere_autenticacion(): void
    {
        $this->get(route('pecosa.inicial.plantilla'))->assertRedirect(route('login'));
    }

    public function test_plantilla_se_descarga_correctamente(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pecosa.inicial.plantilla'));

        $response->assertOk();
    }
}
