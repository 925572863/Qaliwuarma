<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosQuintoGradoCSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'EDELFIN JESUS ENRIQUE',   'apellido_paterno' => 'ABAD',           'apellido_materno' => 'VIDAL',         'genero' => 'M', 'fecha_nacimiento' => '2013-10-09'],
            ['nombre' => 'JATDIEL USIAS',           'apellido_paterno' => 'ALCALDE',        'apellido_materno' => 'SEFERINO',      'genero' => 'M', 'fecha_nacimiento' => '2016-03-03'],
            ['nombre' => 'CARMEN LUCIA',            'apellido_paterno' => 'ARICA',          'apellido_materno' => 'MORALES',       'genero' => 'F', 'fecha_nacimiento' => '2014-07-16'],
            ['nombre' => 'ALEJANDRO RAFAEL',        'apellido_paterno' => 'ARTEAGA',        'apellido_materno' => 'CALOPINO',      'genero' => 'M', 'fecha_nacimiento' => '2015-07-25'],
            ['nombre' => 'DYLÁN NICOLAS',           'apellido_paterno' => 'BRAVO',          'apellido_materno' => 'SANDOVAL',      'genero' => 'M', 'fecha_nacimiento' => '2015-11-29'],
            ['nombre' => 'FERNANDO ANDRE',          'apellido_paterno' => 'CAMPOS',         'apellido_materno' => 'TINEDO',        'genero' => 'M', 'fecha_nacimiento' => '2015-04-02'],
            ['nombre' => 'GABRIEL ENRIQUE',         'apellido_paterno' => 'CRISANTO',       'apellido_materno' => 'MOGOLLON',      'genero' => 'M', 'fecha_nacimiento' => '2016-03-06'],
            ['nombre' => 'LUIS EDUARDO',            'apellido_paterno' => 'CUNYA',          'apellido_materno' => 'DIAZ',          'genero' => 'M', 'fecha_nacimiento' => '2015-10-06'],
            ['nombre' => 'JANDIARA KARINE',         'apellido_paterno' => 'GAMARRA',        'apellido_materno' => 'TALLEDO',       'genero' => 'F', 'fecha_nacimiento' => '2015-07-13'],
            ['nombre' => 'SAMIR ENRIQUE',           'apellido_paterno' => 'GONZALES',       'apellido_materno' => 'CALLE',         'genero' => 'M', 'fecha_nacimiento' => '2015-09-07'],
            ['nombre' => 'ASUCENA VALENTINA',       'apellido_paterno' => 'HUAMAN',         'apellido_materno' => 'SAAVEDRA',      'genero' => 'F', 'fecha_nacimiento' => '2014-04-17'],
            ['nombre' => 'VALERY ORIANA',           'apellido_paterno' => 'JUAREZ',         'apellido_materno' => 'CEFERINO',      'genero' => 'F', 'fecha_nacimiento' => '2014-07-26'],
            ['nombre' => 'JESUS ANDERSON',          'apellido_paterno' => 'JUAREZ',         'apellido_materno' => 'DIOSES',        'genero' => 'M', 'fecha_nacimiento' => '2014-09-25'],
            ['nombre' => 'THOMAS ENRIQUE',          'apellido_paterno' => 'JUAREZ',         'apellido_materno' => 'PACHERRES',     'genero' => 'M', 'fecha_nacimiento' => '2015-05-15'],
            ['nombre' => 'JHEIDY LUANA',            'apellido_paterno' => 'LAMA',           'apellido_materno' => 'POZO',          'genero' => 'F', 'fecha_nacimiento' => '2016-01-11'],
            ['nombre' => 'JESUS DANIEL',            'apellido_paterno' => 'MARRERO',        'apellido_materno' => 'SIFONTES',      'genero' => 'M', 'fecha_nacimiento' => '2014-07-10'],
            ['nombre' => 'THIAGO ALESSANDRO',       'apellido_paterno' => 'MENDOZA',        'apellido_materno' => 'FLORES',        'genero' => 'M', 'fecha_nacimiento' => '2014-05-15'],
            ['nombre' => 'KARILÍN CECILIA',         'apellido_paterno' => 'MORAN',          'apellido_materno' => 'CHUNGA',        'genero' => 'F', 'fecha_nacimiento' => '2015-06-11'],
            ['nombre' => 'ALEJANDRA NOEMI',         'apellido_paterno' => 'MURILLO',        'apellido_materno' => 'VILLEGAS',      'genero' => 'F', 'fecha_nacimiento' => '2015-04-24'],
            ['nombre' => 'NOE ALBERTO',             'apellido_paterno' => 'NAQUICHE',       'apellido_materno' => 'ASTETE',        'genero' => 'M', 'fecha_nacimiento' => '2015-06-04'],
            ['nombre' => 'MARIO YAIR',              'apellido_paterno' => 'NIÑO',           'apellido_materno' => 'ABAD',          'genero' => 'M', 'fecha_nacimiento' => '2015-05-03'],
            ['nombre' => 'DAYRON IVAN',             'apellido_paterno' => 'NORES',          'apellido_materno' => 'CHERO',         'genero' => 'M', 'fecha_nacimiento' => '2015-04-18'],
            ['nombre' => 'ANGHELO SMITH',           'apellido_paterno' => 'PARDO',          'apellido_materno' => 'CALVA',         'genero' => 'M', 'fecha_nacimiento' => '2015-04-12'],
            ['nombre' => 'KEREN YIRÉ',              'apellido_paterno' => 'PAUCAR',         'apellido_materno' => 'OTOYA',         'genero' => 'F', 'fecha_nacimiento' => '2015-01-29'],
            ['nombre' => 'IVANA LIZETH',            'apellido_paterno' => 'PEÑA',           'apellido_materno' => 'ABAD',          'genero' => 'F', 'fecha_nacimiento' => '2015-08-11'],
            ['nombre' => 'BRIANNA AYLIN',           'apellido_paterno' => 'PEÑA',           'apellido_materno' => 'ALVARADO',      'genero' => 'F', 'fecha_nacimiento' => '2015-02-04'],
            ['nombre' => 'LIANA YASMÍN GUADALUPE',  'apellido_paterno' => 'PURIZACA',       'apellido_materno' => 'CRUZ',          'genero' => 'F', 'fecha_nacimiento' => '2016-01-22'],
            ['nombre' => 'LEONARDO JUAQUIN',        'apellido_paterno' => 'RUIZ',           'apellido_materno' => 'ARIZAGA',       'genero' => 'M', 'fecha_nacimiento' => '2013-09-30'],
            ['nombre' => 'GENESIS ABIGAIL',         'apellido_paterno' => 'SEFERINO',       'apellido_materno' => 'LALUPU',        'genero' => 'F', 'fecha_nacimiento' => '2014-09-27'],
            ['nombre' => 'MARIEL ALEXANDRA',        'apellido_paterno' => 'SERNAQUE',       'apellido_materno' => 'PORTOCARRERO', 'genero' => 'F', 'fecha_nacimiento' => '2015-07-29'],
            ['nombre' => 'MICAELA MIDRASH',         'apellido_paterno' => 'SIANCAS',        'apellido_materno' => 'SAUCEDO',       'genero' => 'F', 'fecha_nacimiento' => '2015-05-27'],
            ['nombre' => 'CRISTIAN YOEL',           'apellido_paterno' => 'YARLEQUE',       'apellido_materno' => 'CASTILLO',      'genero' => 'M', 'fecha_nacimiento' => '2015-07-23'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P5C{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '5° GRADO C',
                    'semestre'          => 5,
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
