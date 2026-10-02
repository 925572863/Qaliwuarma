<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ControlRacionAnemia extends Model
{
    protected $table = 'controles_racion_anemia';

    protected $fillable = [
        'alumno_anemia_id', 'fecha', 'recibio_racion', 'observacion', 'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'recibio_racion' => 'boolean',
    ];

    public function alumnoAnemia()
    {
        return $this->belongsTo(AlumnoAnemia::class);
    }
}
