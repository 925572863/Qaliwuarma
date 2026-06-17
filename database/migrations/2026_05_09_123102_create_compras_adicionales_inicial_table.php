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
        Schema::create('compras_adicionales_inicial', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion', 300);
            $table->string('unidad', 30)->default('UNIDAD');
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->decimal('precio_unitario', 10, 2)->nullable();
            $table->text('nota')->nullable();
            $table->enum('estado', ['pendiente', 'comprado'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras_adicionales_inicial');
    }
};
