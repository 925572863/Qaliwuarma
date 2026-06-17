<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aportes_config', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('anio');
            $table->string('grado', 20);     // '3 Años', '4 Años', '5 Años'
            $table->string('seccion', 5);    // 'A', 'B', 'C'...
            $table->string('profesora', 150)->nullable();
            $table->decimal('cuota_por_dia', 5, 2)->default(0.80);
            $table->timestamps();

            $table->unique(['anio', 'grado', 'seccion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aportes_config');
    }
};
