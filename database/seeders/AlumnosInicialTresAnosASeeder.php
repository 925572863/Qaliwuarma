<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosInicialTresAnosASeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'ALMUDENA DEL ROSARIO',    'apellido_paterno' => 'ADANAQUE',       'apellido_materno' => 'CHUMACERO',    'genero' => 'F'],
            ['nombre' => 'MARCO AURELIO',           'apellido_paterno' => 'AGUILAR',        'apellido_materno' => 'CASTILLO',     'genero' => 'M'],
            ['nombre' => 'LITA ELENA',               'apellido_paterno' => 'ANTON',          'apellido_materno' => 'ESTRADA',      'genero' => 'F'],
            ['nombre' => 'IKER ANDRES',              'apellido_paterno' => 'BARBOZA',        'apellido_materno' => 'BANCAYAN',     'genero' => 'M'],
            ['nombre' => 'FABIO BERLIN',            'apellido_paterno' => 'BENITES',        'apellido_materno' => 'BALAREZO',     'genero' => 'M'],
            ['nombre' => 'MIRAN NASUH',              'apellido_paterno' => 'CERVANTES',      'apellido_materno' => 'HUAMAN',       'genero' => 'M'],
            ['nombre' => 'MILAN ALEPH',              'apellido_paterno' => 'CORDOVA',        'apellido_materno' => 'NAQUICHE',     'genero' => 'M'],
            ['nombre' => 'THIAGO ADRIAN',            'apellido_paterno' => 'CORREA',         'apellido_materno' => 'SIGUENZA',     'genero' => 'M'],
            ['nombre' => 'JHOSUA EMIR',              'apellido_paterno' => 'ESPARRAGA',      'apellido_materno' => 'HERNANDEZ',    'genero' => 'M'],
            ['nombre' => 'JOSEPH ALEJANDRO',        'apellido_paterno' => 'FLORES',         'apellido_materno' => 'JUAREZ',       'genero' => 'M'],
            ['nombre' => 'PABLO DAVID',              'apellido_paterno' => 'FLORES',         'apellido_materno' => 'SILVA',        'genero' => 'M'],
            ['nombre' => 'CÉSAR FABIAN',            'apellido_paterno' => 'GALLO',          'apellido_materno' => 'ECHE',         'genero' => 'M'],
            ['nombre' => 'DEVRAN HOLGER',            'apellido_paterno' => 'IZQUIERDO',      'apellido_materno' => 'SARANGO',      'genero' => 'M'],
            ['nombre' => 'RONALDO RAÚL',            'apellido_paterno' => 'NUÑEZ',          'apellido_materno' => 'RODRIGUEZ',     'genero' => 'M'],
            ['nombre' => 'MATEO GAEL',               'apellido_paterno' => 'PANTA',          'apellido_materno' => 'BELLO',        'genero' => 'M'],
            ['nombre' => 'SALVADOR JOAQUÍN',         'apellido_paterno' => 'PANTA',          'apellido_materno' => 'CARRASCO',     'genero' => 'M'],
            ['nombre' => 'HANNAH GISELLE',           'apellido_paterno' => 'POZO',           'apellido_materno' => 'VILLEGAS',     'genero' => 'F'],
            ['nombre' => 'THIAGO AZIEL',             'apellido_paterno' => 'REQUENA',        'apellido_materno' => 'IZQUIERDO',     'genero' => 'M'],
            ['nombre' => 'SOFHIA KALLESSY',          'apellido_paterno' => 'RODAS',          'apellido_materno' => 'MOROCHO',      'genero' => 'F'],
            ['nombre' => 'IAM SMIC',                 'apellido_paterno' => 'ROJAS',          'apellido_materno' => 'CHAVEZ',       'genero' => 'M'],
            ['nombre' => 'DOMENICA LUCIA',           'apellido_paterno' => 'SAAVEDRA',       'apellido_materno' => 'CASTILLO',     'genero' => 'F'],
            ['nombre' => 'ANGEL JHADIEL',            'apellido_paterno' => 'VELASCO',        'apellido_materno' => 'TICLIAHUANCA', 'genero' => 'M'],
            ['nombre' => 'ENOC GABRIEL',             'apellido_paterno' => 'YAMUNAQUE',      'apellido_materno' => 'SARANGO',      'genero' => 'M'],
            ['nombre' => 'EMMA GUADALUPE',           'apellido_paterno' => 'YARLEQUE',       'apellido_materno' => 'AYALA',        'genero' => 'F'],
            ['nombre' => 'LEYLANI ITZEL',            'apellido_paterno' => 'ZEVALLOS',       'apellido_materno' => 'RAMOS',        'genero' => 'F'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026I3A{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'inicial',
                    'carrera'           => 'INICIAL 3 AÑOS A',
                    'semestre'          => 0, // O el valor que corresponda para inicial
                    'fecha_nacimiento' => '2023-01-01', // Fecha por defecto
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
