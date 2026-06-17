<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroAsistencia extends Model
{
    protected $table = 'registros_asistencia';

    protected $fillable = [
        'fecha', 'nivel', 'grado', 'seccion',
        'total_alumnos', 'presentes', 'raciones', 'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
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
}
