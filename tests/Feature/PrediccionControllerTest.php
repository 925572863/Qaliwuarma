<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\RegistroAsistencia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PrediccionControllerTest extends TestCase
{
    use RefreshDatabase;

    private function alumno(array $overrides = []): Alumno
    {
        return Alumno::create(array_merge([
            'matricula'  => 'M-' . fake()->unique()->numerify('#####'),
            'nivel'      => 'primaria',
            'nombre'     => 'Juan',
            'apellido_paterno' => 'Perez',
            'apellido_materno' => 'Lopez',
            'carrera'    => '1ER GRADO A',
            'semestre'   => 1,
            'fecha_inscripcion' => now()->toDateString(),
            'estado'     => 'activo',
        ], $overrides));
    }

    // ── index / create ──────────────────────────────────────────────────

    public function test_index_requiere_autenticacion(): void
    {
        $this->get(route('prediccion.index'))->assertRedirect(route('login'));
    }

    public function test_index_carga_correctamente_para_un_usuario_autenticado(): void
    {
        $user = User::factory()->investigador()->create();

        $response = $this->actingAs($user)->get(route('prediccion.index', ['nivel' => 'primaria']));

        $response->assertOk();
    }

    public function test_create_carga_correctamente(): void
    {
        $user = User::factory()->investigador()->create();

        $response = $this->actingAs($user)->get(route('prediccion.create', ['nivel' => 'primaria']));

        $response->assertOk();
    }

    // ── store ────────────────────────────────────────────────────────────

    public function test_store_crea_un_registro_de_asistencia(): void
    {
        $user = User::factory()->investigador()->create();

        $response = $this->actingAs($user)->post(route('prediccion.store'), [
            'fecha' => now()->toDateString(),
            'nivel' => 'primaria',
            'grado' => '1er grado',
            'seccion' => 'A',
            'total_alumnos' => 25,
            'presentes' => 20,
            'raciones' => 20,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('registros_asistencia', [
            'grado' => '1er grado',
            'seccion' => 'A',
            'presentes' => 20,
            'fase' => 'pretest',
        ]);
    }

    public function test_store_rechaza_presentes_mayor_que_total_alumnos(): void
    {
        $user = User::factory()->investigador()->create();

        $response = $this->actingAs($user)->post(route('prediccion.store'), [
            'fecha' => now()->toDateString(),
            'nivel' => 'primaria',
            'grado' => '1er grado',
            'seccion' => 'A',
            'total_alumnos' => 10,
            'presentes' => 15,
            'raciones' => 10,
        ]);

        $response->assertSessionHasErrors('presentes');
        $this->assertDatabaseMissing('registros_asistencia', ['grado' => '1er grado']);
    }

    public function test_store_actualiza_en_vez_de_duplicar_si_ya_existe_el_registro_del_dia(): void
    {
        $user = User::factory()->investigador()->create();
        $fecha = now()->toDateString();

        $this->actingAs($user)->post(route('prediccion.store'), [
            'fecha' => $fecha, 'nivel' => 'primaria', 'grado' => '2do grado', 'seccion' => 'B',
            'total_alumnos' => 20, 'presentes' => 15, 'raciones' => 15,
        ]);
        $this->actingAs($user)->post(route('prediccion.store'), [
            'fecha' => $fecha, 'nivel' => 'primaria', 'grado' => '2do grado', 'seccion' => 'B',
            'total_alumnos' => 20, 'presentes' => 18, 'raciones' => 18,
        ]);

        $this->assertDatabaseCount('registros_asistencia', 1);
        $this->assertDatabaseHas('registros_asistencia', ['presentes' => 18]);
    }

    public function test_store_guarda_raciones_planificadas_y_variables_de_contexto(): void
    {
        $user = User::factory()->investigador()->create();

        $this->actingAs($user)->post(route('prediccion.store'), [
            'fecha' => now()->toDateString(),
            'nivel' => 'primaria', 'grado' => '3er grado', 'seccion' => 'C',
            'total_alumnos' => 30, 'presentes' => 28, 'raciones' => 26,
            'raciones_planificadas' => 30,
            'condicion_climatica' => 'lluvioso',
            'evento_especial' => '1',
        ]);

        $registro = RegistroAsistencia::where('grado', '3er grado')->first();
        $this->assertSame(30, $registro->raciones_planificadas);
        $this->assertSame(4, $registro->desviacion_raciones); // 30 - 26
        $this->assertSame('lluvioso', $registro->condicion_climatica);
        $this->assertTrue($registro->evento_especial);
    }

    // ── destroy ──────────────────────────────────────────────────────────

    public function test_destroy_elimina_el_registro(): void
    {
        $user = User::factory()->investigador()->create();
        $registro = RegistroAsistencia::create([
            'fecha' => now()->toDateString(), 'nivel' => 'primaria',
            'grado' => '1er grado', 'seccion' => 'A',
            'total_alumnos' => 20, 'presentes' => 18, 'raciones' => 18,
        ]);

        $this->actingAs($user)->delete(route('prediccion.destroy', $registro));

        $this->assertDatabaseMissing('registros_asistencia', ['id' => $registro->id]);
    }

    // ── secciones-grado / alumnos-aula ──────────────────────────────────

    public function test_secciones_grado_devuelve_las_secciones_activas_del_grado(): void
    {
        $user = User::factory()->investigador()->create();
        $this->alumno(['carrera' => '1ER GRADO A']);
        $this->alumno(['carrera' => '1ER GRADO B']);
        $this->alumno(['carrera' => '2DO GRADO A', 'nivel' => 'primaria']);
        $this->alumno(['carrera' => '1ER GRADO A', 'estado' => 'inactivo']);

        $response = $this->actingAs($user)->getJson(route('prediccion.secciones-grado', [
            'nivel' => 'primaria', 'grado' => '1ER GRADO',
        ]));

        $response->assertOk();
        $response->assertJson(['secciones' => ['A', 'B']]);
    }

    public function test_alumnos_aula_devuelve_el_total_y_listado_de_matriculados(): void
    {
        $user = User::factory()->investigador()->create();
        $this->alumno(['carrera' => '1ER GRADO A', 'apellido_paterno' => 'Aguirre']);
        $this->alumno(['carrera' => '1ER GRADO A', 'apellido_paterno' => 'Zegarra']);
        $this->alumno(['carrera' => '1ER GRADO B']); // otra sección, no debe contar

        $response = $this->actingAs($user)->getJson(route('prediccion.alumnos-aula', [
            'nivel' => 'primaria', 'grado' => '1ER GRADO', 'seccion' => 'A',
        ]));

        $response->assertOk();
        $response->assertJsonCount(2, 'alumnos');
        $response->assertJsonPath('total', 2);
        // Orden alfabético por apellido paterno
        $response->assertJsonPath('alumnos.0.nombre', 'Aguirre Lopez, Juan');
    }

    // ── importarHistorico ────────────────────────────────────────────────

    public function test_importar_historico_rechaza_archivo_sin_columnas_requeridas(): void
    {
        $user = User::factory()->investigador()->create();

        $csv = "columna_invalida\nvalor\n";
        $archivo = \Illuminate\Http\UploadedFile::fake()->createWithContent('sin_encabezados.csv', $csv);

        $response = $this->actingAs($user)->post(route('prediccion.importar'), [
            'archivo' => $archivo,
            'nivel' => 'primaria',
        ]);

        $response->assertSessionHasErrors('archivo');
    }

    public function test_importar_historico_crea_registros_desde_csv_valido(): void
    {
        $user = User::factory()->investigador()->create();

        $csv = "fecha,grado,seccion,presentes,total_alumnos,raciones\n"
             . "2026-03-02,1er grado,A,18,20,18\n"
             . "2026-03-03,1er grado,A,19,20,19\n";
        $archivo = \Illuminate\Http\UploadedFile::fake()->createWithContent('historico.csv', $csv);

        $response = $this->actingAs($user)->post(route('prediccion.importar'), [
            'archivo' => $archivo,
            'nivel' => 'primaria',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseCount('registros_asistencia', 2);
        $this->assertDatabaseHas('registros_asistencia', ['fecha' => '2026-03-02', 'presentes' => 18]);
    }
}
