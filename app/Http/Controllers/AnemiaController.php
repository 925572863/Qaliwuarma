<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoAnemia;
use App\Models\CuestionarioAnemia;
use App\Models\RecomendacionNutricion;
use Illuminate\Http\Request;

class AnemiaController extends Controller
{
    public function index(Request $request)
    {
        $casos = AlumnoAnemia::with('alumno')
            ->where('activo', true)
            ->orderByDesc('fecha_tamizaje')
            ->get();

        $recomendaciones = RecomendacionNutricion::orderBy('nivel')->orderBy('producto')->get();

        return view('anemia.index', compact('casos', 'recomendaciones'));
    }

    public function buscarAlumno(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        if ($q === '') {
            return response()->json([]);
        }

        $alumnos = Alumno::where('estado', 'activo')
            ->where(function ($query) use ($q) {
                $query->where('matricula', 'like', "%{$q}%")
                    ->orWhere('nombre', 'like', "%{$q}%")
                    ->orWhere('apellido_paterno', 'like', "%{$q}%");
            })
            ->limit(10)
            ->get(['id', 'matricula', 'nombre', 'apellido_paterno', 'apellido_materno', 'carrera', 'nivel']);

        return response()->json($alumnos);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'alumno_id'             => 'required|exists:alumnos,id|unique:alumnos_anemia,alumno_id',
            'fecha_tamizaje'        => 'required|date',
            'establecimiento_salud' => 'nullable|string|max:200',
            'observaciones'         => 'nullable|string|max:1000',
        ]);
        $data['user_id'] = auth()->id();

        AlumnoAnemia::create($data);

        return redirect()->route('anemia.index')->with('success', 'Estudiante registrado en el módulo de orientación.');
    }

    public function destroy(AlumnoAnemia $caso)
    {
        $caso->delete();
        return redirect()->route('anemia.index')->with('success', 'Registro eliminado.');
    }

    // ── Recomendaciones de preparación (validadas por nutricionista) ──────

    public function storeRecomendacion(Request $request)
    {
        $data = $request->validate([
            'nivel'         => 'required|in:inicial,primaria',
            'producto'      => 'required|string|max:200',
            'recomendacion' => 'required|string|max:2000',
            'validado_por'  => 'nullable|string|max:200',
        ]);

        RecomendacionNutricion::create($data);

        return redirect()->route('anemia.index')->with('success', 'Recomendación guardada.');
    }

    public function destroyRecomendacion(RecomendacionNutricion $recomendacion)
    {
        $recomendacion->delete();
        return redirect()->route('anemia.index')->with('success', 'Recomendación eliminada.');
    }

    // ── Resultados de los cuestionarios (pretest/postest) ──────────────────

    public function resultados()
    {
        $resultados = CuestionarioAnemia::with('alumno')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('alumno_id');

        return view('anemia.resultados', compact('resultados'));
    }
}
