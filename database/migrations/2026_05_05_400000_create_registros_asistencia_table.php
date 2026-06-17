<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registros_asistencia', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->enum('nivel', ['inicial', 'primaria']);
            $table->string('grado', 30);
            $table->string('seccion', 5);
            $table->unsignedSmallInteger('total_alumnos');
            $table->unsignedSmallInteger('presentes');
            $table->unsignedSmallInteger('raciones');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['fecha', 'nivel', 'grado', 'seccion']);
            $table->index(['nivel', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registros_asistencia');
    }
};
