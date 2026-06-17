<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('receta_nutricional', function (Blueprint $table) {
            $table->id();
            $table->string('producto')->unique();
            $table->decimal('gramos_racion', 8, 2)->default(0);
            $table->decimal('calorias_racion', 8, 2)->default(0);
            $table->decimal('proteinas_racion', 8, 2)->default(0);
            $table->decimal('carbohidratos_racion', 8, 2)->default(0);
            $table->text('preparacion')->nullable();
            $table->string('tiempo_preparacion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receta_nutricional');
    }
};
