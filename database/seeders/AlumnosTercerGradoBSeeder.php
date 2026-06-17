<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosTercerGradoBSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'CARLOS DANIEL',           'apellido_paterno' => 'ANCAJIMA',      'apellido_materno' => 'CORDOVA',     'genero' => 'M', 'fecha_nacimiento' => '2018-01-22'],
            ['nombre' => 'LUCAS JADIEL',            'apellido_paterno' => 'CARMEN',        'apellido_materno' => 'FIESTAS',     'genero' => 'M', 'fecha_nacimiento' => '2017-07-04'],
            ['nombre' => 'RAMON AGUSTIN',           'apellido_paterno' => 'CASTRO',        'apellido_materno' => 'CHUMACERO',   'genero' => 'M', 'fecha_nacimiento' => '2017-10-14'],
            ['nombre' => 'ESPERANZA ROSA MÍA',      'apellido_paterno' => 'CASTRO',        'apellido_materno' => 'GONZALES',    'genero' => 'F', 'fecha_nacimiento' => '2015-12-06'],
            ['nombre' => 'MILLER JOSUE',            'apellido_paterno' => 'CHUQUIHUANGA',  'apellido_materno' => 'RAMIREZ',     'genero' => 'M', 'fecha_nacimiento' => '2017-06-05'],
            ['nombre' => 'JEREMY ISAID',            'apellido_paterno' => 'CORONADO',      'apellido_materno' => 'ESPINOZA',    'genero' => 'M', 'fecha_nacimiento' => '2017-10-02'],
            ['nombre' => 'ALIZON CRISTEL',          'apellido_paterno' => 'DOMINGUEZ',     'apellido_materno' => 'BARRANZUELA', 'genero' => 'F', 'fecha_nacimiento' => '2018-03-04'],
            ['nombre' => 'VALERY BELÉN',            'apellido_paterno' => 'GUERRERO',      'apellido_materno' => 'JULCA',       'genero' => 'F', 'fecha_nacimiento' => '2017-09-20'],
            ['nombre' => 'BENJAMIN JAFETH',         'apellido_paterno' => 'HIDALGO',       'apellido_materno' => 'LACHIRA',     'genero' => 'M', 'fecha_nacimiento' => '2018-03-02'],
            ['nombre' => 'ASHLEY DAYANA',           'apellido_paterno' => 'HINOJOSA',      'apellido_materno' => 'MIO',         'genero' => 'F', 'fecha_nacimiento' => '2016-11-19'],
            ['nombre' => 'JASÓN ZAID',              'apellido_paterno' => 'HUAMAN',        'apellido_materno' => 'SAAVEDRA',    'genero' => 'M', 'fecha_nacimiento' => '2017-07-19'],
            ['nombre' => 'DANNA VALENTINA',         'apellido_paterno' => 'JIMENEZ',       'apellido_materno' => 'CAMPOS',      'genero' => 'F', 'fecha_nacimiento' => '2017-03-27'],
            ['nombre' => 'ROSILLO BELEN',           'apellido_paterno' => 'MALDONADO',     'apellido_materno' => 'CHINCHAY',    'genero' => 'F', 'fecha_nacimiento' => '2017-07-06'],
            ['nombre' => 'ALESSIA',                 'apellido_paterno' => 'MASACHE',       'apellido_materno' => 'JIMENEZ',     'genero' => 'F', 'fecha_nacimiento' => '2017-12-25'],
            ['nombre' => 'HANS JARED',              'apellido_paterno' => 'MEJIA',         'apellido_materno' => 'VIERA',       'genero' => 'M', 'fecha_nacimiento' => '2017-04-03'],
            ['nombre' => 'CELESTE THAIS',           'apellido_paterno' => 'MORE',          'apellido_materno' => 'VIDAL',       'genero' => 'F', 'fecha_nacimiento' => '2016-12-09'],
            ['nombre' => 'DANILO GAEL',             'apellido_paterno' => 'NAVARRO',       'apellido_materno' => 'ABAD',        'genero' => 'M', 'fecha_nacimiento' => '2017-09-28'],
            ['nombre' => 'ÍTALA BRUNELLA',          'apellido_paterno' => 'NUÑEZ',         'apellido_materno' => 'NAVARRO',     'genero' => 'F', 'fecha_nacimiento' => '2017-06-29'],
            ['nombre' => 'TANIA BERENICE',          'apellido_paterno' => 'NUÑEZ',         'apellido_materno' => 'RODRIGUEZ',   'genero' => 'F', 'fecha_nacimiento' => '2017-05-23'],
            ['nombre' => 'HENYIEL NAOMY',           'apellido_paterno' => 'OVIEDO',        'apellido_materno' => 'TALLEDO',     'genero' => 'F', 'fecha_nacimiento' => '2017-04-19'],
            ['nombre' => 'STEFHANY GUADALUPE',      'apellido_paterno' => 'RODAS',         'apellido_materno' => 'MOROCHO',     'genero' => 'F', 'fecha_nacimiento' => '2017-11-13'],
            ['nombre' => 'DYLAN YADIEL',            'apellido_paterno' => 'SAAVEDRA',      'apellido_materno' => 'VIDAL',       'genero' => 'M', 'fecha_nacimiento' => '2017-08-11'],
            ['nombre' => 'FERNANDO ARQUIMEDES',     'apellido_paterno' => 'SIGUENZA',      'apellido_materno' => 'SANCHEZ',     'genero' => 'M', 'fecha_nacimiento' => '2017-05-29'],
            ['nombre' => 'NORMA MIA',               'apellido_paterno' => 'SILVA',         'apellido_materno' => 'ARROYO',      'genero' => 'F', 'fecha_nacimiento' => '2017-09-12'],
            ['nombre' => 'EVA LUNA',                'apellido_paterno' => 'VELASCO',       'apellido_materno' => 'BENAVENTE',   'genero' => 'F', 'fecha_nacimiento' => '2017-11-17'],
            ['nombre' => 'EMILY CAYETANA',          'apellido_paterno' => 'VELASQUEZ',     'apellido_materno' => 'CASTILLO',    'genero' => 'F', 'fecha_nacimiento' => '2017-07-05'],
            ['nombre' => 'YARITZA CAMILA',          'apellido_paterno' => 'ZEGARRA',       'apellido_materno' => 'COBA',        'genero' => 'F', 'fecha_nacimiento' => '2017-07-14'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P3B{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '3° GRADO B',
                    'semestre'          => 3,
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
