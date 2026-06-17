<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PecosaInicial extends Model
{
    protected $table = 'pecosa_inicial';

    protected $fillable = [
        'cant', 'unid', 'descripcion', 'marca',
        'presentacion', 'volumen', 'lote', 'stock_actual', 'fecha_vencimiento',
    ];

    protected $casts = [
        'cant'              => 'integer',
        'presentacion'      => 'decimal:3',
        'volumen'           => 'decimal:3',
        'stock_actual'      => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];

    public function getPorcentajeStockAttribute(): float
    {
        if (!$this->volumen || $this->volumen == 0) return 0;
        $stock = $this->stock_actual ?? $this->volumen;
        return round(($stock / $this->volumen) * 100, 1);
    }

    public function getStockBajoAttribute(): bool
    {
        return $this->porcentaje_stock <= 20;
    }

    public function getStockCriticoAttribute(): bool
    {
        return $this->porcentaje_stock <= 10;
    }

    public function getStockEfectivoAttribute(): float
    {
        return $this->stock_actual ?? $this->volumen ?? 0;
    }
}
