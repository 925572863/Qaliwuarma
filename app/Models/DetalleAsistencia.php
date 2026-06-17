<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleAsistencia extends Model
{
    protected $table = 'detalle_asistencias';

    protected $fillable = ['registro_asistencia_id', 'alumno_id', 'presente'];

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }

    public function registro()
    {
        return $this->belongsTo(RegistroAsistencia::class, 'registro_asistencia_id');
    }
}
