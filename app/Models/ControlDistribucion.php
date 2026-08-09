<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Ficha de registro N.° 6 (VD) — Eficiencia en la distribución y control del desperdicio.
 */
class ControlDistribucion extends Model
{
    protected $table = 'controles_distribucion';

    protected $fillable = [
        'fecha', 'nivel', 'fase', 'kg_desperdiciados', 'kg_distribuidos', 'tiempo_distribucion_min',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function getIndiceMermasAttribute(): float
    {
        if ((float) $this->kg_distribuidos === 0.0) {
            return 0.0;
        }
        return round(($this->kg_desperdiciados / $this->kg_distribuidos) * 100, 2);
    }
}
