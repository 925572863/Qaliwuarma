<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosInicialCuatroAnosASeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'SAMIR EIDÁN ANTONIO', 'apellido_paterno' => 'AGURTO', 'apellido_materno' => 'AREVALO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92603222'],
            ['nombre' => 'DYLAN JOSÉ', 'apellido_paterno' => 'ARROYO', 'apellido_materno' => 'CORDOVA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92802438'],
            ['nombre' => 'STEPHANO ADRIEL', 'apellido_paterno' => 'CASTILLO', 'apellido_materno' => 'GARRIDO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92381564'],
            ['nombre' => 'MIA NASLY', 'apellido_paterno' => 'CEDEÑO', 'apellido_materno' => 'HERRERA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92317391'],
            ['nombre' => 'ABRAHAM HIDELBRANDON', 'apellido_paterno' => 'CHUNGA', 'apellido_materno' => 'SAAVEDRA', 'genero' => 'M', 'estado' => 'baja', 'curp' => '92494466'],
            ['nombre' => 'LIDIA VALERIA', 'apellido_paterno' => 'FLORES', 'apellido_materno' => 'SILVA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92564140'],
            ['nombre' => 'AKEMY KATALEYA', 'apellido_paterno' => 'FLORES', 'apellido_materno' => 'YANAYACO', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92567623'],
            ['nombre' => 'LIAM STEVEN', 'apellido_paterno' => 'GIRON', 'apellido_materno' => 'ERAZO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92496673'],
            ['nombre' => 'REINER MATHIAS', 'apellido_paterno' => 'HERNANDEZ', 'apellido_materno' => 'CASTILLO', 'genero' => 'M', 'estado' => 'activo', 'curp' => null],
            ['nombre' => 'MILCAR YADIEL ALEXANDER', 'apellido_paterno' => 'HERRERA', 'apellido_materno' => 'CUNYA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92301245'],
            ['nombre' => 'VANIA SARAI', 'apellido_paterno' => 'INFANTE', 'apellido_materno' => 'VILLEGAS', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92801538'],
            ['nombre' => 'AITANA GUADALUPE', 'apellido_paterno' => 'LOPEZ', 'apellido_materno' => 'ZETA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92718087'],
            ['nombre' => 'ANDRE SEBASTIAN', 'apellido_paterno' => 'LOPEZ', 'apellido_materno' => 'ZETA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92718096'],
            ['nombre' => 'DOMINIC DYLAN', 'apellido_paterno' => 'QUEZADA', 'apellido_materno' => 'RIVERA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92455188'],
            ['nombre' => 'DANNA SOFIA', 'apellido_paterno' => 'REATEGUI', 'apellido_materno' => 'CASTILLO', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92307933'],
            ['nombre' => 'DILAN JASSIEL', 'apellido_paterno' => 'ROBLEDO', 'apellido_materno' => 'PAZ', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92418142'],
            ['nombre' => 'KIOMY MASSIEL', 'apellido_paterno' => 'RODAS', 'apellido_materno' => 'PAULINI', 'genero' => 'F', 'estado' => 'baja', 'curp' => '92752824'],
            ['nombre' => 'GUSTAVO JESÚS', 'apellido_paterno' => 'SAAVEDRA', 'apellido_materno' => 'PANTA', 'genero' => 'M', 'estado' => 'baja', 'curp' => '92676461'],
            ['nombre' => 'DAYRON TEODORO', 'apellido_paterno' => 'SANCARRANCO', 'apellido_materno' => 'SANCHEZ', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92654371'],
            ['nombre' => 'GIAN FRANCO', 'apellido_paterno' => 'SOLARI', 'apellido_materno' => 'FLORES', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92331093'],
            ['nombre' => 'SAMANTA LISBETH', 'apellido_paterno' => 'TORRES', 'apellido_materno' => 'PINGO', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92320769'],
            ['nombre' => 'MÍA BEATRIZ', 'apellido_paterno' => 'VALDEZ', 'apellido_materno' => 'FUENTES', 'genero' => 'F', 'estado' => 'baja', 'curp' => '92471616'],
            ['nombre' => 'EMMA SALOMÉ', 'apellido_paterno' => 'ZAPATA', 'apellido_materno' => 'SOLANO', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92625640'],
            ['nombre' => 'ARLETTE AYNARA', 'apellido_paterno' => 'ZEVALLOS', 'apellido_materno' => 'CUNYA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92585545'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026I4A{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'inicial',
                    'carrera'           => 'INICIAL 4 AÑOS A',
                    'semestre'          => 0,
                    'fecha_nacimiento' => '2022-01-01',
                    'fecha_inscripcion' => '2026-03-01',
                ])
            );
        }
    }
}
