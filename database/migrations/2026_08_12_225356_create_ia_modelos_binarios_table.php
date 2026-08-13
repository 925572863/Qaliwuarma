<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Copia de respaldo, en base de datos, de los modelos IA entrenados
 * (archivos .joblib). Necesaria porque el filesystem de los planes free de
 * hosting (ej. Render) no es persistente: se pierde en cada deploy. La base
 * de datos sí persiste, así que al entrenar guardamos aquí una copia, y al
 * predecir/consultar si existe el modelo, la restauramos a disco si hace
 * falta. Ver App\Services\PrediccionIAService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ia_modelos_binarios', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique(); // nivel + slug del grado, ej. "inicial" o "inicial:3-anos"
            $table->string('nivel');
            $table->string('grado')->nullable();
            $table->longText('contenido'); // .joblib codificado en base64
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ia_modelos_binarios');
    }
};
