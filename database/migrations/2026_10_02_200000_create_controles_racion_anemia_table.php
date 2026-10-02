<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Control diario de que un alumno registrado con anemia sí recibió
     * su ración ese día (responsabilidad del CAE: dar la alimentación,
     * no el diagnóstico clínico que corresponde al centro de salud).
     */
    public function up(): void
    {
        Schema::create('controles_racion_anemia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_anemia_id')->constrained('alumnos_anemia')->cascadeOnDelete();
            $table->date('fecha');
            $table->boolean('recibio_racion')->default(true);
            $table->string('observacion', 300)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->unique(['alumno_anemia_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('controles_racion_anemia');
    }
};
