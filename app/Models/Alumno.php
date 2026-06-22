<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    use HasFactory;

    protected $fillable = [
        'matricula',
        'nivel',
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'fecha_nacimiento',
        'genero',
        'curp',
        'telefono',
        'email',
        'direccion',
        'carrera',
        'semestre',
        'fecha_inscripcion',
        'estado',
        'foto',
        'observaciones',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'fecha_inscripcion' => 'date',
    ];

    public function getNivelBadgeAttribute(): string
    {
        return match ($this->nivel) {
            'inicial'  => 'bg-yellow-100 text-yellow-800',
            'primaria' => 'bg-blue-100 text-blue-800',
            default    => 'bg-gray-100 text-gray-800',
        };
    }

    public function getNivelLabelAttribute(): string
    {
        return match ($this->nivel) {
            'inicial'  => 'Inicial',
            'primaria' => 'Primaria',
            default    => ucfirst($this->nivel),
        };
    }

    public function getNombreCompletoAttribute(): string
    {
        return trim("{$this->nombre} {$this->apellido_paterno} {$this->apellido_materno}");
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'activo'   => 'bg-green-100 text-green-800',
            'inactivo' => 'bg-gray-100 text-gray-800',
            'egresado' => 'bg-blue-100 text-blue-800',
            'baja'     => 'bg-red-100 text-red-800',
            default    => 'bg-gray-100 text-gray-800',
        };
    }

    public function getEstadoLabelAttribute(): string
    {
        return match ($this->estado) {
            'activo'   => 'Activo',
            'inactivo' => 'Trasladado',
            'egresado' => 'Egresado',
            'baja'     => 'Baja',
            default    => ucfirst($this->estado),
        };
    }
}
