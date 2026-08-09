<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fichas de registro N.° 1, 2 y 3 (VI) — Preprocesamiento, entrenamiento/validación
 * y arquitectura del modelo Random Forest. Cada corrida de entrenamiento genera
 * una fila con las tres dimensiones de la variable independiente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ia_entrenamientos', function (Blueprint $table) {
            $table->id();
            $table->string('nivel', 20);

            // Ficha 1 — Preprocesamiento de datos
            $table->unsignedInteger('registros_totales');
            $table->unsignedInteger('registros_depurados');
            $table->decimal('pct_depurados', 5, 2);
            $table->decimal('pct_completos', 5, 2);

            // Ficha 2 — Entrenamiento y validación (agregado + detalle por fold)
            $table->unsignedTinyInteger('k_folds');
            $table->decimal('mae', 10, 3)->nullable();
            $table->decimal('rmse', 10, 3)->nullable();
            $table->decimal('mape', 10, 3)->nullable();
            $table->decimal('r2', 6, 4)->nullable();
            $table->json('folds_detalle')->nullable()
                  ->comment('Array [{fold, mae, rmse, r2}] por cada pliegue de la validación cruzada');

            // Ficha 3 — Arquitectura del modelo
            $table->unsignedSmallInteger('n_estimators');
            $table->unsignedTinyInteger('max_depth');
            $table->decimal('tiempo_entrenamiento_seg', 8, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia_entrenamientos');
    }
};
