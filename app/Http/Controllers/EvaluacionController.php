<?php

namespace App\Http\Controllers;

use App\Models\EvaluacionUsabilidad;
use Illuminate\Http\Request;

class EvaluacionController extends Controller
{
    public function index()
    {
        $evaluaciones = EvaluacionUsabilidad::orderByDesc('fecha')->orderByDesc('id')->get();

        $promedios = null;
        if ($evaluaciones->count() > 0) {
            $promedios = [
                'p1' => round($evaluaciones->avg('p1_facilidad'), 2),
                'p2' => round($evaluaciones->avg('p2_claridad'), 2),
                'p3' => round($evaluaciones->avg('p3_utilidad'), 2),
                'p4' => round($evaluaciones->avg('p4_organizacion'), 2),
                'p5' => round($evaluaciones->avg('p5_velocidad'), 2),
                'general' => round($evaluaciones->avg('promedio'), 2),
                'total' => $evaluaciones->count(),
            ];
        }

        return view('evaluacion.index', compact('evaluaciones', 'promedios'));
    }

    public function create()
    {
        return view('evaluacion.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'evaluador'      => 'required|string|max:150',
            'cargo'          => 'nullable|string|max:100',
            'fecha'          => 'required|date|before_or_equal:today',
            'p1_facilidad'   => 'required|integer|min:1|max:5',
            'p2_claridad'    => 'required|integer|min:1|max:5',
            'p3_utilidad'    => 'required|integer|min:1|max:5',
            'p4_organizacion'=> 'required|integer|min:1|max:5',
            'p5_velocidad'   => 'required|integer|min:1|max:5',
            'comentario'     => 'nullable|string|max:500',
        ]);

        $eval = new EvaluacionUsabilidad($data);
        $eval->promedio = $eval->calcularPromedio();
        $eval->save();

        return redirect()->route('evaluacion.index')
            ->with('success', 'Evaluación registrada correctamente.');
    }

    public function destroy(EvaluacionUsabilidad $evaluacion)
    {
        $evaluacion->delete();
        return back()->with('success', 'Evaluación eliminada.');
    }
}
