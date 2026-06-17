<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosTercerGradoASeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'OLIVER ESNAIDER',          'apellido_paterno' => 'AGUILAR',      'apellido_materno' => 'CASTILLO',    'genero' => 'M', 'fecha_nacimiento' => '2018-03-03'],
            ['nombre' => 'JEFRY SEBASTIAN',          'apellido_paterno' => 'ALCOSER',      'apellido_materno' => 'DIAZ',        'genero' => 'M', 'fecha_nacimiento' => '2017-06-22'],
            ['nombre' => 'IKER DARKIEL',             'apellido_paterno' => 'ARRIOLA',      'apellido_materno' => 'NUÑEZ',       'genero' => 'M', 'fecha_nacimiento' => '2018-03-14'],
            ['nombre' => 'DIEGO ALEJANDRO',          'apellido_paterno' => 'BALLESTEROS',  'apellido_materno' => 'VILLEGAS',    'genero' => 'M', 'fecha_nacimiento' => '2016-10-02'],
            ['nombre' => 'BELEN MAHANAIM',           'apellido_paterno' => 'CASTRO',       'apellido_materno' => 'FLORES',      'genero' => 'F', 'fecha_nacimiento' => '2017-12-12'],
            ['nombre' => 'MILAN ANTOINIE',           'apellido_paterno' => 'CORDOVA',      'apellido_materno' => 'CHAMBA',      'genero' => 'M', 'fecha_nacimiento' => '2017-07-08'],
            ['nombre' => 'LUCAS JARED',              'apellido_paterno' => 'CRISANTO',     'apellido_materno' => 'MEDINA',      'genero' => 'M', 'fecha_nacimiento' => '2017-12-13'],
            ['nombre' => 'CÉDRIC KOVACIC',           'apellido_paterno' => 'CUEVA',        'apellido_materno' => 'JUAREZ',      'genero' => 'M', 'fecha_nacimiento' => '2017-07-01'],
            ['nombre' => 'THIAGO NICOLAS',           'apellido_paterno' => 'CURAY',        'apellido_materno' => 'FLORES',      'genero' => 'M', 'fecha_nacimiento' => '2017-09-14'],
            ['nombre' => 'LIAN VALENTINO',           'apellido_paterno' => 'DAVIS',        'apellido_materno' => 'FEBRES',      'genero' => 'M', 'fecha_nacimiento' => '2017-05-04'],
            ['nombre' => 'PRISCILA PAOLA',           'apellido_paterno' => 'DIOSES',       'apellido_materno' => 'YAMUNAQUE',   'genero' => 'F', 'fecha_nacimiento' => '2017-07-22'],
            ['nombre' => 'ARIANA ANTONELLA',         'apellido_paterno' => 'ESCATE',       'apellido_materno' => 'LLONTOP',     'genero' => 'F', 'fecha_nacimiento' => '2017-09-06'],
            ['nombre' => 'ANNIE KRISTHEL',           'apellido_paterno' => 'FARFAN',       'apellido_materno' => 'PINZON',      'genero' => 'F', 'fecha_nacimiento' => '2017-09-24'],
            ['nombre' => 'KALILA MIKEYLA',           'apellido_paterno' => 'GALVEZ',       'apellido_materno' => 'RUFINO',      'genero' => 'F', 'fecha_nacimiento' => '2017-05-13'],
            ['nombre' => 'GUADALUPE YARET',          'apellido_paterno' => 'GONZALES',     'apellido_materno' => 'MOROCHO',     'genero' => 'F', 'fecha_nacimiento' => '2017-07-03'],
            ['nombre' => 'IVANNA ISAMAR',            'apellido_paterno' => 'HERNANDEZ',    'apellido_materno' => 'FERIA',       'genero' => 'F', 'fecha_nacimiento' => '2017-09-26'],
            ['nombre' => 'ARTHUR BENJAMÍN',          'apellido_paterno' => 'HUANCAS',      'apellido_materno' => 'COVEÑAS',     'genero' => 'M', 'fecha_nacimiento' => '2018-02-07'],
            ['nombre' => 'PIEERS MATHIAS MISAEL',    'apellido_paterno' => 'LIZANA',       'apellido_materno' => 'BELLO',       'genero' => 'M', 'fecha_nacimiento' => '2017-04-29'],
            ['nombre' => 'BRITTANY YAMILETH',        'apellido_paterno' => 'MAZA',         'apellido_materno' => 'QUEZADA',     'genero' => 'F', 'fecha_nacimiento' => '2015-12-10'],
            ['nombre' => 'LIAM RAFAEL',              'apellido_paterno' => 'MECHAN',       'apellido_materno' => 'SALAZAR',     'genero' => 'M', 'fecha_nacimiento' => '2018-03-06'],
            ['nombre' => 'ANDERSÓN JESÚS',           'apellido_paterno' => 'MEJIA',        'apellido_materno' => 'REQUENA',     'genero' => 'M', 'fecha_nacimiento' => '2017-09-27'],
            ['nombre' => 'MILEY CIRUS',              'apellido_paterno' => 'MELENDRES',    'apellido_materno' => 'HERRERA',     'genero' => 'F', 'fecha_nacimiento' => '2017-08-31'],
            ['nombre' => 'GUSTAVO LEONEL',           'apellido_paterno' => 'NAQUICHE',     'apellido_materno' => 'PEÑA',        'genero' => 'M', 'fecha_nacimiento' => '2016-05-20'],
            ['nombre' => 'JOSUÉ PAÚL',               'apellido_paterno' => 'NAVARRO',      'apellido_materno' => 'VELASQUEZ',   'genero' => 'M', 'fecha_nacimiento' => '2017-07-19'],
            ['nombre' => 'SANTIAGO NICOLAS',         'apellido_paterno' => 'RIOFRIO',      'apellido_materno' => 'NAVARRO',     'genero' => 'M', 'fecha_nacimiento' => '2017-07-07'],
            ['nombre' => 'TEO GAEL',                 'apellido_paterno' => 'ROJAS',        'apellido_materno' => 'RAMIREZ',     'genero' => 'M', 'fecha_nacimiento' => '2017-07-19'],
            ['nombre' => 'ORIANA VALENTINA',         'apellido_paterno' => 'SANTUR',       'apellido_materno' => 'HERRERA',     'genero' => 'F', 'fecha_nacimiento' => '2017-05-15'],
            ['nombre' => 'ALEJANDRO ANGEL MANUEL',   'apellido_paterno' => 'TAPIA',        'apellido_materno' => 'CHAVEZ',      'genero' => 'M', 'fecha_nacimiento' => '2018-01-28'],
            ['nombre' => 'DANNA DE LOS ANGELES',     'apellido_paterno' => 'TORRES',       'apellido_materno' => 'VILCA',       'genero' => 'F', 'fecha_nacimiento' => '2016-04-10'],
            ['nombre' => 'DAYANNA DE LA LUZ',        'apellido_paterno' => 'ZAPATA',       'apellido_materno' => 'GARCES',      'genero' => 'F', 'fecha_nacimiento' => '2017-07-03'],
            ['nombre' => 'ANGELINA NAOMY',           'apellido_paterno' => 'ZARATE',       'apellido_materno' => 'RODRIGUEZ',   'genero' => 'F', 'fecha_nacimiento' => '2016-07-13'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P3A{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '3° GRADO A',
                    'semestre'          => 3,
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
