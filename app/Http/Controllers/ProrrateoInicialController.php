<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProrrateoInicialController extends Controller
{
    private function getSecciones(): array
    {
        $rows = DB::select(
            'SELECT carrera, COUNT(*) as total
             FROM alumnos WHERE nivel = ? AND estado = ?
             GROUP BY carrera ORDER BY carrera',
            ['inicial', 'activo']
        );

        $secciones = [];
        foreach ($rows as $row) {
            $secciones[] = ['nombre' => $row->carrera, 'alumnos' => (int) $row->total];
        }
        return $secciones;
    }

    /**
     * Solo usa los productos de la Pecosa más reciente (igual que el
     * listado de Pecosa Inicial), para no mezclar entregas de distintas
     * fechas en un mismo reparto. Si ningún producto tiene nombre_pecosa
     * (datos antiguos, de antes de esa función), usa todos.
     */
    private function getProductos(): array
    {
        $pecosaMasReciente = DB::table('pecosa_inicial')
            ->whereNotNull('nombre_pecosa')
            ->select('nombre_pecosa', 'created_at')
            ->get()
            ->groupBy('nombre_pecosa')
            ->map(fn($grupo) => $grupo->max('created_at'))
            ->sortDesc()
            ->keys()
            ->first();

        return DB::table('pecosa_inicial')
            ->when($pecosaMasReciente, fn($q) => $q->where('nombre_pecosa', $pecosaMasReciente))
            ->orderBy('descripcion')->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'nombre'       => $p->descripcion,
                'unid'         => $p->unid,
                'presentacion' => number_format($p->presentacion, 3),
                'cant_total'   => (int) $p->cant,
            ])->toArray();
    }

    /**
     * Reparte $total de forma proporcional a $pesos (alumnos por sección)
     * garantizando que la suma de las partes sea EXACTAMENTE $total (método
     * del resto mayor / largest remainder). Un round() simple por sección no
     * garantiza esto: por redondeos independientes puede sobrar o faltar 1-2
     * unidades frente al total real de la PECOSA (fila "Diferencia" != 0).
     */
    private function distribuirExacto(int $total, array $pesos): array
    {
        $sumaPesos = array_sum($pesos);
        if ($sumaPesos <= 0 || $total <= 0) {
            return array_fill_keys(array_keys($pesos), 0);
        }

        $partes = [];
        $asignado = 0;
        foreach ($pesos as $clave => $peso) {
            $exacto = $total * $peso / $sumaPesos;
            $piso   = (int) floor($exacto);
            $partes[$clave] = ['cant' => $piso, 'resto' => $exacto - $piso];
            $asignado += $piso;
        }

        $faltan = $total - $asignado;
        if ($faltan > 0) {
            $ordenPorResto = $partes;
            uasort($ordenPorResto, fn($a, $b) => $b['resto'] <=> $a['resto']);
            foreach (array_keys($ordenPorResto) as $clave) {
                if ($faltan <= 0) break;
                $partes[$clave]['cant']++;
                $faltan--;
            }
        }

        $resultado = [];
        foreach ($partes as $clave => $p) {
            $resultado[$clave] = $p['cant'];
        }
        return $resultado;
    }

    private function construirTabla(array $secciones, array $productos, $guardado = null): array
    {
        $totalAlumnos     = array_sum(array_column($secciones, 'alumnos'));
        $hayGuardado      = $guardado !== null && $guardado->isNotEmpty();
        $data             = array_fill_keys(array_keys($secciones), null);
        $totalesProductos = array_fill(0, count($productos), 0);
        $totalGeneral     = 0;

        $pesos = [];
        foreach ($secciones as $i => $sec) {
            $pesos[$i] = $sec['alumnos'];
        }

        foreach ($secciones as $i => $sec) {
            $data[$i] = ['seccion' => $sec['nombre'], 'alumnos' => $sec['alumnos'], 'items' => [], 'total' => 0];
        }

        foreach ($productos as $index => $prod) {
            if ($hayGuardado) {
                foreach ($secciones as $i => $sec) {
                    $reg  = ($guardado[$sec['nombre']] ?? null)?->firstWhere('pecosa_inicial_id', $prod['id']);
                    $cant = $reg ? (int) $reg->cantidad : 0;
                    $data[$i]['items'][]        = $cant;
                    $data[$i]['total']         += $cant;
                    $totalesProductos[$index]  += $cant;
                }
            } else {
                // La tabla arranca en 0: el usuario reparte a mano cuánto le
                // da a cada aula, y la fila "CANTIDAD" del encabezado va
                // descontando en vivo lo que queda disponible de la PECOSA.
                foreach ($secciones as $i => $sec) {
                    $data[$i]['items'][]       = 0;
                    $totalesProductos[$index] += 0;
                }
            }
        }

        foreach ($data as $fila) {
            $totalGeneral += $fila['total'];
        }

        return [array_values($data), $totalesProductos, $totalGeneral, $totalAlumnos];
    }

    public function index()
    {
        $secciones = $this->getSecciones();
        $productos = $this->getProductos();

        $ultimaVersion = DB::table('prorrateo_inicial_versiones')->latest()->first();
        $guardado      = null;
        $hayGuardado   = false;
        $ultimaActualizacion = null;

        if ($ultimaVersion) {
            $guardado    = DB::table('prorrateo_inicial')
                ->where('version_id', $ultimaVersion->id)
                ->get()->groupBy('seccion');
            $hayGuardado = $guardado->isNotEmpty();
            $ultimaActualizacion = $ultimaVersion->created_at;
        }

        [$data, $totalesProductos, $totalGeneral, $totalAlumnos] =
            $this->construirTabla($secciones, $productos, $guardado);

        $totalVersiones = DB::table('prorrateo_inicial_versiones')->count();

        return view('pecosa.inicial.prorrateo', compact(
            'data', 'productos', 'totalesProductos', 'totalGeneral',
            'totalAlumnos', 'hayGuardado', 'ultimaActualizacion', 'totalVersiones'
        ));
    }

    public function guardar(Request $request)
    {
        $cantidades    = $request->input('cantidades', []);
        $alumnosPorSec = $request->input('alumnos', []);
        $nombre        = $request->input('nombre');
        $now           = now();

        $totalAlumnos  = array_sum($alumnosPorSec);
        $totalUnidades = 0;
        foreach ($cantidades as $productos) {
            $totalUnidades += array_sum($productos);
        }

        $version = DB::table('prorrateo_inicial_versiones')->insertGetId([
            'nombre'         => $nombre ?: 'Distribución ' . now()->format('d/m/Y H:i'),
            'total_alumnos'  => $totalAlumnos,
            'total_unidades' => $totalUnidades,
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);

        $inserts = [];
        foreach ($cantidades as $seccion => $productos) {
            foreach ($productos as $pecosaId => $cantidad) {
                $inserts[] = [
                    'version_id'       => $version,
                    'seccion'          => $seccion,
                    'alumnos'          => (int) ($alumnosPorSec[$seccion] ?? 0),
                    'pecosa_inicial_id' => (int) $pecosaId,
                    'cantidad'         => max(0, (int) $cantidad),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        foreach (array_chunk($inserts, 200) as $chunk) {
            DB::table('prorrateo_inicial')->insert($chunk);
        }

        return redirect()->route('pecosa.inicial.distribuciones')
            ->with('success', 'Distribución guardada correctamente.');
    }

    public function historial()
    {
        $versiones = DB::table('prorrateo_inicial_versiones')
            ->orderByDesc('created_at')
            ->get();

        return view('pecosa.inicial.distribuciones', compact('versiones'));
    }

    public function verVersion($id)
    {
        $version = DB::table('prorrateo_inicial_versiones')->find($id);
        abort_if(!$version, 404);

        $registros = DB::table('prorrateo_inicial')->where('version_id', $id)->get();

        $esImportada = $registros->whereNull('pecosa_inicial_id')->isNotEmpty();

        if ($esImportada) {
            $productos = $registros->pluck('producto_nombre')->unique()->values()
                ->map(fn($n) => ['id' => $n, 'nombre' => $n, 'unid' => '', 'presentacion' => '1', 'cant_total' => 0])
                ->toArray();

            $secciones = $registros->groupBy('seccion')->map(fn($rows) => [
                'nombre'  => $rows->first()->seccion,
                'alumnos' => (int) $rows->first()->alumnos,
            ])->values()->toArray();

            $guardadoMap = $registros->groupBy('seccion');

            $data = [];
            $totalesProductos = array_fill(0, count($productos), 0);
            $totalGeneral = 0;
            $totalAlumnos = array_sum(array_column($secciones, 'alumnos'));

            foreach ($secciones as $sec) {
                $fila = ['seccion' => $sec['nombre'], 'alumnos' => $sec['alumnos'], 'items' => [], 'total' => 0];
                $rows = $guardadoMap[$sec['nombre']] ?? collect();
                foreach ($productos as $idx => $prod) {
                    $reg  = $rows->firstWhere('producto_nombre', $prod['id']);
                    $cant = $reg ? (int) $reg->cantidad : 0;
                    $fila['items'][] = $cant;
                    $fila['total'] += $cant;
                    $totalesProductos[$idx] += $cant;
                }
                $totalGeneral += $fila['total'];
                $data[] = $fila;
            }
        } else {
            $secciones = $this->getSecciones();
            $productos = $this->getProductos();
            $guardado  = $registros->groupBy('seccion');
            [$data, $totalesProductos, $totalGeneral, $totalAlumnos] =
                $this->construirTabla($secciones, $productos, $guardado);
        }

        return view('pecosa.inicial.version', compact(
            'version', 'data', 'productos', 'totalesProductos', 'totalGeneral', 'totalAlumnos'
        ));
    }

    public function eliminarVersion($id)
    {
        DB::table('prorrateo_inicial_versiones')->where('id', $id)->delete();

        return redirect()->route('pecosa.inicial.distribuciones')
            ->with('success', 'Distribución eliminada.');
    }

    public function importarExcel(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls|max:5120',
            'nombre'  => 'nullable|string|max:200',
        ]);

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($request->file('archivo')->getPathname());
            $hoja  = $spreadsheet->getActiveSheet();
            $filas = $hoja->toArray(null, true, true, false);

            if (count($filas) < 2) {
                return back()->with('error', 'El archivo no tiene datos suficientes.');
            }

            $encabezados = array_map(fn($v) => strtoupper(trim((string) $v)), $filas[0]);

            $colSeccion = 0;
            $colAlumnos = 1;
            foreach ($encabezados as $i => $enc) {
                if (str_contains($enc, 'SECCI') || str_contains($enc, 'AULA') || str_contains($enc, 'AÑOS')) $colSeccion = $i;
                if (str_contains($enc, 'ALUMNO') || str_contains($enc, 'TOTAL')) $colAlumnos = $i;
            }

            $colProductos = [];
            for ($i = 0; $i < count($encabezados); $i++) {
                if ($i === $colSeccion || $i === $colAlumnos) continue;
                $nombre = $encabezados[$i];
                if ($nombre === '' || $nombre === null) continue;
                $colProductos[$i] = $nombre;
            }

            if (empty($colProductos)) {
                return back()->with('error', 'No se encontraron columnas de productos en el archivo.');
            }

            $inserts = [];
            $totalAlumnos  = 0;
            $totalUnidades = 0;
            $seccionesVistas = [];
            $now = now();

            foreach (array_slice($filas, 1) as $fila) {
                $seccion = trim((string)($fila[$colSeccion] ?? ''));
                $alumnos = (int)($fila[$colAlumnos] ?? 0);
                if ($seccion === '') continue;

                if (!in_array($seccion, $seccionesVistas)) {
                    $totalAlumnos += $alumnos;
                    $seccionesVistas[] = $seccion;
                }

                foreach ($colProductos as $col => $nombreProducto) {
                    $cantidad = max(0, (int) str_replace([',', ' '], ['', ''], (string)($fila[$col] ?? 0)));
                    $totalUnidades += $cantidad;
                    $inserts[] = [
                        'seccion'          => $seccion,
                        'alumnos'          => $alumnos,
                        'producto_nombre'  => $nombreProducto,
                        'producto_unidad'  => null,
                        'pecosa_inicial_id' => null,
                        'cantidad'         => $cantidad,
                        'created_at'       => $now,
                        'updated_at'       => $now,
                    ];
                }
            }

            if (empty($inserts)) {
                return back()->with('error', 'No se encontraron filas de secciones válidas en el archivo.');
            }

            $versionId = DB::table('prorrateo_inicial_versiones')->insertGetId([
                'nombre'         => $request->input('nombre') ?: 'Importado ' . now()->format('d/m/Y H:i'),
                'total_alumnos'  => $totalAlumnos,
                'total_unidades' => $totalUnidades,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            foreach ($inserts as &$row) {
                $row['version_id'] = $versionId;
            }

            foreach (array_chunk($inserts, 200) as $chunk) {
                DB::table('prorrateo_inicial')->insert($chunk);
            }

            $numSecciones = count($seccionesVistas);
            return redirect()->route('pecosa.inicial.distribuciones')
                ->with('success', "Distribución importada: {$numSecciones} sección(es), " . count($colProductos) . " producto(s), {$totalUnidades} unidades en total.");

        } catch (\Exception $e) {
            return back()->with('error', 'Error al leer el archivo: ' . $e->getMessage());
        }
    }

    public function listadoAula($versionId, $seccion)
    {
        $version = DB::table('prorrateo_inicial_versiones')->find($versionId);
        abort_if(!$version, 404);

        $productos = $this->getProductos();

        $registros = DB::table('prorrateo_inicial')
            ->where('version_id', $versionId)
            ->where('seccion', $seccion)
            ->pluck('cantidad', 'pecosa_inicial_id');

        $alumnos = DB::table('alumnos')
            ->where('nivel', 'inicial')
            ->where('estado', 'activo')
            ->where('carrera', $seccion)
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get(['nombre', 'apellido_paterno', 'apellido_materno']);

        $totalAlumnos    = $alumnos->count();
        $porAlumno       = [];
        $productosAlumno = [];
        $productosAula   = [];

        foreach ($productos as $prod) {
            $totalSeccion  = (int) ($registros[$prod['id']] ?? 0);
            $cantPorAlumno = $totalAlumnos > 0
                ? (int) round($totalSeccion / $totalAlumnos)
                : 0;

            if ($cantPorAlumno >= 1) {
                $porAlumno[$prod['id']] = $cantPorAlumno;
                $productosAlumno[]      = $prod;
            } else {
                $productosAula[] = array_merge($prod, ['total_aula' => $totalSeccion]);
            }
        }

        $totalesCol = [];
        foreach ($productosAlumno as $prod) {
            $totalesCol[$prod['id']] = $porAlumno[$prod['id']] * $totalAlumnos;
        }

        return view('pecosa.inicial.listado_aula', compact(
            'version', 'seccion',
            'alumnos', 'porAlumno', 'totalesCol', 'totalAlumnos',
            'productosAula',
        ) + ['productos' => $productosAlumno]);
    }
}
