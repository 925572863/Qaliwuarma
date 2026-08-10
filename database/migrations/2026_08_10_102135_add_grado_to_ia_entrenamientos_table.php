<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Permite entrenar modelos por grado (ej. "3 Años", "4 Años", "5 Años" en
 * inicial) además del modelo agregado por nivel. grado=NULL significa que
 * el registro corresponde al modelo general de todo el nivel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ia_entrenamientos', function (Blueprint $table) {
            $table->string('grado', 50)->nullable()->after('nivel');
        });
    }

    public function down(): void
    {
        Schema::table('ia_entrenamientos', function (Blueprint $table) {
            $table->dropColumn('grado');
        });
    }
};
