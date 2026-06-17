<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecetaNutricional extends Model
{
    protected $table = 'receta_nutricional';

    protected $fillable = [
        'producto',
        'gramos_racion',
        'calorias_racion',
        'proteinas_racion',
        'carbohidratos_racion',
        'preparacion',
        'tiempo_preparacion',
    ];
}
