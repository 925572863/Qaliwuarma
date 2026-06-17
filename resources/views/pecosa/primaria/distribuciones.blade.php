@extends('layouts.app')
@section('title', 'Distribuciones Guardadas')
@section('page-title', 'Historial de Distribuciones')
@section('breadcrumb', 'Distribuciones guardadas — Nivel Primaria')

@section('content')

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-blue-50 to-transparent">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Distribuciones Guardadas</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ $versiones->count() }} distribución(es) registrada(s)</p>
        </div>
        <a href="{{ route('pecosa.primaria.prorrateo') }}"
           class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Nueva Distribución</span>
        </a>
    </div>

    @if($versiones->isEmpty())
        <div class="py-20 text-center">
            <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-400 text-sm">No hay distribuciones guardadas aún.</p>
            <a href="{{ route('pecosa.primaria.prorrateo') }}"
               class="mt-3 inline-block bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Crear primera distribución
            </a>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($versiones as $version)
                <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $version->nombre }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($version->created_at)->format('d/m/Y H:i') }}
                                &nbsp;·&nbsp;
                                {{ number_format($version->total_alumnos) }} alumnos
                                &nbsp;·&nbsp;
                                {{ number_format($version->total_unidades) }} unidades distribuidas
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('pecosa.primaria.distribuciones.ver', $version->id) }}"
                           class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-medium transition-colors flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span>Ver</span>
                        </a>
                        <form method="POST"
                              action="{{ route('pecosa.primaria.distribuciones.eliminar', $version->id) }}"
                              onsubmit="return confirm('¿Eliminar esta distribución?')">
                            @csrf @method('DELETE')
                            <button type="submit"
                                    class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition-colors flex items-center space-x-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <span>Eliminar</span>
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
