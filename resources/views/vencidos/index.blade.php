@extends('layouts.app')
@section('title', 'Stock Vigente')
@section('page-title', 'Control de Vencimientos')
@section('breadcrumb', 'Stock Vigente')

@section('content')

{{-- Cabecera con botón imprimir --}}
<div class="flex items-center justify-between mb-5 no-print">
    <div>
        <p class="text-xs text-gray-400">Reporte generado al: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
    <a href="{{ route('vencidos.reporte') }}" target="_blank"
       class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors flex items-center gap-2 shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        Imprimir Reporte
    </a>
</div>

{{-- Encabezado visible solo al imprimir --}}
<div class="hidden print-show mb-6 text-center border-b pb-4">
    <p class="text-xs text-gray-500 uppercase tracking-wide">I.E. 14008 Leonor Cerna de Valdiviezo — Programa PAE</p>
    <h1 class="text-xl font-bold text-gray-800 mt-1">Reporte de Control de Vencimientos</h1>
    <p class="text-xs text-gray-500 mt-1">Fecha: {{ now()->format('d/m/Y H:i') }}</p>
</div>

{{-- Resumen --}}
@if($vencidos->count())
<div class="mb-4 bg-red-50 border border-red-200 rounded-xl px-5 py-3 flex items-center gap-3">
    <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <p class="text-sm text-red-700 font-semibold">Atención: hay <strong>{{ $vencidos->count() }}</strong> producto(s) vencido(s) que deben retirarse del inventario.</p>
</div>
@endif
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="bg-yellow-50 border border-yellow-200 rounded-xl px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-yellow-700">{{ $porVencer->count() }}</p>
            <p class="text-xs font-semibold text-yellow-500 uppercase tracking-wide">Por Vencer (30 días)</p>
        </div>
    </div>
    <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-green-700">{{ $vigentes->count() }}</p>
            <p class="text-xs font-semibold text-green-500 uppercase tracking-wide">Vigentes</p>
        </div>
    </div>
</div>

{{-- POR VENCER --}}
@if($porVencer->count())
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <span class="inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">
            <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
            Por Vencer
        </span>
        <span class="text-xs text-gray-400">— vencen en los próximos 30 días</span>
    </div>
    <div class="bg-white rounded-xl border border-yellow-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-yellow-500 text-white text-[10px] uppercase tracking-wide">
                    <th class="px-4 py-2.5 text-left">Descripción</th>
                    <th class="px-4 py-2.5 text-left">Nivel</th>
                    <th class="px-4 py-2.5 text-left">Marca</th>
                    <th class="px-4 py-2.5 text-left">Lote</th>
                    <th class="px-4 py-2.5 text-center">Cant.</th>
                    <th class="px-4 py-2.5 text-center">Venc.</th>
                    <th class="px-4 py-2.5 text-center">Días restantes</th>
                    <th class="px-4 py-2.5 text-center">Editar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-yellow-50">
                @foreach($porVencer as $p)
                <tr class="hover:bg-yellow-50 transition-colors">
                    <td class="px-4 py-2.5 font-medium text-gray-800">{{ $p['descripcion'] }}</td>
                    <td class="px-4 py-2.5">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $p['nivel'] === 'Inicial' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700' }} font-semibold">
                            {{ $p['nivel'] }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $p['marca'] ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $p['lote'] ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-center text-xs">{{ $p['cant'] }} {{ $p['unid'] }}</td>
                    <td class="px-4 py-2.5 text-center text-xs font-bold text-yellow-700">
                        {{ $p['fecha_vencimiento']->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="text-xs font-bold text-yellow-700 bg-yellow-100 px-2 py-0.5 rounded-full">
                            {{ $hoy->diffInDays($p['fecha_vencimiento']) }} días
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <a href="{{ $p['edit_route'] }}" class="text-xs text-gray-400 hover:text-gray-700 underline">Editar</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- VIGENTES --}}
@if($vigentes->count())
<div class="mb-6">
    <div class="flex items-center gap-2 mb-3">
        <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">
            <span class="w-2 h-2 bg-green-500 rounded-full"></span>
            Vigentes
        </span>
        <span class="text-xs text-gray-400">— vencen en más de 30 días</span>
    </div>
    <div class="bg-white rounded-xl border border-green-200 overflow-hidden shadow-sm">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-green-600 text-white text-[10px] uppercase tracking-wide">
                    <th class="px-4 py-2.5 text-left">Descripción</th>
                    <th class="px-4 py-2.5 text-left">Nivel</th>
                    <th class="px-4 py-2.5 text-left">Marca</th>
                    <th class="px-4 py-2.5 text-left">Lote</th>
                    <th class="px-4 py-2.5 text-center">Cant.</th>
                    <th class="px-4 py-2.5 text-center">Venc.</th>
                    <th class="px-4 py-2.5 text-center">Días restantes</th>
                    <th class="px-4 py-2.5 text-center">Editar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-green-50">
                @foreach($vigentes as $p)
                <tr class="hover:bg-green-50 transition-colors">
                    <td class="px-4 py-2.5 font-medium text-gray-800">{{ $p['descripcion'] }}</td>
                    <td class="px-4 py-2.5">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $p['nivel'] === 'Inicial' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700' }} font-semibold">
                            {{ $p['nivel'] }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $p['marca'] ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $p['lote'] ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-center text-xs">{{ $p['cant'] }} {{ $p['unid'] }}</td>
                    <td class="px-4 py-2.5 text-center text-xs font-bold text-green-700">
                        {{ $p['fecha_vencimiento']->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <span class="text-xs font-bold text-green-700 bg-green-100 px-2 py-0.5 rounded-full">
                            {{ $hoy->diffInDays($p['fecha_vencimiento']) }} días
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-center">
                        <a href="{{ $p['edit_route'] }}" class="text-xs text-gray-400 hover:text-gray-700 underline">Editar</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@if($porVencer->count() === 0 && $vigentes->count() === 0)
<div class="text-center py-16 text-gray-400">
    <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <p class="text-sm font-medium">Ningún producto tiene fecha de vencimiento registrada.</p>
    <p class="text-xs mt-1">Edita los productos en PECOSA Inicial o Primaria para agregar fechas.</p>
</div>
@endif

@push('scripts')
<style>
    .print-show { display: none; }

    @media print {
        aside, header, .no-print { display: none !important; }
        html, body { background: white !important; margin: 0 !important; padding: 0 !important; }
        .flex.h-screen { display: block !important; height: auto !important; overflow: visible !important; }
        main { margin: 0 !important; padding: 12px !important; }
        .print-show { display: block !important; }
        table { page-break-inside: auto; font-size: 11px; }
        tr { page-break-inside: avoid; }
        .rounded-xl, .rounded-lg, .shadow-sm { border-radius: 0 !important; box-shadow: none !important; }
        a { text-decoration: none !important; color: inherit !important; }
    }
</style>
@endpush

@endsection

