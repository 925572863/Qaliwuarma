<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ia_analisis', function (Blueprint $table) {
            $table->id();
            $table->string('nivel', 20);
            $table->text('analisis');
            $table->string('receta', 1000)->nullable();
            $table->integer('dias_historico')->default(0);
            $table->string('ultimo_registro')->nullable();
            $table->timestamps();

            $table->index('nivel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia_analisis');
    }
};
