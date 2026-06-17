<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aportes_semanas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('config_id')->constrained('aportes_config')->cascadeOnDelete();
            $table->unsignedTinyInteger('mes');         // 1-12
            $table->unsignedTinyInteger('semana_num');  // 1-5 (semana dentro del mes)
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->boolean('lunes')->default(true);
            $table->boolean('martes')->default(true);
            $table->boolean('miercoles')->default(true);
            $table->boolean('jueves')->default(true);
            $table->boolean('viernes')->default(true);
            $table->boolean('es_vacaciones')->default(false);
            $table->timestamps();

            $table->unique(['config_id', 'mes', 'semana_num']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aportes_semanas');
    }
};
