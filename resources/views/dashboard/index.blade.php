@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb', 'Resumen general del sistema')

@section('header-actions')
    <a href="{{ route('alumnos.create') }}"
       class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Nuevo Alumno</span>
    </a>
@endsection

@section('content')

{{-- ── Alerta stock crítico ── --}}
@if($productosStockCritico->count() > 0)
<div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-5 flex items-start gap-3">
    <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <div class="flex-1">
        <p class="text-sm font-bold text-red-700">⚠️ Stock CRÍTICO — Productos por agotarse</p>
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach($productosStockCritico as $p)
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-red-100 text-red-800 text-xs font-semibold">
                    {{ $p->descripcion }} — {{ $p->stock_efectivo }} kg ({{ $p->porcentaje_stock }}%)
                </span>
            @endforeach
        </div>
    </div>
</div>
@elseif($productosStockBajo->count() > 0)
<div class="bg-yellow-50 border border-yellow-200 rounded-xl px-5 py-4 mb-5 flex items-start gap-3">
    <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
    </svg>
    <div class="flex-1">
        <p class="text-sm font-bold text-yellow-700">Stock bajo en algunos productos PECOSA</p>
        <div class="mt-2 flex flex-wrap gap-2">
            @foreach($productosStockBajo as $p)
                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-yellow-100 text-yellow-800 text-xs font-semibold">
                    {{ $p->descripcion }} — {{ $p->stock_efectivo }} kg ({{ $p->porcentaje_stock }}%)
                </span>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- ── Stats cards ── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-7">

    {{-- Total --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 flex items-center space-x-4">
        <div class="w-13 h-13 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0" style="width:52px;height:52px">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Alumnos</p>
            <p class="text-3xl font-bold text-gray-800 leading-tight">{{ $stats['total'] }}</p>
        </div>
    </div>

    {{-- Activos --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center space-x-4 mb-3">
            <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm text-gray-500 font-medium">Activos</p>
                <p class="text-3xl font-bold text-gray-800 leading-tight">{{ $stats['activos'] }}</p>
            </div>
        </div>
        @if($stats['total'] > 0)
            <div class="flex items-center space-x-2">
                <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                    <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ round(($stats['activos'] / $stats['total']) * 100) }}%"></div>
                </div>
                <span class="text-xs text-gray-400">{{ round(($stats['activos'] / $stats['total']) * 100) }}%</span>
            </div>
        @endif
    </div>

    {{-- Inactivos --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 flex items-center space-x-4">
        <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Inactivos</p>
            <p class="text-3xl font-bold text-gray-800 leading-tight">{{ $stats['inactivos'] }}</p>
        </div>
    </div>

    {{-- Nuevos mes --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 flex items-center space-x-4">
        <div class="w-12 h-12 bg-purple-50 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500 font-medium">Nuevos este Mes</p>
            <p class="text-3xl font-bold text-gray-800 leading-tight">{{ $stats['nuevos_mes'] }}</p>
        </div>
    </div>
</div>

{{-- ── Recent students ── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-800">Alumnos Recientes</h2>
        <a href="{{ route('alumnos.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
            Ver todos →
        </a>
    </div>

    @if($recientes->isEmpty())
        <div class="py-16 text-center">
            <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-gray-400 text-sm">No hay alumnos registrados aún.</p>
            <a href="{{ route('alumnos.create') }}"
               class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Registrar primer alumno
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Matrícula</th>
                        <th class="px-6 py-3">Nombre completo</th>
                        <th class="px-6 py-3">Nivel</th>
                        <th class="px-6 py-3">Estado</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($recientes as $alumno)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-gray-900">{{ $alumno->matricula }}</td>
                            <td class="px-6 py-3.5 text-gray-700">{{ $alumno->nombre_completo }}</td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $alumno->nivel_badge }}">
                                    {{ $alumno->nivel_label }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $alumno->estado_badge }}">
                                    {{ $alumno->estado_label }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5">
                                <a href="{{ route('alumnos.show', $alumno) }}"
                                   class="text-blue-600 hover:text-blue-800 font-medium text-xs">Ver</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- ── Historial de descuentos de stock ── --}}
@if($historialReciente->count() > 0)
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mt-5">
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-800">Últimos descuentos de stock PECOSA</h2>
        <span class="text-xs text-gray-400">Registrados por la IA</span>
    </div>
    <div class="divide-y divide-gray-50">
        @foreach($historialReciente as $h)
        <div class="px-6 py-3 flex items-center justify-between gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $h->descripcion_producto }}</p>
                <p class="text-xs text-gray-400 mt-0.5">
                    Nivel {{ ucfirst($h->nivel) }} ·
                    {{ \Carbon\Carbon::parse($h->created_at)->locale('es')->diffForHumans() }}
                </p>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-sm font-bold text-red-600">-{{ number_format($h->cantidad_descontada, 3) }} kg</p>
                <p class="text-xs text-gray-400">{{ $h->stock_anterior }} → {{ $h->stock_nuevo }} kg</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endsection
