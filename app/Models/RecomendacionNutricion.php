<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecomendacionNutricion extends Model
{
    protected $table = 'recomendaciones_nutricion';

    protected $fillable = [
        'nivel', 'producto', 'recomendacion', 'validado_por', 'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];
}
