@extends('layouts.app')
@section('title', 'Evaluaciones de Usabilidad')
@section('page-title', 'Evaluaciones de Usabilidad')
@section('breadcrumb', 'Resultados del cuestionario de satisfacción')

@section('header-actions')
    <a href="{{ route('evaluacion.create') }}"
       class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Nueva Evaluación</span>
    </a>
@endsection

@section('content')

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 mb-5 text-green-700 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

{{-- Resumen general --}}
@if($promedios)
<div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">

    <div class="xl:col-span-2 bg-blue-600 rounded-xl p-5 text-white flex flex-col justify-between shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-blue-100">Promedio General</p>
        <div>
            <p class="text-5xl font-bold leading-tight">{{ $promedios['general'] }}</p>
            <p class="text-sm text-blue-100 mt-1">de 5.0 · {{ $promedios['total'] }} {{ $promedios['total'] == 1 ? 'evaluación' : 'evaluaciones' }}</p>
        </div>
    </div>

    @php
    $items = [
        ['label' => 'Facilidad de uso',       'val' => $promedios['p1']],
        ['label' => 'Claridad de datos',      'val' => $promedios['p2']],
        ['label' => 'Utilidad predicción',    'val' => $promedios['p3']],
        ['label' => 'Organización alumnos',   'val' => $promedios['p4']],
        ['label' => 'Velocidad respuesta',    'val' => $promedios['p5']],
    ];
    @endphp

    @foreach($items as $item)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-col justify-between">
        <p class="text-xs text-gray-500 leading-tight">{{ $item['label'] }}</p>
        <div>
            <p class="text-3xl font-bold text-gray-800 leading-tight">{{ $item['val'] }}</p>
            <div class="mt-1.5 bg-gray-100 rounded-full h-1.5">
                <div class="h-1.5 rounded-full {{ $item['val'] >= 4.5 ? 'bg-green-500' : ($item['val'] >= 3 ? 'bg-yellow-400' : 'bg-red-400') }}"
                     style="width: {{ ($item['val'] / 5) * 100 }}%"></div>
            </div>
        </div>
    </div>
    @endforeach

</div>
@endif

{{-- Tabla de evaluaciones --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Registros de Evaluación</h2>
    </div>

    @if($evaluaciones->isEmpty())
    <div class="py-16 text-center">
        <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-gray-400 text-sm">Aún no hay evaluaciones registradas.</p>
        <a href="{{ route('evaluacion.create') }}"
           class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Registrar primera evaluación
        </a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Evaluador</th>
                    <th class="px-5 py-3">Cargo</th>
                    <th class="px-5 py-3">Fecha</th>
                    <th class="px-5 py-3 text-center">P1</th>
                    <th class="px-5 py-3 text-center">P2</th>
                    <th class="px-5 py-3 text-center">P3</th>
                    <th class="px-5 py-3 text-center">P4</th>
                    <th class="px-5 py-3 text-center">P5</th>
                    <th class="px-5 py-3 text-center">Promedio</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($evaluaciones as $ev)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5 font-medium text-gray-900">{{ $ev->evaluador }}</td>
                    <td class="px-5 py-3.5 text-gray-500">{{ $ev->cargo ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-gray-600">{{ $ev->fecha->format('d/m/Y') }}</td>
                    @foreach(['p1_facilidad','p2_claridad','p3_utilidad','p4_organizacion','p5_velocidad'] as $p)
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold
                            {{ $ev->$p >= 5 ? 'bg-green-100 text-green-700' : ($ev->$p >= 4 ? 'bg-blue-100 text-blue-700' : ($ev->$p >= 3 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-600')) }}">
                            {{ $ev->$p }}
                        </span>
                    </td>
                    @endforeach
                    <td class="px-5 py-3.5 text-center">
                        <span class="font-bold text-base {{ $ev->promedio >= 4.5 ? 'text-green-600' : ($ev->promedio >= 3 ? 'text-yellow-600' : 'text-red-500') }}">
                            {{ $ev->promedio }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        <form method="POST" action="{{ route('evaluacion.destroy', $ev) }}"
                              onsubmit="return confirm('¿Eliminar esta evaluación?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-xs">Eliminar</button>
                        </form>
                    </td>
                </tr>
                @if($ev->comentario)
                <tr class="bg-gray-50">
                    <td colspan="10" class="px-5 py-2 text-xs text-gray-500 italic">
                        <span class="font-semibold text-gray-600">Comentario:</span> {{ $ev->comentario }}
                    </td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@endsection
