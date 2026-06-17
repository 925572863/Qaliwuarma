<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AporteConfig extends Model
{
    protected $table = 'aportes_config';

    protected $fillable = ['anio', 'grado', 'seccion', 'profesora', 'cuota_por_dia'];

    public function semanas()
    {
        return $this->hasMany(AporteSemana::class, 'config_id');
    }

    public function getNombreAulaAttribute(): string
    {
        return "{$this->grado} \"{$this->seccion}\"";
    }
}
