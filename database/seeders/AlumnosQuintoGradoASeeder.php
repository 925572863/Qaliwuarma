<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosQuintoGradoASeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'ERICK LOAN YOSHIRO',          'apellido_paterno' => 'ACHO',           'apellido_materno' => 'LLONTOP',       'genero' => 'M'],
            ['nombre' => 'ASHLIE NICOLE',               'apellido_paterno' => 'BURGOS',         'apellido_materno' => 'LLACSAHUANGA',  'genero' => 'F'],
            ['nombre' => 'VALERY GUIULIETT',            'apellido_paterno' => 'CARREÑO',        'apellido_materno' => 'ZOCOLA',        'genero' => 'F'],
            ['nombre' => 'KEYLOR KERIN',                'apellido_paterno' => 'CASTILLO',       'apellido_materno' => 'REYES',         'genero' => 'M'],
            ['nombre' => 'JESÚS ANGEL',                 'apellido_paterno' => 'CASTRO',         'apellido_materno' => 'CHERRE',        'genero' => 'M'],
            ['nombre' => 'VALENTINA MILEY',             'apellido_paterno' => 'CHAVEZ',         'apellido_materno' => 'ALVARADO',      'genero' => 'F'],
            ['nombre' => 'ALEJANDRA VALENTINA',         'apellido_paterno' => 'CORNEJO',        'apellido_materno' => 'VARONA',        'genero' => 'F'],
            ['nombre' => 'YANDEL ALEXANDER',            'apellido_paterno' => 'DOMINGUEZ',      'apellido_materno' => 'FERIA',         'genero' => 'M'],
            ['nombre' => 'THIAGO JOEL',                 'apellido_paterno' => 'DUQUE',          'apellido_materno' => 'HUAMAN',        'genero' => 'M'],
            ['nombre' => 'ANDREA VALESKA',              'apellido_paterno' => 'ESPINOZA',       'apellido_materno' => 'OLIVARES',      'genero' => 'F'],
            ['nombre' => 'GIANELLA VALENTINA',          'apellido_paterno' => 'FIESTAS',        'apellido_materno' => 'VARGAS',        'genero' => 'F'],
            ['nombre' => 'NAIARA ROSALID',              'apellido_paterno' => 'GALVEZ',         'apellido_materno' => 'RUFINO',        'genero' => 'F'],
            ['nombre' => 'IVANNA JAZMIN DE LOS ANGELES','apellido_paterno' => 'GASPAR',         'apellido_materno' => 'GALVEZ',        'genero' => 'F'],
            ['nombre' => 'ANDREA MERCEDES',             'apellido_paterno' => 'HERRERA',        'apellido_materno' => 'NAIRA',         'genero' => 'F'],
            ['nombre' => 'YHONNIER ADRIANO',            'apellido_paterno' => 'JIMENEZ',        'apellido_materno' => 'CAMPOS',        'genero' => 'M'],
            ['nombre' => 'MIGUEL ÁNGEL',                'apellido_paterno' => 'JUAREZ',         'apellido_materno' => 'HUANCAS',       'genero' => 'M'],
            ['nombre' => 'SANDRO JAVIER',               'apellido_paterno' => 'LEON',           'apellido_materno' => 'GARCIA',        'genero' => 'M'],
            ['nombre' => 'LIAM JADIEL',                 'apellido_paterno' => 'LUCERO',         'apellido_materno' => 'NAVARRO',       'genero' => 'M'],
            ['nombre' => 'JHEREMY NICOLAS',             'apellido_paterno' => 'MECHAN',         'apellido_materno' => 'SALAZAR',       'genero' => 'M'],
            ['nombre' => 'TONY SANTIAGO',               'apellido_paterno' => 'MENDOZA',        'apellido_materno' => 'CRUZ',          'genero' => 'M'],
            ['nombre' => 'SILVER IDILIO',               'apellido_paterno' => 'MERINO',         'apellido_materno' => 'ALBERCA',       'genero' => 'M'],
            ['nombre' => 'JOSE FELIX',                  'apellido_paterno' => 'MORALES',        'apellido_materno' => 'SILVA',         'genero' => 'M'],
            ['nombre' => 'IKER ALESSANDRO',             'apellido_paterno' => 'MORAN',          'apellido_materno' => 'OLIVARES',      'genero' => 'M'],
            ['nombre' => 'HILLARY THAIZ',               'apellido_paterno' => 'OLIVARES',       'apellido_materno' => 'RUFINO',        'genero' => 'F'],
            ['nombre' => 'MAGY GUADALUPE ESCARLEY',     'apellido_paterno' => 'RODRIGUEZ',      'apellido_materno' => 'CASTILLO',      'genero' => 'F'],
            ['nombre' => 'PEDRO RAFAEL',                'apellido_paterno' => 'ROJAS',          'apellido_materno' => 'RAMIREZ',       'genero' => 'M'],
            ['nombre' => 'ARIANA CRISTEL',              'apellido_paterno' => 'SAAVEDRA',       'apellido_materno' => 'VIDAL',         'genero' => 'F'],
            ['nombre' => 'THADEO JOSUE',                'apellido_paterno' => 'SOLARI',         'apellido_materno' => 'FLORES',        'genero' => 'M'],
            ['nombre' => 'THIAGO MATEO',                'apellido_paterno' => 'TOCTO',          'apellido_materno' => 'BALLIVIAN',     'genero' => 'M'],
            ['nombre' => 'ESTRELLA MARIA',              'apellido_paterno' => 'TRONCOS',        'apellido_materno' => 'CESPEDES',      'genero' => 'F'],
            ['nombre' => 'DIANA ELIZABETH',             'apellido_paterno' => 'YAHUANA',        'apellido_materno' => 'LAZO',          'genero' => 'F'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P5A{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '5° GRADO A',
                    'semestre'          => 5,
                    'fecha_nacimiento' => '2015-01-01', // Fecha por defecto
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
