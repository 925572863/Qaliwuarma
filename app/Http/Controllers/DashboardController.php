<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\PecosaInicial;
use App\Models\StockHistorial;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total'      => Alumno::count(),
            'activos'    => Alumno::where('estado', 'activo')->count(),
            'inactivos'  => Alumno::where('estado', 'inactivo')->count(),
            'nuevos_mes' => Alumno::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        $recientes = Alumno::latest()->take(10)->get();

        $todosPecosa           = PecosaInicial::all();
        $productosStockBajo    = $todosPecosa->filter(fn($p) => $p->stock_bajo)->values();
        $productosStockCritico = $todosPecosa->filter(fn($p) => $p->stock_critico)->values();
        $historialReciente     = StockHistorial::latest()->take(5)->get();

        return view('dashboard.index', compact(
            'stats', 'recientes',
            'productosStockBajo', 'productosStockCritico', 'historialReciente'
        ));
    }
}
