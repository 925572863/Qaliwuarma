@extends('layouts.app')
@section('title', 'Control Nutricional')
@section('page-title', 'Precisión en el Cálculo de Raciones Nutricionales')
@section('breadcrumb', 'Ficha 5 (VD) — Gramos planificados vs servidos por ración')

@section('header-actions')
    @can('gestionar-investigacion')
    <form method="POST" action="{{ route('control-nutricional.importar') }}" enctype="multipart/form-data" class="inline-flex items-center space-x-2">
        @csrf
        <label class="inline-flex items-center space-x-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
            </svg>
            <span>Importar Excel/CSV</span>
            <input type="file" name="archivo" accept=".xlsx,.xls,.csv" class="hidden" onchange="this.form.submit()">
        </label>
    </form>
    <a href="{{ route('control-nutricional.plantilla') }}"
       class="inline-flex items-center space-x-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span>Plantilla</span>
    </a>
    <a href="{{ route('exportar.nutricional') }}"
       class="inline-flex items-center space-x-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        <span>Exportar CSV</span>
    </a>
    <a href="{{ route('exportar.comparativo.nutricional') }}"
       title="Error en gramos pretest vs postest, listo para prueba de muestras relacionadas en SPSS"
       class="inline-flex items-center space-x-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
        </svg>
        <span>Comparativo SPSS</span>
    </a>
    <a href="{{ route('control-nutricional.create') }}"
       class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Nuevo Registro</span>
    </a>
    @endcan
@endsection

@section('content')

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 mb-5 text-green-700 text-sm font-medium">
    {{ session('success') }}
</div>
@endif

@if(session('warning'))
<div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-3 mb-5 text-amber-700 text-sm font-medium">
    {{ session('warning') }}
</div>
@endif

@if($errors->any())
<div class="bg-red-50 border border-red-200 rounded-xl px-5 py-3 mb-5 text-red-700 text-sm">
    @foreach($errors->all() as $error)
    <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-blue-600 rounded-xl p-5 text-white shadow-sm">
        <p class="text-xs font-semibold uppercase tracking-wide text-blue-100">% Cumplimiento nutricional</p>
        <p class="text-4xl font-bold leading-tight mt-1">{{ $pctCumplimiento !== null ? $pctCumplimiento.'%' : '—' }}</p>
        <p class="text-sm text-blue-100 mt-1">{{ $totalEvaluadas }} raciones evaluadas</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs text-gray-500">Fórmula</p>
        <p class="text-sm text-gray-700 mt-1">%C = (raciones que cumplen / total evaluadas) × 100</p>
        <p class="text-xs text-gray-400 mt-2">Cumple si |planificado − servido| ≤ 10% de lo planificado.</p>
    </div>
</div>

{{-- Comparativo pretest-postest (diseño preexperimental) --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Comparación Pretest vs Postest</h2>
    </div>
    <div class="grid grid-cols-2 divide-x divide-gray-100">
        @foreach(['pretest' => 'Pretest (antes)', 'postest' => 'Postest (después)'] as $fase => $label)
        <div class="p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">{{ $label }}</p>
            <p class="text-3xl font-bold text-gray-800 mt-1">
                {{ $comparativo[$fase]['pct'] !== null ? $comparativo[$fase]['pct'].'%' : '—' }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ $comparativo[$fase]['total'] }} registros</p>
        </div>
        @endforeach
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Registros</h2>
    </div>

    @if($registros->isEmpty())
    <div class="py-16 text-center">
        <p class="text-gray-400 text-sm">Aún no hay registros nutricionales.</p>
        <a href="{{ route('control-nutricional.create') }}"
           class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            Registrar el primero
        </a>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="px-5 py-3">Fecha</th>
                    <th class="px-5 py-3">Fase</th>
                    <th class="px-5 py-3">Nivel</th>
                    <th class="px-5 py-3">Menú</th>
                    <th class="px-5 py-3 text-center">Gr. planificados</th>
                    <th class="px-5 py-3 text-center">Gr. servidos</th>
                    <th class="px-5 py-3 text-center">Diferencia</th>
                    <th class="px-5 py-3 text-center">Cumple</th>
                    <th class="px-5 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($registros as $r)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-3.5 text-gray-600">{{ $r->fecha->format('d/m/Y') }}</td>
                    <td class="px-5 py-3.5">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $r->fase === 'postest' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($r->fase) }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 capitalize text-gray-600">{{ $r->nivel }}</td>
                    <td class="px-5 py-3.5 font-medium text-gray-900">{{ $r->menu_dia }}</td>
                    <td class="px-5 py-3.5 text-center">{{ $r->gramos_planificados }} g</td>
                    <td class="px-5 py-3.5 text-center">{{ $r->gramos_servidos }} g</td>
                    <td class="px-5 py-3.5 text-center">{{ $r->diferencia }} g</td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                            {{ $r->cumple_requerimiento ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $r->cumple_requerimiento ? 'Sí' : 'No' }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                        @can('gestionar-investigacion')
                        <form method="POST" action="{{ route('control-nutricional.destroy', $r) }}"
                              onsubmit="return confirm('¿Eliminar este registro?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 text-xs">Eliminar</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4">{{ $registros->links() }}</div>
    @endif
</div>

@endsection
