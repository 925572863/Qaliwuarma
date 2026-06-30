<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AporteConfig;
use App\Models\AportePago;
use App\Models\AporteSemana;
use Illuminate\Http\Request;

class AporteController extends Controller
{
    public const MESES = [
        1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',
        7=>'Julio',8=>'Agosto',9=>'Setiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre',
    ];

    // ── Planilla mensual: alumnos como filas, semanas como columnas ───────
    public function index(Request $request)
    {
        $anio = (int) $request->get('anio', now()->year);
        $mes  = max(1, min(12, (int) $request->get('mes', now()->month)));

        // Carreras únicas de nivel inicial
        $carreras = Alumno::where('nivel', 'inicial')
            ->where('estado', 'activo')
            ->whereNotNull('carrera')
            ->select('carrera')
            ->distinct()
            ->orderBy('carrera')
            ->pluck('carrera')
            ->unique();

        $aulas = $carreras->map(function ($carrera) use ($anio, $mes) {
            $partes  = explode(' ', trim($carrera));
            $seccion = array_pop($partes);
            if (isset($partes[0]) && strtoupper($partes[0]) === 'INICIAL') {
                array_shift($partes);
            }
            $grado = implode(' ', $partes);

            $config = AporteConfig::firstOrCreate(
                ['anio' => $anio, 'grado' => $grado, 'seccion' => $seccion],
                ['cuota_por_dia' => 0.80]
            );

            $alumnos = Alumno::where('nivel', 'inicial')
                ->where('carrera', $carrera)
                ->where('estado', 'activo')
                ->orderBy('apellido_paterno')
                ->orderBy('apellido_materno')
                ->get(['id','nombre','apellido_paterno','apellido_materno']);

            // Auto-generar semanas del mes si no existen
            $semanas = AporteSemana::where('config_id', $config->id)
                ->where('mes', $mes)
                ->orderBy('semana_num')
                ->get();

            if ($semanas->isEmpty()) {
                $semanas = $this->generarSemanasDelMes($config->id, $anio, $mes);
            }

            // Cargar pagos en cada semana
            $semanas = AporteSemana::whereIn('id', $semanas->pluck('id'))
                ->orderBy('semana_num')
                ->with('pagos')
                ->get();

            $pagosMap   = [];
            $firmadoMap = [];
            foreach ($semanas as $semana) {
                foreach ($semana->pagos as $p) {
                    $pagosMap[$semana->id][$p->alumno_id]   = (float) $p->monto_aportado;
                    $firmadoMap[$semana->id][$p->alumno_id] = (bool)  $p->firmado;
                }
            }

            return compact('config', 'alumnos', 'semanas', 'pagosMap', 'firmadoMap');
        })->unique(fn($a) => $a['config']->grado . $a['config']->seccion)
          ->values();

        $meses = self::MESES;

        return view('aportes.index', compact('aulas', 'anio', 'mes', 'meses'));
    }

    // ── Genera automáticamente las semanas L-V de un mes ─────────────────
    private function generarSemanasDelMes(int $configId, int $anio, int $mes): \Illuminate\Support\Collection
    {
        $inicio = \Carbon\Carbon::createFromDate($anio, $mes, 1);
        $fin    = $inicio->copy()->endOfMonth();
        $semanas = collect();
        $semanaNum = 1;
        $current = $inicio->copy()->startOfWeek(\Carbon\Carbon::MONDAY);

        while ($current->lte($fin)) {
            $semanaInicio = $current->copy()->max($inicio);
            $semanaFin    = $current->copy()->endOfWeek(\Carbon\Carbon::FRIDAY)->min($fin);

            if ($semanaInicio->lte($semanaFin) && $semanaInicio->month === $mes) {
                // Días L-V que caen dentro del mes
                $dias = [];
                for ($d = 0; $d < 5; $d++) {
                    $dia = $semanaInicio->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->addDays($d);
                    $dias[$d] = $dia->gte($inicio) && $dia->lte($fin) && $dia->month === $mes;
                }

                $semana = AporteSemana::updateOrCreate(
                    ['config_id' => $configId, 'mes' => $mes, 'semana_num' => $semanaNum],
                    [
                        'fecha_inicio'  => $semanaInicio->toDateString(),
                        'fecha_fin'     => $semanaFin->toDateString(),
                        'lunes'         => $dias[0],
                        'martes'        => $dias[1],
                        'miercoles'     => $dias[2],
                        'jueves'        => $dias[3],
                        'viernes'       => $dias[4],
                        'es_vacaciones' => false,
                    ]
                );
                $semanas->push($semana);
                $semanaNum++;
            }

            $current->addWeek();
            if ($semanaNum > 6) break;
        }

        return $semanas;
    }

