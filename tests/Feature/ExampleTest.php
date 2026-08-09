<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * La raíz de la app redirige a /dashboard, protegido por auth.
     */
    public function test_la_raiz_redirige_al_login_si_no_hay_sesion(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('dashboard'));
    }
}
