<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\AlumnoAnemia;
use App\Models\CuestionarioAnemia;
use App\Models\RecomendacionNutricion;
use Illuminate\Http\Request;

/**
 * Módulo de orientación a familias de estudiantes con anemia (sin sesión,
 * pensado para que el padre/madre acceda desde un enlace con la matrícula
 * de su hijo/a, sin necesidad de credenciales del sistema interno).
 */
class OrientacionPadresController extends Controller
{
    public function show(string $matricula)
    {
        $alumno = Alumno::where('matricula', $matricula)->firstOrFail();
        $caso = AlumnoAnemia::where('alumno_id', $alumno->id)->where('activo', true)->first();

        abort_if(!$caso, 404, 'Este estudiante no está registrado en el módulo de orientación.');

        $recomendaciones = RecomendacionNutricion::where('nivel', $alumno->nivel)
            ->where('activo', true)
            ->orderBy('producto')
            ->get();

        $pretest = CuestionarioAnemia::where('alumno_id', $alumno->id)->where('tipo', 'pretest')->first();
        $postest = CuestionarioAnemia::where('alumno_id', $alumno->id)->where('tipo', 'postest')->first();

        $preguntas = config('anemia_cuestionario');

        return view('orientacion.show', compact('alumno', 'caso', 'recomendaciones', 'pretest', 'postest', 'preguntas'));
    }

    public function guardarCuestionario(Request $request, string $matricula)
    {
        $alumno = Alumno::where('matricula', $matricula)->firstOrFail();
        $caso = AlumnoAnemia::where('alumno_id', $alumno->id)->where('activo', true)->first();
        abort_if(!$caso, 404);

        $tipo = $request->input('tipo');
        abort_unless(in_array($tipo, ['pretest', 'postest'], true), 422);

        if (CuestionarioAnemia::where('alumno_id', $alumno->id)->where('tipo', $tipo)->exists()) {
            return back()->with('error', 'Ya se registró el ' . $tipo . ' de este estudiante.');
        }

        $preguntas = config('anemia_cuestionario');
        $respuestas = $request->input('respuestas', []);

        $correctas = 0;
        foreach ($preguntas as $i => $preg) {
            if ((int) ($respuestas[$i] ?? -1) === $preg['correcta']) {
                $correctas++;
            }
        }
        $puntaje = round(($correctas / count($preguntas)) * 20, 1);

        CuestionarioAnemia::create([
            'alumno_id'  => $alumno->id,
            'tipo'       => $tipo,
            'respuestas' => $respuestas,
            'puntaje'    => $puntaje,
        ]);

        return redirect()->route('orientacion.show', $matricula)
            ->with('success', "Cuestionario ({$tipo}) registrado. Puntaje: {$puntaje}/20.");
    }
}
