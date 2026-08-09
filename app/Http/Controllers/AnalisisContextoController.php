<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CalculaContextoDiario;
use App\Models\ControlDistribucion;
use App\Models\ControlNutricional;
use App\Models\RegistroAsistencia;
use App\Support\Stats;

/**
 * Reporte que valida empíricamente las variables de contexto mencionadas en
 * el marco teórico de la tesis (condiciones climáticas, calendario de
 * festividades/eventos especiales) frente a los datos reales del sistema:
 * demanda de raciones (ficha 4), desperdicio/distribución (ficha 6) y
 * cumplimiento nutricional (ficha 5), con prueba t de Welch para determinar
 * si las diferencias observadas son estadísticamente significativas.
 */
class AnalisisContextoController extends Controller
{
    use CalculaContextoDiario;

    private function resumenPorCategoria(array $valores, string $categoria, int $n): ?array
    {
        if ($n === 0) return null;
        return [
            'categoria' => $categoria,
            'promedio' => round(array_sum($valores) / $n, 2),
            'n' => $n,
        ];
    }

    public function index()
    {
        $niveles = ['inicial', 'primaria'];

        $resultadoClima = [];
        $resultadoEvento = [];
        $cruceMermas = [];
        $cruceNutricion = [];

        foreach ($niveles as $nivel) {
            $dias = collect($this->contextoDiario($nivel));

            // ── Clima vs demanda de raciones ────────────────────────────
            $porClima = $dias->groupBy('clima');
            $raciones = fn ($clima) => $porClima->get($clima, collect())->pluck('raciones')->all();

            $rSoleado = $raciones('soleado');
            $rNublado = $raciones('nublado');
            $rLluvioso = $raciones('lluvioso');

            $resultadoClima[$nivel] = [
                'categorias' => array_filter([
                    $this->resumenPorCategoria($rSoleado, 'soleado', count($rSoleado)),
                    $this->resumenPorCategoria($rNublado, 'nublado', count($rNublado)),
                    $this->resumenPorCategoria($rLluvioso, 'lluvioso', count($rLluvioso)),
                ]),
                'test' => Stats::welchTTest($rSoleado, $rLluvioso),
            ];

            // ── Evento especial vs demanda de raciones ──────────────────
            $porEvento = $dias->groupBy('evento_especial');
            $rNormal = $porEvento->get(false, collect())->pluck('raciones')->all();
            $rEspecial = $porEvento->get(true, collect())->pluck('raciones')->all();

            $resultadoEvento[$nivel] = [
                'normal' => $this->resumenPorCategoria($rNormal, 'normal', count($rNormal)),
                'especial' => $this->resumenPorCategoria($rEspecial, 'especial', count($rEspecial)),
                'test' => Stats::welchTTest($rNormal, $rEspecial),
            ];

            // ── Mapa fecha => clima del día, para cruzar con fichas 5 y 6 ──
            $climaPorFecha = $dias->pluck('clima', 'fecha');

            // ── Cruce clima × índice de mermas (ficha 6) ────────────────
            $mermasPorClima = ['soleado' => [], 'nublado' => [], 'lluvioso' => []];
            ControlDistribucion::where('nivel', $nivel)->get()->each(function ($r) use (&$mermasPorClima, $climaPorFecha) {
                $clima = $climaPorFecha->get($r->fecha->toDateString());
                if ($clima && isset($mermasPorClima[$clima])) {
                    $mermasPorClima[$clima][] = $r->indice_mermas;
                }
            });
            $cruceMermas[$nivel] = array_filter(array_map(
                fn ($clima, $valores) => $this->resumenPorCategoria($valores, $clima, count($valores)),
                array_keys($mermasPorClima), $mermasPorClima
            ));

            // ── Cruce clima × cumplimiento nutricional (ficha 5) ────────
            $cumplePorClima = ['soleado' => [], 'nublado' => [], 'lluvioso' => []];
            ControlNutricional::where('nivel', $nivel)->get()->each(function ($r) use (&$cumplePorClima, $climaPorFecha) {
                $clima = $climaPorFecha->get($r->fecha->toDateString());
                if ($clima && isset($cumplePorClima[$clima])) {
                    $cumplePorClima[$clima][] = $r->cumple_requerimiento ? 1 : 0;
                }
            });
            $cruceNutricion[$nivel] = array_filter(array_map(function ($clima, $valores) {
                $n = count($valores);
                if ($n === 0) return null;
                return ['categoria' => $clima, 'pct_cumple' => round((array_sum($valores) / $n) * 100, 1), 'n' => $n];
            }, array_keys($cumplePorClima), $cumplePorClima));
        }

        $totalConClima  = RegistroAsistencia::whereNotNull('condicion_climatica')->count();
        $totalRegistros = RegistroAsistencia::count();

        // Cobertura del dato de clima por nivel, para evidenciar huecos de captura
        $coberturaPorNivel = collect($niveles)->mapWithKeys(fn ($nivel) => [
            $nivel => [
                'con_clima' => RegistroAsistencia::where('nivel', $nivel)->whereNotNull('condicion_climatica')->count(),
                'total'     => RegistroAsistencia::where('nivel', $nivel)->count(),
            ],
        ]);

        return view('analisis-contexto.index', compact(
            'resultadoClima', 'resultadoEvento', 'cruceMermas', 'cruceNutricion',
            'totalConClima', 'totalRegistros', 'coberturaPorNivel'
        ));
    }
}
