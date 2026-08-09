<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ficha de registro N.° 5 (VD) — Precisión en el cálculo de raciones nutricionales.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controles_nutricionales', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->enum('nivel', ['inicial', 'primaria']);
            $table->string('menu_dia', 150);
            $table->decimal('gramos_planificados', 8, 2);
            $table->decimal('gramos_servidos', 8, 2);
            $table->boolean('cumple_requerimiento')->default(false);
            $table->timestamps();

            $table->index(['nivel', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controles_nutricionales');
    }
};
