<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosInicialCuatroAnosBSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'ISABELLA MAURIETT', 'apellido_paterno' => 'ADRIANO', 'apellido_materno' => 'BALLESTEROS', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92485013'],
            ['nombre' => 'JOAQUÍN ALEJANDRO', 'apellido_paterno' => 'ALVARADO', 'apellido_materno' => 'HONORES', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92436341'],
            ['nombre' => 'LIAM MATEO', 'apellido_paterno' => 'AVELLANEDA', 'apellido_materno' => 'ESPINOZA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92345202'],
            ['nombre' => 'AITHANA ARLET', 'apellido_paterno' => 'CASTILLO', 'apellido_materno' => 'MENDOZA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92347385'],
            ['nombre' => 'ERICK GABRIEL', 'apellido_paterno' => 'CASTRO', 'apellido_materno' => 'LLONTOP', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92375338'],
            ['nombre' => 'CAMILA CRISTINA', 'apellido_paterno' => 'CHUNGA', 'apellido_materno' => 'ROSILLO', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92328607'],
            ['nombre' => 'LAHM EMERSON', 'apellido_paterno' => 'FARFAN', 'apellido_materno' => 'FLORES', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92411661'],
            ['nombre' => 'MIRANDA JULIETH', 'apellido_paterno' => 'FARIAS', 'apellido_materno' => 'TORRES', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92425994'],
            ['nombre' => 'MANUEL ANDRÉS', 'apellido_paterno' => 'GAMBOA', 'apellido_materno' => 'RIVERA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92350095'],
            ['nombre' => 'KATALEYA VALENTINA', 'apellido_paterno' => 'HUACHES', 'apellido_materno' => 'QUINTANA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92662623'],
            ['nombre' => 'LUAR JIREH', 'apellido_paterno' => 'MORALES', 'apellido_materno' => 'GONZALES', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92556809'],
            ['nombre' => 'KRISTELL AITANA', 'apellido_paterno' => 'NAVARRO', 'apellido_materno' => 'CAÑOLA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92536372'],
            ['nombre' => 'MARÍA DEL ROSARIO', 'apellido_paterno' => 'PULACHE', 'apellido_materno' => 'MARQUEZ', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92639697'],
            ['nombre' => 'EMIR ARMANDO', 'apellido_paterno' => 'RENGIFO', 'apellido_materno' => 'VILCHEZ', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92300666'],
            ['nombre' => 'AYSHLIN CATALEYA', 'apellido_paterno' => 'REYES', 'apellido_materno' => 'RAMOS', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92362984'],
            ['nombre' => 'DERECK ALESSANDRO', 'apellido_paterno' => 'SAAVEDRA', 'apellido_materno' => 'SANDOVAL', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92659303'],
            ['nombre' => 'LUCAS ANDRÉS', 'apellido_paterno' => 'SAAVEDRA', 'apellido_materno' => 'VIDAL', 'genero' => 'M', 'estado' => 'baja', 'curp' => '92359427'],
            ['nombre' => 'STEFFANO GAEL', 'apellido_paterno' => 'SANCHEZ', 'apellido_materno' => 'GOMEZ', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92365016'],
            ['nombre' => 'ABRAHAM MATEOS', 'apellido_paterno' => 'SANDOVAL', 'apellido_materno' => 'SILVA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92374926'],
            ['nombre' => 'EMMA JERALIN', 'apellido_paterno' => 'SEMINARIO', 'apellido_materno' => 'RODRIGUEZ', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92800820'],
            ['nombre' => 'ALESSIA SIHIEY', 'apellido_paterno' => 'TIMOTEO', 'apellido_materno' => 'GARCIA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92794731'],
            ['nombre' => 'RONALDO DAVID', 'apellido_paterno' => 'YANGUA', 'apellido_materno' => 'LEON', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92350283'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026I4B{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'inicial',
                    'carrera'           => 'INICIAL 4 AÑOS B',
                    'semestre'          => 0,
                    'fecha_nacimiento' => '2022-01-01',
                    'fecha_inscripcion' => '2026-03-01',
                ])
            );
        }
    }
}
