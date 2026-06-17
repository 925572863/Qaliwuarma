<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosSextoGradoBSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'MYLAN ISAIAS',             'apellido_paterno' => 'ARBOLEDA',       'apellido_materno' => 'VILLA',         'genero' => 'M', 'fecha_nacimiento' => '2015-03-08'],
            ['nombre' => 'KEVIN ANTONIO',            'apellido_paterno' => 'ARROYO',         'apellido_materno' => 'RIVERA',        'genero' => 'M', 'fecha_nacimiento' => '2014-12-27'],
            ['nombre' => 'MIRIAM ANGELA KRISTELL',   'apellido_paterno' => 'CARREÑO',        'apellido_materno' => 'ANCAJIMA',      'genero' => 'F', 'fecha_nacimiento' => '2014-05-19'],
            ['nombre' => 'JORGE MATHÍAS',            'apellido_paterno' => 'CASTILLO',       'apellido_materno' => 'SERNAQUE',      'genero' => 'M', 'fecha_nacimiento' => '2014-12-30'],
            ['nombre' => 'ALONDRA VALENTINA',        'apellido_paterno' => 'CHAPOÑAN',       'apellido_materno' => 'BARRANZUELA',   'genero' => 'F', 'fecha_nacimiento' => '2014-04-11'],
            ['nombre' => 'LIAM SALVADOR ESTIK',      'apellido_paterno' => 'CHUMACERO',      'apellido_materno' => 'PAIBA',         'genero' => 'M', 'fecha_nacimiento' => '2014-11-07'],
            ['nombre' => 'BRIANA DE LOS ANGELES',    'apellido_paterno' => 'CRUZ',           'apellido_materno' => 'BARRANZUELA',   'genero' => 'F', 'fecha_nacimiento' => '2014-03-05'],
            ['nombre' => 'ANABELISA YESENIA',        'apellido_paterno' => 'DAVIS',          'apellido_materno' => 'FEBRES',        'genero' => 'F', 'fecha_nacimiento' => '2013-05-02'],
            ['nombre' => 'JOSEP ALONSO',             'apellido_paterno' => 'ESTRADA',        'apellido_materno' => 'MORE',          'genero' => 'M', 'fecha_nacimiento' => '2014-04-23'],
            ['nombre' => 'JAMES TADEO',              'apellido_paterno' => 'FACUNDO',        'apellido_materno' => 'SILVA',         'genero' => 'M', 'fecha_nacimiento' => '2014-09-28'],
            ['nombre' => 'JACKELINE ISABEL',         'apellido_paterno' => 'FARFAN',         'apellido_materno' => 'PINZON',        'genero' => 'F', 'fecha_nacimiento' => '2014-06-27'],
            ['nombre' => 'CARLOS EDUARDO',           'apellido_paterno' => 'GARCES',         'apellido_materno' => 'CASTILLO',      'genero' => 'M', 'fecha_nacimiento' => '2014-07-30'],
            ['nombre' => 'MARIA ALEJANDRA',          'apellido_paterno' => 'GARCIA',         'apellido_materno' => 'ROSAS',         'genero' => 'F', 'fecha_nacimiento' => '2015-03-14'],
            ['nombre' => 'DIANA LUCIA',              'apellido_paterno' => 'GARCIA',         'apellido_materno' => 'TRONCOS',       'genero' => 'F', 'fecha_nacimiento' => '2013-09-01'],
            ['nombre' => 'HAIDANNA NAZARETH',        'apellido_paterno' => 'GUEVARA',        'apellido_materno' => 'RIVERO',        'genero' => 'F', 'fecha_nacimiento' => '2013-08-26'],
            ['nombre' => 'MARCELO BENJAMIN',         'apellido_paterno' => 'JIMENEZ',        'apellido_materno' => 'CORDOVA',       'genero' => 'M', 'fecha_nacimiento' => '2014-07-06'],
            ['nombre' => 'SEBASTIAN ANDRE',          'apellido_paterno' => 'JIMENEZ',        'apellido_materno' => 'CORDOVA',       'genero' => 'M', 'fecha_nacimiento' => '2014-07-06'],
            ['nombre' => 'DYLAN ELIAS',              'apellido_paterno' => 'LUDEÑA',         'apellido_materno' => 'AÑAZCO',        'genero' => 'M', 'fecha_nacimiento' => '2014-07-25'],
            ['nombre' => 'ARIADNE VALESKA',          'apellido_paterno' => 'MORENO',         'apellido_materno' => 'ZOCOLA',        'genero' => 'F', 'fecha_nacimiento' => '2014-07-21'],
            ['nombre' => 'VALERIA VALESKA',          'apellido_paterno' => 'NUÑEZ',          'apellido_materno' => 'RODRIGUEZ',     'genero' => 'F', 'fecha_nacimiento' => '2015-03-05'],
            ['nombre' => 'SANTIAGO DE JESUS',        'apellido_paterno' => 'PEREZ',          'apellido_materno' => 'APONTE',        'genero' => 'M', 'fecha_nacimiento' => '2014-06-27'],
            ['nombre' => 'JEFFERSON JEANCARLOS',     'apellido_paterno' => 'PRADO',          'apellido_materno' => 'GUERRERO',      'genero' => 'M', 'fecha_nacimiento' => '2014-08-22'],
            ['nombre' => 'THIAGO ALEXANDER',         'apellido_paterno' => 'QUINDE',         'apellido_materno' => 'MORALES',       'genero' => 'M', 'fecha_nacimiento' => '2013-12-16'],
            ['nombre' => 'ROCXY MERCEDES',           'apellido_paterno' => 'RAMIREZ',        'apellido_materno' => 'CHIRA',         'genero' => 'F', 'fecha_nacimiento' => '2014-04-30'],
            ['nombre' => 'MANUEL ALEXANDER',         'apellido_paterno' => 'RAMIREZ',        'apellido_materno' => 'GARCIA',        'genero' => 'M', 'fecha_nacimiento' => '2013-05-01'],
            ['nombre' => 'ROBERT BLADIMIR',          'apellido_paterno' => 'RUIZ',           'apellido_materno' => 'DIAZ',          'genero' => 'M', 'fecha_nacimiento' => '2015-03-02'],
            ['nombre' => 'MIA SARAH',                'apellido_paterno' => 'SAAVEDRA',       'apellido_materno' => 'CALDERON',      'genero' => 'F', 'fecha_nacimiento' => '2014-12-03'],
            ['nombre' => 'JESUS DAVID',              'apellido_paterno' => 'SAAVEDRA',       'apellido_materno' => 'RUESTA',        'genero' => 'M', 'fecha_nacimiento' => '2014-07-09'],
            ['nombre' => 'STEVEN MANUEL',            'apellido_paterno' => 'SALAZAR',        'apellido_materno' => 'MAYANGA',       'genero' => 'M', 'fecha_nacimiento' => '2015-02-02'],
            ['nombre' => 'VANIA VALERIA',            'apellido_paterno' => 'ZEVALLOS',       'apellido_materno' => 'CORREA',        'genero' => 'F', 'fecha_nacimiento' => '2012-10-10'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P6B{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '6° GRADO B',
                    'semestre'          => 6,
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
