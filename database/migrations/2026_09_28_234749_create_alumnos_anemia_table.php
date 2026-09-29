<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro de estudiantes identificados con anemia por el
     * establecimiento de salud, para el módulo de orientación a familias.
     */
    public function up(): void
    {
        Schema::create('alumnos_anemia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->date('fecha_tamizaje');
            $table->string('establecimiento_salud', 200)->nullable();
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique('alumno_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos_anemia');
    }
};