    // ── Guardar configuración de semana para un aula ──────────────────────
    public function storeSemana(Request $request, AporteConfig $config)
    {
        $data = $request->validate([
            'mes'          => 'required|integer|min:1|max:12',
            'semana_num'   => 'required|integer|min:1|max:6',
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
            'lunes'        => 'nullable|boolean',
            'martes'       => 'nullable|boolean',
            'miercoles'    => 'nullable|boolean',
            'jueves'       => 'nullable|boolean',
            'viernes'      => 'nullable|boolean',
            'es_vacaciones'=> 'nullable|boolean',
        ]);

        AporteSemana::updateOrCreate(
            ['config_id' => $config->id, 'mes' => $data['mes'], 'semana_num' => $data['semana_num']],
            [
                'config_id'     => $config->id,
                'mes'           => $data['mes'],
                'semana_num'    => $data['semana_num'],
                'fecha_inicio'  => $data['fecha_inicio'],
                'fecha_fin'     => $data['fecha_fin'],
                'lunes'         => $request->boolean('lunes'),
                'martes'        => $request->boolean('martes'),
                'miercoles'     => $request->boolean('miercoles'),
                'jueves'        => $request->boolean('jueves'),
                'viernes'       => $request->boolean('viernes'),
                'es_vacaciones' => $request->boolean('es_vacaciones'),
            ]
        );

        return redirect()->route('aportes.index', [
            'anio' => $config->anio, 'mes' => $data['mes'], 'semana_num' => $data['semana_num']
        ])->with('success', "Semana {$data['semana_num']} configurada para {$config->grado} \"{$config->seccion}\".");
    }

    // ── Guardar pagos de todas las aulas del mes ──────────────────────────
    public function registrarPagos(Request $request)
    {
        $anio = $request->input('anio');
        $mes  = $request->input('mes');

        $firmados = $request->input('firmado', []);

        foreach ($request->input('pagos', []) as $semanaId => $alumnoPagos) {
            foreach ($alumnoPagos as $alumnoId => $monto) {
                AportePago::updateOrCreate(
                    ['semana_id' => $semanaId, 'alumno_id' => $alumnoId],
                    [
                        'monto_aportado' => max(0, (float) $monto),
                        'firmado'        => isset($firmados[$semanaId][$alumnoId]),
                    ]
                );
            }
        }

        return redirect()->route('aportes.index', ['anio' => $anio, 'mes' => $mes])
            ->with('success', 'Aportes guardados correctamente.');
    }

    // ── Crear configuración de aula ───────────────────────────────────────
    public function storeConfig(Request $request)
    {
        $data = $request->validate([
            'anio'          => 'required|integer|min:2020|max:2099',
            'grado'         => 'required|string|max:20',
            'seccion'       => 'required|string|max:5',
            'profesora'     => 'nullable|string|max:150',
            'cuota_por_dia' => 'required|numeric|min:0|max:99',
        ]);

        AporteConfig::firstOrCreate(
            ['anio' => $data['anio'], 'grado' => $data['grado'], 'seccion' => $data['seccion']],
            ['profesora' => $data['profesora'], 'cuota_por_dia' => $data['cuota_por_dia']]
        );

        return redirect()->route('aportes.index', ['anio' => $data['anio']])
            ->with('success', "Aula {$data['grado']} \"{$data['seccion']}\" configurada.");
    }

    // ── Eliminar configuración de aula ────────────────────────────────────
    public function destroyConfig(AporteConfig $config)
    {
        $anio = $config->anio;
        $config->delete();
        return redirect()->route('aportes.index', ['anio' => $anio])
            ->with('success', 'Configuración de aula eliminada.');
    }
}
