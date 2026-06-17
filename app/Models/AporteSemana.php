<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AporteSemana extends Model
{
    protected $table = 'aportes_semanas';

    protected $fillable = [
        'config_id', 'mes', 'semana_num',
        'fecha_inicio', 'fecha_fin',
        'lunes', 'martes', 'miercoles', 'jueves', 'viernes',
        'es_vacaciones',
    ];

    protected $casts = [
        'fecha_inicio'  => 'date',
        'fecha_fin'     => 'date',
        'lunes'         => 'boolean',
        'martes'        => 'boolean',
        'miercoles'     => 'boolean',
        'jueves'        => 'boolean',
        'viernes'       => 'boolean',
        'es_vacaciones' => 'boolean',
    ];

    public function config()
    {
        return $this->belongsTo(AporteConfig::class, 'config_id');
    }

    public function pagos()
    {
        return $this->hasMany(AportePago::class, 'semana_id');
    }

    public function getDiasHabilesAttribute(): int
    {
        if ($this->es_vacaciones) return 0;
        return (int) $this->lunes + $this->martes + $this->miercoles + $this->jueves + $this->viernes;
    }

    public function getCuotaSemanaAttribute(): float
    {
        return $this->dias_habiles * $this->config->cuota_por_dia;
    }
}
