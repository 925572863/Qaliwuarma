<?php

namespace Database\Seeders;

use App\Models\Alumno;
use Illuminate\Database\Seeder;

class AlumnosInicialCuatroAnosDSeeder extends Seeder
{
    public function run(): void
    {
        $alumnos = [
            ['nombre' => 'GUILLERMO MAURICIO', 'apellido_paterno' => 'AMACHI', 'apellido_materno' => 'VILLANUEVA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92356448'],
            ['nombre' => 'HENRY JOEL', 'apellido_paterno' => 'AQUINO', 'apellido_materno' => 'VALLE', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92783248'],
            ['nombre' => 'THIAGO ALEXANDER', 'apellido_paterno' => 'ARCIA', 'apellido_materno' => 'MORILLO', 'genero' => 'M', 'estado' => 'activo', 'curp' => null],
            ['nombre' => 'MAELY THAIS', 'apellido_paterno' => 'CASTILLO', 'apellido_materno' => 'PERALTA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92324168'],
            ['nombre' => 'ALESSIA VICTORIA', 'apellido_paterno' => 'CASTRO', 'apellido_materno' => 'JIMENEZ', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92682549'],
            ['nombre' => 'LENNY SANTIAGO', 'apellido_paterno' => 'CHIRA', 'apellido_materno' => 'SOSA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92429606'],
            ['nombre' => 'YONIANGELIS LUCIANA', 'apellido_paterno' => 'COROPA', 'apellido_materno' => 'ARCIA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92635451'],
            ['nombre' => 'ISMAEL BENJAMIN', 'apellido_paterno' => 'CRUZ', 'apellido_materno' => 'CHANDUVI', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92667593'],
            ['nombre' => 'DYLAN ADRIAN', 'apellido_paterno' => 'DUARTE', 'apellido_materno' => 'PIZARRO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92653690'],
            ['nombre' => 'ADHARA YAMILETH', 'apellido_paterno' => 'GARCIA', 'apellido_materno' => 'GARCIA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92561478'],
            ['nombre' => 'THIAGO VALENTINO', 'apellido_paterno' => 'GIRON', 'apellido_materno' => 'SANTA MARIA', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92395344'],
            ['nombre' => 'DOMENICA VALENTINA', 'apellido_paterno' => 'LEON', 'apellido_materno' => 'ZAPATA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92653950'],
            ['nombre' => 'ADRIHEL EDUARDO', 'apellido_paterno' => 'ORDINOLA', 'apellido_materno' => 'CASTILLO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92637843'],
            ['nombre' => 'EMMA MARICEL', 'apellido_paterno' => 'PANTA', 'apellido_materno' => 'MIJA', 'genero' => 'F', 'estado' => 'activo', 'curp' => '92790880'],
            ['nombre' => 'SEGUNDO GILMER', 'apellido_paterno' => 'RIOS', 'apellido_materno' => 'MOROCHO', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92672869'],
            ['nombre' => 'WUIYERSON DARKIEL', 'apellido_paterno' => 'VELAZCO', 'apellido_materno' => 'MENESES', 'genero' => 'M', 'estado' => 'activo', 'curp' => '92589319'],
        ];

        foreach ($alumnos as $i => $alumno) {
            $num = str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            Alumno::firstOrCreate(
                ['matricula' => "2026I4D{$num}"],
                array_merge($alumno, [
                    'nivel'             => 'inicial',
                    'carrera'           => 'INICIAL 4 AÑOS D',
                    'semestre'          => 0,
                    'fecha_nacimiento' => '2022-01-01',
                    'fecha_inscripcion' => '2026-03-01',
                ])
            );
        }
    }
}
