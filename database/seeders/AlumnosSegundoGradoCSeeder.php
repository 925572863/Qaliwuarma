<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosSegundoGradoCSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'SOFIA DE LOS ANGELES',   'apellido_paterno' => 'AGUILAR',      'apellido_materno' => 'FLORES',    'genero' => 'F', 'fecha_nacimiento' => '2016-04-15'],
            ['nombre' => 'LYAN FABIAN',             'apellido_paterno' => 'AGUIRRE',      'apellido_materno' => 'YARLEQUE',  'genero' => 'M', 'fecha_nacimiento' => '2018-07-12'],
            ['nombre' => 'ALEXA SEINETN',           'apellido_paterno' => 'BANEO',        'apellido_materno' => 'CUNYA',     'genero' => 'F', 'fecha_nacimiento' => '2019-01-29'],
            ['nombre' => 'KAMILA ISABEL',           'apellido_paterno' => 'CASTILLO',     'apellido_materno' => 'MATIAS',    'genero' => 'F', 'fecha_nacimiento' => '2019-01-05'],
            ['nombre' => 'LEANDRO FRANCISCO',       'apellido_paterno' => 'CASTRO',       'apellido_materno' => 'GONZALES',  'genero' => 'M', 'fecha_nacimiento' => '2018-04-25'],
            ['nombre' => 'EXON KALETH',             'apellido_paterno' => 'CHERO',        'apellido_materno' => 'MIRANDA',   'genero' => 'M', 'fecha_nacimiento' => '2019-01-26'],
            ['nombre' => 'JHONATAN SAÚL',           'apellido_paterno' => 'CHERRE',       'apellido_materno' => 'MECA',      'genero' => 'M', 'fecha_nacimiento' => '2018-04-10'],
            ['nombre' => 'MOISES NATAEL',           'apellido_paterno' => 'COBEÑAS',      'apellido_materno' => 'CALLE',     'genero' => 'M', 'fecha_nacimiento' => '2019-01-28'],
            ['nombre' => 'ABEL ARON',               'apellido_paterno' => 'DUARTE',       'apellido_materno' => 'TORRES',    'genero' => 'M', 'fecha_nacimiento' => '2019-03-06'],
            ['nombre' => 'SEGUNDO EDUARDO',         'apellido_paterno' => 'DUQUE',        'apellido_materno' => 'HUAMAN',    'genero' => 'M', 'fecha_nacimiento' => '2018-12-19'],
            ['nombre' => 'MIA ISABELLA',            'apellido_paterno' => 'ENCALADA',     'apellido_materno' => 'PINGO',     'genero' => 'F', 'fecha_nacimiento' => '2018-09-08'],
            ['nombre' => 'CRISTHIAN SAULO',         'apellido_paterno' => 'FLORES',       'apellido_materno' => 'SEFERINO',  'genero' => 'M', 'fecha_nacimiento' => '2018-05-30'],
            ['nombre' => 'SHEREEN CRISTEL',         'apellido_paterno' => 'FLORES',       'apellido_materno' => 'SOLIS',     'genero' => 'F', 'fecha_nacimiento' => '2018-12-29'],
            ['nombre' => 'CRISTHIAN GAEL',          'apellido_paterno' => 'GARCIA',       'apellido_materno' => 'AREVALO',   'genero' => 'M', 'fecha_nacimiento' => '2019-02-21'],
            ['nombre' => 'ZAORY ALEXANDRA',         'apellido_paterno' => 'GUANILO',      'apellido_materno' => 'IZQUIERDO', 'genero' => 'F', 'fecha_nacimiento' => '2018-05-09'],
            ['nombre' => 'KALEB BECIEL',            'apellido_paterno' => 'LALUPU',       'apellido_materno' => 'PEÑA',      'genero' => 'M', 'fecha_nacimiento' => '2017-05-26'],
            ['nombre' => 'ALESSANDRO DEL PIERO',    'apellido_paterno' => 'LEON',         'apellido_materno' => 'PINGO',     'genero' => 'M', 'fecha_nacimiento' => '2019-01-23'],
            ['nombre' => 'KAMILA DEL SOCORRO',      'apellido_paterno' => 'LOZADA',       'apellido_materno' => 'MOGOLLON',  'genero' => 'F', 'fecha_nacimiento' => '2019-03-18'],
            ['nombre' => 'BRYANA YAMILE',           'apellido_paterno' => 'MENDOZA',      'apellido_materno' => 'FLORES',    'genero' => 'F', 'fecha_nacimiento' => '2017-01-15'],
            ['nombre' => 'GAEL VALENTINO',          'apellido_paterno' => 'MORALES',      'apellido_materno' => 'SILVA',     'genero' => 'M', 'fecha_nacimiento' => '2018-04-04'],
            ['nombre' => 'SOFIA DANIELA',           'apellido_paterno' => 'PAUCAR',       'apellido_materno' => 'PAZ',       'genero' => 'F', 'fecha_nacimiento' => '2018-10-27'],
            ['nombre' => 'LUISMAR SINAI',           'apellido_paterno' => 'RAMIREZ',      'apellido_materno' => 'BECERRA',   'genero' => 'F', 'fecha_nacimiento' => '2018-12-24'],
            ['nombre' => 'LIAM GABRIEL',            'apellido_paterno' => 'RAMIREZ',      'apellido_materno' => 'GUARNIZO',  'genero' => 'M', 'fecha_nacimiento' => '2018-10-30'],
            ['nombre' => 'ZAID NICOLAS',            'apellido_paterno' => 'REYES',        'apellido_materno' => 'CHIRA',     'genero' => 'M', 'fecha_nacimiento' => '2018-11-16'],
            ['nombre' => 'JULIAN ENRIQUE',          'apellido_paterno' => 'ROSILLO',      'apellido_materno' => 'MONDRAGON', 'genero' => 'M', 'fecha_nacimiento' => '2018-04-09'],
            ['nombre' => 'ALICIA VALENTINA',        'apellido_paterno' => 'RUIZ',         'apellido_materno' => 'DIAZ',      'genero' => 'F', 'fecha_nacimiento' => '2018-04-26'],
            ['nombre' => 'FABRIZIO ABDIEL',         'apellido_paterno' => 'SANCARRANCO',  'apellido_materno' => 'CASTRO',    'genero' => 'M', 'fecha_nacimiento' => '2018-12-20'],
            ['nombre' => 'MATEO SANTIAGO',          'apellido_paterno' => 'SANCHEZ',      'apellido_materno' => 'PINGO',     'genero' => 'M', 'fecha_nacimiento' => '2018-07-02'],
            ['nombre' => 'AARÓN',                   'apellido_paterno' => 'VILCHERREZ',   'apellido_materno' => 'URBINA',    'genero' => 'M', 'fecha_nacimiento' => '2018-09-19'],
            ['nombre' => 'SHEYLA ELIANA',           'apellido_paterno' => 'YANGUA',       'apellido_materno' => 'JIMENEZ',   'genero' => 'F', 'fecha_nacimiento' => '2018-11-21'],
            ['nombre' => 'ZOE JADETT',              'apellido_paterno' => 'ZARATE',       'apellido_materno' => 'RODRIGUEZ', 'genero' => 'F', 'fecha_nacimiento' => '2018-08-10'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P2C{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '2° GRADO C',
                    'semestre'          => 2,
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
