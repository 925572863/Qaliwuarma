<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosInicialCincoAnosASeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'ANÍBAL AARÓN', 'apellido_paterno' => 'ABANTO', 'apellido_materno' => 'JUAREZ', 'genero' => 'M', 'estado' => 'activo', 'curp' => '91798627'],
            ['nombre' => 'LUHANA NICOLLE', 'apellido_paterno' => 'CASTILLO', 'apellido_materno' => 'MENDOZA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '91997084'],
            ['nombre' => 'JAZIEL IVÁN', 'apellido_paterno' => 'CASTRO', 'apellido_materno' => 'GRILLO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92277141'],
            ['nombre' => 'LIAN ADRIAM', 'apellido_paterno' => 'CHIRA', 'apellido_materno' => 'PEÑA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92030716'],
            ['nombre' => 'GADIEL YOSEPH', 'apellido_paterno' => 'CORDOVA', 'apellido_materno' => 'NAQUICHE', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92092075'],
            ['nombre' => 'ROBINSON JOAQUIN', 'apellido_paterno' => 'CORREA', 'apellido_materno' => 'LACHIRA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92048863'],
            ['nombre' => 'ANGÉL GHAÉL', 'apellido_paterno' => 'DAVIS', 'apellido_materno' => 'FEBRES', 'genero' => 'M', 'estado' => 'activo', 'curp' => '91976140'],
            ['nombre' => 'ROUSE AMBAR', 'apellido_paterno' => 'FARFAN', 'apellido_materno' => 'CORDOVA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92083559'],
            ['nombre' => 'ELA DANIELA', 'apellido_paterno' => 'FARIAS', 'apellido_materno' => 'TORRES', 'genero' => 'F', 'estado' => 'activo', 'curp' => '91829288'],
            ['nombre' => 'JUNIOR MATEO', 'apellido_paterno' => 'FERNANDEZ', 'apellido_materno' => 'CALLE', 'genero' => 'M', 'estado' => 'activo', 'curp' => '91557992'],
            ['nombre' => 'MIA YATZIRI', 'apellido_paterno' => 'GASPAR', 'apellido_materno' => 'GALVEZ', 'genero' => 'F', 'estado' => 'activo', 'curp' => '91975315'],
            ['nombre' => 'BRITNNY DALEYSA', 'apellido_paterno' => 'GONZALES', 'apellido_materno' => 'CULCAS', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92025810'],
            ['nombre' => 'EDUARDO FRANCO', 'apellido_paterno' => 'HERREROS', 'apellido_materno' => 'ANTON', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92293949'],
            ['nombre' => 'MIA ALONDRA', 'apellido_paterno' => 'MACO', 'apellido_materno' => 'ROMERO', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92290525'],
            ['nombre' => 'KEYRI THAIS', 'apellido_paterno' => 'MASACHE', 'apellido_materno' => 'JIMENEZ', 'genero' => 'F', 'estado' => 'activo', 'curp' => '91823016'],
            ['nombre' => 'SOFIA LICET', 'apellido_paterno' => 'MAZA', 'apellido_materno' => 'SEMINARIO', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92252601'],
            ['nombre' => 'BRIANNA DARIAN', 'apellido_paterno' => 'MORALES', 'apellido_materno' => 'JIMENEZ', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92123172'],
            ['nombre' => 'EMILIANO ELIAS', 'apellido_paterno' => 'OTERO', 'apellido_materno' => 'CHUMACERO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92163678'],
            ['nombre' => 'JERARD JULIAN', 'apellido_paterno' => 'ROJAS', 'apellido_materno' => 'SILUPU', 'genero' => 'M', 'estado' => 'activo', 'curp' => '91824413'],
            ['nombre' => 'ERIKA MERCEDES', 'apellido_paterno' => 'SAAVEDRA', 'apellido_materno' => 'ROSADO', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92289129'],
            ['nombre' => 'YAMILETH SAYUMI', 'apellido_paterno' => 'SALVADOR', 'apellido_materno' => 'HUAMAN', 'genero' => 'F', 'estado' => 'activo', 'curp' => '91965038'],
            ['nombre' => 'MARIAFÉ ESPERANZA', 'apellido_paterno' => 'SANJINEZ', 'apellido_materno' => 'VILCHEZ', 'genero' => 'F', 'estado' => 'activo', 'curp' => '91867833'],
            ['nombre' => 'DASHA KILLARI', 'apellido_paterno' => 'VIERA', 'apellido_materno' => 'MENDOZA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92049053'],
            ['nombre' => 'JEREMY URIEL', 'apellido_paterno' => 'YANAYACO', 'apellido_materno' => 'ALVARADO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92288198'],
            ['nombre' => 'GERSON AMIR', 'apellido_paterno' => 'ZAPATA', 'apellido_materno' => 'GALARRETA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92111274'],
            ['nombre' => 'EITHAN BENJAMIN', 'apellido_paterno' => 'ZAPATA', 'apellido_materno' => 'VERA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '91815334'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026I5A{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'inicial',
                    'carrera'           => 'INICIAL 5 AÑOS A',
                    'semestre'          => 0,
                    'fecha_nacimiento' => '2021-01-01',
                    'fecha_inscripcion' => '2026-03-01',
                ])
            );
        }
    }
}
