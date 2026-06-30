<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProrrateoController extends Controller
{
    // ── Helpers ──────────────────────────────────────────────────────────────

    private function getSecciones(): array
    {
        $rows = DB::select(
            'SELECT carrera, semestre, COUNT(*) as total
             FROM alumnos WHERE nivel = ? AND estado = ?
             GROUP BY carrera, semestre ORDER BY semestre, carrera',
            ['primaria', 'activo']
        );

        $secciones = [];
        foreach ($rows as $row) {
            $pos   = strrpos($row->carrera, ' ');
            $letra = $pos !== false ? trim(substr($row->carrera, $pos + 1)) : trim($row->carrera);
            $secciones[] = ['nombre' => $row->semestre . 'º ' . $letra, 'alumnos' => (int) $row->total];
        }
        return $secciones;
    }

    private function getProductos(): array
    {
        return DB::table('pecosa_primaria')->orderBy('descripcion')->get()
            ->map(fn($p) => [
                'id'           => $p->id,
                'nombre'       => $p->descripcion,
                'unid'         => $p->unid,
                'presentacion' => number_format($p->presentacion, 3),
                'cant_total'   => (int) $p->cant,
            ])->toArray();
    }

    private function construirTabla(array $secciones, array $productos, $guardado = null): array
    {
        $totalAlumnos     = array_sum(array_column($secciones, 'alumnos'));
        $hayGuardado      = $guardado !== null && $guardado->isNotEmpty();
        $data             = [];
        $totalesProductos = array_fill(0, count($productos), 0);
        $totalGeneral     = 0;

        foreach ($secciones as $sec) {
            $fila = ['seccion' => $sec['nombre'], 'alumnos' => $sec['alumnos'], 'items' => [], 'total' => 0];

            foreach ($productos as $index => $prod) {
                if ($hayGuardado && isset($guardado[$sec['nombre']])) {
                    $reg  = $guardado[$sec['nombre']]->firstWhere('pecosa_primaria_id', $prod['id']);
                    $cant = $reg ? (int) $reg->cantidad : 0;
                } else {
                    $cant = $totalAlumnos > 0
                        ? (int) round($prod['cant_total'] * $sec['alumnos'] / $totalAlumnos)
                        : 0;
                }

                $fila['items'][]          = $cant;
                $fila['total']           += $cant;
                $totalesProductos[$index] += $cant;
            }

            $totalGeneral += $fila['total'];
            $data[] = $fila;
        }

        return [$data, $totalesProductos, $totalGeneral, $totalAlumnos];
    }

    // ── Vistas ───────────────────────────────────────────────────────────────

    public function primaria()
    {
        $secciones = $this->getSecciones();
        $productos = $this->getProductos();

        $ultimaVersion = DB::table('prorrateo_versiones')->latest()->first();
        $guardado      = null;
        $hayGuardado   = false;
        $ultimaActualizacion = null;

        if ($ultimaVersion) {
            $guardado    = DB::table('prorrateo_primaria')
                ->where('version_id', $ultimaVersion->id)
                ->get()->groupBy('seccion');
            $hayGuardado = $guardado->isNotEmpty();
            $ultimaActualizacion = $ultimaVersion->created_at;
        }

        [$data, $totalesProductos, $totalGeneral, $totalAlumnos] =
            $this->construirTabla($secciones, $productos, $guardado);

        $totalVersiones = DB::table('prorrateo_versiones')->count();

        return view('pecosa.primaria.prorrateo', compact(
            'data', 'productos', 'totalesProductos', 'totalGeneral',
            'totalAlumnos', 'hayGuardado', 'ultimaActualizacion', 'totalVersiones'
        ));
    }

    public function guardar(Request $request)
    {
        $cantidades   = $request->input('cantidades', []);
        $alumnosPorSec = $request->input('alumnos', []);
        $nombre       = $request->input('nombre');
        $now          = now();

        $totalAlumnos  = array_sum($alumnosPorSec);
        $totalUnidades = 0;
        foreach ($cantidades as $productos) {
            $totalUnidades += array_sum($productos);
        }

        $version = DB::table('prorrateo_versiones')->insertGetId([
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
                    'version_id'         => $version,
                    'seccion'            => $seccion,
                    'alumnos'            => (int) ($alumnosPorSec[$seccion] ?? 0),
                    'pecosa_primaria_id' => (int) $pecosaId,
                    'cantidad'           => max(0, (int) $cantidad),
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }
        }

        foreach (array_chunk($inserts, 200) as $chunk) {
            DB::table('prorrateo_primaria')->insert($chunk);
        }

        return redirect()->route('pecosa.primaria.distribuciones')
            ->with('success', 'Distribución guardada correctamente.');
    }

    public function historial()
    {
        $versiones = DB::table('prorrateo_versiones')
            ->orderByDesc('created_at')
            ->get();

        return view('pecosa.primaria.distribuciones', compact('versiones'));
    }

    public function verVersion($id)
    {
        $version = DB::table('prorrateo_versiones')->find($id);
        abort_if(!$version, 404);

        $registros = DB::table('prorrateo_primaria')->where('version_id', $id)->get();

        // Detectar si es una distribución importada (sin FK a pecosa_primaria)
        $esImportada = $registros->whereNull('pecosa_primaria_id')->isNotEmpty();

        if ($esImportada) {
            // Construir tabla libre desde producto_nombre
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

        return view('pecosa.primaria.version', compact(
            'version', 'data', 'productos', 'totalesProductos', 'totalGeneral', 'totalAlumnos'
        ));
    }

    public function eliminarVersion($id)
    {
        DB::table('prorrateo_versiones')->where('id', $id)->delete();

        return redirect()->route('pecosa.primaria.distribuciones')
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
            $hoja = $spreadsheet->getActiveSheet();
            $filas = $hoja->toArray(null, true, true, false);

            if (count($filas) < 2) {
                return back()->with('error', 'El archivo no tiene datos suficientes.');
            }

            // Fila 0: encabezados → [Sección, Alumnos, PROD1, PROD2, ...]
            $encabezados = array_map(fn($v) => strtoupper(trim((string) $v)), $filas[0]);

            // Detectar columnas de sección y alumnos (por defecto col 0 y 1)
            $colSeccion = 0;
            $colAlumnos = 1;
            foreach ($encabezados as $i => $enc) {
                if (str_contains($enc, 'SECCI') || str_contains($enc, 'AULA') || str_contains($enc, 'GRADO')) $colSeccion = $i;
                if (str_contains($enc, 'ALUMNO') || str_contains($enc, 'TOTAL')) $colAlumnos = $i;
            }

            // Columnas de productos: todo lo que no sea sección/alumnos
            $colProductos = []; // índice => nombre del producto
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
                        'seccion'            => $seccion,
                        'alumnos'            => $alumnos,
                        'producto_nombre'    => $nombreProducto,
                        'producto_unidad'    => null,
                        'pecosa_primaria_id' => null,
                        'cantidad'           => $cantidad,
                        'created_at'         => $now,
                        'updated_at'         => $now,
                    ];
                }
            }

            if (empty($inserts)) {
                return back()->with('error', 'No se encontraron filas de secciones válidas en el archivo.');
            }

            $versionId = DB::table('prorrateo_versiones')->insertGetId([
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
                DB::table('prorrateo_primaria')->insert($chunk);
            }

            $numSecciones = count($seccionesVistas);
            return redirect()->route('pecosa.primaria.distribuciones')
                ->with('success', "Distribución importada: {$numSecciones} sección(es), " . count($colProductos) . " producto(s), {$totalUnidades} unidades en total.");

        } catch (\Exception $e) {
            return back()->with('error', 'Error al leer el archivo: ' . $e->getMessage());
        }
    }

    public function listadoAula($versionId, $seccion)
    {
        $version = DB::table('prorrateo_versiones')->find($versionId);
        abort_if(!$version, 404);

        // Productos PECOSA ordenados igual que el prorrateo
        $productos = $this->getProductos();

        // Totales de la sección guardados en el prorrateo
        $registros = DB::table('prorrateo_primaria')
            ->where('version_id', $versionId)
            ->where('seccion', $seccion)
            ->pluck('cantidad', 'pecosa_primaria_id');

        // Extraer grado (número) y letra de "1º A" → grado=1, letra="A"
        $grado = (int) $seccion;
        $letra = strtoupper(trim(substr($seccion, strrpos($seccion, ' ') + 1)));

        // Alumnos activos de esa sección ordenados por apellidos
        $alumnos = DB::table('alumnos')
            ->where('nivel', 'primaria')
            ->where('estado', 'activo')
            ->where('semestre', $grado)
            ->where('carrera', 'like', '% ' . $letra)
            ->orderBy('apellido_paterno')
            ->orderBy('apellido_materno')
            ->orderBy('nombre')
            ->get(['nombre', 'apellido_paterno', 'apellido_materno']);

        // Separar productos: por alumno (≥1 unidad c/u) y por aula (< 1 por alumno)
        $totalAlumnos       = $alumnos->count();
        $porAlumno          = [];
        $productosAlumno    = [];   // columnas del listado individual
        $productosAula      = [];   // cuadro resumen de aula

        foreach ($productos as $prod) {
            $totalSeccion  = (int) ($registros[$prod['id']] ?? 0);
            $cantPorAlumno = $totalAlumnos > 0
                ? (int) round($totalSeccion / $totalAlumnos)
                : 0;

            if ($cantPorAlumno >= 1) {
                $porAlumno[$prod['id']] = $cantPorAlumno;
                $productosAlumno[]      = $prod;
            } else {
                $productosAula[] = array_merge($prod, [
                    'total_aula' => $totalSeccion,
                ]);
            }
        }

        // Totales columna para productos por alumno
        $totalesCol = [];
        foreach ($productosAlumno as $prod) {
            $totalesCol[$prod['id']] = $porAlumno[$prod['id']] * $totalAlumnos;
        }

        return view('pecosa.primaria.listado_aula', compact(
            'version', 'seccion',
            'alumnos', 'porAlumno', 'totalesCol', 'totalAlumnos',
            'productosAula',
        ) + ['productos' => $productosAlumno]);
    }
}
