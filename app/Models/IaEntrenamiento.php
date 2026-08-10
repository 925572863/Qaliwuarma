<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registro histórico de cada entrenamiento del modelo Random Forest.
 * Cubre las fichas 1 (preprocesamiento), 2 (entrenamiento/validación)
 * y 3 (arquitectura) de la tesis.
 */
class IaEntrenamiento extends Model
{
    protected $table = 'ia_entrenamientos';

    protected $fillable = [
        'nivel', 'grado', 'fase',
        'registros_totales', 'registros_depurados', 'pct_depurados', 'pct_completos',
        'k_folds', 'mae', 'rmse', 'mape', 'r2', 'folds_detalle',
        'n_estimators', 'max_depth', 'tiempo_entrenamiento_seg',
    ];

    protected $casts = [
        'folds_detalle' => 'array',
    ];
}
