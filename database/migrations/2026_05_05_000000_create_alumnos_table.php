<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumnos', function (Blueprint $table) {
            $table->id();
            $table->string('matricula', 20)->unique();
            $table->string('nombre', 100);
            $table->string('apellido_paterno', 100);
            $table->string('apellido_materno', 100)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('genero', ['M', 'F', 'Otro'])->nullable();
            $table->string('curp', 18)->nullable()->unique();
            $table->string('telefono', 15)->nullable();
            $table->string('email')->nullable();
            $table->text('direccion')->nullable();
            $table->string('carrera', 200);
            $table->unsignedTinyInteger('semestre')->default(1);
            $table->date('fecha_inscripcion');
            $table->enum('estado', ['activo', 'inactivo', 'egresado', 'baja'])->default('activo');
            $table->string('foto')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumnos');
    }
};
