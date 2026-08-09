<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ficha de registro N.° 4 (VD) — Estimación de la demanda de raciones.
 * Permite calcular la desviación RP - RC (planificadas vs consumidas).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->unsignedSmallInteger('raciones_planificadas')->nullable()->after('raciones')
                  ->comment('Raciones planificadas/preparadas para la jornada (RP), para comparar con "raciones" (RC, consumidas)');
        });
    }

    public function down(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->dropColumn('raciones_planificadas');
        });
    }
};
