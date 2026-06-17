<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prorrateo_versiones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->nullable();
            $table->integer('total_alumnos')->default(0);
            $table->integer('total_unidades')->default(0);
            $table->timestamps();
        });

        Schema::table('prorrateo_primaria', function (Blueprint $table) {
            $table->dropUnique(['seccion', 'pecosa_primaria_id']);
            $table->unsignedBigInteger('version_id')->nullable()->after('id');
            $table->foreign('version_id')
                  ->references('id')->on('prorrateo_versiones')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('prorrateo_primaria', function (Blueprint $table) {
            $table->dropForeign(['version_id']);
            $table->dropColumn('version_id');
        });
        Schema::dropIfExists('prorrateo_versiones');
    }
};
