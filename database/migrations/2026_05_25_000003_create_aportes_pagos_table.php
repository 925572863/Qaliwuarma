<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aportes_pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('semana_id')->constrained('aportes_semanas')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->decimal('monto_aportado', 6, 2)->default(0);
            $table->date('fecha_pago')->nullable();
            $table->string('observaciones', 200)->nullable();
            $table->timestamps();

            $table->unique(['semana_id', 'alumno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aportes_pagos');
    }
};
