<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosSegundoGradoASeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'PEDRO PABLO GHAEL',   'apellido_paterno' => 'CASTRO',    'apellido_materno' => 'GRILLO',     'genero' => 'M', 'fecha_nacimiento' => '2018-06-29'],
            ['nombre' => 'JOSUEP FRANCISCO',     'apellido_paterno' => 'CRUZ',      'apellido_materno' => 'CHERO',      'genero' => 'M', 'fecha_nacimiento' => '2018-06-06'],
            ['nombre' => 'FILIS SEYNETH',        'apellido_paterno' => 'CUENCA',    'apellido_materno' => 'ABAD',       'genero' => 'F', 'fecha_nacimiento' => '2019-03-07'],
            ['nombre' => 'GRIEZMANN NICOLAS',    'apellido_paterno' => 'DE LA CRUZ','apellido_materno' => 'PASHANASI',  'genero' => 'M', 'fecha_nacimiento' => '2019-02-22'],
            ['nombre' => 'CARLOS IVAN',          'apellido_paterno' => 'DOMINGUEZ', 'apellido_materno' => 'HUAMAN',     'genero' => 'M', 'fecha_nacimiento' => '2018-01-14'],
            ['nombre' => 'THIAGO ISMAEL',        'apellido_paterno' => 'ESCOBAR',   'apellido_materno' => 'VICENTE',    'genero' => 'M', 'fecha_nacimiento' => '2019-02-03'],
            ['nombre' => 'GIA VALESKA',          'apellido_paterno' => 'GALVEZ',    'apellido_materno' => 'RUFINO',     'genero' => 'F', 'fecha_nacimiento' => '2019-01-15'],
            ['nombre' => 'ADRIAN MATEO',         'apellido_paterno' => 'GARCIA',    'apellido_materno' => 'OLIVARES',   'genero' => 'M', 'fecha_nacimiento' => '2018-07-13'],
            ['nombre' => 'LIAM ALEXANDRO JOAO',  'apellido_paterno' => 'GASPAR',    'apellido_materno' => 'GALVEZ',     'genero' => 'M', 'fecha_nacimiento' => '2018-09-23'],
            ['nombre' => 'MIA MISUKIY',          'apellido_paterno' => 'GONZALES',  'apellido_materno' => 'HUANCAS',    'genero' => 'F', 'fecha_nacimiento' => '2018-12-15'],
            ['nombre' => 'KÁEL ALONSO',          'apellido_paterno' => 'GONZALES',  'apellido_materno' => 'LOPEZ',      'genero' => 'M', 'fecha_nacimiento' => '2018-10-03'],
            ['nombre' => 'KRISTEL VALENTINA',    'apellido_paterno' => 'GUTIERREZ', 'apellido_materno' => 'CHAMBA',     'genero' => 'F', 'fecha_nacimiento' => '2017-06-15'],
            ['nombre' => 'JEREMIE ABDEL',        'apellido_paterno' => 'HERRERA',   'apellido_materno' => 'ABAD',       'genero' => 'M', 'fecha_nacimiento' => '2018-09-13'],
            ['nombre' => 'ISAAC STEFANO',        'apellido_paterno' => 'JARAMILLO', 'apellido_materno' => 'ESPINOZA',   'genero' => 'M', 'fecha_nacimiento' => '2019-02-15'],
            ['nombre' => 'LIA MELANY',           'apellido_paterno' => 'JIMENEZ',   'apellido_materno' => 'CAMPOS',     'genero' => 'F', 'fecha_nacimiento' => '2018-09-06'],
            ['nombre' => 'JOSÉ IGNACIO',         'apellido_paterno' => 'JIMENEZ',   'apellido_materno' => 'PEREZ',      'genero' => 'M', 'fecha_nacimiento' => '2018-04-19'],
            ['nombre' => 'MATHEO EDRICK',        'apellido_paterno' => 'JIMENEZ',   'apellido_materno' => 'UMBO',       'genero' => 'M', 'fecha_nacimiento' => '2018-12-15'],
            ['nombre' => 'VALERIA CAMILA',       'apellido_paterno' => 'LOPEZ',     'apellido_materno' => 'PARRILLA',   'genero' => 'F', 'fecha_nacimiento' => '2019-02-26'],
            ['nombre' => 'MELANY ANTHONELLA',    'apellido_paterno' => 'MARIN',     'apellido_materno' => 'JIMENEZ',    'genero' => 'F', 'fecha_nacimiento' => '2018-12-03'],
            ['nombre' => 'XIMENA GUADALUPE',     'apellido_paterno' => 'MORE',      'apellido_materno' => 'TEMPLE',     'genero' => 'F', 'fecha_nacimiento' => '2018-04-08'],
            ['nombre' => 'LIAM STARLING',        'apellido_paterno' => 'MORE',      'apellido_materno' => 'VIDAL',      'genero' => 'M', 'fecha_nacimiento' => '2018-09-02'],
            ['nombre' => 'GIANELLA ALEXA',       'apellido_paterno' => 'NEYRA',     'apellido_materno' => 'BARRETO',    'genero' => 'F', 'fecha_nacimiento' => '2019-03-16'],
            ['nombre' => 'RAFAEL SANTIAGO',      'apellido_paterno' => 'PEÑA',      'apellido_materno' => 'RAMIREZ',    'genero' => 'M', 'fecha_nacimiento' => '2017-05-05'],
            ['nombre' => 'SOFIA',                'apellido_paterno' => 'PINTADO',   'apellido_materno' => 'GARCIA',     'genero' => 'F', 'fecha_nacimiento' => '2019-03-22'],
            ['nombre' => 'DAYRON ALEXANDER',     'apellido_paterno' => 'PULACHE',   'apellido_materno' => 'MARQUEZ',    'genero' => 'M', 'fecha_nacimiento' => '2018-01-27'],
            ['nombre' => 'ELVIS PATRICK ISAÍ',   'apellido_paterno' => 'RAMIREZ',   'apellido_materno' => 'FUENTES',    'genero' => 'M', 'fecha_nacimiento' => '2018-12-23'],
            ['nombre' => 'CECILIA THAIS',        'apellido_paterno' => 'RIVERA',    'apellido_materno' => 'ARROYO',     'genero' => 'F', 'fecha_nacimiento' => '2018-02-10'],
            ['nombre' => 'MIA CRISTELL',         'apellido_paterno' => 'ROJAS',     'apellido_materno' => 'SILUPU',     'genero' => 'F', 'fecha_nacimiento' => '2018-06-15'],
            ['nombre' => 'THAYRA MARIAN',        'apellido_paterno' => 'SEMINARIO', 'apellido_materno' => 'AREVALO',    'genero' => 'F', 'fecha_nacimiento' => '2018-06-19'],
            ['nombre' => 'VALERIA VICTHORYA',    'apellido_paterno' => 'VELASCO',   'apellido_materno' => 'MARCHAN',    'genero' => 'F', 'fecha_nacimiento' => '2018-10-09'],
            ['nombre' => 'IVANA DARIANA',        'apellido_paterno' => 'VELIZ',     'apellido_materno' => 'RAMOS',      'genero' => 'F', 'fecha_nacimiento' => '2018-12-05'],
            ['nombre' => 'VALENTINO MIGUEL',     'apellido_paterno' => 'YOVERA',    'apellido_materno' => 'MONTALBAN',  'genero' => 'M', 'fecha_nacimiento' => '2019-02-27'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P2A{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '2° GRADO A',
                    'semestre'          => 2,
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
