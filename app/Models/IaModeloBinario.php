<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Copia de respaldo en base de datos de un modelo IA entrenado (.joblib
 * codificado en base64). Ver PrediccionIAService::guardarModeloEnBD() y
 * ::restaurarModeloDesdeDB().
 */
class IaModeloBinario extends Model
{
    protected $table = 'ia_modelos_binarios';

    protected $fillable = [
        'clave',
        'nivel',
        'grado',
        'contenido',
    ];
}
