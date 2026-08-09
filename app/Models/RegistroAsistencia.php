<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroAsistencia extends Model
{
    protected $table = 'registros_asistencia';

    protected $fillable = [
        'fecha', 'nivel', 'fase', 'grado', 'seccion',
        'total_alumnos', 'presentes', 'raciones', 'raciones_planificadas', 'observaciones',
        'condicion_climatica', 'evento_especial',
    ];

    protected $casts = [
        'fecha' => 'date',
        'evento_especial' => 'boolean',
    ];

    public function getAusentesAttribute(): int
    {
        return max(0, $this->total_alumnos - $this->presentes);
    }

    public function getPorcentajeAsistenciaAttribute(): float
    {
        if ($this->total_alumnos === 0) return 0;
        return round(($this->presentes / $this->total_alumnos) * 100, 1);
    }

    /**
     * Ficha 4 (VD): Desv = RP - RC (raciones planificadas - raciones consumidas).
     * Null si no se registró el dato de planificación para esta jornada.
     */
    public function getDesviacionRacionesAttribute(): ?int
    {
        if ($this->raciones_planificadas === null) return null;
        return $this->raciones_planificadas - $this->raciones;
    }
}
