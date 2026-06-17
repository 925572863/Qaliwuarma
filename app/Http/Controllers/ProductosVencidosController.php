<?php

namespace App\Http\Controllers;

use App\Models\PecosaInicial;
use App\Models\PecosaPrimaria;
use Illuminate\Support\Carbon;

class ProductosVencidosController extends Controller
{
    public function index()
    {
        $hoy      = Carbon::today();
        $limite   = Carbon::today()->addDays(30);

        $inicial  = PecosaInicial::whereNotNull('fecha_vencimiento')->orderBy('fecha_vencimiento')->get();
        $primaria = PecosaPrimaria::whereNotNull('fecha_vencimiento')->orderBy('fecha_vencimiento')->get();

        $todos = collect();

        foreach ($inicial as $item) {
            $todos->push([
                'id'                => $item->id,
                'nivel'             => 'Inicial',
                'descripcion'       => $item->descripcion,
                'marca'             => $item->marca,
                'lote'              => $item->lote,
                'cant'              => $item->cant,
                'unid'              => $item->unid,
                'fecha_vencimiento' => $item->fecha_vencimiento,
                'edit_route'        => route('pecosa.inicial.edit', $item),
                'estado'            => $this->estado($item->fecha_vencimiento, $hoy, $limite),
            ]);
        }

        foreach ($primaria as $item) {
            $todos->push([
                'id'                => $item->id,
                'nivel'             => 'Primaria',
                'descripcion'       => $item->descripcion,
                'marca'             => $item->marca,
                'lote'              => $item->lote,
                'cant'              => $item->cant,
                'unid'              => $item->unid,
                'fecha_vencimiento' => $item->fecha_vencimiento,
                'edit_route'        => route('pecosa.primaria.edit', $item),
                'estado'            => $this->estado($item->fecha_vencimiento, $hoy, $limite),
            ]);
        }

        $todos = $todos->sortBy('fecha_vencimiento')->values();

        $vencidos     = $todos->where('estado', 'vencido');
        $porVencer    = $todos->where('estado', 'por_vencer');
        $vigentes     = $todos->where('estado', 'vigente');

        return view('vencidos.index', compact('vencidos', 'porVencer', 'vigentes', 'hoy'));
    }

    public function reporte()
    {
        $hoy      = Carbon::today();
        $limite   = Carbon::today()->addDays(30);

        $inicial  = PecosaInicial::whereNotNull('fecha_vencimiento')->orderBy('fecha_vencimiento')->get();
        $primaria = PecosaPrimaria::whereNotNull('fecha_vencimiento')->orderBy('fecha_vencimiento')->get();

        $todos = collect();

        foreach ($inicial as $item) {
            $todos->push([
                'nivel'             => 'Inicial',
                'descripcion'       => $item->descripcion,
                'marca'             => $item->marca,
                'lote'              => $item->lote,
                'cant'              => $item->cant,
                'unid'              => $item->unid,
                'fecha_vencimiento' => $item->fecha_vencimiento,
                'estado'            => $this->estado($item->fecha_vencimiento, $hoy, $limite),
            ]);
        }

        foreach ($primaria as $item) {
            $todos->push([
                'nivel'             => 'Primaria',
                'descripcion'       => $item->descripcion,
                'marca'             => $item->marca,
                'lote'              => $item->lote,
                'cant'              => $item->cant,
                'unid'              => $item->unid,
                'fecha_vencimiento' => $item->fecha_vencimiento,
                'estado'            => $this->estado($item->fecha_vencimiento, $hoy, $limite),
            ]);
        }

        $todos = $todos->sortBy('fecha_vencimiento')->values();

        $vencidos  = $todos->where('estado', 'vencido');
        $porVencer = $todos->where('estado', 'por_vencer');
        $vigentes  = $todos->where('estado', 'vigente');

        return view('vencidos.reporte', compact('vencidos', 'porVencer', 'vigentes', 'hoy'));
    }

    private function estado(Carbon $fecha, Carbon $hoy, Carbon $limite): string
    {
        if ($fecha->lt($hoy))       return 'vencido';
        if ($fecha->lte($limite))   return 'por_vencer';
        return 'vigente';
    }
}
