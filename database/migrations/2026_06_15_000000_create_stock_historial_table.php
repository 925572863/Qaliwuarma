<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_historial', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pecosa_inicial_id')->constrained('pecosa_inicial')->onDelete('cascade');
            $table->string('descripcion_producto');
            $table->string('nivel', 20)->default('inicial');
            $table->string('receta')->nullable();
            $table->decimal('cantidad_descontada', 10, 3);
            $table->decimal('stock_anterior', 10, 2);
            $table->decimal('stock_nuevo', 10, 2);
            $table->string('unidad', 20)->default('kg');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_historial');
    }
};
