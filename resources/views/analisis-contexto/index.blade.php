@extends('layouts.app')
@section('title', 'Análisis de Contexto')
@section('page-title', 'Análisis de Variables de Contexto')
@section('breadcrumb', 'Clima y eventos especiales frente a la demanda, mermas y cumplimiento nutricional')

@section('header-actions')
    @can('gestionar-investigacion')
    <a href="{{ route('exportar.contexto') }}"
       class="inline-flex items-center space-x-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        <span>Exportar CSV</span>
    </a>
    @endcan
@endsection

@section('content')

<div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-3 mb-6 text-blue-800 text-sm">
    Este reporte contrasta las variables de contexto del marco teórico (clima, eventos especiales) con
    la demanda de raciones, el desperdicio y el cumplimiento nutricional, usando una prueba t de Welch
    (α = 0.05) para indicar si la diferencia observada es estadísticamente significativa o solo variación
    muestral. Los días se agregan sumando todas las secciones registradas.
</div>

{{-- ── Cobertura del dato de clima ──────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
    @foreach($coberturaPorNivel as $nivel => $c)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
        <p class="text-xs text-gray-500 uppercase tracking-wide capitalize">{{ $nivel }} — cobertura de clima</p>
        <p class="text-2xl font-bold text-gray-800 mt-1">
            {{ $c['total'] > 0 ? round(($c['con_clima'] / $c['total']) * 100, 1) : 0 }}%
        </p>
        <p class="text-xs text-gray-400 mt-1">{{ $c['con_clima'] }} de {{ $c['total'] }} registros con clima capturado</p>
        @if($c['total'] > 0 && ($c['con_clima'] / $c['total']) < 0.5)
        <p class="text-xs text-amber-600 mt-1">⚠ Cobertura baja — captura el clima al registrar asistencia para fortalecer este análisis.</p>
        @endif
    </div>
    @endforeach
</div>

{{-- ── Clima vs demanda de raciones ────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Clima vs. demanda de raciones (Ficha 4)</h2>
        <p class="text-xs text-gray-500 mt-0.5">Promedio de raciones diarias totales por condición climática, prueba t soleado vs. lluvioso.</p>
    </div>
    <div class="p-6 space-y-6">
        @foreach($resultadoClima as $nivel => $r)
        <div>
            <p class="text-sm font-semibold text-gray-700 capitalize mb-2">{{ $nivel }}</p>
            @if(empty($r['categorias']))
                <p class="text-sm text-gray-400">Sin datos de clima capturados para este nivel.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($r['categorias'] as $cat)
                    <div class="rounded-lg border border-gray-100 p-4 {{ $cat['categoria'] === 'lluvioso' ? 'bg-blue-50' : ($cat['categoria'] === 'nublado' ? 'bg-gray-50' : 'bg-amber-50') }}">
                        <p class="text-xs text-gray-500 uppercase tracking-wide capitalize">{{ $cat['categoria'] }}</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $cat['promedio'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">raciones/día promedio · n={{ $cat['n'] }} días</p>
                    </div>
                    @endforeach
                </div>

                @if($r['test'])
                    <p class="text-xs mt-3 {{ $r['test']['significativo'] ? 'text-green-700 font-medium' : 'text-gray-500' }}">
                        Prueba t de Welch (soleado vs. lluvioso): t={{ $r['test']['t'] }}, df={{ $r['test']['df'] }}, p={{ $r['test']['p'] }}
                        —
                        @if($r['test']['significativo'])
                            diferencia <strong>estadísticamente significativa</strong> (p &lt; 0.05).
                        @else
                            diferencia no significativa con los datos disponibles.
                        @endif
                    </p>
                @else
                    <p class="text-xs text-gray-400 mt-3">Se necesitan al menos 2 días soleados y 2 lluviosos para calcular significancia.</p>
                @endif
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- ── Eventos especiales vs demanda de raciones ───────────────────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Eventos especiales vs. demanda de raciones (Ficha 4)</h2>
        <p class="text-xs text-gray-500 mt-0.5">Feriados próximos, actividades escolares u otros eventos no recurrentes.</p>
    </div>
    <div class="p-6 space-y-6">
        @foreach($resultadoEvento as $nivel => $r)
        <div>
            <p class="text-sm font-semibold text-gray-700 capitalize mb-2">{{ $nivel }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-lg border border-gray-100 bg-gray-50 p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Jornada normal</p>
                    @if($r['normal'])
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $r['normal']['promedio'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">raciones/día · n={{ $r['normal']['n'] }} días</p>
                    @else
                        <p class="text-lg text-gray-300 mt-1">Sin datos</p>
                    @endif
                </div>
                <div class="rounded-lg border border-gray-100 bg-purple-50 p-4">
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Con evento especial</p>
                    @if($r['especial'])
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $r['especial']['promedio'] }}</p>
                        <p class="text-xs text-gray-400 mt-1">raciones/día · n={{ $r['especial']['n'] }} días</p>
                    @else
                        <p class="text-lg text-gray-300 mt-1">Sin datos</p>
                    @endif
                </div>
            </div>

            @if($r['test'])
                <p class="text-xs mt-3 {{ $r['test']['significativo'] ? 'text-green-700 font-medium' : 'text-gray-500' }}">
                    Prueba t de Welch: t={{ $r['test']['t'] }}, df={{ $r['test']['df'] }}, p={{ $r['test']['p'] }}
                    —
                    @if($r['test']['significativo'])
                        diferencia <strong>estadísticamente significativa</strong> (p &lt; 0.05).
                    @else
                        diferencia no significativa con los datos disponibles.
                    @endif
                </p>
            @else
                <p class="text-xs text-gray-400 mt-3">Se necesitan al menos 2 jornadas normales y 2 con evento especial para calcular significancia.</p>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- ── Clima vs desperdicio (Ficha 6) ──────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-6">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Clima vs. índice de mermas (Ficha 6)</h2>
        <p class="text-xs text-gray-500 mt-0.5">Cruce por fecha entre el clima del día y los registros de distribución/desperdicio.</p>
    </div>
    <div class="p-6 space-y-4">
        @foreach($cruceMermas as $nivel => $categorias)
        <div>
            <p class="text-sm font-semibold text-gray-700 capitalize mb-2">{{ $nivel }}</p>
            @if(empty($categorias))
                <p class="text-sm text-gray-400">Sin registros de distribución cruzables con el clima del día para este nivel.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($categorias as $cat)
                    <div class="rounded-lg border border-gray-100 p-4 bg-gray-50">
                        <p class="text-xs text-gray-500 uppercase tracking-wide capitalize">{{ $cat['categoria'] }}</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $cat['promedio'] }}%</p>
                        <p class="text-xs text-gray-400 mt-1">índice de mermas promedio · n={{ $cat['n'] }}</p>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- ── Clima vs cumplimiento nutricional (Ficha 5) ─────────────────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Clima vs. cumplimiento nutricional (Ficha 5)</h2>
        <p class="text-xs text-gray-500 mt-0.5">Cruce por fecha entre el clima del día y los registros de control nutricional.</p>
    </div>
    <div class="p-6 space-y-4">
        @foreach($cruceNutricion as $nivel => $categorias)
        <div>
            <p class="text-sm font-semibold text-gray-700 capitalize mb-2">{{ $nivel }}</p>
            @if(empty($categorias))
                <p class="text-sm text-gray-400">Sin registros nutricionales cruzables con el clima del día para este nivel.</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($categorias as $cat)
                    <div class="rounded-lg border border-gray-100 p-4 bg-gray-50">
                        <p class="text-xs text-gray-500 uppercase tracking-wide capitalize">{{ $cat['categoria'] }}</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $cat['pct_cumple'] }}%</p>
                        <p class="text-xs text-gray-400 mt-1">cumplimiento nutricional · n={{ $cat['n'] }}</p>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        @endforeach
    </div>
</div>

@endsection
