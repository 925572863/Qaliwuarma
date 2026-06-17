<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosTercerGradoCSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'AISLINN YAMÍLE',          'apellido_paterno' => 'BALTAZAR',     'apellido_materno' => 'CARRASCO',    'genero' => 'F', 'fecha_nacimiento' => '2018-02-07'],
            ['nombre' => 'YAEL BENJAMIN',           'apellido_paterno' => 'BURBANK',      'apellido_materno' => 'DIAZ',        'genero' => 'M', 'fecha_nacimiento' => '2017-06-22'],
            ['nombre' => 'RYAN MISHAAL',            'apellido_paterno' => 'CARMENES',     'apellido_materno' => 'CORREA',      'genero' => 'M', 'fecha_nacimiento' => '2017-07-16'],
            ['nombre' => 'EDXON ALESSANDRO',        'apellido_paterno' => 'CHAMBA',       'apellido_materno' => 'SARANGO',     'genero' => 'M', 'fecha_nacimiento' => '2018-01-24'],
            ['nombre' => 'DILAN KALET',             'apellido_paterno' => 'CHAVEZ',       'apellido_materno' => 'BERECHE',     'genero' => 'M', 'fecha_nacimiento' => '2016-08-11'],
            ['nombre' => 'LUIS AUGUSTO',            'apellido_paterno' => 'CORNEJO',      'apellido_materno' => 'CACERES',     'genero' => 'M', 'fecha_nacimiento' => '2017-08-12'],
            ['nombre' => 'LIAN SNEIDER',            'apellido_paterno' => 'ESTRADA',      'apellido_materno' => 'VALLEJOS',    'genero' => 'M', 'fecha_nacimiento' => '2016-08-17'],
            ['nombre' => 'DELIA KAELY',             'apellido_paterno' => 'FARFAN',       'apellido_materno' => 'FLORES',      'genero' => 'F', 'fecha_nacimiento' => '2018-01-29'],
            ['nombre' => 'EVANS JEAN PIERRE',       'apellido_paterno' => 'GONZALES',     'apellido_materno' => 'SILUPU',      'genero' => 'M', 'fecha_nacimiento' => '2015-03-11'],
            ['nombre' => 'HELLEN GISSELL',          'apellido_paterno' => 'HERRERA',      'apellido_materno' => 'INGA',        'genero' => 'F', 'fecha_nacimiento' => '2018-01-20'],
            ['nombre' => 'EMILY DALESKA',           'apellido_paterno' => 'JUAREZ',       'apellido_materno' => 'DIOSES',      'genero' => 'F', 'fecha_nacimiento' => '2017-05-17'],
            ['nombre' => 'NAYSCHA ALEJANDRA',       'apellido_paterno' => 'JUAREZ',       'apellido_materno' => 'ONSEBAY',     'genero' => 'F', 'fecha_nacimiento' => '2015-11-13'],
            ['nombre' => 'THIAGO DAYIRO',           'apellido_paterno' => 'LECARNAQUE',   'apellido_materno' => 'ANTON',       'genero' => 'M', 'fecha_nacimiento' => '2017-11-05'],
            ['nombre' => 'ISAIAS JOSUE',            'apellido_paterno' => 'MARTINEZ',     'apellido_materno' => 'REYES',       'genero' => 'M', 'fecha_nacimiento' => '2017-04-19'],
            ['nombre' => 'EURISMARY ALEJANDRA',     'apellido_paterno' => 'MORALES',      'apellido_materno' => 'FUENMAYOR',   'genero' => 'F', 'fecha_nacimiento' => '2016-06-04'],
            ['nombre' => 'ARANZA JUNETT',           'apellido_paterno' => 'NAQUICHE',     'apellido_materno' => 'ASTETE',      'genero' => 'F', 'fecha_nacimiento' => '2017-10-17'],
            ['nombre' => 'JULIAN ALBERTO',          'apellido_paterno' => 'ORDINOLA',     'apellido_materno' => 'VELIZ',       'genero' => 'M', 'fecha_nacimiento' => '2017-10-24'],
            ['nombre' => 'IVAWHEM HIBRAHEM',        'apellido_paterno' => 'PAUCAR',       'apellido_materno' => 'LOPEZ',       'genero' => 'M', 'fecha_nacimiento' => '2017-05-27'],
            ['nombre' => 'JUAN RODRIGO',            'apellido_paterno' => 'PAZO',         'apellido_materno' => 'GARCIA',      'genero' => 'M', 'fecha_nacimiento' => '2017-11-09'],
            ['nombre' => 'KEYTHMARIS XARIANNYS',    'apellido_paterno' => 'PEROZA',       'apellido_materno' => 'SILVA',       'genero' => 'F', 'fecha_nacimiento' => '2017-08-15'],
            ['nombre' => 'GRIEZMANN GARETH',        'apellido_paterno' => 'RAMIREZ',      'apellido_materno' => 'ALVARADO',    'genero' => 'M', 'fecha_nacimiento' => '2017-04-09'],
            ['nombre' => 'STEFANO YADIEL',          'apellido_paterno' => 'REQUENA',      'apellido_materno' => 'NUÑEZ',       'genero' => 'M', 'fecha_nacimiento' => '2017-11-16'],
            ['nombre' => 'ISAAC DAVID',             'apellido_paterno' => 'ROJAS',        'apellido_materno' => 'IBARRA',      'genero' => 'M', 'fecha_nacimiento' => '2017-06-13'],
            ['nombre' => 'GABRIEL ISAIAS',          'apellido_paterno' => 'SALAZAR',      'apellido_materno' => 'PONCE',       'genero' => 'M', 'fecha_nacimiento' => '2016-01-12'],
            ['nombre' => 'DANNER JOSUÉ',            'apellido_paterno' => 'VICENTE',      'apellido_materno' => 'LOPEZ',       'genero' => 'M', 'fecha_nacimiento' => '2018-02-16'],
            ['nombre' => 'ERICK SMITH',             'apellido_paterno' => 'ZUÑIGA',       'apellido_materno' => 'CAYO',        'genero' => 'M', 'fecha_nacimiento' => '2017-10-14'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P3C{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '3° GRADO C',
                    'semestre'          => 3,
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
