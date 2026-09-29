<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuestionarioAnemia extends Model
{
    protected $table = 'cuestionarios_anemia';

    protected $fillable = [
        'alumno_id', 'tipo', 'respuestas', 'puntaje',
    ];

    protected $casts = [
        'respuestas' => 'array',
    ];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }
}
