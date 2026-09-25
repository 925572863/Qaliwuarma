<?php

namespace App\Http\Controllers;

use App\Models\PecosaPrimaria;
use App\Services\OpenAiVisionService;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class PecosaPrimariaController extends Controller
{
    public function index(Request $request)
    {
        // Lista de Pecosas distintas ya subidas (para el filtro y para saber
        // cual es la mas reciente). Se agrupa en PHP, no con SELECT DISTINCT +
        // ORDER BY de otra columna, que PostgreSQL rechaza si esa columna no
        // está en el SELECT.
        $pecosasOrdenadas = PecosaPrimaria::whereNotNull('nombre_pecosa')
            ->select('nombre_pecosa', 'created_at')
            ->get()
            ->groupBy('nombre_pecosa')
            ->map(fn($grupo) => $grupo->max('created_at'))
            ->sortDesc();
        $pecosasSubidas   = $pecosasOrdenadas->keys()->values();
        $pecosaMasReciente = $pecosasSubidas->first();

        // Por defecto (sin buscar ni elegir Pecosa a proposito) solo se
        // muestra la Pecosa mas reciente, para que la anterior no aparezca
        // mezclada en el cuadro. Con "?todas=1" o eligiendo una del filtro
        // se ve el historial completo.
        $viendoSoloReciente = $pecosaMasReciente
            && !$request->filled('buscar')
            && !$request->has('pecosa')
            && !$request->boolean('todas');

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
        } elseif ($viendoSoloReciente) {
            $query->where('nombre_pecosa', $pecosaMasReciente);
        }

        $items = $query->orderBy('descripcion')->paginate(20)->withQueryString();

        // Totales generales (sin paginar, sobre todos los productos registrados,
        // no solo los de la pagina actual ni los filtrados por la busqueda).
        $totalProductos       = PecosaPrimaria::count();
        $totalProductosUnicos = PecosaPrimaria::distinct()->count('descripcion');
        $totalUnidades        = PecosaPrimaria::sum('cant');

        return view('pecosa.primaria.index', compact(
            'items', 'totalProductos', 'totalProductosUnicos', 'totalUnidades',
            'pecosasSubidas', 'pecosaMasReciente', 'viendoSoloReciente'
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

        $filas             = $request->input('filas', []);
        $nombrePecosaInput = $request->input('nombre_pecosa');
        $nombreEsAutomatico = empty($nombrePecosaInput);
        $nombrePecosa      = $nombrePecosaInput ?: $this->nombrePecosaAutomatico();
        $fechaEntrega      = $request->input('fecha_entrega') ?: null;
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

            if ($this->sumarSiYaExiste('pecosa_primaria', $descripcion, $marca, $lote, $nombrePecosa, $nombreEsAutomatico, $cant, $presentacion, $now)) {
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

    public function destroyAll()
    {
        $total = PecosaPrimaria::count();
        PecosaPrimaria::query()->delete();

        return redirect()->route('pecosa.primaria.index')
            ->with('success', "Se eliminaron los {$total} productos de Pecosa Primaria.");
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

            // Detecta la fila de encabezados y la posición real de cada columna
            // por su nombre (ver mismo fix en PecosaInicialController::importar).
            $headerIdx = null;
            $colCant = $colUnid = $colDesc = $colMarca = $colPresent = $colLote = null;
            foreach ($filas as $idx => $fila) {
                $normH = array_map(fn($h) => $this->normalizeHeaderPecosa((string)($h ?? '')), $fila);
                $tieneCant = in_array('cant', $normH, true);
                $tieneDesc = $this->findColPecosa($normH, ['descripcion_de_productos', 'descripcion', 'producto']) !== null;
                if ($tieneCant && $tieneDesc) {
                    $headerIdx  = $idx;
                    $colCant    = $this->findColPecosa($normH, ['cant']);
                    $colUnid    = $this->findColPecosa($normH, ['unid', 'unidad']);
                    $colDesc    = $this->findColPecosa($normH, ['descripcion_de_productos', 'descripcion', 'producto']);
                    $colMarca   = $this->findColPecosa($normH, ['marca']);
                    $colPresent = $this->findColPecosa($normH, ['present', 'presentacion']);
                    $colLote    = $this->findColPecosa($normH, ['lote_lotes', 'lote', 'lotes']);
                    break;
                }
            }
            if ($headerIdx === null) {
                $headerIdx = 0;
                $colCant = 0; $colUnid = 1; $colDesc = 2; $colMarca = 3; $colPresent = 4; $colLote = 5;
            }

            $nombrePecosaInput = $request->input('nombre_pecosa');
            $nombreEsAutomatico = empty($nombrePecosaInput);
            $nombrePecosa = $nombrePecosaInput ?: $this->nombrePecosaAutomatico();
            $fechaEntrega = $request->input('fecha_entrega') ?: null;
            $now     = now();
            $errores = [];
            $nuevos  = 0;
            $sumados = 0;

            foreach ($filas as $idx => $fila) {
                if ($idx <= $headerIdx) continue;
                if (empty(trim((string)($fila[$colDesc] ?? '')))) continue;

                $cant         = $this->parseCantidad($fila[$colCant] ?? 0);
                $unid         = strtoupper(trim((string)($fila[$colUnid] ?? '')));
                $descripcion  = strtoupper(trim((string)($fila[$colDesc] ?? '')));
                $marca        = $colMarca !== null ? (strtoupper(trim((string)($fila[$colMarca] ?? ''))) ?: null) : null;
                $presentacion = floatval(str_replace(',', '.', $fila[$colPresent] ?? 1));
                $lote         = $colLote !== null ? (trim((string)($fila[$colLote] ?? '')) ?: null) : null;

                if ($cant <= 0 || !$unid || !$descripcion || $presentacion <= 0) {
                    $errores[] = "Fila " . ($idx + 1) . ": datos incompletos o inválidos.";
                    continue;
                }

                if ($this->sumarSiYaExiste('pecosa_primaria', $descripcion, $marca, $lote, $nombrePecosa, $nombreEsAutomatico, $cant, $presentacion, $now)) {
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
            $vision = new OpenAiVisionService();
            if (!$vision->configurado()) {
                return back()->with('error', 'La lectura de fotos con IA aún no está configurada en el servidor.');
            }

            $productos = $vision->extraerProductosDeImagen($request->file('foto')->getPathname());

            if (empty($productos)) {
                return back()->with('error', 'No se reconoció ningún producto en la foto. Intenta con una foto más clara y bien iluminada.');
            }

            $nombrePecosaInput = $request->input('nombre_pecosa');
            $nombreEsAutomatico = empty($nombrePecosaInput);
            $nombrePecosa = $nombrePecosaInput ?: $this->nombrePecosaAutomatico();
            $fechaEntrega = $request->input('fecha_entrega') ?: null;
            $now     = now();
            $nuevos  = 0;
            $sumados = 0;

            foreach ($productos as $p) {
                if ($this->sumarSiYaExiste('pecosa_primaria', $p['descripcion'], $p['marca'], $p['lote'], $nombrePecosa, $nombreEsAutomatico, $p['cant'], $p['presentacion'], $now)) {
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
     * Interpreta la cantidad de un Excel tolerando el punto usado como
     * separador de miles (formato peruano: "6.210" = seis mil doscientos
     * diez, no "6.21"). Un intval()/floatval() normal trunca "6.210" a 6,
     * porque interpreta el punto como separador decimal — este helper
     * detecta el patrón de agrupación de miles (grupos de 3 dígitos después
     * del punto) y lo quita antes de convertir a entero.
     */
    private function parseCantidad($valor): int
    {
        $str = trim((string) $valor);
        // Quita cualquier "." o "," que actúe como separador de miles (va
        // seguido de exactamente 3 dígitos y luego fin de texto u otro
        // separador). Cubre "6.210", "6,210", "1.234.567", "1,234,567" y
        // combinaciones, sin importar qué símbolo haya usado el Excel.
        $str = preg_replace('/[.,](?=\d{3}(?:\D|$))/', '', $str);
        return intval($str);
    }

    /**
     * Si el usuario no escribe un nombre para la Pecosa que sube, se genera
     * uno automático único (fecha y hora) en vez de dejarlo en blanco/null —
     * así nunca dos subidas distintas terminan "sin nombre" y mezclándose
     * entre sí por accidente.
     */
    private function nombrePecosaAutomatico(): string
    {
        return 'Pecosa ' . now()->format('d/m/Y H:i:s');
    }

    /**
     * Evita duplicar un producto que ya existe, sumando la cantidad al
     * registro existente en vez de insertar una fila nueva. Reglas para
     * decidir si "ya existe":
     * - Si el usuario NO escribió un nombre de Pecosa (nombre automático),
     *   se compara solo por descripcion+marca+lote, ignorando el nombre —
     *   así, subir la misma foto dos veces sin ponerle nombre suma en vez
     *   de duplicar.
     * - Si el usuario SÍ escribió un nombre a propósito (ej. "Pecosa 5"),
     *   ese nombre entra en la comparación: nunca se mezcla con una Pecosa
     *   de nombre distinto, aunque el lote coincida (evita que "Pecosa 5"
     *   termine sumándose por error dentro de "Pecosa 1").
     * Devuelve true si sumó a uno existente, false si no encontró
     * coincidencia (debe insertarse aparte).
     */
    private function sumarSiYaExiste(string $tabla, string $descripcion, ?string $marca, ?string $lote, ?string $nombrePecosa, bool $nombreEsAutomatico, int $cant, float $presentacion, $now): bool
    {
        $existente = \Illuminate\Support\Facades\DB::table($tabla)
            ->where('descripcion', $descripcion)
            ->where(function ($q) use ($marca) {
                $marca === null ? $q->whereNull('marca') : $q->where('marca', $marca);
            })
            ->where(function ($q) use ($lote) {
                $lote === null ? $q->whereNull('lote') : $q->where('lote', $lote);
            })
            ->when(!$nombreEsAutomatico, fn($q) => $q->where('nombre_pecosa', $nombrePecosa))
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

    private function normalizeHeaderPecosa(string $h): string
    {
        $h = mb_strtolower(trim($h));
        $h = str_replace(
            ['á','é','í','ó','ú','ü','ñ','à','è','ì','ò','ù','°','º','ª','.'],
            ['a','e','i','o','u','u','n','a','e','i','o','u','','','',''],
            $h
        );
        $h = preg_replace('/\s*\(.*?\)\s*/', '', $h);
        $h = preg_replace('/[^a-z0-9]+/', '_', $h);
        return trim($h, '_');
    }

    private function findColPecosa(array $normalizedHeaders, array $possibleNames): ?int
    {
        foreach ($normalizedHeaders as $idx => $header) {
            if ($header === '') continue;
            if (in_array($header, $possibleNames)) return $idx;
        }
        foreach ($normalizedHeaders as $idx => $header) {
            if ($header === '') continue;
            foreach ($possibleNames as $name) {
                if (str_contains($header, $name) || str_contains($name, $header)) {
                    return $idx;
                }
            }
        }
        return null;
    }
}
