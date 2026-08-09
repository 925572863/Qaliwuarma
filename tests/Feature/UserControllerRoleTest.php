<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_asigna_el_rol_seleccionado(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Nuevo Investigador',
            'email' => 'investigador@qualiwuarma.com',
            'role' => 'investigador',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'investigador@qualiwuarma.com',
            'role' => 'investigador',
        ]);
    }

    public function test_store_rechaza_un_rol_invalido(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Test',
            'email' => 'test@qualiwuarma.com',
            'role' => 'superadmin', // no existe
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('role');
    }

    public function test_update_cambia_el_rol_de_un_usuario(): void
    {
        $admin = User::factory()->admin()->create();
        $usuario = User::factory()->create(); // role por defecto: personal

        $this->actingAs($admin)->put(route('users.update', $usuario), [
            'name' => $usuario->name,
            'email' => $usuario->email,
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', ['id' => $usuario->id, 'role' => 'admin']);
    }

    public function test_nuevo_usuario_por_defecto_es_personal_via_factory(): void
    {
        $usuario = User::factory()->create();

        $this->assertSame('personal', $usuario->role);
    }
}
