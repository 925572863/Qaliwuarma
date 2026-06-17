<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosPrimerGradoCSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'LUHANA MARIEL',           'apellido_paterno' => 'ALBAN',          'apellido_materno' => 'BARRANZUELA', 'genero' => 'F'],
            ['nombre' => 'SANTIAGO URIEL',          'apellido_paterno' => 'ARAUJO',         'apellido_materno' => 'FIESTAS',     'genero' => 'M'],
            ['nombre' => 'RAÚL ANTONIO',            'apellido_paterno' => 'ARROYO',         'apellido_materno' => 'FIESTAS',     'genero' => 'M'],
            ['nombre' => 'JOSIAS OBED',             'apellido_paterno' => 'BOBBIO',         'apellido_materno' => 'RAMIREZ',     'genero' => 'M'],
            ['nombre' => 'SOFÍA SUSANA',            'apellido_paterno' => 'CARRERA',        'apellido_materno' => 'VILLEGAS',    'genero' => 'F'],
            ['nombre' => 'EMILIO ROBERTO',          'apellido_paterno' => 'CHANCOLLA',      'apellido_materno' => 'PARKER',      'genero' => 'M'],
            ['nombre' => 'YONDERVYS ALEXANDER',     'apellido_paterno' => 'COROPA',         'apellido_materno' => 'ARCIA',       'genero' => 'M'],
            ['nombre' => 'GIORGIO EMILIANO',        'apellido_paterno' => 'DE LA FUENTE',   'apellido_materno' => 'LOZADA',      'genero' => 'M'],
            ['nombre' => 'SAMIR FERNANDO',          'apellido_paterno' => 'DOMINGUEZ',      'apellido_materno' => 'HUAMAN',      'genero' => 'M'],
            ['nombre' => 'LENNON GABRIEL',          'apellido_paterno' => 'FALCON',         'apellido_materno' => 'CHUMACERO',   'genero' => 'M'],
            ['nombre' => 'VALERIA FERNANDA',        'apellido_paterno' => 'FLORES',         'apellido_materno' => 'BERMEJO',     'genero' => 'F'],
            ['nombre' => 'AITANA YACMOUR',          'apellido_paterno' => 'GASPAR',         'apellido_materno' => 'MAURICIO',    'genero' => 'F'],
            ['nombre' => 'KEIRO RONET',             'apellido_paterno' => 'GUZMAN',         'apellido_materno' => 'TIMANA',      'genero' => 'M'],
            ['nombre' => 'PIERO ANDRE',             'apellido_paterno' => 'HUAMAN',         'apellido_materno' => 'IMAN',        'genero' => 'M'],
            ['nombre' => 'LUNA LEILANNY',           'apellido_paterno' => 'JIMENEZ',        'apellido_materno' => 'SANCHEZ',     'genero' => 'F'],
            ['nombre' => 'LUANA VALENTINA',         'apellido_paterno' => 'LOPEZ',          'apellido_materno' => 'MOGOLLON',    'genero' => 'F'],
            ['nombre' => 'LUCIANA VALENTINA',       'apellido_paterno' => 'LOPEZ',          'apellido_materno' => 'MOGOLLON',    'genero' => 'F'],
            ['nombre' => 'GABRIEL JESUS',           'apellido_paterno' => 'MANCHAY',        'apellido_materno' => 'MARTINEZ',    'genero' => 'M'],
            ['nombre' => 'BENJAMIN VALENTINO',      'apellido_paterno' => 'MARCELO',        'apellido_materno' => 'DAVIS',       'genero' => 'M'],
            ['nombre' => 'EMILIANO GAEL',           'apellido_paterno' => 'MICHILOT',       'apellido_materno' => 'ALVARADO',    'genero' => 'M'],
            ['nombre' => 'DARÍO HENRIQUE',          'apellido_paterno' => 'NAVARRO',        'apellido_materno' => 'ABAD',        'genero' => 'M'],
            ['nombre' => 'ALDO MATTEO',             'apellido_paterno' => 'RIVERA',         'apellido_materno' => 'ABAD',        'genero' => 'M'],
            ['nombre' => 'ESTIVEN GAEL',            'apellido_paterno' => 'RIVERA',         'apellido_materno' => 'CUEVA',       'genero' => 'M'],
            ['nombre' => 'ENZO DEL PIERO',          'apellido_paterno' => 'RIVERA',         'apellido_materno' => 'HUAMAN',      'genero' => 'M'],
            ['nombre' => 'DÍXON NICOLAS',           'apellido_paterno' => 'ROBLEDO',        'apellido_materno' => 'CURAY',       'genero' => 'M'],
            ['nombre' => 'NAOMY VALENTINA',         'apellido_paterno' => 'RUIZ',           'apellido_materno' => 'SERNAQUE',    'genero' => 'F'],
            ['nombre' => 'ANNY KRISTELL',           'apellido_paterno' => 'SANCHEZ',        'apellido_materno' => 'HUAMAN',      'genero' => 'F'],
            ['nombre' => 'OSWUAL ALEXANDER',        'apellido_paterno' => 'SANTANA',        'apellido_materno' => 'SEQUEDA',     'genero' => 'M'],
            ['nombre' => 'VALERIA DALESKA',         'apellido_paterno' => 'SARANGO',        'apellido_materno' => 'MORA',        'genero' => 'F'],
            ['nombre' => 'FERNANDA ANTONELLA',      'apellido_paterno' => 'VICENTE',        'apellido_materno' => 'LOPEZ',       'genero' => 'F'],
            ['nombre' => 'DANNETH CELESTE',         'apellido_paterno' => 'ZAPATA',         'apellido_materno' => 'JIMÉNEZ',     'genero' => 'F'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P1C{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '1° GRADO C',
                    'semestre'          => 1,
                    'fecha_nacimiento' => '2019-01-01', // Fecha por defecto
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
