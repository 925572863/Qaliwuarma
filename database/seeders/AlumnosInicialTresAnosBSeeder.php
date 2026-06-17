<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosInicialTresAnosBSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'LISBETH KAORI', 'apellido_paterno' => 'ALVAREZ', 'apellido_materno' => 'ABAD', 'genero' => 'F', 'estado' => 'activo'],
            ['nombre' => 'SAORI DANIELA', 'apellido_paterno' => 'AQUINO', 'apellido_materno' => 'GUTIERREZ', 'genero' => 'F', 'estado' => 'activo'],
            ['nombre' => 'JARED ALEXANDER', 'apellido_paterno' => 'ARAUJO', 'apellido_materno' => 'ESCOBEDO', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'KIAN MANUEL', 'apellido_paterno' => 'BARRAGAN', 'apellido_materno' => 'PELEKAIS', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'MIA AITANA', 'apellido_paterno' => 'BAUTISTA', 'apellido_materno' => 'DIOS', 'genero' => 'F', 'estado' => 'activo'],
            ['nombre' => 'NOHAD ABDIEL', 'apellido_paterno' => 'CARREÑO', 'apellido_materno' => 'ORTIZ', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'BRIANA ELVIRA', 'apellido_paterno' => 'CASTILLO', 'apellido_materno' => 'PALOMINO', 'genero' => 'F', 'estado' => 'activo'],
            ['nombre' => 'BRIANA MILAGROS', 'apellido_paterno' => 'CRISANTO', 'apellido_materno' => 'MOGOLLON', 'genero' => 'F', 'estado' => 'activo'],
            ['nombre' => 'AILTON JOSUÉ', 'apellido_paterno' => 'FEBRES', 'apellido_materno' => 'SUYON', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'JORDAN FARID', 'apellido_paterno' => 'GUEVARA', 'apellido_materno' => 'SANCHEZ', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'DANIEL FRANCISCO', 'apellido_paterno' => 'GUTIERREZ', 'apellido_materno' => 'FEBRES', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'AMBAR KATALEYA EDELMIRA', 'apellido_paterno' => 'HUAMAN', 'apellido_materno' => 'SAAVEDRA', 'genero' => 'F', 'estado' => 'activo'],
            ['nombre' => 'LIAM ISRAEL', 'apellido_paterno' => 'JACINTO', 'apellido_materno' => 'ABAD', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'KRISTEL VALENTINA', 'apellido_paterno' => 'LUDEÑA', 'apellido_materno' => 'CARHUACHINCHAY', 'genero' => 'F', 'estado' => 'baja'],
            ['nombre' => 'THIAGO JESUS', 'apellido_paterno' => 'MACHADO', 'apellido_materno' => 'CHUNGA', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'ALEXANDER JUNIOR', 'apellido_paterno' => 'MANCHAY', 'apellido_materno' => 'MARTINEZ', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'EITHAN ABDIEL', 'apellido_paterno' => 'MENDOZA', 'apellido_materno' => 'CALDERON', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'IVANA GISSEL', 'apellido_paterno' => 'MONTERO', 'apellido_materno' => 'GIRON', 'genero' => 'F', 'estado' => 'activo'],
            ['nombre' => 'RICHARD JAVIER', 'apellido_paterno' => 'MORALES', 'apellido_materno' => 'DIAZ', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'ALONDRA ALEXANDRA', 'apellido_paterno' => 'ORDINOLA', 'apellido_materno' => 'PUELLES', 'genero' => 'F', 'estado' => 'activo'],
            ['nombre' => 'RACHELL SOFÍA', 'apellido_paterno' => 'PANTA', 'apellido_materno' => 'FLORES', 'genero' => 'F', 'estado' => 'activo'],
            ['nombre' => 'LIONEL ELÍAS', 'apellido_paterno' => 'QUEVEDO', 'apellido_materno' => 'GONZALES', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'JHAN OMAR', 'apellido_paterno' => 'RAMIREZ', 'apellido_materno' => 'NORIEGA', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'LIAM', 'apellido_paterno' => 'SANTOS', 'apellido_materno' => 'ZEGARRA', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'SALVADOR EZEQUIEL', 'apellido_paterno' => 'SILVA', 'apellido_materno' => 'PALACIOS', 'genero' => 'M', 'estado' => 'activo'],
            ['nombre' => 'MIRELLA ALEXANDRA', 'apellido_paterno' => 'TOCTO', 'apellido_materno' => 'ZAPATA', 'genero' => 'F', 'estado' => 'activo'],
            ['nombre' => 'DAGNY JANDY', 'apellido_paterno' => 'VIERA', 'apellido_materno' => 'MENDOZA', 'genero' => 'F', 'estado' => 'activo'],
            ['nombre' => 'GABRIEL ALEXANDER', 'apellido_paterno' => 'YOVERA', 'apellido_materno' => 'GARCIA', 'genero' => 'M', 'estado' => 'activo'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026I3B{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'inicial',
                    'carrera'           => 'INICIAL 3 AÑOS B',
                    'semestre'          => 0,
                    'fecha_nacimiento' => '2023-01-01',
                    'fecha_inscripcion' => '2026-03-01',
                ])
            );
        }
    }
}
