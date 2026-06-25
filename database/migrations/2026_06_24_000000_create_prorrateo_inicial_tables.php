<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prorrateo_inicial_versiones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->integer('total_alumnos')->default(0);
            $table->integer('total_unidades')->default(0);
            $table->timestamps();
        });

        Schema::create('prorrateo_inicial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('version_id')->constrained('prorrateo_inicial_versiones')->cascadeOnDelete();
            $table->string('seccion');
            $table->integer('alumnos')->default(0);
            $table->foreignId('pecosa_inicial_id')->nullable()->constrained('pecosa_inicial')->cascadeOnDelete();
            $table->string('producto_nombre')->nullable();
            $table->string('producto_unidad')->nullable();
            $table->integer('cantidad')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prorrateo_inicial');
        Schema::dropIfExists('prorrateo_inicial_versiones');
    }
};
