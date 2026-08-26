<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProrrateoControllerTest extends TestCase
{
    use RefreshDatabase;

    private function alumno(string $carrera, int $semestre = 1): Alumno
    {
        return Alumno::create([
            'matricula'  => 'M-' . fake()->unique()->numerify('#####'),
            'nivel'      => 'primaria',
            'nombre'     => fake()->firstName(),
            'apellido_paterno' => fake()->lastName(),
            'apellido_materno' => fake()->lastName(),
            'carrera'    => $carrera,
            'semestre'   => $semestre,
            'fecha_inscripcion' => now()->toDateString(),
            'estado'     => 'activo',
        ]);
    }

    private function producto(array $overrides = []): int
    {
        return DB::table('pecosa_primaria')->insertGetId(array_merge([
            'cant' => 100,
            'unid' => 'kg',
            'descripcion' => 'Arroz',
            'presentacion' => 1,
            'volumen' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    public function test_primaria_requiere_autenticacion(): void
    {
        $this->get(route('pecosa.primaria.prorrateo'))->assertRedirect(route('login'));
    }

    public function test_primaria_arranca_en_cero_sin_reparto_automatico(): void
    {
        $user = User::factory()->create();
        $this->alumno('1º A', 1); // 1 alumno
        for ($i = 0; $i < 3; $i++) { $this->alumno('1º B', 1); } // 3 alumnos
        $this->producto(['descripcion' => 'Arroz', 'cant' => 40]);

        $response = $this->actingAs($user)->get(route('pecosa.primaria.prorrateo'));

        $response->assertOk();
        // La tabla ya no reparte solo; arranca en 0 y el usuario llena a mano
        // cuánto le da a cada aula (el reparto proporcional automático se quitó).
        $data = $response->viewData('data');
        $filaA = collect($data)->firstWhere('seccion', '1º A');
        $filaB = collect($data)->firstWhere('seccion', '1º B');
        $this->assertSame(0, $filaA['items'][0]);
        $this->assertSame(0, $filaB['items'][0]);
    }

    public function test_guardar_crea_version_registros_y_descuenta_stock(): void
    {
        $user = User::factory()->create();
        $pecosaId = $this->producto(['cant' => 100]);

        $response = $this->actingAs($user)->post(route('pecosa.primaria.prorrateo.guardar'), [
            'nombre' => 'Distribución de prueba',
            'alumnos' => ['1º A' => 10, '1º B' => 20],
            'cantidades' => [
                '1º A' => [$pecosaId => 10],
                '1º B' => [$pecosaId => 20],
            ],
        ]);

        $response->assertRedirect(route('pecosa.primaria.distribuciones'));

        $this->assertDatabaseHas('prorrateo_versiones', [
            'nombre' => 'Distribución de prueba',
            'total_alumnos' => 30,
            'total_unidades' => 30,
        ]);
        $this->assertDatabaseCount('prorrateo_primaria', 2);
        $this->assertDatabaseHas('pecosa_primaria', ['id' => $pecosaId, 'cant' => 70]); // 100 - 30
    }

    public function test_guardar_no_permite_cantidades_negativas(): void
    {
        $user = User::factory()->create();
        $pecosaId = $this->producto(['cant' => 50]);

        $this->actingAs($user)->post(route('pecosa.primaria.prorrateo.guardar'), [
            'alumnos' => ['1º A' => 10],
            'cantidades' => ['1º A' => [$pecosaId => -5]],
        ]);

        $this->assertDatabaseHas('prorrateo_primaria', ['cantidad' => 0]);
    }

    public function test_historial_lista_las_versiones_guardadas_mas_recientes_primero(): void
    {
        $user = User::factory()->create();
        DB::table('prorrateo_versiones')->insert([
            ['nombre' => 'Vieja', 'total_alumnos' => 1, 'total_unidades' => 1, 'created_at' => now()->subDay(), 'updated_at' => now()->subDay()],
            ['nombre' => 'Reciente', 'total_alumnos' => 1, 'total_unidades' => 1, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($user)->get(route('pecosa.primaria.distribuciones'));

        $response->assertOk();
        $versiones = $response->viewData('versiones');
        $this->assertSame('Reciente', $versiones->first()->nombre);
    }

    public function test_ver_version_devuelve_404_si_no_existe(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('pecosa.primaria.distribuciones.ver', 999));

        $response->assertNotFound();
    }

    public function test_eliminar_version_borra_la_version_y_sus_registros_en_cascada(): void
    {
        $user = User::factory()->create();
        $pecosaId = $this->producto();
        $versionId = DB::table('prorrateo_versiones')->insertGetId([
            'nombre' => 'A eliminar', 'total_alumnos' => 5, 'total_unidades' => 5,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('prorrateo_primaria')->insert([
            'version_id' => $versionId, 'seccion' => '1º A', 'alumnos' => 5,
            'pecosa_primaria_id' => $pecosaId, 'cantidad' => 5,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($user)->delete(route('pecosa.primaria.distribuciones.eliminar', $versionId));

        $this->assertDatabaseMissing('prorrateo_versiones', ['id' => $versionId]);
        $this->assertDatabaseMissing('prorrateo_primaria', ['version_id' => $versionId]);
    }
}
