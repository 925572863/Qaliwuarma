<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosCuartoGradoCSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'AURA YAMILETH',          'apellido_paterno' => 'ALAMO',           'apellido_materno' => 'CASTILLO',    'genero' => 'F', 'fecha_nacimiento' => '2016-04-01'],
            ['nombre' => 'YKHER STIWAR',           'apellido_paterno' => 'BANCAYAN',        'apellido_materno' => 'CHAPILLIQUEN','genero' => 'M', 'fecha_nacimiento' => '2016-09-23'],
            ['nombre' => 'ARIANA MIRELLA',         'apellido_paterno' => 'BOCANEGRA',       'apellido_materno' => 'CASTILLO',    'genero' => 'F', 'fecha_nacimiento' => '2016-06-13'],
            ['nombre' => 'EDSON JARIEL',           'apellido_paterno' => 'CALDERON',        'apellido_materno' => 'BARRANZUELA', 'genero' => 'M', 'fecha_nacimiento' => '2017-03-08'],
            ['nombre' => 'JAZIEL ESAU',            'apellido_paterno' => 'CASTILLO',        'apellido_materno' => 'TABOADA',     'genero' => 'M', 'fecha_nacimiento' => '2017-03-02'],
            ['nombre' => 'EDUARDO DAVID',          'apellido_paterno' => 'CEDANO',          'apellido_materno' => 'ROMAN',       'genero' => 'M', 'fecha_nacimiento' => '2016-12-12'],
            ['nombre' => 'YONDER RAFAEL',          'apellido_paterno' => 'COROPA',          'apellido_materno' => 'ARCIA',       'genero' => 'F', 'fecha_nacimiento' => '2017-01-31'],
            ['nombre' => 'BRITHANY AYELIN',        'apellido_paterno' => 'DIAZ',            'apellido_materno' => 'GOMEZ',       'genero' => 'F', 'fecha_nacimiento' => '2017-02-06'],
            ['nombre' => 'ALEXIS SNAYDER',         'apellido_paterno' => 'DONAYRE',         'apellido_materno' => 'CUNYA',       'genero' => 'M', 'fecha_nacimiento' => '2017-01-20'],
            ['nombre' => 'MATHEO DAVID',           'apellido_paterno' => 'FLORES',          'apellido_materno' => 'ZETA',        'genero' => 'M', 'fecha_nacimiento' => '2016-02-12'],
            ['nombre' => 'YUDY ZENAIDA',           'apellido_paterno' => 'GALOPINO',        'apellido_materno' => 'RIVAS',       'genero' => 'F', 'fecha_nacimiento' => '2013-08-05'],
            ['nombre' => 'JOHANA ANTONELA',        'apellido_paterno' => 'GASPAR',          'apellido_materno' => 'MAURICIO',    'genero' => 'F', 'fecha_nacimiento' => '2016-01-04'],
            ['nombre' => 'AMY ANJALI',             'apellido_paterno' => 'GUERRERO',        'apellido_materno' => 'MALARA',      'genero' => 'F', 'fecha_nacimiento' => '2016-06-16'],
            ['nombre' => 'LUHANA ISABELLA',        'apellido_paterno' => 'JUAREZ',          'apellido_materno' => 'PACHERRES',   'genero' => 'F', 'fecha_nacimiento' => '2016-10-15'],
            ['nombre' => 'NICOLÁS',                 'apellido_paterno' => 'LLERENA',         'apellido_materno' => 'CAHUANA',     'genero' => 'M', 'fecha_nacimiento' => '2016-11-17'],
            ['nombre' => 'CRISTHIAN ISMAEL GERARDO','apellido_paterno' => 'LOZADA',          'apellido_materno' => 'MOGOLLON',    'genero' => 'M', 'fecha_nacimiento' => '2016-09-24'],
            ['nombre' => 'ASHLEY AILIN',           'apellido_paterno' => 'MALARA',          'apellido_materno' => 'ROÑA',        'genero' => 'F', 'fecha_nacimiento' => '2016-06-06'],
            ['nombre' => 'VICTOR MANUEL',          'apellido_paterno' => 'MANCHAY',         'apellido_materno' => 'MARTINEZ',    'genero' => 'M', 'fecha_nacimiento' => '2015-01-04'],
            ['nombre' => 'LUIS GUSTAVO',           'apellido_paterno' => 'MURGUEYTIO',      'apellido_materno' => 'GOMEZ',       'genero' => 'M', 'fecha_nacimiento' => '2016-06-09'],
            ['nombre' => 'DERET JORGE LUIS',       'apellido_paterno' => 'NUÑEZ',           'apellido_materno' => 'GUARNIZO',    'genero' => 'M', 'fecha_nacimiento' => '2016-04-12'],
            ['nombre' => 'THALÍA ANABEL',          'apellido_paterno' => 'PEÑA',            'apellido_materno' => 'CALLE',       'genero' => 'F', 'fecha_nacimiento' => '2017-02-27'],
            ['nombre' => 'BRYANA GUADALUPE',       'apellido_paterno' => 'RAMIREZ',         'apellido_materno' => 'GUARNIZO',    'genero' => 'F', 'fecha_nacimiento' => '2016-06-05'],
            ['nombre' => 'DAINY SAMANTA',          'apellido_paterno' => 'REMAICUNA',       'apellido_materno' => 'CASTILLO',    'genero' => 'F', 'fecha_nacimiento' => '2016-06-13'],
            ['nombre' => 'LUIS GABRIEL',           'apellido_paterno' => 'REYES',           'apellido_materno' => 'RAMOS',       'genero' => 'M', 'fecha_nacimiento' => '2017-03-15'],
            ['nombre' => 'LUCAS MATIAS',           'apellido_paterno' => 'RIVAS',           'apellido_materno' => 'BENITES',     'genero' => 'M', 'fecha_nacimiento' => '2016-07-27'],
            ['nombre' => 'SANTIAGO BEJAMIN',       'apellido_paterno' => 'SERNAQUE',        'apellido_materno' => 'RODRIGUEZ',   'genero' => 'M', 'fecha_nacimiento' => '2017-02-10'],
            ['nombre' => 'AYLIN MAYERLY',          'apellido_paterno' => 'VALENCIA',        'apellido_materno' => 'CORTEZ',      'genero' => 'F', 'fecha_nacimiento' => '2016-09-08'],
            ['nombre' => 'WILLIAM SMITH',          'apellido_paterno' => 'YAMUNAQUE',       'apellido_materno' => 'CARRASCO',    'genero' => 'M', 'fecha_nacimiento' => '2016-08-12'],
            ['nombre' => 'STIVER STMITH',          'apellido_paterno' => 'ZAVALA',          'apellido_materno' => 'VALLEJOS',    'genero' => 'M', 'fecha_nacimiento' => '2014-07-21'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P4C{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '4° GRADO C',
                    'semestre'          => 4,
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
