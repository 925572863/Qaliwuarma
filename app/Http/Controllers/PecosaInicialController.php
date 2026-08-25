<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\PecosaInicial;
use App\Models\RecetaNutricional;
use App\Services\GeminiService;
use App\Services\GeminiVisionService;
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

        // Totales generales (sin paginar, sobre todos los productos registrados,
        // no solo los de la pagina actual ni los filtrados por la busqueda).
        $totalProductos       = PecosaInicial::count();
        $totalProductosUnicos = PecosaInicial::distinct()->count('descripcion');
        $totalUnidades        = PecosaInicial::sum('cant');

        return view('pecosa.inicial.index', compact('items', 'totalProductos', 'totalProductosUnicos', 'totalUnidades'));
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

        $filas  = $request->input('filas', []);
        $now    = now();
        $nuevos = 0;
        $sumados = 0;

        foreach ($filas as $fila) {
            if (empty($fila['descripcion'])) continue;

            $cant         = (int) $fila['cant'];
            $unid         = strtoupper($fila['unid']);
            $descripcion  = strtoupper($fila['descripcion']);
            $marca        = isset($fila['marca']) && $fila['marca'] !== '' ? strtoupper($fila['marca']) : null;
            $presentacion = (float) $fila['presentacion'];
            $lote         = isset($fila['lote']) && $fila['lote'] !== '' ? $fila['lote'] : null;
            $fechaVenc    = isset($fila['fecha_vencimiento']) && $fila['fecha_vencimiento'] !== '' ? $fila['fecha_vencimiento'] : null;

            if ($this->sumarSiYaExiste('pecosa_inicial', $descripcion, $marca, $lote, $cant, $presentacion, $now)) {
                $sumados++;
            } else {
                \Illuminate\Support\Facades\DB::table('pecosa_inicial')->insert([
                    'cant'              => $cant,
                    'unid'              => $unid,
                    'descripcion'       => $descripcion,
                    'marca'             => $marca,
                    'presentacion'      => $presentacion,
                    'volumen'           => round($cant * $presentacion, 3),
                    'stock_actual'      => round($cant * $presentacion, 3),
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

        return redirect()->route('pecosa.inicial.index')->with('success', $msg);
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
        $receta = (string) $request->input('receta', '');

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

    /**
     * Plantilla Excel descargable con el orden de columnas exacto que espera
     * importar() (por posición, no por nombre de encabezado): cant, unid,
     * descripcion, marca, presentacion, lote. El volumen y el stock inicial
     * se calculan automáticamente al importar (cant × presentacion).
     */
    public function plantilla()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['cant', 'unid', 'descripcion', 'marca', 'presentacion', 'lote'],
            [10, 'BOTELLA', 'ACEITE VEGETAL COMESTIBLE', 'SAO', 1.000, '13/03/27'],
        ]);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $rutaTemporal = tempnam(sys_get_temp_dir(), 'plantilla_pecosa_inicial') . '.xlsx';
        $writer->save($rutaTemporal);

        return response()->download($rutaTemporal, 'plantilla_pecosa_inicial.xlsx')->deleteFileAfterSend(true);
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

            $now     = now();
            $errores = [];
            $nuevos  = 0;
            $sumados = 0;

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

                if ($this->sumarSiYaExiste('pecosa_inicial', $descripcion, $marca, $lote, $cant, $presentacion, $now)) {
                    $sumados++;
                } else {
                    \Illuminate\Support\Facades\DB::table('pecosa_inicial')->insert([
                        'cant'         => $cant,
                        'unid'         => $unid,
                        'descripcion'  => $descripcion,
                        'marca'        => $marca,
                        'presentacion' => $presentacion,
                        'volumen'      => round($cant * $presentacion, 3),
                        'stock_actual' => round($cant * $presentacion, 3),
                        'lote'         => $lote,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);
                    $nuevos++;
                }
            }

            $msg = "{$nuevos} producto(s) importado(s).";
            if ($sumados > 0) $msg .= " {$sumados} ya existían (mismo producto/marca/lote) y se sumó la cantidad, sin duplicar.";
            if (!empty($errores)) {
                $msg .= ' ' . count($errores) . ' fila(s) con error ignoradas.';
            }

            return redirect()->route('pecosa.inicial.index')->with('success', $msg);

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
            'foto' => 'required|image|max:8192',
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

            $now     = now();
            $nuevos  = 0;
            $sumados = 0;

            foreach ($productos as $p) {
                if ($this->sumarSiYaExiste('pecosa_inicial', $p['descripcion'], $p['marca'], $p['lote'], $p['cant'], $p['presentacion'], $now)) {
                    $sumados++;
                } else {
                    \Illuminate\Support\Facades\DB::table('pecosa_inicial')->insert([
                        'cant'         => $p['cant'],
                        'unid'         => $p['unid'],
                        'descripcion'  => $p['descripcion'],
                        'marca'        => $p['marca'],
                        'presentacion' => $p['presentacion'],
                        'volumen'      => round($p['cant'] * $p['presentacion'], 3),
                        'stock_actual' => round($p['cant'] * $p['presentacion'], 3),
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

            return redirect()->route('pecosa.inicial.index')->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Evita duplicar un producto que ya existe (mismo descripcion+marca+lote):
     * en vez de insertar una fila nueva, suma la cantidad al registro
     * existente (cant, volumen y stock_actual). Devuelve true si sumó a uno
     * existente, false si no encontró coincidencia (debe insertarse aparte).
     */
    private function sumarSiYaExiste(string $tabla, string $descripcion, ?string $marca, ?string $lote, int $cant, float $presentacion, $now): bool
    {
        $existente = \Illuminate\Support\Facades\DB::table($tabla)
            ->where('descripcion', $descripcion)
            ->where(function ($q) use ($marca) {
                $marca === null ? $q->whereNull('marca') : $q->where('marca', $marca);
            })
            ->where(function ($q) use ($lote) {
                $lote === null ? $q->whereNull('lote') : $q->where('lote', $lote);
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
