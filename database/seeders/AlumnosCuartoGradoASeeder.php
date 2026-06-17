<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosCuartoGradoASeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'ANYELI DARLETH',          'apellido_paterno' => 'ABAD',           'apellido_materno' => 'YARLEQUE',      'genero' => 'F', 'fecha_nacimiento' => '2017-02-17'],
            ['nombre' => 'ALEJANDRO ADRIAN',        'apellido_paterno' => 'ARIAS',          'apellido_materno' => 'LADINES',       'genero' => 'M', 'fecha_nacimiento' => '2016-04-28'],
            ['nombre' => 'JULIETTE MARYELIZ',       'apellido_paterno' => 'BRAVO',          'apellido_materno' => 'RIVERA',        'genero' => 'F', 'fecha_nacimiento' => '2016-07-24'],
            ['nombre' => 'ESTEBAN FABIÁN',          'apellido_paterno' => 'CASTILLO',       'apellido_materno' => 'SANDOVAL',      'genero' => 'M', 'fecha_nacimiento' => '2016-10-07'],
            ['nombre' => 'AYNARA CRISTHINA',        'apellido_paterno' => 'CASTRO',         'apellido_materno' => 'RUESTA',        'genero' => 'F', 'fecha_nacimiento' => '2015-12-25'],
            ['nombre' => 'BRIANNA YERELEIN',        'apellido_paterno' => 'CHAVEZ',         'apellido_materno' => 'ESPINOZA',      'genero' => 'F', 'fecha_nacimiento' => '2015-06-17'],
            ['nombre' => 'ANA MILET DEL ROSARIO',   'apellido_paterno' => 'CHUMACERO',      'apellido_materno' => 'NUÑEZ',         'genero' => 'F', 'fecha_nacimiento' => '2017-03-20'],
            ['nombre' => 'JIMENA ALEGRIA ISABEL',   'apellido_paterno' => 'CHUQUICONDOR',    'apellido_materno' => 'VALDIVIEZO',    'genero' => 'F', 'fecha_nacimiento' => '2016-07-28'],
            ['nombre' => 'ALEJANDRO VALENTIN',      'apellido_paterno' => 'CORNEJO',        'apellido_materno' => 'VARONA',        'genero' => 'M', 'fecha_nacimiento' => '2013-05-24'],
            ['nombre' => 'ASTRID BRIANA ELIF',      'apellido_paterno' => 'FARFAN',         'apellido_materno' => 'CORDOVA',       'genero' => 'F', 'fecha_nacimiento' => '2016-10-03'],
            ['nombre' => 'AXELL JESÚS',             'apellido_paterno' => 'FERNANDEZ',      'apellido_materno' => 'HUAMANCHUMO',   'genero' => 'M', 'fecha_nacimiento' => '2016-08-05'],
            ['nombre' => 'SALVADOR',                'apellido_paterno' => 'FLORES',         'apellido_materno' => 'ESPINOZA',      'genero' => 'M', 'fecha_nacimiento' => '2016-12-01'],
            ['nombre' => 'ESTRELLA KARINA',         'apellido_paterno' => 'FLORES',         'apellido_materno' => 'JIMENEZ',       'genero' => 'F', 'fecha_nacimiento' => '2017-01-10'],
            ['nombre' => 'AXEL STEVEN JOEL',        'apellido_paterno' => 'GONZALES',       'apellido_materno' => 'MENDOZA',       'genero' => 'M', 'fecha_nacimiento' => '2016-04-17'],
            ['nombre' => 'KRISTEL ZARELA',          'apellido_paterno' => 'LAZO',           'apellido_materno' => 'CHERRE',        'genero' => 'F', 'fecha_nacimiento' => '2016-06-27'],
            ['nombre' => 'LEANDRO FABRICIO',        'apellido_paterno' => 'LLOCLLA',        'apellido_materno' => 'CORNEJO',       'genero' => 'M', 'fecha_nacimiento' => '2016-12-13'],
            ['nombre' => 'ALONDRA EMILIA',          'apellido_paterno' => 'LOPEZ',          'apellido_materno' => 'CUNYA',         'genero' => 'F', 'fecha_nacimiento' => '2015-12-29'],
            ['nombre' => 'VALENTINA KRISTELL',       'apellido_paterno' => 'MURILLO',        'apellido_materno' => 'VILLEGAS',      'genero' => 'F', 'fecha_nacimiento' => '2016-07-20'],
            ['nombre' => 'LEITTHON ENRIQUE',        'apellido_paterno' => 'NEGRETTI',       'apellido_materno' => 'BRICEÑO',       'genero' => 'M', 'fecha_nacimiento' => '2015-10-25'],
            ['nombre' => 'DEYAN OWEN',              'apellido_paterno' => 'PANTA',          'apellido_materno' => 'MIJA',          'genero' => 'M', 'fecha_nacimiento' => '2016-05-20'],
            ['nombre' => 'MAIA',                    'apellido_paterno' => 'PANTA',          'apellido_materno' => 'ZEGARRA',       'genero' => 'F', 'fecha_nacimiento' => '2017-01-23'],
            ['nombre' => 'BIANCA MARILIN',          'apellido_paterno' => 'PAUCAR',         'apellido_materno' => 'CARHUACHINCHAY','genero' => 'F', 'fecha_nacimiento' => '2016-11-16'],
            ['nombre' => 'DAFNE ASHLEY',            'apellido_paterno' => 'RIVERA',         'apellido_materno' => 'LOPEZ',         'genero' => 'F', 'fecha_nacimiento' => '2016-05-04'],
            ['nombre' => 'MADELEYNI MILETT',        'apellido_paterno' => 'RONDOY',         'apellido_materno' => 'LOPEZ',         'genero' => 'F', 'fecha_nacimiento' => '2016-04-30'],
            ['nombre' => 'ISEL KAELY',              'apellido_paterno' => 'ROÑA',           'apellido_materno' => 'ALVARADO',      'genero' => 'F', 'fecha_nacimiento' => '2016-11-17'],
            ['nombre' => 'DAFNE NIRVANA',           'apellido_paterno' => 'SALGADO',        'apellido_materno' => 'SARMIENTO',     'genero' => 'F', 'fecha_nacimiento' => '2016-04-05'],
            ['nombre' => 'YOUSKAR SHAEL',           'apellido_paterno' => 'SANDOVAL',       'apellido_materno' => 'GUZMAN',        'genero' => 'M', 'fecha_nacimiento' => '2016-06-04'],
            ['nombre' => 'TALITA CUMI',             'apellido_paterno' => 'VELASCO',        'apellido_materno' => 'BENAVENTE',     'genero' => 'F', 'fecha_nacimiento' => '2016-07-18'],
            ['nombre' => 'BRIANA YATSUMY',          'apellido_paterno' => 'YANAYACO',       'apellido_materno' => 'SARANGO',       'genero' => 'F', 'fecha_nacimiento' => '2016-07-18'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P4A{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '4° GRADO A',
                    'semestre'          => 4,
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
