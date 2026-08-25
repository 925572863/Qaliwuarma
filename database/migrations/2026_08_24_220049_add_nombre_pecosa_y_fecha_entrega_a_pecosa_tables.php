<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cada Pecosa que se sube (Excel o foto) ahora puede guardarse identificada
 * por su propio nombre (ej. "Pecosa N° 324426" o "Pecosa 1") y la fecha en
 * que se entregó el producto, en vez de mezclarse todo en una sola lista.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pecosa_inicial', function (Blueprint $table) {
            $table->string('nombre_pecosa')->nullable()->after('id');
            $table->date('fecha_entrega')->nullable()->after('nombre_pecosa');
        });

        Schema::table('pecosa_primaria', function (Blueprint $table) {
            $table->string('nombre_pecosa')->nullable()->after('id');
            $table->date('fecha_entrega')->nullable()->after('nombre_pecosa');
        });
    }

    public function down(): void
    {
        Schema::table('pecosa_inicial', function (Blueprint $table) {
            $table->dropColumn(['nombre_pecosa', 'fecha_entrega']);
        });

        Schema::table('pecosa_primaria', function (Blueprint $table) {
            $table->dropColumn(['nombre_pecosa', 'fecha_entrega']);
        });
    }
};
