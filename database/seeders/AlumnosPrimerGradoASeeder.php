<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosPrimerGradoASeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'KIARITA YARELY',    'apellido_paterno' => 'CHAPOÑAN',   'apellido_materno' => 'VENTURA'],
            ['nombre' => 'MYCAEL LIAM',       'apellido_paterno' => 'CORIMAYO',   'apellido_materno' => 'CARRANZA'],
            ['nombre' => 'ANDY SANTIAGO',     'apellido_paterno' => 'CORREA',     'apellido_materno' => 'ARCIA'],
            ['nombre' => 'ARIANA YASLYN',     'apellido_paterno' => 'CUNGUIA',    'apellido_materno' => 'FACUNDO'],
            ['nombre' => 'LUCIANA VALENTINA', 'apellido_paterno' => 'ESCATE',     'apellido_materno' => 'LLONTOP'],
            ['nombre' => 'MATIAS LEONEL',     'apellido_paterno' => 'ESPINOZA',   'apellido_materno' => 'SANDOVAL'],
            ['nombre' => 'KEYLA LISBETH',     'apellido_paterno' => 'FRIAS',      'apellido_materno' => 'PASAPERA'],
            ['nombre' => 'EZIO ANDRÉ',        'apellido_paterno' => 'GIRON',      'apellido_materno' => 'ERAZO'],
            ['nombre' => 'REIKEL ENMANUEL',   'apellido_paterno' => 'HERNANDEZ',  'apellido_materno' => 'CASTILLO'],
            ['nombre' => 'EMMA VALERIA',      'apellido_paterno' => 'HINOJOSA',   'apellido_materno' => 'MIO'],
            ['nombre' => 'DYLAN KERIM',       'apellido_paterno' => 'IMAN',       'apellido_materno' => 'FERNANDEZ'],
            ['nombre' => 'LIAM YAMPIERRE',    'apellido_paterno' => 'LEON',       'apellido_materno' => 'CHIROQUE'],
            ['nombre' => 'LOGAN DWAYNE',      'apellido_paterno' => 'LINGE',      'apellido_materno' => 'CORDOVA'],
            ['nombre' => 'MARIA FERNANDA',    'apellido_paterno' => 'LUDEÑA',     'apellido_materno' => 'IZQUIERDO'],
            ['nombre' => 'ALESSIA DANAE',     'apellido_paterno' => 'MARTINEZ',   'apellido_materno' => 'BERNUY'],
            ['nombre' => 'DAVID SCHMEICHEL',  'apellido_paterno' => 'MULATILLO',  'apellido_materno' => 'MOROCHO'],
            ['nombre' => 'EMILIANO ALESSANDRO','apellido_paterno' => 'NEYRA',     'apellido_materno' => 'NEIRA'],
            ['nombre' => 'JOAO GONZALO',      'apellido_paterno' => 'ORTEGA',     'apellido_materno' => 'CRUZ'],
            ['nombre' => 'MATHIAS NAHAM',     'apellido_paterno' => 'OVIEDO',     'apellido_materno' => 'LUNA'],
            ['nombre' => 'ANDRES GAEL',       'apellido_paterno' => 'PARADA',     'apellido_materno' => 'RESPLANDOR'],
            ['nombre' => 'MATHIAS RICARDO',   'apellido_paterno' => 'POMA',       'apellido_materno' => 'FLORES'],
            ['nombre' => 'BRIANNA YAMILET',   'apellido_paterno' => 'PUELLES',    'apellido_materno' => 'SANTUR'],
            ['nombre' => 'JUAN ESTEBAN',      'apellido_paterno' => 'PULACHE',    'apellido_materno' => 'GIRON'],
            ['nombre' => 'BENJAMIN URIEL',    'apellido_paterno' => 'RIOFRIO',    'apellido_materno' => 'NAVARRO'],
            ['nombre' => 'XIOMARA ISABEL',    'apellido_paterno' => 'ROMAN',      'apellido_materno' => 'GARCES'],
            ['nombre' => 'XIMENA CRISTHEL',   'apellido_paterno' => 'SERNAQUE',   'apellido_materno' => 'SANTOS'],
            ['nombre' => 'HUGO ALBERTO',      'apellido_paterno' => 'SILVA',      'apellido_materno' => 'ARROYO'],
            ['nombre' => 'ANTONY GABRIEL',    'apellido_paterno' => 'TORRES',     'apellido_materno' => 'DIAZ'],
            ['nombre' => 'MERCEDES ABIGAIL',  'apellido_paterno' => 'YARLEQUE',   'apellido_materno' => 'CASTILLO'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026P1A{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'primaria',
                    'carrera'           => '1° GRADO A',
                    'semestre'          => 1,
                    'fecha_inscripcion' => '2026-03-01',
                    'estado'            => 'activo',
                ])
            );
        }
    }
}
