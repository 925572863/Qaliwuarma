<?php

namespace App\Http\Controllers;

use App\Models\PecosaPrimaria;
use App\Services\GeminiVisionService;
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
                  ->orWhere('lote', 'like', "%{$b}%")
                  ->orWhere('nombre_pecosa', 'like', "%{$b}%");
            });
        }

        if ($request->filled('pecosa')) {
            $query->where('nombre_pecosa', $request->input('pecosa'));
        }

        $items = $query->orderBy('descripcion')->paginate(20)->withQueryString();

        // Totales generales (sin paginar, sobre todos los productos registrados,
        // no solo los de la pagina actual ni los filtrados por la busqueda).
        $totalProductos       = PecosaPrimaria::count();
        $totalProductosUnicos = PecosaPrimaria::distinct()->count('descripcion');
        $totalUnidades        = PecosaPrimaria::sum('cant');

        // Lista de Pecosas distintas ya subidas, para el filtro.
        $pecosasSubidas = PecosaPrimaria::whereNotNull('nombre_pecosa')
            ->distinct()->orderByDesc('fecha_entrega')->pluck('nombre_pecosa');

        return view('pecosa.primaria.index', compact(
            'items', 'totalProductos', 'totalProductosUnicos', 'totalUnidades', 'pecosasSubidas'
        ));
    }

    public function create()
    {
        return view('pecosa.primaria.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_pecosa'        => 'nullable|string|max:150',
            'fecha_entrega'        => 'nullable|date',
            'filas.*.cant'         => 'required|integer|min:1',
            'filas.*.unid'         => 'required|string|max:20',
            'filas.*.descripcion'  => 'required|string|max:300',
            'filas.*.marca'        => 'nullable|string|max:150',
            'filas.*.presentacion' => 'required|numeric|min:0.001',
            'filas.*.lote'              => 'nullable|string|max:200',
            'filas.*.fecha_vencimiento' => 'nullable|date',
        ]);

        $filas         = $request->input('filas', []);
        $nombrePecosa  = $request->input('nombre_pecosa') ?: null;
        $fechaEntrega  = $request->input('fecha_entrega') ?: null;
        $now           = now();
        $nuevos        = 0;
        $sumados       = 0;

        foreach ($filas as $fila) {
            if (empty($fila['descripcion'])) continue;

            $cant         = (int) $fila['cant'];
            $unid         = strtoupper($fila['unid']);
            $descripcion  = strtoupper($fila['descripcion']);
            $marca        = isset($fila['marca']) && $fila['marca'] !== '' ? strtoupper($fila['marca']) : null;
            $presentacion = (float) $fila['presentacion'];
            $lote         = isset($fila['lote']) && $fila['lote'] !== '' ? $fila['lote'] : null;
            $fechaVenc    = isset($fila['fecha_vencimiento']) && $fila['fecha_vencimiento'] !== '' ? $fila['fecha_vencimiento'] : null;

            if ($this->sumarSiYaExiste('pecosa_primaria', $descripcion, $marca, $lote, $nombrePecosa, $cant, $presentacion, $now)) {
                $sumados++;
            } else {
                $volumen = round($cant * $presentacion, 3);
                \Illuminate\Support\Facades\DB::table('pecosa_primaria')->insert([
                    'nombre_pecosa'     => $nombrePecosa,
                    'fecha_entrega'     => $fechaEntrega,
                    'cant'              => $cant,
                    'unid'              => $unid,
                    'descripcion'       => $descripcion,
                    'marca'             => $marca,
                    'presentacion'      => $presentacion,
                    'volumen'           => $volumen,
                    'stock_actual'      => $volumen,
                    'lote'              => $lote,
                    'fecha_vencimiento' => $fechaVenc,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);
                $nuevos++;
            }
        }

        $msg = "{$nuevos} producto(s) registrado(s).";
        if ($sumados > 0) $msg .= " {$sumados} ya existían y se sumó su cantidad al stock (no se duplicaron).";

        return redirect()->route('pecosa.primaria.index')->with('success', $msg);
    }

    public function edit(PecosaPrimaria $primarium)
    {
        return view('pecosa.primaria.edit', ['item' => $primarium]);
    }

    public function update(Request $request, PecosaPrimaria $primarium)
    {
        $data = $request->validate([
            'nombre_pecosa' => 'nullable|string|max:150',
            'fecha_entrega' => 'nullable|date',
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
            'archivo'       => 'required|file|mimes:xlsx,xls,csv|max:5120',
            'nombre_pecosa' => 'nullable|string|max:150',
            'fecha_entrega' => 'nullable|date',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('archivo')->getPathname());
            $hoja        = $spreadsheet->getActiveSheet();
            $filas       = $hoja->toArray(null, true, true, false);

            $nombrePecosa = $request->input('nombre_pecosa') ?: null;
            $fechaEntrega = $request->input('fecha_entrega') ?: null;
            $now     = now();
            $errores = [];
            $nuevos  = 0;
            $sumados = 0;

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

                if ($this->sumarSiYaExiste('pecosa_primaria', $descripcion, $marca, $lote, $nombrePecosa, $cant, $presentacion, $now)) {
                    $sumados++;
                } else {
                    $volumen = round($cant * $presentacion, 3);
                    \Illuminate\Support\Facades\DB::table('pecosa_primaria')->insert([
                        'nombre_pecosa' => $nombrePecosa,
                        'fecha_entrega' => $fechaEntrega,
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
                    ]);
                    $nuevos++;
                }
            }

            $msg = "{$nuevos} producto(s) importado(s).";
            if ($sumados > 0) $msg .= " {$sumados} ya existían (mismo producto/marca/lote/pecosa) y se sumó la cantidad, sin duplicar.";
            if (!empty($errores)) {
                $msg .= ' ' . count($errores) . ' fila(s) con error ignoradas.';
            }

            return redirect()->route('pecosa.primaria.index')->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Error al leer el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Igual que importar(), pero en vez de leer un Excel, lee los productos
     * de una foto de la Pecosa usando IA con visión (Gemini).
     */
    public function importarFoto(Request $request)
    {
        $request->validate([
            'foto'          => 'required|image|max:8192',
            'nombre_pecosa' => 'nullable|string|max:150',
            'fecha_entrega' => 'nullable|date',
        ]);

        try {
            $vision = new GeminiVisionService();
            if (!$vision->configurado()) {
                return back()->with('error', 'La lectura de fotos con IA aún no está configurada en el servidor.');
            }

            $productos = $vision->extraerProductosDeImagen($request->file('foto')->getPathname());

            if (empty($productos)) {
                return back()->with('error', 'No se reconoció ningún producto en la foto. Intenta con una foto más clara y bien iluminada.');
            }

            $nombrePecosa = $request->input('nombre_pecosa') ?: null;
            $fechaEntrega = $request->input('fecha_entrega') ?: null;
            $now     = now();
            $nuevos  = 0;
            $sumados = 0;

            foreach ($productos as $p) {
                if ($this->sumarSiYaExiste('pecosa_primaria', $p['descripcion'], $p['marca'], $p['lote'], $nombrePecosa, $p['cant'], $p['presentacion'], $now)) {
                    $sumados++;
                } else {
                    $volumen = round($p['cant'] * $p['presentacion'], 3);
                    \Illuminate\Support\Facades\DB::table('pecosa_primaria')->insert([
                        'nombre_pecosa' => $nombrePecosa,
                        'fecha_entrega' => $fechaEntrega,
                        'cant'         => $p['cant'],
                        'unid'         => $p['unid'],
                        'descripcion'  => $p['descripcion'],
                        'marca'        => $p['marca'],
                        'presentacion' => $p['presentacion'],
                        'volumen'      => $volumen,
                        'stock_actual' => $volumen,
                        'lote'         => $p['lote'],
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);
                    $nuevos++;
                }
            }

            $msg = "{$nuevos} producto(s) reconocido(s) e importado(s) desde la foto.";
            if ($sumados > 0) $msg .= " {$sumados} ya existían y se sumó la cantidad.";
            $msg .= ' Revisa los datos por si la IA se equivocó en algo.';

            return redirect()->route('pecosa.primaria.index')->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Evita duplicar un producto que ya existe (mismo descripcion+marca+lote+
     * nombre_pecosa): en vez de insertar una fila nueva, suma la cantidad al
     * registro existente (cant, volumen y stock_actual). El nombre de la
     * Pecosa entra en la comparación para que subir una Pecosa nueva nunca se
     * mezcle con una anterior de nombre distinto. Devuelve true si sumó a uno
     * existente, false si no encontró coincidencia (debe insertarse aparte).
     */
    private function sumarSiYaExiste(string $tabla, string $descripcion, ?string $marca, ?string $lote, ?string $nombrePecosa, int $cant, float $presentacion, $now): bool
    {
        $existente = \Illuminate\Support\Facades\DB::table($tabla)
            ->where('descripcion', $descripcion)
            ->where(function ($q) use ($marca) {
                $marca === null ? $q->whereNull('marca') : $q->where('marca', $marca);
            })
            ->where(function ($q) use ($lote) {
                $lote === null ? $q->whereNull('lote') : $q->where('lote', $lote);
            })
            ->where(function ($q) use ($nombrePecosa) {
                $nombrePecosa === null ? $q->whereNull('nombre_pecosa') : $q->where('nombre_pecosa', $nombrePecosa);
            })
            ->first();

        if (!$existente) return false;

        $volumenNuevo = round($cant * $presentacion, 3);
        \Illuminate\Support\Facades\DB::table($tabla)->where('id', $existente->id)->update([
            'cant'         => $existente->cant + $cant,
            'volumen'      => round($existente->volumen + $volumenNuevo, 3),
            'stock_actual' => round($existente->stock_actual + $volumenNuevo, 3),
            'updated_at'   => $now,
        ]);

        return true;
    }
}
