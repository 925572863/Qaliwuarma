<?php

namespace Database\Seeders;

use App\Models\RegistroAsistencia;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class RegistroAsistenciaSeeder extends Seeder
{
    public function run(): void
    {
        RegistroAsistencia::truncate();

        $estructura = [
            'primaria' => [
                '1°' => ['A','B','C'],
                '2°' => ['A','B','C'],
                '3°' => ['A','B','C','D'],
                '4°' => ['A','B','C'],
                '5°' => ['A','B','C'],
                '6°' => ['A','B','C','D'],
            ],
            'inicial' => [
                '3 Años' => ['A','B'],
                '4 Años' => ['A','B','C','D'],
                '5 Años' => ['A','B','C'],
            ],
        ];

        $totalesPorGrado = [
            'primaria' => ['1°'=>30,'2°'=>29,'3°'=>31,'4°'=>28,'5°'=>30,'6°'=>32],
            'inicial'  => ['3 Años'=>22,'4 Años'=>24,'5 Años'=>23],
        ];

        for ($d = 60; $d >= 1; $d--) {
            $fecha = Carbon::now()->subDays($d);

            if (in_array($fecha->dayOfWeek, [Carbon::SUNDAY, Carbon::SATURDAY])) {
                continue;
            }

            $progreso = (60 - $d) / 60;

            foreach ($estructura as $nivel => $grados) {
                foreach ($grados as $grado => $secciones) {
                    foreach ($secciones as $seccion) {
                        $total     = $totalesPorGrado[$nivel][$grado] + rand(-1, 1);
                        $baseAsist = 0.82 + ($progreso * 0.06);
                        $presentes = min($total, (int)round($total * ($baseAsist + (rand(-5, 5) / 100))));
                        $presentes = max(0, $presentes);

                        RegistroAsistencia::create([
                            'fecha'         => $fecha->toDateString(),
                            'nivel'         => $nivel,
                            'grado'         => $grado,
                            'seccion'       => $seccion,
                            'total_alumnos' => $total,
                            'presentes'     => $presentes,
                            'raciones'      => $presentes,
                        ]);
                    }
                }
            }
        }
    }
}
