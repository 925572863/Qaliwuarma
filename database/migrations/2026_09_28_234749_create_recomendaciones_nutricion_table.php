<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Recomendaciones de preparacion de los productos de cada remesa,
     * validadas por un profesional en nutricion, para mostrar a los padres
     * de estudiantes con anemia (modulo de orientacion a familias).
     */
    public function up(): void
    {
        Schema::create('recomendaciones_nutricion', function (Blueprint $table) {
            $table->id();
            $table->enum('nivel', ['inicial', 'primaria']);
            $table->string('producto', 200);
            $table->text('recomendacion');
            $table->string('validado_por', 200)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recomendaciones_nutricion');
    }
};
