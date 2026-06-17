<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockHistorial extends Model
{
    protected $table = 'stock_historial';

    protected $fillable = [
        'pecosa_inicial_id', 'descripcion_producto', 'nivel',
        'receta', 'cantidad_descontada', 'stock_anterior', 'stock_nuevo', 'unidad',
    ];

    protected $casts = [
        'cantidad_descontada' => 'decimal:3',
        'stock_anterior'      => 'decimal:2',
        'stock_nuevo'         => 'decimal:2',
    ];

    public function pecosa()
    {
        return $this->belongsTo(PecosaInicial::class, 'pecosa_inicial_id');
    }
}
