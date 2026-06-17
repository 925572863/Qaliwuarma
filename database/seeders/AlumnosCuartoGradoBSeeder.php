<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosCuartoGradoBSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'JONETMEN BALESCA',        'apellido_paterno' => 'ADANAQUE',       'apellido_materno' => 'CHUMACERO',    'genero' => 'F'],
            ['nombre' => 'DAYANA KAORI',            'apellido_paterno' => 'ANCAJIMA',       'apellido_materno' => 'CORDOVA',      'genero' => 'F'],
            ['nombre' => 'MILAN SNAYDER',           'apellido_paterno' => 'BURGOS',         'apellido_materno' => 'GONZA',        'genero' => 'M'],
            ['nombre' => 'JAVIER EDUARDO',          'apellido_paterno' => 'CAMPUZANO',      'apellido_materno' => 'PAZOS',        'genero' => 'M'],
            ['nombre' => 'JEYKO FABRICIO',          'apellido_paterno' => 'CASTILLO',       'apellido_materno' => 'RIVERA',       'genero' => 'M'],
            ['nombre' => 'JOSÉ ESTEBAN NATHANAEL',  'apellido_paterno' => 'CORDOVA',        'apellido_materno' => 'ZAPATA',       'genero' => 'M'],
            ['nombre' => 'JORDAN SMITH',            'apellido_paterno' => 'CORONADO',       'apellido_materno' => 'ESPINOZA',     'genero' => 'M'],
            ['nombre' => 'ANDRÉ ALEJANDRO',         'apellido_paterno' => 'DIOSES',         'apellido_materno' => 'HONORES',      'genero' => 'M'],
            ['nombre' => 'ANTONIO JOSUE ROMARIO',   'apellido_paterno' => 'DIOSES',         'apellido_materno' => 'URIARTE',      'genero' => 'M'],
            ['nombre' => 'BRAYAN DAVID',            'apellido_paterno' => 'ESPINOZA',       'apellido_materno' => 'HERNANDEZ',    'genero' => 'M'],
            ['nombre' => 'ALDAIR THIAGO ALEXANDER', 'apellido_paterno' => 'FIESTAS',        'apellido_materno' => 'BANCAYAN',     'genero' => 'M'],
            ['nombre' => 'RONALD LEANDRO RAFAEL',   'apellido_paterno' => 'JUAREZ',         'apellido_materno' => 'GARCIA',       'genero' => 'M'],
            ['nombre' => 'VALESKHA CRISTEL',        'apellido_paterno' => 'LEON',           'apellido_materno' => 'CHIROQUE',     'genero' => 'F'],
            ['nombre' => 'ADRIANA DEL PILAR',       'apellido_paterno' => 'LLENQUE',        'apellido_materno' => 'HUARANGA',     'genero' => 'F'],
            ['nombre' => 'IVANA SOFIA',             'apellido_paterno' => 'MEDINA',         'apellido_materno' => 'FLORES',       'genero' => 'F'],
            ['nombre' => 'STEFANO RAFAEL',          'apellido_paterno' => 'MORAN',          'apellido_materno' => 'CAMPOS',       'genero' => 'M'],
            ['nombre' => 'ALEXIA VICTORIA',         'apellido_paterno' => 'MUÑICO',         'apellido_materno' => 'FIESTAS',      'genero' => 'F'],
            ['nombre' => 'GÉNESIS NADEXKA',         'apellido_paterno' => 'NAVARRO',        'apellido_materno' => 'GRANADINO',    'genero' => 'F'],
            ['nombre' => 'VANIA SIBELY',            'apellido_paterno' => 'NIEVES',         'apellido_materno' => 'SUYON',        'genero' => 'F'],
            ['nombre' => 'JHARUMI BRIYIT',          'apellido_paterno' => 'PAUCAR',         'apellido_materno' => 'PAZ',          'genero' => 'F'],
            ['nombre' => 'HENRY RENE',              'apellido_paterno' => 'PULACHE',        'apellido_materno' => 'MARQUEZ',      'genero' => 'M'],
            ['nombre' => 'EMELIE FEERD',            'apellido_paterno' => 'REATEGUI',       'apellido_materno' => 'CASTILLO',     'genero' => 'F'],
            ['nombre' => 'SOFIA VALERIA',           'apellido_paterno' => 'RENGIFO',        'apellido_materno' => 'VILCHEZ',      'genero' => 'F'],
            ['nombre' => 'MARYAM NOEMÍ',            'apellido_paterno' => 'SANCARRANCO',    'apellido_materno' => 'CASTRO',       'genero' => 'F'],
            ['nombre' => 'ITZEL ABBY',              'apellido_paterno' => 'SANCHEZ',        'apellido_materno' => 'AREVALO',      'genero' => 'F'],
            ['nombre' => 'ZOE DE LOS ANGELES',      'apellido_paterno' => 'SILVA',          'apellido_materno' => 'RUIZ',         'genero' => 'F'],
            ['nombre' => 'YASURI LIZET',            'apellido_paterno' => 'TAVARA',         'apellido_materno' => 'OLORTEGUI',    'genero' => 'F'],
            ['nombre' => 'BELEN AZUCENA',           'apellido_paterno' => 'VERA',           'apellido_materno' => 'BALAREZO',     'genero' => 'F'],
            ['nombre' => 'MICAHELA YARELY',         'apellido_paterno' => 'VILELA',         'apellido_materno' => 'AGUILAR',      'genero' => 'F'],
            ['nombre' => 'ANDRES SEBASTIAN',        'apellido_paterno' => 'YANGUA',         'apellido_materno' => 'QUISPE',       'genero' => 'M'],
            ['nombre' => 'STEVEN AXEL RYAN',        'apellido_paterno' => 'ZAPATA',         'apellido_materno' => 'SERNAQUE',     'genero' => 'M'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P4B{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '4° GRADO B',
                    'semestre'          => 4,
                    'fecha_nacimiento' => '2016-01-01', // Fecha por defecto
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
