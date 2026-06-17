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
        Schema::create('prorrateo_primaria', function (Blueprint $table) {
            $table->id();
            $table->string('seccion', 20);
            $table->integer('alumnos');
            $table->unsignedBigInteger('pecosa_primaria_id');
            $table->integer('cantidad')->default(0);
            $table->timestamps();

            $table->unique(['seccion', 'pecosa_primaria_id']);
            $table->foreign('pecosa_primaria_id')
                  ->references('id')->on('pecosa_primaria')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prorrateo_primaria');
    }
};
