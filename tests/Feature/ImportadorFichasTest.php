<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportadorFichasTest extends TestCase
{
    use RefreshDatabase;

    // ── Ficha 5 (Control Nutricional) ───────────────────────────────────

    public function test_nutricional_acepta_encabezados_con_acentos_y_sinonimos(): void
    {
        $user = User::factory()->investigador()->create();

        // "Fecha", "Menú" y "Gr. Planificados" son variantes de los nombres canónicos
        $csv = "Fecha,Nivel,Fase,Menú,Gr Planificados,Gr Servidos\n"
             . "2026-03-02,primaria,pretest,Arroz con pollo,300,290\n";
        $archivo = UploadedFile::fake()->createWithContent('ficha5.csv', $csv);

        $response = $this->actingAs($user)->post(route('control-nutricional.importar'), ['archivo' => $archivo]);

        $response->assertRedirect(route('control-nutricional.index'));
        $this->assertDatabaseHas('controles_nutricionales', ['menu_dia' => 'Arroz con pollo']);
    }

    public function test_nutricional_reporta_filas_con_fecha_invalida_sin_detener_la_importacion(): void
    {
        $user = User::factory()->investigador()->create();

        $csv = "fecha,menu_dia,gramos_planificados,gramos_servidos\n"
             . "fecha-mala,Arroz,300,290\n"
             . "2026-03-05,Menestra,250,240\n";
        $archivo = UploadedFile::fake()->createWithContent('ficha5_mixta.csv', $csv);

        $response = $this->actingAs($user)->post(route('control-nutricional.importar'), ['archivo' => $archivo]);

        $response->assertSessionHas('warning');
        $this->assertDatabaseCount('controles_nutricionales', 1);
        $this->assertDatabaseHas('controles_nutricionales', ['menu_dia' => 'Menestra']);
    }

    public function test_nutricional_rechaza_archivo_sin_columnas_obligatorias(): void
    {
        $user = User::factory()->investigador()->create();

        $csv = "columna_random\nvalor\n";
        $archivo = UploadedFile::fake()->createWithContent('sin_columnas.csv', $csv);

        $response = $this->actingAs($user)->post(route('control-nutricional.importar'), ['archivo' => $archivo]);

        $response->assertSessionHasErrors('archivo');
    }

    public function test_nutricional_procesa_fecha_serial_de_excel(): void
    {
        $user = User::factory()->investigador()->create();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['fecha', 'menu_dia', 'gramos_planificados', 'gramos_servidos'],
            [45718, 'Puré con pollo', 250, 245], // serial de Excel para 2025-03-01 aprox
        ]);
        $ruta = tempnam(sys_get_temp_dir(), 'test') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($ruta);
        $archivo = new UploadedFile($ruta, 'ficha5.xlsx', null, null, true);

        $response = $this->actingAs($user)->post(route('control-nutricional.importar'), ['archivo' => $archivo]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('controles_nutricionales', ['menu_dia' => 'Puré con pollo']);
    }

    public function test_nutricional_plantilla_se_descarga_correctamente(): void
    {
        $user = User::factory()->investigador()->create();

        $response = $this->actingAs($user)->get(route('control-nutricional.plantilla'));

        $response->assertOk();
    }

    // ── Ficha 6 (Control Distribución) ──────────────────────────────────

    public function test_distribucion_acepta_encabezados_alternativos(): void
    {
        $user = User::factory()->investigador()->create();

        $csv = "Fecha,Kg Merma,Kg Distribuido,Tiempo Min\n"
             . "2026-03-02,3,60,20\n";
        $archivo = UploadedFile::fake()->createWithContent('ficha6.csv', $csv);

        $response = $this->actingAs($user)->post(route('control-distribucion.importar'), ['archivo' => $archivo]);

        $response->assertRedirect(route('control-distribucion.index'));
        $this->assertDatabaseHas('controles_distribucion', ['kg_desperdiciados' => 3, 'kg_distribuidos' => 60]);
    }

    public function test_distribucion_reporta_filas_con_kg_distribuidos_cero(): void
    {
        $user = User::factory()->investigador()->create();

        $csv = "fecha,kg_desperdiciados,kg_distribuidos\n"
             . "2026-03-02,1,0\n"
             . "2026-03-03,2,40\n";
        $archivo = UploadedFile::fake()->createWithContent('ficha6_mixta.csv', $csv);

        $response = $this->actingAs($user)->post(route('control-distribucion.importar'), ['archivo' => $archivo]);

        $response->assertSessionHas('warning');
        $this->assertDatabaseCount('controles_distribucion', 1);
    }

    public function test_distribucion_plantilla_se_descarga_correctamente(): void
    {
        $user = User::factory()->investigador()->create();

        $response = $this->actingAs($user)->get(route('control-distribucion.plantilla'));

        $response->assertOk();
    }
}
