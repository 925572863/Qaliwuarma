<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluaciones_usabilidad', function (Blueprint $table) {
            $table->id();
            $table->string('evaluador');
            $table->string('cargo')->nullable();
            $table->date('fecha');
            $table->unsignedTinyInteger('p1_facilidad');
            $table->unsignedTinyInteger('p2_claridad');
            $table->unsignedTinyInteger('p3_utilidad');
            $table->unsignedTinyInteger('p4_organizacion');
            $table->unsignedTinyInteger('p5_velocidad');
            $table->decimal('promedio', 4, 2)->default(0);
            $table->text('comentario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluaciones_usabilidad');
    }
};
