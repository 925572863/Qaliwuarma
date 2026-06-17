<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\PecosaInicial;
use App\Models\RecetaNutricional;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PecosaInicialController extends Controller
{
    public function index(Request $request)
    {
        $query = PecosaInicial::query();

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function ($q) use ($b) {
                $q->where('descripcion', 'like', "%{$b}%")
                  ->orWhere('marca', 'like', "%{$b}%")
                  ->orWhere('lote', 'like', "%{$b}%");
            });
        }

        $items = $query->orderBy('descripcion')->paginate(20)->withQueryString();

        return view('pecosa.inicial.index', compact('items'));
    }

    public function create()
    {
        return view('pecosa.inicial.create');
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
            $inserts[] = [
                'cant'              => (int) $fila['cant'],
                'unid'              => strtoupper($fila['unid']),
                'descripcion'       => strtoupper($fila['descripcion']),
                'marca'             => isset($fila['marca']) && $fila['marca'] !== '' ? strtoupper($fila['marca']) : null,
                'presentacion'      => (float) $fila['presentacion'],
                'volumen'           => round((int) $fila['cant'] * (float) $fila['presentacion'], 3),
                'lote'              => isset($fila['lote']) && $fila['lote'] !== '' ? $fila['lote'] : null,
                'fecha_vencimiento' => isset($fila['fecha_vencimiento']) && $fila['fecha_vencimiento'] !== '' ? $fila['fecha_vencimiento'] : null,
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }

        if (!empty($inserts)) {
            \Illuminate\Support\Facades\DB::table('pecosa_inicial')->insert($inserts);
        }

        return redirect()->route('pecosa.inicial.index')
            ->with('success', count($inserts) . ' producto(s) registrado(s) exitosamente.');
    }

    public function edit(PecosaInicial $inicial)
    {
        return view('pecosa.inicial.edit', ['item' => $inicial]);
    }

    public function update(Request $request, PecosaInicial $inicial)
    {
        $data = $request->validate([
            'cant'         => 'required|integer|min:1',
            'unid'         => 'required|string|max:20',
            'descripcion'  => 'required|string|max:300',
            'marca'        => 'nullable|string|max:150',
            'presentacion' => 'required|numeric|min:0.001',
            'lote'              => 'nullable|string|max:200',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        $data['volumen'] = round($data['cant'] * $data['presentacion'], 3);

        $inicial->update($data);

        return redirect()->route('pecosa.inicial.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    public function destroy(PecosaInicial $inicial)
    {
        $inicial->delete();
        return redirect()->route('pecosa.inicial.index')
            ->with('success', 'Producto eliminado.');
    }

    public function nutricion(Request $request)
    {
        $ids    = $request->input('productos', []);
        $receta = $request->input('receta', '');

        $query = PecosaInicial::orderBy('descripcion');
        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }
        $productos = $query->get(['id', 'descripcion', 'presentacion', 'cant', 'marca'])->toArray();

        if (empty($productos)) {
            return response()->json(['error' => 'No hay productos seleccionados.'], 422);
        }

        $totalAlumnos = Alumno::where('nivel', 'inicial')->where('estado', 'activo')->count();
        if ($totalAlumnos === 0) $totalAlumnos = 1;

        $resultado = (new GeminiService())->calcularNutricion($productos, $totalAlumnos, $receta);

        if (isset($resultado['__error']) || empty($resultado)) {
            return response()->json(['error' => 'Error al consultar la IA', 'debug' => $resultado], 422);
        }

        // Guardar en BD para usar en Predicciones
        foreach ($resultado as $item) {
            if (empty($item['descripcion'])) continue;
            RecetaNutricional::updateOrCreate(
                ['producto' => strtoupper($item['descripcion'])],
                [
                    'gramos_racion'       => $item['gramos_racion'] ?? 0,
                    'calorias_racion'     => $item['calorias_racion'] ?? 0,
                    'proteinas_racion'    => $item['proteinas_racion'] ?? 0,
                    'carbohidratos_racion'=> $item['carbohidratos_racion'] ?? 0,
                    'preparacion'         => $item['preparacion'] ?? null,
                    'tiempo_preparacion'  => $item['tiempo_preparacion'] ?? null,
                ]
            );
        }

        return response()->json($resultado);
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
                if ($idx === 0) continue; // saltar encabezado
                if (empty(trim((string)($fila[2] ?? '')))) continue; // saltar filas vacías

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

                $inserts[] = [
                    'cant'         => $cant,
                    'unid'         => $unid,
                    'descripcion'  => $descripcion,
                    'marca'        => $marca,
                    'presentacion' => $presentacion,
                    'volumen'      => round($cant * $presentacion, 3),
                    'lote'         => $lote,
                    'created_at'   => $now,
                    'updated_at'   => $now,
                ];
            }

            if (!empty($inserts)) {
                \Illuminate\Support\Facades\DB::table('pecosa_inicial')->insert($inserts);
            }

            $msg = count($inserts) . ' producto(s) importado(s) correctamente.';
            if (!empty($errores)) {
                $msg .= ' ' . count($errores) . ' fila(s) con error ignoradas.';
            }

            return redirect()->route('pecosa.inicial.index')->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al leer el archivo: ' . $e->getMessage());
        }
    }
}
