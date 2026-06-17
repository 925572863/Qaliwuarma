<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosInicialCincoAnosBSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'GIAN ADRIEL', 'apellido_paterno' => 'ALDEAN', 'apellido_materno' => 'CARRASCO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92256513'],
            ['nombre' => 'YASUMY ANYELI', 'apellido_paterno' => 'BAYONA', 'apellido_materno' => 'FIESTAS', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92013817'],
            ['nombre' => 'JHORDY STANIMIR', 'apellido_paterno' => 'CANGO', 'apellido_materno' => 'TAMAYO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92237800'],
            ['nombre' => 'ARNALDO NATHANIEL', 'apellido_paterno' => 'CARRASCO', 'apellido_materno' => 'MANRIQUE', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92221111'],
            ['nombre' => 'BIANCA THAYSA', 'apellido_paterno' => 'CASTRO', 'apellido_materno' => 'FLORES', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92139087'],
            ['nombre' => 'ABDIEL ALESSANDRO', 'apellido_paterno' => 'CHINININ', 'apellido_materno' => 'ROSAS', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92224055'],
            ['nombre' => 'ANTONI GABRIEL', 'apellido_paterno' => 'CHORRES', 'apellido_materno' => 'SANTOS', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92163174'],
            ['nombre' => 'LUCIANA ANTONELLA', 'apellido_paterno' => 'ACOSTA', 'apellido_materno' => 'CORONEL', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92004400'],
            ['nombre' => 'LUIS JESÚS', 'apellido_paterno' => 'CRUZ', 'apellido_materno' => 'CHUNGA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92156471'],
            ['nombre' => 'EMILY BRAELYN', 'apellido_paterno' => 'FEBRES', 'apellido_materno' => 'ALBAN', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92064994'],
            ['nombre' => 'CARLOS ARTURO', 'apellido_paterno' => 'FLORES', 'apellido_materno' => 'CHAVEZ', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92047448'],
            ['nombre' => 'MATEO VALENTINO', 'apellido_paterno' => 'GALLARDO', 'apellido_materno' => 'HUAMANCHUMO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92125896'],
            ['nombre' => 'OSCAR LIAM MATHEO', 'apellido_paterno' => 'HERNANDEZ', 'apellido_materno' => 'PEREZ', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92207864'],
            ['nombre' => 'SOFIA MAIREE', 'apellido_paterno' => 'HUERTAS', 'apellido_materno' => 'GARCIA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '91904026'],
            ['nombre' => 'AMY CATHALEYA', 'apellido_paterno' => 'LOPEZ', 'apellido_materno' => 'TIMOTEO', 'genero' => 'F', 'estado' => 'baja', 'curp' => '92254467'],
            ['nombre' => 'ERICK ZACDIEL', 'apellido_paterno' => 'LUZON', 'apellido_materno' => 'VILELA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '91862757'],
            ['nombre' => 'NOAH SEBASTIAN', 'apellido_paterno' => 'MAZA', 'apellido_materno' => 'PACHERRES', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92187971'],
            ['nombre' => 'ALONDRA DAYANA', 'apellido_paterno' => 'MIJAHUANGA', 'apellido_materno' => 'RODRIGUEZ', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92014606'],
            ['nombre' => 'LEA NAOMY', 'apellido_paterno' => 'MORALES', 'apellido_materno' => 'SILVA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92165235'],
            ['nombre' => 'DANNA NICOLL', 'apellido_paterno' => 'NOE', 'apellido_materno' => 'VELAOCHAGA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92112902'],
            ['nombre' => 'THAYSA NIKOL', 'apellido_paterno' => 'PANTA', 'apellido_materno' => 'SILUPU', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92102022'],
            ['nombre' => 'AXEL LEONEL', 'apellido_paterno' => 'PONCE', 'apellido_materno' => 'ESPINOZA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92095027'],
            ['nombre' => 'JAIRO ANGEL', 'apellido_paterno' => 'REQUENA', 'apellido_materno' => 'IZQUIERDO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92192457'],
            ['nombre' => 'BRESLY QUETZALY', 'apellido_paterno' => 'RODRIGUEZ', 'apellido_materno' => 'CASTILLO', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92029724'],
            ['nombre' => 'ROCIO BRIGITTE', 'apellido_paterno' => 'SAMANIEGO', 'apellido_materno' => 'PEÑA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '91886431'],
            ['nombre' => 'YAMILETH LIZBETH', 'apellido_paterno' => 'SAUCEDO', 'apellido_materno' => 'RAMOS', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92027653'],
            ['nombre' => 'MÍA GUADALUPE', 'apellido_paterno' => 'TORRES', 'apellido_materno' => 'PIZARRO', 'genero' => 'F', 'estado' => 'activo', 'curp' => '91858659'],
            ['nombre' => 'BREYNER GAEL', 'apellido_paterno' => 'VARILLAS', 'apellido_materno' => 'ROSAS', 'genero' => 'M', 'estado' => 'activo', 'curp' => '91980107'],
            ['nombre' => 'MARIANGELY GABRIELA', 'apellido_paterno' => 'YANGUA', 'apellido_materno' => 'LOPEZ', 'genero' => 'F', 'estado' => 'baja', 'curp' => '91882452'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026I5B{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'inicial',
                    'carrera'           => 'INICIAL 5 AÑOS B',
                    'semestre'          => 0,
                    'fecha_nacimiento' => '2021-01-01',
                    'fecha_inscripcion' => '2026-03-01',
                ])
            );
        }
    }
}
