@extends('layouts.app')
@section('title', 'Resultados del Cuestionario — Anemia')
@section('page-title', 'Resultados del Cuestionario')
@section('breadcrumb', 'Conocimiento de los padres antes y después de la orientación')

@section('content')

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Pretest vs. Postest</h2>
            <p class="text-xs text-gray-500 mt-0.5">Puntaje sobre 20. El postest se habilita 4 semanas después del pretest.</p>
        </div>
        <a href="{{ route('anemia.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200">Volver</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Alumno</th>
                    <th class="px-4 py-3 text-center">Pretest</th>
                    <th class="px-4 py-3 text-center">Postest</th>
                    <th class="px-4 py-3 text-center">Diferencia</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($resultados as $alumnoId => $registros)
                    @php
                        $alumno = $registros->first()->alumno;
                        $pre = $registros->firstWhere('tipo', 'pretest');
                        $post = $registros->firstWhere('tipo', 'postest');
                        $diff = ($pre && $post) ? round($post->puntaje - $pre->puntaje, 1) : null;
                    @endphp
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}, {{ $alumno->nombre }}
                        </td>
                        <td class="px-4 py-3 text-center">{{ $pre ? $pre->puntaje : '—' }}</td>
                        <td class="px-4 py-3 text-center">{{ $post ? $post->puntaje : '—' }}</td>
                        <td class="px-4 py-3 text-center font-bold {{ $diff !== null && $diff > 0 ? 'text-green-600' : ($diff !== null && $diff < 0 ? 'text-red-600' : 'text-gray-400') }}">
                            {{ $diff !== null ? ($diff > 0 ? '+' : '') . $diff : '—' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Aún no hay resultados de cuestionarios.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
