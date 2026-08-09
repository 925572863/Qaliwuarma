<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ficha de registro N.° 5 (VD) — Precisión en el cálculo de raciones nutricionales.
 */
class ControlNutricional extends Model
{
    protected $table = 'controles_nutricionales';

    protected $fillable = [
        'fecha', 'nivel', 'fase', 'menu_dia',
        'gramos_planificados', 'gramos_servidos', 'cumple_requerimiento',
    ];

    protected $casts = [
        'fecha' => 'date',
        'cumple_requerimiento' => 'boolean',
    ];

    public function getDiferenciaAttribute(): float
    {
        return round($this->gramos_planificados - $this->gramos_servidos, 2);
    }
}
