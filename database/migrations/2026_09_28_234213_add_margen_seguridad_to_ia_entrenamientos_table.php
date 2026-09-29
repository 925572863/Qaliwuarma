<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ia_entrenamientos', function (Blueprint $table) {
            $table->float('margen_seguridad_p95')->nullable()->after('folds_detalle');
            $table->unsignedInteger('muestras_entreno_inicial')->nullable()->after('margen_seguridad_p95');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ia_entrenamientos', function (Blueprint $table) {
            $table->dropColumn(['margen_seguridad_p95', 'muestras_entreno_inicial']);
        });
    }
};
