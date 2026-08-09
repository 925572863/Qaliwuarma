<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ImportaExcel;
use App\Models\ControlDistribucion;
use Illuminate\Http\Request;

/**
 * Ficha de registro N.° 6 (VD) — Eficiencia en la distribución y control del desperdicio.
 */
class ControlDistribucionController extends Controller
{
    use ImportaExcel;

    public function index()
    {
        $registros = ControlDistribucion::orderByDesc('fecha')->orderByDesc('id')->paginate(20);

        $totalKgDesperdiciados = (float) ControlDistribucion::sum('kg_desperdiciados');
        $totalKgDistribuidos   = (float) ControlDistribucion::sum('kg_distribuidos');
        $indiceMermasPromedio  = $totalKgDistribuidos > 0
            ? round(($totalKgDesperdiciados / $totalKgDistribuidos) * 100, 2)
            : null;
        $tiempoPromedio = ControlDistribucion::avg('tiempo_distribucion_min');

        // Comparación pretest-postest
        $comparativo = [];
        foreach (['pretest', 'postest'] as $fase) {
            $kgDesp = (float) ControlDistribucion::where('fase', $fase)->sum('kg_desperdiciados');
            $kgDist = (float) ControlDistribucion::where('fase', $fase)->sum('kg_distribuidos');
            $comparativo[$fase] = [
                'indice_mermas' => $kgDist > 0 ? round(($kgDesp / $kgDist) * 100, 2) : null,
                'tiempo_prom'   => ControlDistribucion::where('fase', $fase)->avg('tiempo_distribucion_min'),
            ];
        }

        return view('control-distribucion.index', compact(
            'registros', 'indiceMermasPromedio', 'tiempoPromedio', 'comparativo'
        ));
    }

    public function create()
    {
        return view('control-distribucion.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha'                   => 'required|date|before_or_equal:today',
            'nivel'                   => 'required|in:inicial,primaria',
            'fase'                    => 'required|in:pretest,postest',
            'kg_desperdiciados'       => 'required|numeric|min:0|max:1000',
            'kg_distribuidos'         => 'required|numeric|min:0.01|max:5000',
            'tiempo_distribucion_min' => 'required|integer|min:0|max:600',
        ]);

        ControlDistribucion::create($data);

        return redirect()->route('control-distribucion.index')
            ->with('success', 'Control de distribución registrado correctamente.');
    }

    public function destroy(ControlDistribucion $control_distribucion)
    {
        $control_distribucion->delete();
        return back()->with('success', 'Registro eliminado.');
    }

    /**
     * Carga masiva desde Excel/CSV para la ficha 6. Tolera variantes de nombre
     * de columna y acentos, y reporta fila por fila los registros que no pudo
     * interpretar en vez de saltarlos en silencio.
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('archivo')->getPathname());
            $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, false);
        } catch (\Exception $e) {
            return back()->withErrors(['archivo' => 'No se pudo leer el archivo: ' . $e->getMessage()]);
        }

        $headers = array_map(fn($h) => $this->normalizarEncabezado($h), $rows[0] ?? []);

        $colFecha = $this->buscarColumna($headers, ['fecha', 'fecha_registro', 'dia']);
        $colNivel = $this->buscarColumna($headers, ['nivel']);
        $colFase  = $this->buscarColumna($headers, ['fase', 'periodo', 'etapa', 'momento']);
        $colKgD   = $this->buscarColumna($headers, ['kg_desperdiciados', 'kg_desperdicio', 'desperdicio_kg', 'merma_kg', 'kg_merma']);
        $colKgT   = $this->buscarColumna($headers, ['kg_distribuidos', 'kg_distribuido', 'distribuido_kg']);
        $colTmin  = $this->buscarColumna($headers, ['tiempo_distribucion_min', 'tiempo_min', 'minutos', 'tiempo']);

        if ($colFecha === false || $colKgD === false || $colKgT === false) {
            return back()->withErrors(['archivo' =>
                'No se encontraron las columnas obligatorias (fecha, kg_desperdiciados, kg_distribuidos). '
                . 'Encabezados detectados: ' . implode(', ', array_filter($headers)) . '. '
                . 'Descarga la plantilla para ver el formato exacto.',
            ]);
        }

        $importados = 0;
        $errores = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $numeroFila = $i + 1;
            if (trim(implode('', array_map('strval', $row))) === '') continue;

            $fecha = $this->parsearFechaCelda($row[$colFecha] ?? null);
            if ($fecha === null) {
                $errores[] = "Fila {$numeroFila}: no se pudo interpretar la fecha ('" . ($row[$colFecha] ?? '') . "').";
                continue;
            }

            if (!is_numeric($row[$colKgD] ?? null) || !is_numeric($row[$colKgT] ?? null)) {
                $errores[] = "Fila {$numeroFila}: kg_desperdiciados/kg_distribuidos deben ser numéricos.";
                continue;
            }

            $kgDist = (float) $row[$colKgT];
            if ($kgDist <= 0) {
                $errores[] = "Fila {$numeroFila}: kg_distribuidos debe ser mayor a 0.";
                continue;
            }

            $nivel = $colNivel !== false ? mb_strtolower(trim((string) $row[$colNivel])) : 'primaria';
            if (!in_array($nivel, ['inicial', 'primaria'], true)) {
                $errores[] = "Fila {$numeroFila}: nivel '{$nivel}' inválido, se usó 'primaria'.";
                $nivel = 'primaria';
            }

            try {
                ControlDistribucion::create([
                    'fecha' => $fecha,
                    'nivel' => $nivel,
                    'fase'  => $colFase !== false && mb_strtolower(trim((string) $row[$colFase])) === 'postest' ? 'postest' : 'pretest',
                    'kg_desperdiciados'       => (float) $row[$colKgD],
                    'kg_distribuidos'         => $kgDist,
                    'tiempo_distribucion_min' => $colTmin !== false ? (int) ($row[$colTmin] ?? 0) : 0,
                ]);
                $importados++;
            } catch (\Exception $e) {
                $errores[] = "Fila {$numeroFila}: " . $e->getMessage();
            }
        }

        $mensaje = "Se importaron {$importados} registros de distribución.";
        if ($errores) {
            $mensaje .= ' Filas con problemas (' . count($errores) . '): ' . implode(' | ', array_slice($errores, 0, 5));
            if (count($errores) > 5) {
                $mensaje .= ' … y ' . (count($errores) - 5) . ' más.';
            }
        }

        return redirect()->route('control-distribucion.index')
            ->with($errores ? 'warning' : 'success', $mensaje);
    }

    /**
     * Plantilla Excel descargable con los encabezados exactos esperados.
     */
    public function plantilla()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['fecha', 'nivel', 'fase', 'kg_desperdiciados', 'kg_distribuidos', 'tiempo_distribucion_min'],
            [now()->toDateString(), 'primaria', 'pretest', 2.5, 50, 25],
        ]);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $rutaTemporal = tempnam(sys_get_temp_dir(), 'plantilla_distribucion') . '.xlsx';
        $writer->save($rutaTemporal);

        return response()->download($rutaTemporal, 'plantilla_ficha6_distribucion.xlsx')->deleteFileAfterSend(true);
    }
}
