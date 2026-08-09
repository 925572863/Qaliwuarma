<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ficha de registro N.° 6 (VD) — Eficiencia en la distribución y control del desperdicio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controles_distribucion', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->enum('nivel', ['inicial', 'primaria']);
            $table->decimal('kg_desperdiciados', 8, 2);
            $table->decimal('kg_distribuidos', 8, 2);
            $table->unsignedSmallInteger('tiempo_distribucion_min');
            $table->timestamps();

            $table->index(['nivel', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controles_distribucion');
    }
};
