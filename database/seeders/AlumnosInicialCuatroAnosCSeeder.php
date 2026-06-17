<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosInicialCuatroAnosCSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'AITANA SOFIA', 'apellido_paterno' => 'CAJUSOL', 'apellido_materno' => 'GUTIERREZ', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92758942'],
            ['nombre' => 'AITANA CAMILA', 'apellido_paterno' => 'CHIROQUE', 'apellido_materno' => 'VILCHEZ', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92574488'],
            ['nombre' => 'KHALESSY JOHANA', 'apellido_paterno' => 'CHUQUIHUANGA', 'apellido_materno' => 'SANCHEZ', 'genero' => 'F', 'estado' => 'baja', 'curp' => '92307104'],
            ['nombre' => 'LUCIANA SHANI', 'apellido_paterno' => 'CRUZ', 'apellido_materno' => 'SANCARRANCO', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92825825'],
            ['nombre' => 'JOSUAN ENRIQUE', 'apellido_paterno' => 'ESPINOZA', 'apellido_materno' => 'SANDOVAL', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92569223'],
            ['nombre' => 'ZOE CATALELLA', 'apellido_paterno' => 'FERIA', 'apellido_materno' => 'RONDON', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92312728'],
            ['nombre' => 'ALESSIA LUHANA', 'apellido_paterno' => 'GARRIDO', 'apellido_materno' => 'CASANOVA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92487942'],
            ['nombre' => 'DYLAN GAEL', 'apellido_paterno' => 'GUANILO', 'apellido_materno' => 'IZQUIERDO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92469960'],
            ['nombre' => 'MIA GUADALUPE', 'apellido_paterno' => 'IMAN', 'apellido_materno' => 'YANGUA', 'genero' => 'F', 'estado' => 'baja', 'curp' => '92559343'],
            ['nombre' => 'ABDEL ELIAN', 'apellido_paterno' => 'LEON', 'apellido_materno' => 'CHIROQUE', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92598821'],
            ['nombre' => 'ETHAN KALEB', 'apellido_paterno' => 'LIZANA', 'apellido_materno' => 'GIRON', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92355223'],
            ['nombre' => 'KATALEYA SHARLOT', 'apellido_paterno' => 'LOAYZA', 'apellido_materno' => 'PARRILLA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92812899'],
            ['nombre' => 'EVANS SEBASTIÁN', 'apellido_paterno' => 'PEÑA', 'apellido_materno' => 'RUMICHE', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92673144'],
            ['nombre' => 'NOAH ISAAC AZAEL', 'apellido_paterno' => 'ZAPATA', 'apellido_materno' => 'SERNAQUE', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92767773'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026I4C{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'inicial',
                    'carrera'           => 'INICIAL 4 AÑOS C',
                    'semestre'          => 0,
                    'fecha_nacimiento' => '2022-01-01',
                    'fecha_inscripcion' => '2026-03-01',
                ])
            );
        }
    }
}
