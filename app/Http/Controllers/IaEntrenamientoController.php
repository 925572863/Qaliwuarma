<?php

namespace App\Http\Controllers;

use App\Models\IaEntrenamiento;

/**
 * Historial de entrenamientos del modelo Random Forest (Fichas 1, 2 y 3 - VI).
 * Vista de solo lectura: los registros se generan automáticamente al ejecutar
 * `php artisan ia:entrenar` o el botón "Entrenar IA" del módulo de predicción.
 */
class IaEntrenamientoController extends Controller
{
    public function index()
    {
        $entrenamientos = IaEntrenamiento::orderByDesc('created_at')->paginate(15);

        $ultimoPorNivel = [];
        foreach (['inicial', 'primaria'] as $nivel) {
            $ultimoPorNivel[$nivel] = IaEntrenamiento::where('nivel', $nivel)
                ->orderByDesc('created_at')->first();
        }

        return view('ia-entrenamientos.index', compact('entrenamientos', 'ultimoPorNivel'));
    }
}
