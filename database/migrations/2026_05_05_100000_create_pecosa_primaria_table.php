<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pecosa_primaria', function (Blueprint $table) {
            $table->id();
            $table->integer('cant');
            $table->string('unid', 20);
            $table->string('descripcion', 300);
            $table->string('marca', 150)->nullable();
            $table->decimal('presentacion', 8, 3)->default(1.000);
            $table->decimal('volumen', 12, 3)->default(0);
            $table->string('lote', 200)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pecosa_primaria');
    }
};
