<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumnoAnemia extends Model
{
    protected $table = 'alumnos_anemia';

    protected $fillable = [
        'alumno_id', 'fecha_tamizaje', 'establecimiento_salud',
        'observaciones', 'activo', 'user_id',
    ];

    protected $casts = [
        'fecha_tamizaje' => 'date',
        'activo' => 'boolean',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }
}
