<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * ⚠️ DATOS SIMULADOS — SOLO PARA PROBAR EL SISTEMA A ESCALA COMPLETA.
 *
 * NO son datos reales de la institución y NO deben usarse como evidencia
 * en la tesis. Sirven únicamente para ver cómo se comporta el sistema
 * (entrenamiento del modelo IA, reportes, exportadores) con el volumen de
 * datos que describe la metodología: 180 registros operativos diarios
 * (un día por cada día efectivo del calendario escolar).
 *
 * Reemplaza TODO el contenido de registros_asistencia. No lo ejecutes si
 * ya tienes datos reales que quieras conservar.
 *
 * Uso: php artisan db:seed --class=HistoricoSimuladoSeeder
 */
class HistoricoSimuladoSeeder extends Seeder
{
    /** Aulas de inicial: [grado, seccion, total_alumnos] con matrícula real. */
    private const AULAS_INICIAL = [
        ['3 Años', 'A', 25], ['3 Años', 'B', 28],
        ['4 Años', 'A', 24], ['4 Años', 'B', 22], ['4 Años', 'C', 30],
        ['5 Años', 'A', 26], ['5 Años', 'B', 29], ['5 Años', 'C', 29],
    ];

    /** Aulas de primaria: [grado, seccion, total_alumnos] con matrícula real. */
    private const AULAS_PRIMARIA = [
        ['1°', 'A', 28], ['1°', 'B', 29], ['1°', 'C', 30],
        ['2°', 'A', 30], ['2°', 'B', 35], ['2°', 'C', 31],
        ['3°', 'A', 31], ['3°', 'B', 26], ['3°', 'C', 25], ['3°', 'D', 24],
        ['4°', 'A', 28], ['4°', 'B', 30], ['4°', 'C', 29],
        ['5°', 'A', 33], ['5°', 'B', 33], ['5°', 'C', 31],
        ['6°', 'A', 30], ['6°', 'B', 30], ['6°', 'C', 30], ['6°', 'D', 30],
    ];

    private const DIAS_HABILES = 180;

    public function run(): void
    {
        DB::table('registros_asistencia')->delete();

        $fecha = \Carbon\Carbon::parse('2026-03-02'); // lunes, inicio típico del año escolar peruano
        $diaHabil = 0;
        $registros = [];

        while ($diaHabil < self::DIAS_HABILES) {
            if ($fecha->isWeekend()) {
                $fecha->addDay();
                continue;
            }

            $climaRand = mt_rand(1, 100);
            $clima = $climaRand <= 60 ? 'soleado' : ($climaRand <= 85 ? 'nublado' : 'lluvioso');
            $eventoEspecial = mt_rand(1, 100) <= 6; // ~6% de jornadas con evento especial
            // Primera mitad = pretest (antes del modelo), segunda mitad = postest (después)
            $fase = $diaHabil < intdiv(self::DIAS_HABILES, 2) ? 'pretest' : 'postest';

            foreach (['inicial' => self::AULAS_INICIAL, 'primaria' => self::AULAS_PRIMARIA] as $nivel => $aulas) {
                foreach ($aulas as [$grado, $seccion, $totalAlumnos]) {
                    // Tasa base de asistencia con variación aleatoria, penalizada por lluvia
                    $tasaBase = mt_rand(82, 97) / 100;
                    if ($clima === 'lluvioso') {
                        $tasaBase -= mt_rand(5, 15) / 100;
                    }
                    if ($eventoEspecial) {
                        $tasaBase -= mt_rand(0, 10) / 100;
                    }
                    $tasaBase = max(0.5, min(1.0, $tasaBase));

                    $presentes = (int) round($totalAlumnos * $tasaBase);
                    $racionesPlanificadas = $totalAlumnos; // se planifica para toda la matrícula
                    // Pequeña merma aleatoria entre lo servido y lo realmente consumido
                    $raciones = max(0, $presentes - mt_rand(0, 2));

                    $registros[] = [
                        'fecha'                 => $fecha->toDateString(),
                        'nivel'                 => $nivel,
                        'fase'                  => $fase,
                        'grado'                 => $grado,
                        'seccion'               => $seccion,
                        'total_alumnos'         => $totalAlumnos,
                        'presentes'             => $presentes,
                        'raciones'              => $raciones,
                        'raciones_planificadas' => $racionesPlanificadas,
                        'condicion_climatica'   => $clima,
                        'evento_especial'       => $eventoEspecial,
                        'observaciones'         => null,
                        'created_at'            => now(),
                        'updated_at'            => now(),
                    ];
                }
            }

            $fecha->addDay();
            $diaHabil++;
        }

        foreach (array_chunk($registros, 500) as $lote) {
            DB::table('registros_asistencia')->insert($lote);
        }

        $this->command?->info(count($registros) . ' registros simulados creados (' . self::DIAS_HABILES . ' días hábiles × ' . (count(self::AULAS_INICIAL) + count(self::AULAS_PRIMARIA)) . ' aulas).');
    }
}
