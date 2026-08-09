<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Añade:
 * 1) La marca de fase (pretest/postest) requerida por el diseño preexperimental
 *    de la tesis, a las 4 tablas que registran indicadores medibles.
 * 2) Variables de contexto mencionadas en el marco teórico (condición climática
 *    y evento especial del calendario) como features opcionales para el modelo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->enum('fase', ['pretest', 'postest'])->default('pretest')->after('nivel');
            $table->enum('condicion_climatica', ['soleado', 'nublado', 'lluvioso'])->nullable()->after('observaciones');
            $table->boolean('evento_especial')->default(false)->after('condicion_climatica')
                  ->comment('Feriado cercano, actividad escolar u otro evento no recurrente que afecta la asistencia');
        });

        Schema::table('controles_nutricionales', function (Blueprint $table) {
            $table->enum('fase', ['pretest', 'postest'])->default('pretest')->after('nivel');
        });

        Schema::table('controles_distribucion', function (Blueprint $table) {
            $table->enum('fase', ['pretest', 'postest'])->default('pretest')->after('nivel');
        });

        Schema::table('ia_entrenamientos', function (Blueprint $table) {
            $table->enum('fase', ['pretest', 'postest'])->default('postest')->after('nivel')
                  ->comment('El modelo en sí solo existe en la fase postest (es la intervención)');
        });
    }

    public function down(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->dropColumn(['fase', 'condicion_climatica', 'evento_especial']);
        });
        Schema::table('controles_nutricionales', function (Blueprint $table) {
            $table->dropColumn('fase');
        });
        Schema::table('controles_distribucion', function (Blueprint $table) {
            $table->dropColumn('fase');
        });
        Schema::table('ia_entrenamientos', function (Blueprint $table) {
            $table->dropColumn('fase');
        });
    }
};
