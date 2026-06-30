<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AportePago extends Model
{
    protected $table = 'aportes_pagos';

    protected $fillable = ['semana_id', 'alumno_id', 'monto_aportado', 'fecha_pago', 'observaciones', 'firmado'];

    protected $casts = [
        'fecha_pago'     => 'date',
        'monto_aportado' => 'decimal:2',
        'firmado'        => 'boolean',
    ];

    public function semana()
    {
        return $this->belongsTo(AporteSemana::class, 'semana_id');
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class, 'alumno_id');
    }
}
