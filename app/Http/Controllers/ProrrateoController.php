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

        $secciones = $this->getSecciones();
        $productos = $this->getProductos();

        $guardado = DB::table('prorrateo_primaria')
            ->where('version_id', $id)
            ->get()->groupBy('seccion');

        [$data, $totalesProductos, $totalGeneral, $totalAlumnos] =
            $this->construirTabla($secciones, $productos, $guardado);

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
