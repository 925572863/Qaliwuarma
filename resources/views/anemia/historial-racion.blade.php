@extends('layouts.app')
@section('title', 'Historial de ración — ' . $caso->alumno->nombre)
@section('page-title', 'Historial de ración')
@section('breadcrumb', $caso->alumno->apellido_paterno . ' ' . $caso->alumno->apellido_materno . ', ' . $caso->alumno->nombre)

@section('header-actions')
    <a href="{{ route('anemia.index') }}" class="inline-flex items-center space-x-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <span>← Volver</span>
    </a>
@endsection

@section('content')

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100">
        <h2 class="text-lg font-bold text-gray-800">
            {{ $caso->alumno->apellido_paterno }} {{ $caso->alumno->apellido_materno }}, {{ $caso->alumno->nombre }}
        </h2>
        <p class="text-xs text-gray-500 mt-0.5">{{ $caso->alumno->nivel_label }} · {{ $caso->alumno->carrera }} — últimos {{ $controles->count() }} controles registrados</p>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse($controles as $c)
        <div class="px-5 py-3 flex items-center justify-between">
            <span class="text-sm text-gray-700">{{ $c->fecha->format('d/m/Y') }}</span>
            @if($c->recibio_racion)
                <span class="text-xs font-medium text-green-700 bg-green-50 border border-green-200 px-2.5 py-1 rounded-full">✓ Recibió su ración</span>
            @else
                <span class="text-xs font-medium text-red-700 bg-red-50 border border-red-200 px-2.5 py-1 rounded-full">✗ No recibió</span>
            @endif
        </div>
        @empty
        <p class="p-6 text-center text-gray-400 text-sm">Aún no hay controles registrados para este alumno.</p>
        @endforelse
    </div>
</div>

@endsection
