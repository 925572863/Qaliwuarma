<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosSextoGradoCSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'BORIS DANILO',             'apellido_paterno' => 'AGUIRRE',        'apellido_materno' => 'CUNGUIA',      'genero' => 'M'],
            ['nombre' => 'VALENTINO KAZZÚ',          'apellido_paterno' => 'BAYONA',         'apellido_materno' => 'BERECHE',      'genero' => 'M'],
            ['nombre' => 'LIA FABIANA',              'apellido_paterno' => 'BUSTILLO',       'apellido_materno' => 'VILELA',       'genero' => 'F'],
            ['nombre' => 'SANTIAGO YOHAO',           'apellido_paterno' => 'CALLE',          'apellido_materno' => 'CHAMBA',       'genero' => 'M'],
            ['nombre' => 'ROSA FRANCISCA',           'apellido_paterno' => 'CASTRO',         'apellido_materno' => 'CHERRE',       'genero' => 'F'],
            ['nombre' => 'MIA XIMENA ALEXANDRA',     'apellido_paterno' => 'CASTRO',         'apellido_materno' => 'NOE',          'genero' => 'F'],
            ['nombre' => 'KAOMI VALENTINA',          'apellido_paterno' => 'CHAVEZ',         'apellido_materno' => 'BERECHE',      'genero' => 'F'],
            ['nombre' => 'ALESSANDRO JOSE',          'apellido_paterno' => 'CHIROQUE',       'apellido_materno' => 'CARRASCO',     'genero' => 'M'],
            ['nombre' => 'SNAYDER LEONEL',           'apellido_paterno' => 'CHUMACERO',      'apellido_materno' => 'HERRERA',      'genero' => 'M'],
            ['nombre' => 'DIEGO ADRIEL',             'apellido_paterno' => 'CORDOVA',        'apellido_materno' => 'YARLEQUE',     'genero' => 'M'],
            ['nombre' => 'MAXIMO ALEXANDER',         'apellido_paterno' => 'CRUZ',           'apellido_materno' => 'CHERO',        'genero' => 'M'],
            ['nombre' => 'JUAN JOSE MARTÍN',         'apellido_paterno' => 'ESPINOZA',       'apellido_materno' => 'LABAN',        'genero' => 'M'],
            ['nombre' => 'ERICK ALONSO',             'apellido_paterno' => 'ESTRADA',        'apellido_materno' => 'VALLEJOS',     'genero' => 'M'],
            ['nombre' => 'YARETZI',                  'apellido_paterno' => 'FERNANDEZ',      'apellido_materno' => 'TORRES',       'genero' => 'F'],
            ['nombre' => 'IQUER MANUEL',             'apellido_paterno' => 'FLORES',         'apellido_materno' => 'QUITO',        'genero' => 'M'],
            ['nombre' => 'SHOAMIL YORLE',            'apellido_paterno' => 'IZQUIERDO',      'apellido_materno' => 'SARANGO',      'genero' => 'M'],
            ['nombre' => 'ALEXKA DAMIRA',            'apellido_paterno' => 'LIZANO',         'apellido_materno' => 'YOVERA',       'genero' => 'F'],
            ['nombre' => 'RAYSA AYDANA',             'apellido_paterno' => 'LOZADA',         'apellido_materno' => 'COVEÑAS',      'genero' => 'F'],
            ['nombre' => 'HILDEBRADO ROGELIO',       'apellido_paterno' => 'MIRANDA',        'apellido_materno' => 'POICON',       'genero' => 'M'],
            ['nombre' => 'ALEXKA CAMILA',            'apellido_paterno' => 'NAVARRO',        'apellido_materno' => 'GRANADINO',    'genero' => 'F'],
            ['nombre' => 'MILDER HERNAN',            'apellido_paterno' => 'NAYRA',          'apellido_materno' => 'CHOCAN',       'genero' => 'M'],
            ['nombre' => 'JOSE ABRAHÁN',             'apellido_paterno' => 'OTOYA',          'apellido_materno' => 'SOLANO',       'genero' => 'M'],
            ['nombre' => 'SEBASTIAN RAYHAM',         'apellido_paterno' => 'PEREZ',          'apellido_materno' => 'ZAPATA',       'genero' => 'M'],
            ['nombre' => 'THIAGO ALONSO',            'apellido_paterno' => 'PINTADO',        'apellido_materno' => 'RUIZ',         'genero' => 'M'],
            ['nombre' => 'MARCO ANTONIO',            'apellido_paterno' => 'PULACHE',        'apellido_materno' => 'GIRON',        'genero' => 'M'],
            ['nombre' => 'NANCY VALENTINA',          'apellido_paterno' => 'PURIZACA',       'apellido_materno' => 'CRUZ',          'genero' => 'F'],
            ['nombre' => 'DAMARIS ISABEL',           'apellido_paterno' => 'QUINDE',         'apellido_materno' => 'HUAMAN',       'genero' => 'F'],
            ['nombre' => 'MIA AYELEN',               'apellido_paterno' => 'REATEGUI',       'apellido_materno' => 'CASTILLO',     'genero' => 'F'],
            ['nombre' => 'MANUEL ADRIANO',           'apellido_paterno' => 'RIOFRIO',        'apellido_materno' => 'NAVARRO',      'genero' => 'M'],
            ['nombre' => 'XIMENA GAELA',             'apellido_paterno' => 'RIVAS',          'apellido_materno' => 'LLOCLLA',       'genero' => 'F'],
            ['nombre' => 'MARCELA TATIANA',          'apellido_paterno' => 'ZAPATA',         'apellido_materno' => 'SANDOVAL',     'genero' => 'F'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P6C{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '6° GRADO C',
                    'semestre'          => 6,
                    'fecha_nacimiento' => '2014-01-01', // Fecha por defecto
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
