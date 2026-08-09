<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ImportaExcel;
use App\Models\ControlNutricional;
use Illuminate\Http\Request;

/**
 * Ficha de registro N.° 5 (VD) — Precisión en el cálculo de raciones nutricionales.
 */
class ControlNutricionalController extends Controller
{
    use ImportaExcel;

    public function index()
    {
        $registros = ControlNutricional::orderByDesc('fecha')->orderByDesc('id')->paginate(20);

        $totalEvaluadas = ControlNutricional::count();
        $totalCumplen   = ControlNutricional::where('cumple_requerimiento', true)->count();
        $pctCumplimiento = $totalEvaluadas > 0 ? round(($totalCumplen / $totalEvaluadas) * 100, 2) : null;

        // Comparación pretest-postest (diseño preexperimental de la tesis)
        $comparativo = [];
        foreach (['pretest', 'postest'] as $fase) {
            $total   = ControlNutricional::where('fase', $fase)->count();
            $cumplen = ControlNutricional::where('fase', $fase)->where('cumple_requerimiento', true)->count();
            $comparativo[$fase] = [
                'total'    => $total,
                'pct'      => $total > 0 ? round(($cumplen / $total) * 100, 2) : null,
            ];
        }

        return view('control-nutricional.index', compact('registros', 'pctCumplimiento', 'totalEvaluadas', 'comparativo'));
    }

    public function create()
    {
        return view('control-nutricional.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha'               => 'required|date|before_or_equal:today',
            'nivel'               => 'required|in:inicial,primaria',
            'fase'                => 'required|in:pretest,postest',
            'menu_dia'            => 'required|string|max:150',
            'gramos_planificados' => 'required|numeric|min:0|max:2000',
            'gramos_servidos'     => 'required|numeric|min:0|max:2000',
        ]);

        // Tolerancia del 10% respecto a lo planificado para considerar que cumple el requerimiento
        $tolerancia = $data['gramos_planificados'] * 0.10;
        $data['cumple_requerimiento'] = abs($data['gramos_planificados'] - $data['gramos_servidos']) <= $tolerancia;

        ControlNutricional::create($data);

        return redirect()->route('control-nutricional.index')
            ->with('success', 'Control nutricional registrado correctamente.');
    }

    public function destroy(ControlNutricional $control_nutricional)
    {
        $control_nutricional->delete();
        return back()->with('success', 'Registro eliminado.');
    }

    /**
     * Carga masiva desde Excel/CSV, para poblar la ficha 5 con los 180 registros
     * del periodo lectivo sin capturarlos uno por uno. Tolera variantes de
     * nombre de columna y acentos (ver ImportaExcel::buscarColumna) y reporta
     * fila por fila los registros que no pudo interpretar, en vez de saltarlos
     * en silencio.
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
        $colMenu  = $this->buscarColumna($headers, ['menu_dia', 'menu', 'menu_del_dia', 'plato']);
        $colGP    = $this->buscarColumna($headers, ['gramos_planificados', 'gr_planificados', 'planificado', 'gramos_plan']);
        $colGS    = $this->buscarColumna($headers, ['gramos_servidos', 'gr_servidos', 'servido', 'gramos_serv']);

        if ($colFecha === false || $colGP === false || $colGS === false) {
            return back()->withErrors(['archivo' =>
                'No se encontraron las columnas obligatorias (fecha, gramos_planificados, gramos_servidos). '
                . 'Encabezados detectados: ' . implode(', ', array_filter($headers)) . '. '
                . 'Descarga la plantilla para ver el formato exacto.',
            ]);
        }

        $importados = 0;
        $errores = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $numeroFila = $i + 1; // +1 porque los índices de PhpSpreadsheet arrancan en 0 y la fila 1 es el encabezado
            if (trim(implode('', array_map('strval', $row))) === '') continue;

            $fecha = $this->parsearFechaCelda($row[$colFecha] ?? null);
            if ($fecha === null) {
                $errores[] = "Fila {$numeroFila}: no se pudo interpretar la fecha ('" . ($row[$colFecha] ?? '') . "').";
                continue;
            }

            if (!is_numeric($row[$colGP] ?? null) || !is_numeric($row[$colGS] ?? null)) {
                $errores[] = "Fila {$numeroFila}: gramos_planificados/gramos_servidos deben ser numéricos.";
                continue;
            }

            $gp = (float) $row[$colGP];
            $gs = (float) $row[$colGS];
            $nivel = $colNivel !== false ? mb_strtolower(trim((string) $row[$colNivel])) : 'primaria';

            if (!in_array($nivel, ['inicial', 'primaria'], true)) {
                $errores[] = "Fila {$numeroFila}: nivel '{$nivel}' inválido (debe ser 'inicial' o 'primaria'), se usó 'primaria'.";
                $nivel = 'primaria';
            }

            try {
                ControlNutricional::create([
                    'fecha'    => $fecha,
                    'nivel'    => $nivel,
                    'fase'     => $colFase !== false && mb_strtolower(trim((string) $row[$colFase])) === 'postest' ? 'postest' : 'pretest',
                    'menu_dia' => $colMenu !== false ? trim((string) $row[$colMenu]) : 'Sin especificar',
                    'gramos_planificados'  => $gp,
                    'gramos_servidos'      => $gs,
                    'cumple_requerimiento' => $gp > 0 && abs($gp - $gs) <= $gp * 0.10,
                ]);
                $importados++;
            } catch (\Exception $e) {
                $errores[] = "Fila {$numeroFila}: " . $e->getMessage();
            }
        }

        $mensaje = "Se importaron {$importados} registros nutricionales.";
        if ($errores) {
            $mensaje .= ' Filas con problemas (' . count($errores) . '): ' . implode(' | ', array_slice($errores, 0, 5));
            if (count($errores) > 5) {
                $mensaje .= ' … y ' . (count($errores) - 5) . ' más.';
            }
        }

        return redirect()->route('control-nutricional.index')
            ->with($errores ? 'warning' : 'success', $mensaje);
    }

    /**
     * Plantilla Excel descargable con los encabezados exactos esperados,
     * para evitar el desajuste de columnas al importar.
     */
    public function plantilla()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['fecha', 'nivel', 'fase', 'menu_dia', 'gramos_planificados', 'gramos_servidos'],
            [now()->toDateString(), 'primaria', 'pretest', 'Arroz con pollo', 300, 285],
        ]);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $rutaTemporal = tempnam(sys_get_temp_dir(), 'plantilla_nutricional') . '.xlsx';
        $writer->save($rutaTemporal);

        return response()->download($rutaTemporal, 'plantilla_ficha5_nutricional.xlsx')->deleteFileAfterSend(true);
    }
}
