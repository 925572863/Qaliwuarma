<?php

namespace Tests\Unit;

use App\Support\Stats;
use PHPUnit\Framework\TestCase;

class StatsTest extends TestCase
{
    public function test_media_calcula_el_promedio(): void
    {
        $this->assertEqualsWithDelta(20.0, Stats::media([10, 20, 30]), 0.0001);
    }

    public function test_varianza_de_una_sola_muestra_es_cero(): void
    {
        $this->assertSame(0.0, Stats::varianza([5]));
    }

    public function test_welch_t_test_devuelve_null_con_muestras_muy_pequenas(): void
    {
        $this->assertNull(Stats::welchTTest([1], [2, 3]));
    }

    public function test_welch_t_test_devuelve_null_si_ambos_grupos_no_varian(): void
    {
        // Mismos valores constantes en ambos grupos -> varianza cero -> error estándar cero
        $this->assertNull(Stats::welchTTest([5, 5, 5], [5, 5, 5]));
    }

    /**
     * Valor de referencia conocido: para 10 grados de libertad, el valor crítico
     * de t a dos colas con alfa=0.05 es 2.228 (tabla t estándar). El p-valor
     * calculado en ese punto debe ser ~0.05.
     */
    public function test_p_valor_coincide_con_el_valor_critico_de_tabla_t_df10(): void
    {
        $reflexion = new \ReflectionClass(Stats::class);
        $metodo = $reflexion->getMethod('pValorTDosColas');
        $metodo->setAccessible(true);

        $p = $metodo->invoke(null, 2.228, 10.0);

        $this->assertEqualsWithDelta(0.05, $p, 0.002);
    }

    public function test_p_valor_coincide_con_el_valor_critico_de_tabla_t_df20(): void
    {
        $reflexion = new \ReflectionClass(Stats::class);
        $metodo = $reflexion->getMethod('pValorTDosColas');
        $metodo->setAccessible(true);

        // df=20, t crítico a alfa=0.05 dos colas = 2.086
        $p = $metodo->invoke(null, 2.086, 20.0);

        $this->assertEqualsWithDelta(0.05, $p, 0.002);
    }

    public function test_welch_detecta_diferencia_significativa_con_medias_muy_separadas(): void
    {
        $grupoA = [10, 11, 9, 10, 12, 10, 11, 9, 10, 11];
        $grupoB = [30, 31, 29, 30, 32, 30, 31, 29, 30, 31];

        $resultado = Stats::welchTTest($grupoA, $grupoB);

        $this->assertNotNull($resultado);
        $this->assertTrue($resultado['significativo']);
        $this->assertLessThan(0.001, $resultado['p']);
    }

    public function test_welch_no_detecta_diferencia_significativa_con_medias_similares_y_ruido(): void
    {
        $grupoA = [10, 12, 8, 11, 9];
        $grupoB = [9, 11, 10, 12, 8];

        $resultado = Stats::welchTTest($grupoA, $grupoB);

        $this->assertNotNull($resultado);
        $this->assertFalse($resultado['significativo']);
        $this->assertGreaterThan(0.05, $resultado['p']);
    }

    public function test_welch_reporta_n_de_cada_grupo(): void
    {
        $resultado = Stats::welchTTest([1, 2, 3], [4, 5]);

        $this->assertSame(3, $resultado['n1']);
        $this->assertSame(2, $resultado['n2']);
    }
}
