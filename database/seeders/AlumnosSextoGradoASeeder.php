<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosSextoGradoASeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'ALIANA YAMILET',          'apellido_paterno' => 'AGURTO',         'apellido_materno' => 'CALLE',        'genero' => 'F'],
            ['nombre' => 'JORDAN NICOLAS',           'apellido_paterno' => 'ALAMO',          'apellido_materno' => 'ADANAQUE',     'genero' => 'M'],
            ['nombre' => 'CESAR DANIEL SMITH',       'apellido_paterno' => 'ARICA',          'apellido_materno' => 'MORALES',      'genero' => 'M'],
            ['nombre' => 'NOÁ MAYSSA MEGUMY',        'apellido_paterno' => 'BACA',           'apellido_materno' => 'ZUTA',         'genero' => 'F'],
            ['nombre' => 'HEDERSON NEYMAR',          'apellido_paterno' => 'BARBOZA',        'apellido_materno' => 'BANCAYAN',     'genero' => 'M'],
            ['nombre' => 'NIALL FABIAN',             'apellido_paterno' => 'CERRO',          'apellido_materno' => 'MORALES',      'genero' => 'M'],
            ['nombre' => 'YHARY XIOMARA',            'apellido_paterno' => 'FERNANDEZ',      'apellido_materno' => 'DIAZ',         'genero' => 'F'],
            ['nombre' => 'IKER SANTIAGO',            'apellido_paterno' => 'FLORES',         'apellido_materno' => 'LOPEZ',        'genero' => 'M'],
            ['nombre' => 'THIAGO PERCEO',            'apellido_paterno' => 'GEMIN',          'apellido_materno' => 'JIMENEZ',      'genero' => 'M'],
            ['nombre' => 'MAYCOLL ALEXIS',           'apellido_paterno' => 'GUZMAN',         'apellido_materno' => 'QUEZADA',      'genero' => 'M'],
            ['nombre' => 'CONSUELO DE LOS ANGELES',  'apellido_paterno' => 'HIDALGO',        'apellido_materno' => 'LACHIRA',      'genero' => 'F'],
            ['nombre' => 'MIKEYLA GABRIELA',         'apellido_paterno' => 'HONORIO',        'apellido_materno' => 'ZAPATA',       'genero' => 'F'],
            ['nombre' => 'JAIRO JOSHUA',             'apellido_paterno' => 'LUZON',          'apellido_materno' => 'VILELA',       'genero' => 'M'],
            ['nombre' => 'SOL ALEJANDRA',            'apellido_paterno' => 'MALDONADO',      'apellido_materno' => 'CHINCHAY',     'genero' => 'F'],
            ['nombre' => 'JAMILA LUANA',             'apellido_paterno' => 'MULATILLO',      'apellido_materno' => 'LOAIZA',       'genero' => 'F'],
            ['nombre' => 'JOSUÉ ABRAHAN',            'apellido_paterno' => 'NUÑEZ',          'apellido_materno' => 'HUMBO',        'genero' => 'M'],
            ['nombre' => 'PIERO ALEXANDER',          'apellido_paterno' => 'PEÑA',           'apellido_materno' => 'FARIAS',       'genero' => 'M'],
            ['nombre' => 'ARUMY ALEIDA',             'apellido_paterno' => 'PEÑA',           'apellido_materno' => 'RAMIREZ',      'genero' => 'F'],
            ['nombre' => 'THIAGO SNYDER',            'apellido_paterno' => 'PINGO',          'apellido_materno' => 'REQUENA',      'genero' => 'M'],
            ['nombre' => 'XOANA JAZMIN',             'apellido_paterno' => 'RAMOS',          'apellido_materno' => 'AGUILERA',     'genero' => 'F'],
            ['nombre' => 'ESWIN JESUS',              'apellido_paterno' => 'RETO',           'apellido_materno' => 'AYALA',        'genero' => 'M'],
            ['nombre' => 'LIAN JARED',               'apellido_paterno' => 'RIVERA',         'apellido_materno' => 'CHIROQUE',     'genero' => 'M'],
            ['nombre' => 'SEBASTIAN ALEXANDER',       'apellido_paterno' => 'RUIZ',          'apellido_materno' => 'SERNAQUE',     'genero' => 'M'],
            ['nombre' => 'JAZMÍN YAMILEE',           'apellido_paterno' => 'SILVA',          'apellido_materno' => 'RUIZ',         'genero' => 'F'],
            ['nombre' => 'SHEINER LUIS',             'apellido_paterno' => 'SUAREZ',         'apellido_materno' => 'PELEKAIS',     'genero' => 'M'],
            ['nombre' => 'KEIBER GABRIEL',           'apellido_paterno' => 'TORRES',         'apellido_materno' => 'DIAZ',         'genero' => 'M'],
            ['nombre' => 'XIOMARA DAYANA',           'apellido_paterno' => 'VILCHEZ',        'apellido_materno' => 'GARCIA',       'genero' => 'F'],
            ['nombre' => 'ADERLY LEONARDO',          'apellido_paterno' => 'VILCHEZ',        'apellido_materno' => 'PINGO',        'genero' => 'M'],
            ['nombre' => 'MAIKEL ESTEVEN',           'apellido_paterno' => 'YANAYACO',       'apellido_materno' => 'SARANGO',      'genero' => 'M'],
            ['nombre' => 'LÍAM MATHIAS',             'apellido_paterno' => 'YPANAQUE',       'apellido_materno' => 'SANDOVAL',     'genero' => 'M'],
            ['nombre' => 'MIA KAMILA VALESKA',       'apellido_paterno' => 'YUPANQUI',       'apellido_materno' => 'LOZADA',       'genero' => 'F'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P6A{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '6° GRADO A',
                    'semestre'          => 6,
                    'fecha_nacimiento' => '2014-01-01', // Fecha por defecto
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
