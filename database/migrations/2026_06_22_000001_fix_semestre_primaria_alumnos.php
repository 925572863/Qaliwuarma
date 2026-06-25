<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Corrige alumnos de primaria donde semestre=1 pero carrera contiene el grado
        // Ej: carrera="2° A" semestre=1 → semestre=2
        $alumnos = DB::table('alumnos')
            ->where('nivel', 'primaria')
            ->get(['id', 'carrera', 'semestre']);

        foreach ($alumnos as $alumno) {
            if (preg_match('/^(\d+)/', $alumno->carrera, $m)) {
                $grado = (int) $m[1];
                if ($grado !== (int) $alumno->semestre) {
                    DB::table('alumnos')
                        ->where('id', $alumno->id)
                        ->update(['semestre' => $grado]);
                }
            }
        }
    }

    public function down(): void {}
};
