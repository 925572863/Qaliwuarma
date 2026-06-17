<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 2. Añadir User ID a tablas de gestión para auditoría
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('set null');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('set null');
        });

        Schema::table('pecosa_inicial', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('set null');
        });

        Schema::table('pecosa_primaria', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('set null');
        });

        // 3. Crear tabla de detalle de asistencia para relacionar Alumnos con el Registro General
        // Esto permite saber EXACTAMENTE qué alumno asistió y cuál no
        Schema::create('asistencia_alumnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registro_asistencia_id')->constrained('registros_asistencia')->onDelete('cascade');
            $table->foreignId('alumno_id')->constrained('alumnos')->onDelete('cascade');
            $table->enum('estado', ['presente', 'falta', 'justificado'])->default('presente');
            $table->timestamps();

            $table->unique(['registro_asistencia_id', 'alumno_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencia_alumnos');

        Schema::table('pecosa_primaria', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('pecosa_inicial', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
