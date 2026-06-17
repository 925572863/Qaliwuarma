<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComprasAdicionalesController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->input('buscar');

        $query = DB::table('compras_adicionales_inicial')->orderBy('estado')->orderBy('descripcion');

        if ($buscar) {
            $query->where(function ($q) use ($buscar) {
                $q->where('descripcion', 'like', "%{$buscar}%")
                  ->orWhere('nota', 'like', "%{$buscar}%");
            });
        }

        $items      = $query->paginate(25)->withQueryString();
        $pendientes = DB::table('compras_adicionales_inicial')->where('estado', 'pendiente')->count();
        $comprados  = DB::table('compras_adicionales_inicial')->where('estado', 'comprado')->count();

        $totalPresupuesto = DB::table('compras_adicionales_inicial')
            ->whereNotNull('precio_unitario')
            ->selectRaw('SUM(cantidad * precio_unitario) as total')
            ->value('total') ?? 0;

        $totalPendiente = DB::table('compras_adicionales_inicial')
            ->where('estado', 'pendiente')
            ->whereNotNull('precio_unitario')
            ->selectRaw('SUM(cantidad * precio_unitario) as total')
            ->value('total') ?? 0;

        return view('pecosa.inicial.compras', compact(
            'items', 'buscar', 'pendientes', 'comprados', 'totalPresupuesto', 'totalPendiente'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'descripcion'     => 'required|string|max:300',
            'unidad'          => 'required|string|max:30',
            'cantidad'        => 'required|numeric|min:0.01',
            'precio_unitario' => 'nullable|numeric|min:0',
            'nota'            => 'nullable|string|max:500',
        ]);

        $data['descripcion'] = strtoupper($data['descripcion']);
        $data['nota']        = $data['nota'] ? strtoupper($data['nota']) : null;
        $data['estado']      = 'pendiente';

        DB::table('compras_adicionales_inicial')->insert($data + [
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('pecosa.inicial.compras')->with('success', 'Producto agregado a la lista.');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'descripcion'     => 'required|string|max:300',
            'unidad'          => 'required|string|max:30',
            'cantidad'        => 'required|numeric|min:0.01',
            'precio_unitario' => 'nullable|numeric|min:0',
            'nota'            => 'nullable|string|max:500',
        ]);

        $data['descripcion'] = strtoupper($data['descripcion']);
        $data['nota']        = $data['nota'] ? strtoupper($data['nota']) : null;
        $data['updated_at']  = now();

        DB::table('compras_adicionales_inicial')->where('id', $id)->update($data);

        return redirect()->route('pecosa.inicial.compras')->with('success', 'Producto actualizado.');
    }

    public function toggleEstado($id)
    {
        $item = DB::table('compras_adicionales_inicial')->find($id);
        abort_if(!$item, 404);

        $nuevo = $item->estado === 'pendiente' ? 'comprado' : 'pendiente';
        DB::table('compras_adicionales_inicial')->where('id', $id)->update([
            'estado'     => $nuevo,
            'updated_at' => now(),
        ]);

        return response()->json(['estado' => $nuevo]);
    }

    public function destroy($id)
    {
        DB::table('compras_adicionales_inicial')->where('id', $id)->delete();
        return redirect()->route('pecosa.inicial.compras')->with('success', 'Producto eliminado.');
    }

    public function limpiarComprados()
    {
        DB::table('compras_adicionales_inicial')->where('estado', 'comprado')->delete();
        return redirect()->route('pecosa.inicial.compras')->with('success', 'Productos comprados eliminados.');
    }
}
