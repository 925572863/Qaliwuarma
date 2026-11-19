<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * El histórico 2025 se importó con grados como "1.° grado", mientras que el
     * sistema registra "1°". Con dos nombres, el modelo IA trataba al mismo
     * grado como dos grados distintos.
     */
    public function up(): void
    {
        foreach (range(1, 6) as $n) {
            DB::table('registros_asistencia')
                ->where('nivel', 'primaria')
                ->where('grado', "{$n}.° grado")
                ->update(['grado' => "{$n}°"]);
        }
    }

    public function down(): void
    {
        // Solo el histórico 2025 venía con el formato largo.
        foreach (range(1, 6) as $n) {
            DB::table('registros_asistencia')
                ->where('nivel', 'primaria')
                ->where('grado', "{$n}°")
                ->where('fecha', '<', '2026-01-01')
                ->update(['grado' => "{$n}.° grado"]);
        }
    }
};
