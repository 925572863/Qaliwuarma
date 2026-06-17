<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosInicialCincoAnosCSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'ANTONELLA ALEXANDRA', 'apellido_paterno' => 'ADRIANZEN', 'apellido_materno' => 'ESPINOZA', 'genero' => 'F', 'estado' => 'baja', 'curp' => '92148021'],
            ['nombre' => 'HARLEY AIXA', 'apellido_paterno' => 'ALVA VILLEGAS', 'apellido_materno' => '', 'genero' => 'F', 'estado' => 'activo', 'curp' => '91883858'],
            ['nombre' => 'KHUSHY ADRIANA KAZUMY', 'apellido_paterno' => 'CALDERON', 'apellido_materno' => 'NIMA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92038039'],
            ['nombre' => 'DAFNE CATALINA', 'apellido_paterno' => 'CONTRERAS', 'apellido_materno' => 'NOBLECILLA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92236438'],
            ['nombre' => 'LUIS SMITH', 'apellido_paterno' => 'CUNYA', 'apellido_materno' => 'RUIZ', 'genero' => 'M', 'estado' => 'activo', 'curp' => '91856581'],
            ['nombre' => 'ISABELLA DEL VALLE', 'apellido_paterno' => 'ECHEVERRIA', 'apellido_materno' => 'MARTINEZ', 'genero' => 'F', 'estado' => 'activo', 'curp' => '91989517'],
            ['nombre' => 'ADIRA BELEN', 'apellido_paterno' => 'ESCOBAR', 'apellido_materno' => 'VICENTE', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92262844'],
            ['nombre' => 'YHOMBERLY VICTORIA', 'apellido_paterno' => 'GUERRERO', 'apellido_materno' => 'SANCHEZ', 'genero' => 'F', 'estado' => 'baja', 'curp' => null],
            ['nombre' => 'LIAM ABDIEL', 'apellido_paterno' => 'HIDALGO', 'apellido_materno' => 'BARBOZA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92102765'],
            ['nombre' => 'JOSMAR ABDIEL', 'apellido_paterno' => 'ICANAQUE', 'apellido_materno' => 'TOCTO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92071680'],
            ['nombre' => 'DIEGO SEBASTIAN', 'apellido_paterno' => 'JIMENEZ', 'apellido_materno' => 'CHAVEZ', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92200649'],
            ['nombre' => 'GUADALUPE KATHERINE', 'apellido_paterno' => 'LECARNAQUE', 'apellido_materno' => 'ANTON', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92081585'],
            ['nombre' => 'GERALF SEBASTIÁN', 'apellido_paterno' => 'LEON', 'apellido_materno' => 'ZAPATA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '91956132'],
            ['nombre' => 'LUCAS SANTIAGO', 'apellido_paterno' => 'LOPEZ', 'apellido_materno' => 'MOGOLLON', 'genero' => 'M', 'estado' => 'activo', 'curp' => null],
            ['nombre' => 'VICTOR ALESSANDRO', 'apellido_paterno' => 'MONDRAGON', 'apellido_materno' => 'RUIDIAS', 'genero' => 'M', 'estado' => 'activo', 'curp' => '91963098'],
            ['nombre' => 'YATZIRI BRISEL', 'apellido_paterno' => 'NAMUCHE', 'apellido_materno' => 'MECA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92267754'],
            ['nombre' => 'GAELA ASLYN', 'apellido_paterno' => 'PALACIOS', 'apellido_materno' => 'CARHUATOCTO', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92116776'],
            ['nombre' => 'ELEAZAR DAVID', 'apellido_paterno' => 'PARDO', 'apellido_materno' => 'CARRASCO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '91895750'],
            ['nombre' => 'ÁMBAR RAFAELLA', 'apellido_paterno' => 'RAMIREZ', 'apellido_materno' => 'NORIEGA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92062797'],
            ['nombre' => 'FRANKIE JOSUÉ', 'apellido_paterno' => 'RUIZ', 'apellido_materno' => 'GIL', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92245254'],
            ['nombre' => 'GERARDO ISMAEL', 'apellido_paterno' => 'SANTILLANA', 'apellido_materno' => 'PINTADO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92006005'],
            ['nombre' => 'FLAVIA MELEK', 'apellido_paterno' => 'SIPION', 'apellido_materno' => 'GAONA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92260875'],
            ['nombre' => 'BASTIÁN ANDRÉS', 'apellido_paterno' => 'TAVARA', 'apellido_materno' => 'RAMIREZ', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92256604'],
            ['nombre' => 'ITHAM ORLANDO', 'apellido_paterno' => 'VALENCIA', 'apellido_materno' => 'CORTEZ', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92261373'],
            ['nombre' => 'ARANTZA ELIZA', 'apellido_paterno' => 'VENTURA', 'apellido_materno' => 'FERNANDEZ', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92036376'],
            ['nombre' => 'ANA BELÉN', 'apellido_paterno' => 'VILCHERREZ', 'apellido_materno' => 'URBINA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92155301'],
            ['nombre' => 'DILAN JAEL', 'apellido_paterno' => 'YAMUNAQUE', 'apellido_materno' => 'CARRASCO', 'genero' => 'M', 'estado' => 'baja', 'curp' => '92122860'],
            ['nombre' => 'ELIAS ISMAEL', 'apellido_paterno' => 'YARLEQUE', 'apellido_materno' => 'AYALA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '91931371'],
            ['nombre' => 'EISHELL ESTEFANIA', 'apellido_paterno' => 'YEPEZ', 'apellido_materno' => 'GARCIAS', 'genero' => 'F', 'estado' => 'activo', 'curp' => null],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026I5C{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'inicial',
                    'carrera'           => 'INICIAL 5 AÑOS C',
                    'semestre'          => 0,
                    'fecha_nacimiento' => '2021-01-01',
                    'fecha_inscripcion' => '2026-03-01',
                ])
            );
        }
    }
}
