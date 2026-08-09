<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumnoControllerTest extends TestCase
{
    use RefreshDatabase;

    private function datosValidos(array $overrides = []): array
    {
        return array_merge([
            'matricula'  => 'M-' . fake()->unique()->numerify('#####'),
            'nivel'      => 'primaria',
            'nombre'     => 'Juan',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Lopez',
            'carrera'    => '1er grado A',
            'semestre'   => 1,
            'fecha_inscripcion' => now()->toDateString(),
            'estado'     => 'activo',
        ], $overrides);
    }

    public function test_index_requiere_autenticacion(): void
    {
        $this->get(route('alumnos.index'))->assertRedirect(route('login'));
    }

    public function test_index_carga_correctamente(): void
    {
        $user = User::factory()->create();
        Alumno::create($this->datosValidos());

        $response = $this->actingAs($user)->get(route('alumnos.index'));

        $response->assertOk();
    }

    public function test_index_filtra_por_texto_de_busqueda(): void
    {
        $user = User::factory()->create();
        Alumno::create($this->datosValidos(['nombre' => 'Maria', 'matricula' => 'M-11111']));
        Alumno::create($this->datosValidos(['nombre' => 'Carlos', 'matricula' => 'M-22222']));

        $response = $this->actingAs($user)->get(route('alumnos.index', ['buscar' => 'Maria']));

        $response->assertOk();
        $alumnos = $response->viewData('alumnos');
        $this->assertCount(1, $alumnos);
        $this->assertSame('Maria', $alumnos->first()->nombre);
    }

    public function test_store_crea_un_alumno(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('alumnos.store'), $this->datosValidos(['nombre' => 'Ana']));

        $response->assertRedirect(route('alumnos.index'));
        $this->assertDatabaseHas('alumnos', ['nombre' => 'Ana']);
    }

    public function test_store_rechaza_matricula_duplicada(): void
    {
        $user = User::factory()->create();
        Alumno::create($this->datosValidos(['matricula' => 'M-DUP01']));

        $response = $this->actingAs($user)->post(route('alumnos.store'), $this->datosValidos(['matricula' => 'M-DUP01']));

        $response->assertSessionHasErrors('matricula');
        $this->assertDatabaseCount('alumnos', 1);
    }

    public function test_store_rechaza_nivel_invalido(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('alumnos.store'), $this->datosValidos(['nivel' => 'secundaria']));

        $response->assertSessionHasErrors('nivel');
    }

    public function test_show_muestra_el_alumno(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::create($this->datosValidos());

        $response = $this->actingAs($user)->get(route('alumnos.show', $alumno));

        $response->assertOk();
        $response->assertViewHas('alumno', fn ($a) => $a->id === $alumno->id);
    }

    public function test_update_modifica_los_datos_del_alumno(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::create($this->datosValidos(['nombre' => 'Original']));

        $response = $this->actingAs($user)->put(
            route('alumnos.update', $alumno),
            $this->datosValidos(['matricula' => $alumno->matricula, 'nombre' => 'Modificado'])
        );

        $response->assertRedirect(route('alumnos.show', $alumno));
        $this->assertDatabaseHas('alumnos', ['id' => $alumno->id, 'nombre' => 'Modificado']);
    }

    public function test_update_permite_conservar_la_misma_matricula(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::create($this->datosValidos(['matricula' => 'M-MISMA']));

        $response = $this->actingAs($user)->put(
            route('alumnos.update', $alumno),
            $this->datosValidos(['matricula' => 'M-MISMA', 'nombre' => 'Cambiado'])
        );

        $response->assertSessionHasNoErrors();
    }

    public function test_destroy_elimina_el_alumno(): void
    {
        $user = User::factory()->create();
        $alumno = Alumno::create($this->datosValidos());

        $this->actingAs($user)->delete(route('alumnos.destroy', $alumno));

        $this->assertDatabaseMissing('alumnos', ['id' => $alumno->id]);
    }

    public function test_plantilla_inicial_descarga_un_archivo(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('alumnos.plantilla-inicial'));

        $response->assertOk();
    }

    public function test_plantilla_primaria_descarga_un_archivo(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('alumnos.plantilla-primaria'));

        $response->assertOk();
    }
}
