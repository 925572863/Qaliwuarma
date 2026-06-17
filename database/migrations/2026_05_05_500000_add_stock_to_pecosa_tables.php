<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pecosa_inicial', function (Blueprint $table) {
            $table->decimal('stock_actual', 10, 2)->nullable()->after('volumen')
                  ->comment('Unidades restantes del producto');
        });

        Schema::table('pecosa_primaria', function (Blueprint $table) {
            $table->decimal('stock_actual', 10, 2)->nullable()->after('volumen')
                  ->comment('Unidades restantes del producto');
        });
    }

    public function down(): void
    {
        Schema::table('pecosa_inicial', function (Blueprint $table) {
            $table->dropColumn('stock_actual');
        });
        Schema::table('pecosa_primaria', function (Blueprint $table) {
            $table->dropColumn('stock_actual');
        });
    }
};
