<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_asistencia_id')->constrained('registros_asistencia')->cascadeOnDelete();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->boolean('presente')->default(true);
            $table->timestamps();

            $table->unique(['registro_asistencia_id', 'alumno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_asistencias');
    }
};
