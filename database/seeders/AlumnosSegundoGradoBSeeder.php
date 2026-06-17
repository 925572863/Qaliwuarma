<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosSegundoGradoBSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'JEYSA NAIARA',          'apellido_paterno' => 'ABAD',        'apellido_materno' => 'HUAMAN',       'genero' => 'F', 'fecha_nacimiento' => '2019-03-13'],
            ['nombre' => 'LIAM ANDRÉE',            'apellido_paterno' => 'ANGULO',      'apellido_materno' => 'CASTILLO',     'genero' => 'M', 'fecha_nacimiento' => '2018-07-24'],
            ['nombre' => 'ADRIAM MATHEO',          'apellido_paterno' => 'BAYONA',      'apellido_materno' => 'ROSADO',       'genero' => 'M', 'fecha_nacimiento' => '2019-03-26'],
            ['nombre' => 'AMY MARIBEL',            'apellido_paterno' => 'CALLE',       'apellido_materno' => 'ADANAQUE',     'genero' => 'F', 'fecha_nacimiento' => '2019-03-25'],
            ['nombre' => 'GIANELLA SOFÍA',         'apellido_paterno' => 'CASALINO',    'apellido_materno' => 'CHIROQUE',     'genero' => 'F', 'fecha_nacimiento' => '2018-10-09'],
            ['nombre' => 'KHALEESI ANTHONELLA',    'apellido_paterno' => 'CASTRO',      'apellido_materno' => 'ANCAJIMA',     'genero' => 'F', 'fecha_nacimiento' => '2018-04-20'],
            ['nombre' => 'AMY KHALEESI',           'apellido_paterno' => 'CASTRO',      'apellido_materno' => 'ZUTA',         'genero' => 'F', 'fecha_nacimiento' => '2018-06-05'],
            ['nombre' => 'JHURANY ABIGAIL',        'apellido_paterno' => 'CORDOVA',     'apellido_materno' => 'CHUQUICUSMA',  'genero' => 'F', 'fecha_nacimiento' => '2018-06-17'],
            ['nombre' => 'SANTHIAGO LIAM',         'apellido_paterno' => 'CORDOVA',     'apellido_materno' => 'LIVIAPOMA',    'genero' => 'M', 'fecha_nacimiento' => '2019-02-05'],
            ['nombre' => 'MIA GUADALUPE',          'apellido_paterno' => 'CORNEJO',     'apellido_materno' => 'PONGO',        'genero' => 'F', 'fecha_nacimiento' => '2018-12-04'],
            ['nombre' => 'DYLAN MARTIN',           'apellido_paterno' => 'CRUZ',        'apellido_materno' => 'SANCARRANCO',  'genero' => 'M', 'fecha_nacimiento' => '2018-07-01'],
            ['nombre' => 'MAYCKOL',                'apellido_paterno' => 'CUEVA',       'apellido_materno' => 'RUIZ',         'genero' => 'M', 'fecha_nacimiento' => '2018-04-20'],
            ['nombre' => 'PAOLO JESUS',            'apellido_paterno' => 'ESPINOZA',    'apellido_materno' => 'ELESPURU',     'genero' => 'M', 'fecha_nacimiento' => '2018-05-31'],
            ['nombre' => 'JHORDY SMITH',           'apellido_paterno' => 'ESTRADA',     'apellido_materno' => 'VALLEJOS',     'genero' => 'M', 'fecha_nacimiento' => '2019-03-02'],
            ['nombre' => 'AXEL SMIK',              'apellido_paterno' => 'FLORES',      'apellido_materno' => 'JIMENEZ',      'genero' => 'M', 'fecha_nacimiento' => '2018-12-27'],
            ['nombre' => 'JOHANA ELIZABETH',       'apellido_paterno' => 'GARCIA',      'apellido_materno' => 'ROSAS',        'genero' => 'F', 'fecha_nacimiento' => '2019-01-24'],
            ['nombre' => 'MONICA ELIZABETH',       'apellido_paterno' => 'GOMEZ',       'apellido_materno' => 'SALAZAR',      'genero' => 'F', 'fecha_nacimiento' => '2016-06-07'],
            ['nombre' => 'DALEYZA ELSSA BRICELY',  'apellido_paterno' => 'GUTIERREZ',   'apellido_materno' => 'MULATILLO',    'genero' => 'F', 'fecha_nacimiento' => '2018-07-02'],
            ['nombre' => 'LIAM ALEXANDER GABRIEL', 'apellido_paterno' => 'JIMENEZ',     'apellido_materno' => 'CALLE',        'genero' => 'M', 'fecha_nacimiento' => '2019-03-16'],
            ['nombre' => 'KIARA MIZEILY',          'apellido_paterno' => 'NAQUICHE',    'apellido_materno' => 'ASTETE',       'genero' => 'F', 'fecha_nacimiento' => '2019-01-24'],
            ['nombre' => 'IKER FERNANDO',          'apellido_paterno' => 'ORDINOLA',    'apellido_materno' => 'PUELLES',      'genero' => 'M', 'fecha_nacimiento' => '2018-04-17'],
            ['nombre' => 'JEYCKEL GABRIEL',        'apellido_paterno' => 'PASACHE',     'apellido_materno' => 'HERNANDEZ',    'genero' => 'M', 'fecha_nacimiento' => '2018-08-20'],
            ['nombre' => 'FRANCIS AARÓN',          'apellido_paterno' => 'PULACHE',     'apellido_materno' => 'TORRES',       'genero' => 'M', 'fecha_nacimiento' => '2019-03-05'],
            ['nombre' => 'DUAN VALENTINO',         'apellido_paterno' => 'QUIJANO',     'apellido_materno' => 'CASTRO',       'genero' => 'M', 'fecha_nacimiento' => '2019-02-17'],
            ['nombre' => 'SNAYDER LAÍRD',          'apellido_paterno' => 'RUIZ',        'apellido_materno' => 'CORNEJO',      'genero' => 'M', 'fecha_nacimiento' => '2018-10-28'],
            ['nombre' => 'MIA KHALEESI',           'apellido_paterno' => 'SAAVEDRA',    'apellido_materno' => 'YARLEQUE',     'genero' => 'F', 'fecha_nacimiento' => '2018-06-15'],
            ['nombre' => 'STEPHANNO ANDRÉ',        'apellido_paterno' => 'SALDARRIAGA', 'apellido_materno' => 'CRISANTO',     'genero' => 'M', 'fecha_nacimiento' => '2018-11-27'],
            ['nombre' => 'GAELA FABIANA',          'apellido_paterno' => 'SALGADO',     'apellido_materno' => 'SARMIENTO',    'genero' => 'F', 'fecha_nacimiento' => '2019-02-26'],
            ['nombre' => 'DALESKA LUHANA',         'apellido_paterno' => 'SANDOVAL',    'apellido_materno' => 'GUZMAN',       'genero' => 'F', 'fecha_nacimiento' => '2018-11-16'],
            ['nombre' => 'JOSUE MIQUEAS',          'apellido_paterno' => 'SEFERINO',    'apellido_materno' => 'LALUPU',       'genero' => 'M', 'fecha_nacimiento' => '2018-02-12'],
            ['nombre' => 'ELISA FERNANDA',         'apellido_paterno' => 'SIALAS',      'apellido_materno' => 'GUEVARA',      'genero' => 'F', 'fecha_nacimiento' => '2018-07-21'],
            ['nombre' => 'ELIANA YANELLA',         'apellido_paterno' => 'SILVA',       'apellido_materno' => 'RUIZ',         'genero' => 'F', 'fecha_nacimiento' => '2019-03-06'],
            ['nombre' => 'KASUMI ALEXANDRA',       'apellido_paterno' => 'VASQUEZ',     'apellido_materno' => 'HUAMAN',       'genero' => 'F', 'fecha_nacimiento' => '2018-07-27'],
            ['nombre' => 'MATHIAS ANDRÉ',          'apellido_paterno' => 'VILLASECA',   'apellido_materno' => 'HUAMANCHUMO',  'genero' => 'M', 'fecha_nacimiento' => '2018-08-17'],
            ['nombre' => 'ZOE CATALELLA',          'apellido_paterno' => 'YAMUNAQUE',   'apellido_materno' => 'CARRASCO',     'genero' => 'F', 'fecha_nacimiento' => '2018-10-13'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P2B{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '2° GRADO B',
                    'semestre'          => 2,
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
