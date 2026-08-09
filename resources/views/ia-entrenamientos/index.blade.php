@extends('layouts.app')
@section('title', 'Historial de Entrenamientos IA')
@section('page-title', 'Historial de Entrenamientos del Modelo (Random Forest)')
@section('breadcrumb', 'Fichas 1, 2 y 3 (VI) — Preprocesamiento, entrenamiento/validación y arquitectura')

@section('header-actions')
    <a href="{{ route('exportar.ia-entrenamientos') }}"
       class="inline-flex items-center space-x-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        <span>Exportar CSV</span>
    </a>
@endsection

@section('content')

{{-- Último entrenamiento por nivel --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
    @foreach($ultimoPorNivel as $nivel => $e)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 capitalize">{{ $nivel }} — último entrenamiento</p>
        @if($e)
        <div class="grid grid-cols-2 gap-3 mt-3 text-sm">
            <div><span class="text-gray-400">MAE:</span> <span class="font-semibold">{{ $e->mae }}</span></div>
            <div><span class="text-gray-400">RMSE:</span> <span class="font-semibold">{{ $e->rmse }}</span></div>
            <div><span class="text-gray-400">MAPE:</span> <span class="font-semibold">{{ $e->mape }}%</span></div>
            <div><span class="text-gray-400">R²:</span> <span class="font-semibold">{{ $e->r2 }}</span></div>
            <div><span class="text-gray-400">Árboles:</span> <span class="font-semibold">{{ $e->n_estimators }}</span></div>
            <div><span class="text-gray-400">Profundidad:</span> <span class="font-semibold">{{ $e->max_depth }}</span></div>
            <div><span class="text-gray-400">% depurados:</span> <span class="font-semibold">{{ $e->pct_depurados }}%</span></div>
            <div><span class="text-gray-400">Tiempo:</span> <span class="font-semibold">{{ $e->tiempo_entrenamiento_seg }}s</span></div>
        </div>
        <p class="text-xs text-gray-400 mt-3">{{ $e->created_at->format('d/m/Y H:i') }} · {{ $e->muestras ?? $e->registros_depurados }} muestras · {{ $e->k_folds }}-fold CV</p>
        @else
        <p class="text-sm text-gray-400 mt-3">Aún no se ha entrenado ningún modelo para este nivel.</p>
        @endif
    </div>
    @endforeach
</div>

{{-- Historial completo --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Historial de entrenamientos</h2>
    </div>

    @if($entrenamientos->isEmpty())
    <div class="py-16 text-center">
        <p class="text-gray-400 text-sm">Aún no hay entrenamientos registrados.</p>
        <p class="text-xs text-gray-400 mt-1">Ejecuta <code class="bg-gray-100 px-1.5 py-0.5 rounded">php artisan ia:entrenar</code> o usa el botón "Entrenar IA" en Predicción.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Fecha</th>
                    <th class="px-5 py-3">Nivel</th>
                    <th class="px-5 py-3">Fase</th>
                    <th class="px-5 py-3 text-center">% Depurados</th>
                    <th class="px-5 py-3 text-center">% Completos</th>
                    <th class="px-5 py-3 text-center">MAE</th>
                    <th class="px-5 py-3 text-center">RMSE</th>
                    <th class="px-5 py-3 text-center">MAPE</th>
                    <th class="px-5 py-3 text-center">R²</th>
                    <th class="px-5 py-3 text-center">Árboles/Prof.</th>
                    <th class="px-5 py-3 text-center">Tiempo (s)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($entrenamientos as $e)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5 text-gray-600">{{ $e->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3.5 capitalize font-medium text-gray-900">{{ $e->nivel }}</td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $e->fase === 'postest' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($e->fase) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">{{ $e->pct_depurados }}%</td>
                    <td class="px-5 py-3.5 text-center">{{ $e->pct_completos }}%</td>
                    <td class="px-5 py-3.5 text-center">{{ $e->mae }}</td>
                    <td class="px-5 py-3.5 text-center">{{ $e->rmse }}</td>
                    <td class="px-5 py-3.5 text-center">{{ $e->mape }}%</td>
                    <td class="px-5 py-3.5 text-center">{{ $e->r2 }}</td>
                    <td class="px-5 py-3.5 text-center">{{ $e->n_estimators }} / {{ $e->max_depth }}</td>
                    <td class="px-5 py-3.5 text-center">{{ $e->tiempo_entrenamiento_seg }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4">{{ $entrenamientos->links() }}</div>
    @endif
</div>

@endsection
