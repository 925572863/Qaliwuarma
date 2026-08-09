<?php

namespace App\Support;

/**
 * Utilidades estadísticas mínimas para comparar dos grupos independientes
 * sin depender de una librería externa: prueba t de Welch (no asume
 * varianzas iguales) con p-valor exacto vía la función beta incompleta
 * regularizada (algoritmo estándar de Numerical Recipes).
 *
 * Se usa en el reporte de Análisis de Contexto para determinar si las
 * diferencias observadas (ej. raciones en días lluviosos vs soleados) son
 * estadísticamente significativas o solo variación muestral.
 */
class Stats
{
    public static function media(array $valores): float
    {
        return array_sum($valores) / count($valores);
    }

    public static function varianza(array $valores): float
    {
        $n = count($valores);
        if ($n < 2) return 0.0;
        $m = self::media($valores);
        $sumaCuadrados = array_sum(array_map(fn ($v) => ($v - $m) ** 2, $valores));
        return $sumaCuadrados / ($n - 1);
    }

    /**
     * Prueba t de Welch para dos muestras independientes.
     * Devuelve null si alguna muestra tiene menos de 2 observaciones o si
     * ambas tienen varianza cero (no hay forma de estimar el error estándar).
     *
     * @return array{t: float, df: float, p: ?float, significativo: bool, n1: int, n2: int}|null
     */
    public static function welchTTest(array $a, array $b, float $alpha = 0.05): ?array
    {
        $n1 = count($a);
        $n2 = count($b);
        if ($n1 < 2 || $n2 < 2) {
            return null;
        }

        $m1 = self::media($a);
        $m2 = self::media($b);
        $v1 = self::varianza($a);
        $v2 = self::varianza($b);

        $errorEstandar = sqrt($v1 / $n1 + $v2 / $n2);
        if ($errorEstandar == 0.0) {
            return null;
        }

        $t = ($m1 - $m2) / $errorEstandar;

        $df = ($v1 / $n1 + $v2 / $n2) ** 2
            / ((($v1 / $n1) ** 2) / ($n1 - 1) + (($v2 / $n2) ** 2) / ($n2 - 1));

        $p = self::pValorTDosColas(abs($t), $df);

        return [
            't' => round($t, 3),
            'df' => round($df, 2),
            'p' => $p !== null ? round($p, 4) : null,
            'significativo' => $p !== null && $p < $alpha,
            'n1' => $n1,
            'n2' => $n2,
        ];
    }

    private static function pValorTDosColas(float $t, float $df): ?float
    {
        if ($df <= 0) return null;
        $x = $df / ($df + $t * $t);
        return self::betaIncompletaRegularizada($df / 2, 0.5, $x);
    }

    private static function betaIncompletaRegularizada(float $a, float $b, float $x): float
    {
        if ($x <= 0.0) return 0.0;
        if ($x >= 1.0) return 1.0;

        $bt = exp(
            self::logGamma($a + $b) - self::logGamma($a) - self::logGamma($b)
            + $a * log($x) + $b * log(1 - $x)
        );

        if ($x < ($a + 1) / ($a + $b + 2)) {
            return $bt * self::fraccionContinuaBeta($a, $b, $x) / $a;
        }
        return 1 - $bt * self::fraccionContinuaBeta($b, $a, 1 - $x) / $b;
    }

    private static function fraccionContinuaBeta(float $a, float $b, float $x, int $maxIter = 200, float $eps = 1e-10): float
    {
        $qab = $a + $b;
        $qap = $a + 1;
        $qam = $a - 1;
        $c = 1.0;
        $d = 1 - $qab * $x / $qap;
        if (abs($d) < 1e-30) $d = 1e-30;
        $d = 1 / $d;
        $h = $d;

        for ($m = 1; $m <= $maxIter; $m++) {
            $m2 = 2 * $m;
            $aa = $m * ($b - $m) * $x / (($qam + $m2) * ($a + $m2));
            $d = 1 + $aa * $d; if (abs($d) < 1e-30) $d = 1e-30;
            $c = 1 + $aa / $c; if (abs($c) < 1e-30) $c = 1e-30;
            $d = 1 / $d;
            $h *= $d * $c;

            $aa = -($a + $m) * ($qab + $m) * $x / (($a + $m2) * ($qap + $m2));
            $d = 1 + $aa * $d; if (abs($d) < 1e-30) $d = 1e-30;
            $c = 1 + $aa / $c; if (abs($c) < 1e-30) $c = 1e-30;
            $d = 1 / $d;
            $delta = $d * $c;
            $h *= $delta;

            if (abs($delta - 1.0) < $eps) break;
        }
        return $h;
    }

    /** Aproximación de Lanczos para ln(Γ(x)). */
    private static function logGamma(float $x): float
    {
        static $coeficientes = [
            676.5203681218851, -1259.1392167224028, 771.32342877765313,
            -176.61502916214059, 12.507343278686905, -0.13857109526572012,
            9.9843695780195716e-6, 1.5056327351493116e-7,
        ];

        if ($x < 0.5) {
            return log(M_PI / sin(M_PI * $x)) - self::logGamma(1 - $x);
        }

        $x -= 1;
        $a = 0.99999999999980993;
        $t = $x + 7.5;
        foreach ($coeficientes as $i => $c) {
            $a += $c / ($x + $i + 1);
        }

        return 0.5 * log(2 * M_PI) + ($x + 0.5) * log($t) - $t + log($a);
    }
}
