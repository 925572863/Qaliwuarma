<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosTercerGradoDSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'IKER GAEL',                 'apellido_paterno' => 'AREVALO',     'apellido_materno' => 'CORREA',      'genero' => 'M'],
            ['nombre' => 'JOSE ANGEL DUVAL',          'apellido_paterno' => 'CRUZ',        'apellido_materno' => 'CHUNGA',      'genero' => 'M'],
            ['nombre' => 'ASHLEY GUADALUPE',          'apellido_paterno' => 'CUSTODIO',    'apellido_materno' => 'KAWAJIGASHI', 'genero' => 'F'],
            ['nombre' => 'ALISSON KHALEESI NOEMY',    'apellido_paterno' => 'FUENTES',     'apellido_materno' => 'MOGOLLON',    'genero' => 'F'],
            ['nombre' => 'SEGUNDO MIGUEL',            'apellido_paterno' => 'GUERRERO',    'apellido_materno' => 'TEJADA',      'genero' => 'M'],
            ['nombre' => 'GAEL ADRIAN',               'apellido_paterno' => 'GUZMAN',      'apellido_materno' => 'TORRES',      'genero' => 'M'],
            ['nombre' => 'LIAM JAZIEL',               'apellido_paterno' => 'INGA',        'apellido_materno' => 'MACUYAMA',    'genero' => 'M'],
            ['nombre' => 'LINDA ZOE',                 'apellido_paterno' => 'JULCA',       'apellido_materno' => 'MANRIQUE',    'genero' => 'F'],
            ['nombre' => 'FELIX EMANUEL',             'apellido_paterno' => 'LAZO',        'apellido_materno' => 'CHERRE',      'genero' => 'M'],
            ['nombre' => 'IKER LEONEL',               'apellido_paterno' => 'LIZANO',      'apellido_materno' => 'YOVERA',      'genero' => 'M'],
            ['nombre' => 'JADE ALEXANDRA',            'apellido_paterno' => 'LLENQUE',     'apellido_materno' => 'HUARANGA',    'genero' => 'F'],
            ['nombre' => 'ZOE AITANA',                'apellido_paterno' => 'MARTINEZ',    'apellido_materno' => 'BERNUY',      'genero' => 'F'],
            ['nombre' => 'ASHLA ORIANA',              'apellido_paterno' => 'NAMUCHE',     'apellido_materno' => 'QUINDE',      'genero' => 'F'],
            ['nombre' => 'IVANA ALEXA',               'apellido_paterno' => 'NORES',       'apellido_materno' => 'CHERO',       'genero' => 'F'],
            ['nombre' => 'JESÚS PAOLO ALEJANDRO',     'apellido_paterno' => 'PRADO',       'apellido_materno' => 'GUERRERO',    'genero' => 'M'],
            ['nombre' => 'ALEXANDRA DE LOS ÁNGELES',  'apellido_paterno' => 'QUEREVALU',   'apellido_materno' => 'CASTILLO',    'genero' => 'F'],
            ['nombre' => 'IVANNA KENDRA',             'apellido_paterno' => 'REQUENA',     'apellido_materno' => 'SERNAQUE',    'genero' => 'F'],
            ['nombre' => 'ZAMARA SARAY',              'apellido_paterno' => 'RODRIGUEZ',   'apellido_materno' => 'TANDIOY',     'genero' => 'F'],
            ['nombre' => 'ESNAYDER FABRISIO',         'apellido_paterno' => 'RUIZ',        'apellido_materno' => 'SERNAQUE',    'genero' => 'M'],
            ['nombre' => 'LAURA JOSEFA',              'apellido_paterno' => 'SAGUMA',      'apellido_materno' => 'BARRIENTOS',  'genero' => 'F'],
            ['nombre' => 'JUAN DE DIOS',              'apellido_paterno' => 'TIMANA',      'apellido_materno' => 'VIDAL',       'genero' => 'M'],
            ['nombre' => 'PATRIK ALEXANDER',          'apellido_paterno' => 'VELASQUEZ',   'apellido_materno' => 'IGLESIAS',    'genero' => 'M'],
            ['nombre' => 'MÍA GUADALUPE',             'apellido_paterno' => 'VILELA',      'apellido_materno' => 'GONZALES',    'genero' => 'F'],
            ['nombre' => 'VICTOR JOSUE',              'apellido_paterno' => 'ZAPATA',      'apellido_materno' => 'RUIZ',        'genero' => 'M'],
            ['nombre' => 'LOUIS STÉFANO',             'apellido_paterno' => 'ZAPATA',      'apellido_materno' => 'VERA',        'genero' => 'M'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P3D{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '3° GRADO D',
                    'semestre'          => 3,
                    'fecha_nacimiento' => '2017-01-01', // Fecha por defecto
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
