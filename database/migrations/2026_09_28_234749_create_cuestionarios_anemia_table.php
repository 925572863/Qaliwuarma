<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Resultados del cuestionario de conocimientos sobre preparacion de
     * alimentos del programa, aplicado a padres de estudiantes con anemia
     * antes (pretest) y despues (postest) de usar el modulo de orientacion.
     */
    public function up(): void
    {
        Schema::create('cuestionarios_anemia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->enum('tipo', ['pretest', 'postest']);
            $table->json('respuestas');
            $table->decimal('puntaje', 4, 1); // escala 0 a 20
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuestionarios_anemia');
    }
};
