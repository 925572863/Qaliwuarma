<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosQuintoGradoBSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'DAYRON IVAN',             'apellido_paterno' => 'ADRIANZEN',      'apellido_materno' => 'ROSAS',         'genero' => 'M'],
            ['nombre' => 'ANTONELLA FERNANDA',      'apellido_paterno' => 'BALLONA',        'apellido_materno' => 'ORDINOLA',      'genero' => 'F'],
            ['nombre' => 'OSMAN DAIR',              'apellido_paterno' => 'CALLE',          'apellido_materno' => 'IPARRAGUIRRE', 'genero' => 'M'],
            ['nombre' => 'DAYRON ADRIANO',          'apellido_paterno' => 'CALLE',          'apellido_materno' => 'SAAVEDRA',      'genero' => 'M'],
            ['nombre' => 'AMANDA JADIEL',           'apellido_paterno' => 'CARRASCO',       'apellido_materno' => 'MANRIQUE',      'genero' => 'F'],
            ['nombre' => 'MADDIE CALANY',           'apellido_paterno' => 'CASTRO',         'apellido_materno' => 'ZUTA',          'genero' => 'F'],
            ['nombre' => 'SAORI LISBETH',           'apellido_paterno' => 'CUNYA',          'apellido_materno' => 'TEZEN',         'genero' => 'F'],
            ['nombre' => 'TADEO EMMANUEL',          'apellido_paterno' => 'CURAY',          'apellido_materno' => 'VILLEGAS',      'genero' => 'M'],
            ['nombre' => 'JORGE DARWIN',            'apellido_paterno' => 'DIAZ',           'apellido_materno' => 'GARCIA',        'genero' => 'M'],
            ['nombre' => 'SNAYDER JEANPIER',        'apellido_paterno' => 'FIESTAS',        'apellido_materno' => 'VILCA',         'genero' => 'M'],
            ['nombre' => 'ASHLEY YULIETH',          'apellido_paterno' => 'GARCIA',         'apellido_materno' => 'HEREDIA',       'genero' => 'F'],
            ['nombre' => 'ELIAS ISAAC',             'apellido_paterno' => 'GUTIERREZ',      'apellido_materno' => 'PINTO',         'genero' => 'M'],
            ['nombre' => 'DYLAN CALEB',             'apellido_paterno' => 'HUAMAN',         'apellido_materno' => 'RIVAS',         'genero' => 'M'],
            ['nombre' => 'FRANK ANTONIO',           'apellido_paterno' => 'JUAREZ',         'apellido_materno' => 'GARCIA',        'genero' => 'M'],
            ['nombre' => 'YARITZA VALENTINA',       'apellido_paterno' => 'LIVIAPOMA',      'apellido_materno' => 'ARROYO',        'genero' => 'F'],
            ['nombre' => 'THIAGO DAYIRO',           'apellido_paterno' => 'LOZADA',         'apellido_materno' => 'COVEÑAS',       'genero' => 'M'],
            ['nombre' => 'YARIANNIS CAROLINA',      'apellido_paterno' => 'MACHADO',        'apellido_materno' => 'SANCHEZ',       'genero' => 'F'],
            ['nombre' => 'BETTSY KATHERINE',        'apellido_paterno' => 'MORE',          'apellido_materno' => 'CARRASCO',      'genero' => 'F'],
            ['nombre' => 'CLEMENTINA PAOLA',        'apellido_paterno' => 'MORE',          'apellido_materno' => 'TEMPLE',        'genero' => 'F'],
            ['nombre' => 'HANCELL OSMAR DALLANKY',  'apellido_paterno' => 'OJEDA',         'apellido_materno' => 'HERRERA',       'genero' => 'M'],
            ['nombre' => 'XOANA MAIA LEONOR',       'apellido_paterno' => 'OLAYA',         'apellido_materno' => 'MIRANDA',       'genero' => 'F'],
            ['nombre' => 'URIEL ALEXIS',            'apellido_paterno' => 'ORDINOLA',      'apellido_materno' => 'GARCES',        'genero' => 'M'],
            ['nombre' => 'NICOLL SAMARA YAMILETH',  'apellido_paterno' => 'RAMIREZ',       'apellido_materno' => 'FUENTES',       'genero' => 'F'],
            ['nombre' => 'MILAN GAEL',               'apellido_paterno' => 'RAMIREZ',       'apellido_materno' => 'GARCIA',        'genero' => 'M'],
            ['nombre' => 'DAMARIS DALESKA',         'apellido_paterno' => 'RAMOS',         'apellido_materno' => 'AGUILERA',      'genero' => 'F'],
            ['nombre' => 'WILLIAM SMITH',           'apellido_paterno' => 'RUIZ',          'apellido_materno' => 'ARIZAGA',       'genero' => 'M'],
            ['nombre' => 'ISMAEL ARMANDO JR',       'apellido_paterno' => 'SARANGO',       'apellido_materno' => 'CHANTA',        'genero' => 'M'],
            ['nombre' => 'MIA ALESSIA',             'apellido_paterno' => 'SIANCAS',       'apellido_materno' => 'NUÑEZ',         'genero' => 'F'],
            ['nombre' => 'BRUNELLA LUHANA',         'apellido_paterno' => 'SOLIS',         'apellido_materno' => 'GARCIA',        'genero' => 'F'],
            ['nombre' => 'AMY OLENKA',              'apellido_paterno' => 'SUAREZ',        'apellido_materno' => 'CARRASCO',      'genero' => 'F'],
            ['nombre' => 'OSCAR ROBERTO',           'apellido_paterno' => 'VEGA',          'apellido_materno' => 'QUINDE',        'genero' => 'M'],
            ['nombre' => 'ROSITA ALONDRA',          'apellido_paterno' => 'WONG',          'apellido_materno' => 'OROZCO',        'genero' => 'F'],
            ['nombre' => 'EDERLYN ALEXA',           'apellido_paterno' => 'YANGUA',        'apellido_materno' => 'JIMENEZ',       'genero' => 'F'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P5B{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '5° GRADO B',
                    'semestre'          => 5,
                    'fecha_nacimiento' => '2015-01-01', // Fecha por defecto
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
