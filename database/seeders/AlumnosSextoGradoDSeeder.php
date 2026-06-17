<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosSextoGradoDSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'VALERI JUDITH EMPERATRIZ', 'apellido_paterno' => 'ABANTO',         'apellido_materno' => 'LOPEZ',        'genero' => 'F'],
            ['nombre' => 'FRANCISCO MATEO',          'apellido_paterno' => 'ADRIANZEN',      'apellido_materno' => 'HERRERA',      'genero' => 'M'],
            ['nombre' => 'ARIANY CAORY',             'apellido_paterno' => 'ALAMO',          'apellido_materno' => 'CASTILLO',     'genero' => 'F'],
            ['nombre' => 'XIMENA ALESSANDRA',        'apellido_paterno' => 'ATARAMA',        'apellido_materno' => 'SANTIAGO',     'genero' => 'F'],
            ['nombre' => 'DOMINIC SANTIAGO',         'apellido_paterno' => 'CASTILLO',       'apellido_materno' => 'MORE',         'genero' => 'M'],
            ['nombre' => 'DAYANA MILETH',            'apellido_paterno' => 'CORTEZ',         'apellido_materno' => 'LOZADA',       'genero' => 'F'],
            ['nombre' => 'NASHIARA BIANCA',          'apellido_paterno' => 'FIESTAS',        'apellido_materno' => 'BANCAYAN',     'genero' => 'F'],
            ['nombre' => 'JAKORI LISETH',            'apellido_paterno' => 'GALLEGO',        'apellido_materno' => 'ANDIA',        'genero' => 'F'],
            ['nombre' => 'JOSELINE KRISTELL',        'apellido_paterno' => 'GIRON',          'apellido_materno' => 'ERAZO',        'genero' => 'F'],
            ['nombre' => 'YAZULLY GEDMATHAIL',       'apellido_paterno' => 'INGA',           'apellido_materno' => 'NOE',          'genero' => 'F'],
            ['nombre' => 'JESÚS DAYIRO NELVER',      'apellido_paterno' => 'MONTERO',        'apellido_materno' => 'CHUYE',        'genero' => 'M'],
            ['nombre' => 'PIER LERI STEINER',        'apellido_paterno' => 'MORALES',        'apellido_materno' => 'PANTA',        'genero' => 'M'],
            ['nombre' => 'THIAGO ALDAIR',            'apellido_paterno' => 'NAVARRO',        'apellido_materno' => 'SAAVEDRA',     'genero' => 'M'],
            ['nombre' => 'DAYRÓN AARÓN',             'apellido_paterno' => 'NIÑO',           'apellido_materno' => 'SARANGO',      'genero' => 'M'],
            ['nombre' => 'EDSON KARIM',              'apellido_paterno' => 'NIZAMA',         'apellido_materno' => 'CALERO',       'genero' => 'M'],
            ['nombre' => 'CESAR AUGUSTO',            'apellido_paterno' => 'OTERO',          'apellido_materno' => 'RIVERA',       'genero' => 'M'],
            ['nombre' => 'ESTEFANO FAVIAN',          'apellido_paterno' => 'QUEZADA',        'apellido_materno' => 'SALAZAR',      'genero' => 'M'],
            ['nombre' => 'GAEL ALONSO',              'apellido_paterno' => 'REQUENA',        'apellido_materno' => 'CASTRO',       'genero' => 'M'],
            ['nombre' => 'MELISSA ANGELINE',         'apellido_paterno' => 'REQUENA',        'apellido_materno' => 'NUÑEZ',        'genero' => 'F'],
            ['nombre' => 'LUCIANA JAZMIN',           'apellido_paterno' => 'RIMAYCUNA',      'apellido_materno' => 'CASTILLO',     'genero' => 'F'],
            ['nombre' => 'MILAGRITOS',               'apellido_paterno' => 'RIVERA',         'apellido_materno' => 'ARROYO',       'genero' => 'F'],
            ['nombre' => 'OLENKA JAZMIN',            'apellido_paterno' => 'ROSAS',          'apellido_materno' => 'ANDRADE',      'genero' => 'F'],
            ['nombre' => 'YEFRID SMITH',             'apellido_paterno' => 'SEMINARIO',      'apellido_materno' => 'MORA',         'genero' => 'M'],
            ['nombre' => 'SANTIAGO ALONSO',          'apellido_paterno' => 'SIPION',         'apellido_materno' => 'GAONA',        'genero' => 'M'],
            ['nombre' => 'FABIO LEONARDO',           'apellido_paterno' => 'TORRES',         'apellido_materno' => 'VILCA',        'genero' => 'M'],
            ['nombre' => 'ASHLY ANTHONE',            'apellido_paterno' => 'VALDEZ',         'apellido_materno' => 'CUEVA',        'genero' => 'F'],
            ['nombre' => 'BRIAM YAEL',               'apellido_paterno' => 'VILCHEZ',        'apellido_materno' => 'MORENO',       'genero' => 'M'],
            ['nombre' => 'ANGEL ABDIEL',             'apellido_paterno' => 'VILELA',         'apellido_materno' => 'VALVERDE',     'genero' => 'M'],
            ['nombre' => 'MELODY ALONDRA',           'apellido_paterno' => 'YAHUANA',        'apellido_materno' => 'CRUZ',         'genero' => 'F'],
            ['nombre' => 'NORELI CASANDRA CRISTAL',  'apellido_paterno' => 'ZUÑIGA',         'apellido_materno' => 'CAYO',         'genero' => 'F'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P6D{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '6° GRADO D',
                    'semestre'          => 6,
                    'fecha_nacimiento' => '2014-01-01', // Fecha por defecto
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
