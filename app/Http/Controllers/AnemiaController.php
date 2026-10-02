<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoAnemia;
use App\Models\ControlRacionAnemia;
use App\Models\CuestionarioAnemia;
use App\Models\RecomendacionNutricion;
use Illuminate\Http\Request;

class AnemiaController extends Controller
{
    public function index(Request $request)
    {
        $hoy = now()->toDateString();
        $inicioMes = now()->startOfMonth()->toDateString();

        $casos = AlumnoAnemia::with(['alumno', 'controlesRacion' => function ($q) use ($inicioMes) {
                $q->where('fecha', '>=', $inicioMes);
            }])
            ->where('activo', true)
            ->orderByDesc('fecha_tamizaje')
            ->get()
            ->map(function ($caso) use ($hoy) {
                $controlHoy = $caso->controlesRacion->first(fn($c) => $c->fecha->toDateString() === $hoy);
                $caso->recibio_hoy = $controlHoy?->recibio_racion;
                $caso->dias_marcados_mes = $caso->controlesRacion->count();
                $caso->dias_recibidos_mes = $caso->controlesRacion->where('recibio_racion', true)->count();
                return $caso;
            });

        $recomendaciones = RecomendacionNutricion::orderBy('nivel')->orderBy('producto')->get();

        return view('anemia.index', compact('casos', 'recomendaciones'));
    }

    // ── Control diario de ración (responsabilidad del CAE) ─────────────────

    public function marcarRacion(Request $request, AlumnoAnemia $caso)
    {
        $recibio = $request->boolean('recibio_racion', true);

        ControlRacionAnemia::updateOrCreate(
            ['alumno_anemia_id' => $caso->id, 'fecha' => now()->toDateString()],
            ['recibio_racion' => $recibio, 'user_id' => auth()->id()]
        );

        return back()->with('success', 'Control de ración actualizado.');
    }

    public function historialRacion(AlumnoAnemia $caso)
    {
        $controles = $caso->controlesRacion()->orderByDesc('fecha')->limit(90)->get();

        return view('anemia.historial-racion', compact('caso', 'controles'));
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
