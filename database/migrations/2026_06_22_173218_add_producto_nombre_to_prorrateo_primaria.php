<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prorrateo_primaria', function (Blueprint $table) {
            $table->string('producto_nombre', 300)->nullable()->after('seccion');
            $table->string('producto_unidad', 20)->nullable()->after('producto_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('prorrateo_primaria', function (Blueprint $table) {
            $table->dropColumn(['producto_nombre', 'producto_unidad']);
        });
    }
};
