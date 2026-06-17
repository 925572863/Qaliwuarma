<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosPrimerGradoBSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'JAMILE DE LOS ANGELES',    'apellido_paterno' => 'ABAD',           'apellido_materno' => 'YARLEQUE',    'genero' => 'F'],
            ['nombre' => 'GAEL STEFANO',             'apellido_paterno' => 'ADRIANZEN',      'apellido_materno' => 'ESPINOZA',    'genero' => 'M'],
            ['nombre' => 'KASSIAN JOSE GAEL',        'apellido_paterno' => 'ALVITRES',       'apellido_materno' => 'ROFNER',      'genero' => 'M'],
            ['nombre' => 'MATIAS NICOLAS',           'apellido_paterno' => 'ARCE',           'apellido_materno' => 'PIZARRO',     'genero' => 'M'],
            ['nombre' => 'RENZO EMILIANO',           'apellido_paterno' => 'BRICEÑO',        'apellido_materno' => 'RODRIGUEZ',   'genero' => 'M'],
            ['nombre' => 'CAMILO JARETH',            'apellido_paterno' => 'CABRERA',        'apellido_materno' => 'LAZO',        'genero' => 'M'],
            ['nombre' => 'MATHEO',                   'apellido_paterno' => 'CARDONA',        'apellido_materno' => 'RIVERO',      'genero' => 'M'],
            ['nombre' => 'YADIR ANTONIO',            'apellido_paterno' => 'CASTILLO',       'apellido_materno' => 'MORE',        'genero' => 'M'],
            ['nombre' => 'DAYLEFH CATTALEYA',        'apellido_paterno' => 'CONTRERAS',      'apellido_materno' => 'NOBLECILLA',  'genero' => 'F'],
            ['nombre' => 'DANIELA ISABEL',           'apellido_paterno' => 'CRISANTO',       'apellido_materno' => 'REBOLLEDO',   'genero' => 'F'],
            ['nombre' => 'LUIS ADDIEL',              'apellido_paterno' => 'DIAZ',           'apellido_materno' => 'GOMEZ',       'genero' => 'M'],
            ['nombre' => 'EMILY MARIANA',            'apellido_paterno' => 'DOMINGUEZ',      'apellido_materno' => 'FERIA',       'genero' => 'F'],
            ['nombre' => 'ADRIAN MATEO',             'apellido_paterno' => 'GRANADOS',       'apellido_materno' => 'SANTA CRUZ',  'genero' => 'M'],
            ['nombre' => 'ÓSCAR GAEL',               'apellido_paterno' => 'GUZMAN',         'apellido_materno' => 'NUNURA',      'genero' => 'M'],
            ['nombre' => 'EMMA SOFÍA',               'apellido_paterno' => 'JUAREZ',         'apellido_materno' => 'DIOSES',      'genero' => 'F'],
            ['nombre' => 'DANNAÉ KRISTHEL',          'apellido_paterno' => 'LALUPU',         'apellido_materno' => 'CHUNGA',      'genero' => 'F'],
            ['nombre' => 'AINHOA DANUSKA',           'apellido_paterno' => 'LEON',           'apellido_materno' => 'ZAPATA',      'genero' => 'F'],
            ['nombre' => 'EMILY ANTONELLA',          'apellido_paterno' => 'LOPEZ',          'apellido_materno' => 'COTRINA',     'genero' => 'F'],
            ['nombre' => 'MILAGROS DEL PILAR',       'apellido_paterno' => 'MIÑAN',          'apellido_materno' => 'VENTURA',     'genero' => 'F'],
            ['nombre' => 'MATHIAS ANDREU',           'apellido_paterno' => 'MONSALVE',       'apellido_materno' => 'SOSA',        'genero' => 'M'],
            ['nombre' => 'GABRIEL SEBASTIAN',        'apellido_paterno' => 'MORALES',        'apellido_materno' => 'FIESTAS',     'genero' => 'M'],
            ['nombre' => 'MARCELO VALENTINO',        'apellido_paterno' => 'PALOMINO',       'apellido_materno' => 'GARCIA',      'genero' => 'M'],
            ['nombre' => 'ITZAYANA MAHIA',           'apellido_paterno' => 'PEÑA',           'apellido_materno' => 'FERNANDEZ',   'genero' => 'F'],
            ['nombre' => 'YARELY BETSABET',          'apellido_paterno' => 'PONCE',          'apellido_materno' => 'SARANGO',     'genero' => 'F'],
            ['nombre' => 'CHARLOTTE BELÉN',          'apellido_paterno' => 'REATEGUI',       'apellido_materno' => 'CASTILLO',    'genero' => 'F'],
            ['nombre' => 'ÁNGEL YADIEL',             'apellido_paterno' => 'RIMAYCUNA',      'apellido_materno' => 'CASTILLO',    'genero' => 'M'],
            ['nombre' => 'ADRIAN CESAR',             'apellido_paterno' => 'RIOS',           'apellido_materno' => 'MOROCHO',     'genero' => 'M'],
            ['nombre' => 'AYTHANA DARLYN',           'apellido_paterno' => 'SANDOVAL',       'apellido_materno' => 'MORA',        'genero' => 'F'],
            ['nombre' => 'KEIMI LARISSA',            'apellido_paterno' => 'SUGA',           'apellido_materno' => 'ROSILLO',     'genero' => 'F'],
            ['nombre' => 'LUCK EMILIO',              'apellido_paterno' => 'VELASQUEZ',      'apellido_materno' => 'CASTILLO',    'genero' => 'M'],
            ['nombre' => 'GAEL IKKER ALAIN',         'apellido_paterno' => 'ZAPATA',         'apellido_materno' => 'SERNAQUE',    'genero' => 'M'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P1B{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '1° GRADO B',
                    'semestre'          => 1,
                    'fecha_nacimiento' => '2019-01-01', // Fecha por defecto
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
