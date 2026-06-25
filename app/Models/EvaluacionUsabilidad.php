<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluacionUsabilidad extends Model
{
    protected $table = 'evaluaciones_usabilidad';

    protected $fillable = [
        'evaluador', 'cargo', 'fecha',
        'p1_facilidad', 'p2_claridad', 'p3_utilidad', 'p4_organizacion', 'p5_velocidad',
        'promedio', 'comentario',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function calcularPromedio(): float
    {
        return round(($this->p1_facilidad + $this->p2_claridad + $this->p3_utilidad + $this->p4_organizacion + $this->p5_velocidad) / 5, 2);
    }
}
