<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CalculaContextoDiario;
use App\Models\ControlDistribucion;
use App\Models\ControlNutricional;
use App\Models\IaEntrenamiento;
use App\Models\RegistroAsistencia;
use App\Support\Stats;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exportadores CSV de las fichas de recolección de datos (Anexo 2 de la tesis),
 * listos para abrir en Excel o importar en SPSS. Cada método reproduce las
 * columnas exactas de su ficha correspondiente.
 */
class ExportController extends Controller
{
    use CalculaContextoDiario;

    /**
     * Construye el CSV completo en memoria y lo devuelve como respuesta normal
     * (no streaming). El streaming (fputcsv sobre php://output pedazo por
     * pedazo) no llega completo al navegador cuando pasa por el proxy de
     * Render/Cloudflare junto con el servidor de desarrollo de Laravel
     * (php artisan serve): la pestaña se abre en blanco en vez de descargar
     * el archivo. Construir el contenido de una vez y enviarlo con
     * Content-Length explícito evita depender de que el streaming funcione
     * bien a través de ese proxy.
     */
    private function csvResponse(string $nombreArchivo, array $encabezados, iterable $filas): Response
    {
        $handle = fopen('php://temp', 'r+');
        fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 para Excel
        fputcsv($handle, $encabezados, ';');
        foreach ($filas as $fila) {
            fputcsv($handle, $fila, ';');
        }
        rewind($handle);
        $contenido = stream_get_contents($handle);
        fclose($handle);

        return response($contenido, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$nombreArchivo.'"',
            'Content-Length'      => strlen($contenido),
        ]);
    }

    /**
     * Ficha 4 — Estimación de la demanda de raciones.
     */
    public function raciones(): Response
    {
        $encabezados = ['fecha', 'nivel', 'fase', 'grado', 'seccion', 'raciones_planificadas', 'raciones_consumidas', 'desviacion', 'condicion_climatica', 'evento_especial'];

        $filas = RegistroAsistencia::orderBy('fecha')->cursor()->map(fn ($r) => [
            $r->fecha->toDateString(),
            $r->nivel,
            $r->fase,
            $r->grado,
            $r->seccion,
            $r->raciones_planificadas,
            $r->raciones,
            $r->desviacion_raciones,
            $r->condicion_climatica,
            $r->evento_especial ? 1 : 0,
        ]);

        return $this->csvResponse('ficha4_demanda_raciones.csv', $encabezados, $filas);
    }

    /**
     * Ficha 5 — Precisión en el cálculo de raciones nutricionales.
     */
    public function nutricional(): Response
    {
        $encabezados = ['fecha', 'nivel', 'fase', 'menu_dia', 'gramos_planificados', 'gramos_servidos', 'diferencia', 'cumple_requerimiento'];

        $filas = ControlNutricional::orderBy('fecha')->cursor()->map(fn ($r) => [
            $r->fecha->toDateString(),
            $r->nivel,
            $r->fase,
            $r->menu_dia,
            $r->gramos_planificados,
            $r->gramos_servidos,
            $r->diferencia,
            $r->cumple_requerimiento ? 1 : 0,
        ]);

        return $this->csvResponse('ficha5_precision_nutricional.csv', $encabezados, $filas);
    }

    /**
     * Ficha 6 — Eficiencia en la distribución y control del desperdicio.
     */
    public function distribucion(): Response
    {
        $encabezados = ['fecha', 'nivel', 'fase', 'kg_desperdiciados', 'kg_distribuidos', 'indice_mermas_pct', 'tiempo_distribucion_min'];

        $filas = ControlDistribucion::orderBy('fecha')->cursor()->map(fn ($r) => [
            $r->fecha->toDateString(),
            $r->nivel,
            $r->fase,
            $r->kg_desperdiciados,
            $r->kg_distribuidos,
            $r->indice_mermas,
            $r->tiempo_distribucion_min,
        ]);

        return $this->csvResponse('ficha6_distribucion_desperdicio.csv', $encabezados, $filas);
    }

    /**
     * Fichas 1, 2 y 3 — Preprocesamiento, entrenamiento/validación y arquitectura del modelo.
     */
    public function iaEntrenamientos(): Response
    {
        $encabezados = [
            'fecha', 'nivel', 'fase', 'pct_depurados', 'pct_completos',
            'k_folds', 'mae', 'rmse', 'mape', 'r2', 'n_estimators', 'max_depth', 'tiempo_entrenamiento_seg',
        ];

        $filas = IaEntrenamiento::orderBy('created_at')->cursor()->map(fn ($e) => [
            $e->created_at->toDateTimeString(),
            $e->nivel,
            $e->fase,
            $e->pct_depurados,
            $e->pct_completos,
            $e->k_folds,
            $e->mae,
            $e->rmse,
            $e->mape,
            $e->r2,
            $e->n_estimators,
            $e->max_depth,
            $e->tiempo_entrenamiento_seg,
        ]);

        return $this->csvResponse('fichas1-3_modelo_ia.csv', $encabezados, $filas);
    }

    // ─────────────────────────────────────────────────────────────────────
    // Exportadores PAREADOS pretest/postest, listos para Shapiro-Wilk +
    // t-Student de muestras relacionadas / Wilcoxon en SPSS (metodología,
    // diseño preexperimental de mediciones repetidas). Cada uno produce
    // exactamente dos columnas: pretest y postest, del mismo indicador.
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Combina dos listas de valores en filas [pretest, postest], pareadas por
     * orden secuencial. Si un grupo tiene más casos que el otro, las celdas
     * sobrantes quedan vacías (SPSS las trata como perdidas por pareja, que
     * es el manejo estándar en la prueba de muestras relacionadas).
     */
    private function parearPretestPostest(array $pretest, array $postest): array
    {
        $n = max(count($pretest), count($postest));
        $filas = [];
        for ($i = 0; $i < $n; $i++) {
            $filas[] = [$pretest[$i] ?? '', $postest[$i] ?? ''];
        }
        return $filas;
    }

    /**
     * Comparativo pareado — Hipótesis específica 1: error de estimación de
     * raciones (|RP - RC|) antes y después de la implementación del modelo.
     */
    public function comparativoRaciones(): Response
    {
        $valores = fn (string $fase) => RegistroAsistencia::where('fase', $fase)
            ->whereNotNull('raciones_planificadas')
            ->orderBy('fecha')
            ->get()
            ->map(fn ($r) => abs($r->desviacion_raciones))
            ->all();

        $filas = $this->parearPretestPostest($valores('pretest'), $valores('postest'));

        return $this->csvResponse(
            'comparativo_h1_error_estimacion_raciones.csv',
            ['error_estimacion_pretest', 'error_estimacion_postest'],
            $filas
        );
    }

    /**
     * Comparativo pareado — Hipótesis específica 3: nivel de cumplimiento de
     * raciones nutricionales, medido como el error absoluto en gramos
     * (|planificado - servido|; a menor valor, mayor cumplimiento).
     */
    public function comparativoNutricional(): Response
    {
        $valores = fn (string $fase) => ControlNutricional::where('fase', $fase)
            ->orderBy('fecha')
            ->get()
            ->map(fn ($r) => abs($r->diferencia))
            ->all();

        $filas = $this->parearPretestPostest($valores('pretest'), $valores('postest'));

        return $this->csvResponse(
            'comparativo_h3_precision_nutricional.csv',
            ['error_gramos_pretest', 'error_gramos_postest'],
            $filas
        );
    }

    /**
     * Comparativo pareado — Hipótesis específica 4: eficiencia de distribución,
     * componente de desperdicio (índice de mermas, %).
     */
    public function comparativoMermas(): Response
    {
        $valores = fn (string $fase) => ControlDistribucion::where('fase', $fase)
            ->orderBy('fecha')
            ->get()
            ->map(fn ($r) => $r->indice_mermas)
            ->all();

        $filas = $this->parearPretestPostest($valores('pretest'), $valores('postest'));

        return $this->csvResponse(
            'comparativo_h4_indice_mermas.csv',
            ['indice_mermas_pretest', 'indice_mermas_postest'],
            $filas
        );
    }

    /**
     * Comparativo pareado — Hipótesis específica 4: eficiencia de distribución,
     * componente de tiempo de distribución (minutos).
     */
    public function comparativoTiempoDistribucion(): Response
    {
        $valores = fn (string $fase) => ControlDistribucion::where('fase', $fase)
            ->orderBy('fecha')
            ->pluck('tiempo_distribucion_min')
            ->all();

        $filas = $this->parearPretestPostest($valores('pretest'), $valores('postest'));

        return $this->csvResponse(
            'comparativo_h4_tiempo_distribucion.csv',
            ['tiempo_min_pretest', 'tiempo_min_postest'],
            $filas
        );
    }

    /**
     * Análisis de variables de contexto (clima, eventos especiales) frente a
     * la demanda de raciones, con la prueba t de Welch por nivel. Complementa
     * el reporte visual en /analisis-contexto con un CSV listo para el anexo
     * de resultados de la tesis.
     */
    public function contexto(): Response
    {
        $encabezados = ['nivel', 'variable', 'categoria', 'promedio_raciones_dia', 'n', 't', 'df', 'p', 'significativo'];
        $filas = [];

        foreach (['inicial', 'primaria'] as $nivel) {
            $dias = collect($this->contextoDiario($nivel));

            $porClima = $dias->groupBy('clima');
            $rSoleado = $porClima->get('soleado', collect())->pluck('raciones')->all();
            $rLluvioso = $porClima->get('lluvioso', collect())->pluck('raciones')->all();
            $testClima = Stats::welchTTest($rSoleado, $rLluvioso);

            foreach (['soleado', 'nublado', 'lluvioso'] as $clima) {
                $valores = $porClima->get($clima, collect())->pluck('raciones')->all();
                if (empty($valores)) continue;
                $filas[] = [
                    $nivel, 'clima', $clima,
                    round(array_sum($valores) / count($valores), 2), count($valores),
                    $testClima['t'] ?? '', $testClima['df'] ?? '', $testClima['p'] ?? '',
                    ($testClima['significativo'] ?? false) ? 1 : 0,
                ];
            }

            $porEvento = $dias->groupBy('evento_especial');
            $rNormal = $porEvento->get(false, collect())->pluck('raciones')->all();
            $rEspecial = $porEvento->get(true, collect())->pluck('raciones')->all();
            $testEvento = Stats::welchTTest($rNormal, $rEspecial);

            foreach (['normal' => $rNormal, 'especial' => $rEspecial] as $categoria => $valores) {
                if (empty($valores)) continue;
                $filas[] = [
                    $nivel, 'evento_especial', $categoria,
                    round(array_sum($valores) / count($valores), 2), count($valores),
                    $testEvento['t'] ?? '', $testEvento['df'] ?? '', $testEvento['p'] ?? '',
                    ($testEvento['significativo'] ?? false) ? 1 : 0,
                ];
            }
        }

        return $this->csvResponse('analisis_contexto.csv', $encabezados, $filas);
    }
}
