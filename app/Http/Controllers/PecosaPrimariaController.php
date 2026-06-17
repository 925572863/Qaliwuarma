<?php

namespace App\Http\Controllers;

use App\Models\PecosaPrimaria;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PecosaPrimariaController extends Controller
{
    public function index(Request $request)
    {
        $query = PecosaPrimaria::query();

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('descripcion', 'like', "%{$b}%")
                  ->orWhere('marca', 'like', "%{$b}%")
                  ->orWhere('lote', 'like', "%{$b}%");
            });
        }

        $items = $query->orderBy('descripcion')->paginate(20)->withQueryString();

        return view('pecosa.primaria.index', compact('items'));
    }

    public function create()
    {
        return view('pecosa.primaria.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'filas.*.cant'         => 'required|integer|min:1',
            'filas.*.unid'         => 'required|string|max:20',
            'filas.*.descripcion'  => 'required|string|max:300',
            'filas.*.marca'        => 'nullable|string|max:150',
            'filas.*.presentacion' => 'required|numeric|min:0.001',
            'filas.*.lote'              => 'nullable|string|max:200',
            'filas.*.fecha_vencimiento' => 'nullable|date',
        ]);

        $filas   = $request->input('filas', []);
        $inserts = [];
        $now     = now();

        foreach ($filas as $fila) {
            if (empty($fila['descripcion'])) continue;
            $volumen   = round((int) $fila['cant'] * (float) $fila['presentacion'], 3);
            $inserts[] = [
                'cant'              => (int) $fila['cant'],
                'unid'              => strtoupper($fila['unid']),
                'descripcion'       => strtoupper($fila['descripcion']),
                'marca'             => isset($fila['marca']) && $fila['marca'] !== '' ? strtoupper($fila['marca']) : null,
                'presentacion'      => (float) $fila['presentacion'],
                'volumen'           => $volumen,
                'stock_actual'      => $volumen,
                'lote'              => isset($fila['lote']) && $fila['lote'] !== '' ? $fila['lote'] : null,
                'fecha_vencimiento' => isset($fila['fecha_vencimiento']) && $fila['fecha_vencimiento'] !== '' ? $fila['fecha_vencimiento'] : null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        if (!empty($inserts)) {
            \Illuminate\Support\Facades\DB::table('pecosa_primaria')->insert($inserts);
        }

        return redirect()->route('pecosa.primaria.index')
            ->with('success', count($inserts) . ' producto(s) registrado(s) exitosamente.');
    }

    public function edit(PecosaPrimaria $primarium)
    {
        return view('pecosa.primaria.edit', ['item' => $primarium]);
    }

    public function update(Request $request, PecosaPrimaria $primarium)
    {
        $data = $request->validate([
            'cant'         => 'required|integer|min:1',
            'unid'         => 'required|string|max:20',
            'descripcion'  => 'required|string|max:300',
            'marca'        => 'nullable|string|max:150',
            'presentacion' => 'required|numeric|min:0.001',
            'lote'              => 'nullable|string|max:200',
            'stock_actual'      => 'nullable|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        $data['volumen'] = round($data['cant'] * $data['presentacion'], 3);

        $primarium->update($data);

        return redirect()->route('pecosa.primaria.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(PecosaPrimaria $primarium)
    {
        $primarium->delete();
        return redirect()->route('pecosa.primaria.index')
            ->with('success', 'Producto eliminado.');
    }

    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('archivo')->getPathname());
            $hoja        = $spreadsheet->getActiveSheet();
            $filas       = $hoja->toArray(null, true, true, false);

            $inserts = [];
            $now     = now();
            $errores = [];

            foreach ($filas as $idx => $fila) {
                if ($idx === 0) continue;
                if (empty(trim((string)($fila[2] ?? '')))) continue;

                $cant         = intval($fila[0] ?? 0);
                $unid         = strtoupper(trim((string)($fila[1] ?? '')));
                $descripcion  = strtoupper(trim((string)($fila[2] ?? '')));
                $marca        = strtoupper(trim((string)($fila[3] ?? ''))) ?: null;
                $presentacion = floatval(str_replace(',', '.', $fila[4] ?? 1));
                $lote         = trim((string)($fila[5] ?? '')) ?: null;

                if ($cant <= 0 || !$unid || !$descripcion || $presentacion <= 0) {
                    $errores[] = "Fila " . ($idx + 1) . ": datos incompletos o inválidos.";
                    continue;
                }

                $volumen   = round($cant * $presentacion, 3);
                $inserts[] = [
                    'cant'         => $cant,
                    'unid'         => $unid,
                    'descripcion'  => $descripcion,
                    'marca'        => $marca,
                    'presentacion' => $presentacion,
                    'volumen'      => $volumen,
                    'stock_actual' => $volumen,
                    'lote'         => $lote,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }

            if (!empty($inserts)) {
                \Illuminate\Support\Facades\DB::table('pecosa_primaria')->insert($inserts);
            }

            $msg = count($inserts) . ' producto(s) importado(s) correctamente.';
            if (!empty($errores)) {
                $msg .= ' ' . count($errores) . ' fila(s) con error ignoradas.';
            }

            return redirect()->route('pecosa.primaria.index')->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al leer el archivo: ' . $e->getMessage());
        }
    }
}
