@extends('layouts.app')
@section('title', 'Listado ' . $seccion)
@section('page-title', 'Listado por Aula — ' . $seccion)
@section('breadcrumb', $version->nombre)

@section('content')

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    {{-- Cabecera --}}
    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-yellow-50 to-transparent no-print">
        <div>
            <h2 class="text-lg font-bold text-gray-800">{{ $seccion }} — {{ $version->nombre }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">{{ $totalAlumnos }} alumnos · {{ count($productos) }} productos</p>
        </div>
        <div class="flex space-x-2">
            <button onclick="window.print()" type="button"
                    class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span>Imprimir</span>
            </button>
            <a href="{{ route('pecosa.primaria.distribuciones.ver', $version->id) }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                Volver
            </a>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto p-4">

        {{-- Título imprimible --}}
        <div class="print-title hidden mb-3">
            <div class="flex justify-between items-start">
                <div>
                    <p class="font-bold text-sm uppercase">{{ $seccion }} / {{ $version->nombre }}</p>
                </div>
                <div class="text-right text-xs text-gray-500">
                    <p>Fecha: {{ now()->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>

        <table class="w-full text-xs border-collapse" id="tabla-listado">
            <thead>
                {{-- Fila 1: cabecera grupos --}}
                <tr>
                    <th rowspan="2" colspan="2"
                        class="border-2 border-gray-700 bg-yellow-300 text-gray-800 font-bold text-center px-2 py-2 uppercase text-xs">
                        {{ $seccion }} / {{ $version->nombre }}
                    </th>
                    <th colspan="{{ count($productos) }}"
                        class="border-2 border-gray-700 bg-gray-100 text-gray-700 font-bold text-center px-2 py-1 uppercase text-[10px] tracking-widest">
                        PRODUCTOS
                    </th>
                    <th rowspan="2"
                        class="border-2 border-gray-700 bg-yellow-300 text-gray-800 font-bold text-center px-2 py-2 uppercase text-[10px]">
                        Nº<br>BOLSAS
                    </th>
                </tr>
                {{-- Fila 2: nombres de productos --}}
                <tr>
                    @foreach($productos as $prod)
                        <th class="border border-gray-500 bg-yellow-200 text-gray-800 font-bold text-center px-1 py-1 text-[8px] uppercase leading-tight max-w-[50px]">
                            {{ $prod['nombre'] }}<br>
                            <span class="font-normal text-gray-500">{{ $prod['presentacion'] }}</span>
                        </th>
                    @endforeach
                </tr>
                {{-- Fila 3: encabezado Nº | Apellidos --}}
                <tr class="bg-gray-700 text-white">
                    <th class="border border-gray-500 px-2 py-1.5 text-center font-bold uppercase text-[9px] w-8">Nº</th>
                    <th class="border border-gray-500 px-3 py-1.5 text-left font-bold uppercase text-[9px]">Apellidos y nombres</th>
                    @foreach($productos as $prod)
                        <th class="border border-gray-500 px-1 py-1.5 text-center font-bold text-[9px] w-8"></th>
                    @endforeach
                    <th class="border border-gray-500 px-1 py-1.5 text-center font-bold text-[9px] w-8"></th>
                </tr>
            </thead>

            <tbody>
                @foreach($alumnos as $i => $alumno)
                    @php
                        $apellidos = strtoupper(trim($alumno->apellido_paterno . ' ' . $alumno->apellido_materno));
                        $nombre    = strtoupper(trim($alumno->nombre));
                        $nombreCompleto = $apellidos . ', ' . $nombre;
                        $esUltimo = $i === $totalAlumnos - 1;
                    @endphp
                    <tr class="{{ $esUltimo ? 'bg-yellow-50 font-semibold' : ($i % 2 === 0 ? 'bg-white' : 'bg-gray-50') }}">
                        <td class="border border-gray-300 text-center px-2 py-1 text-gray-700 font-medium">{{ $i + 1 }}</td>
                        <td class="border border-gray-300 px-3 py-1 text-gray-800 font-medium uppercase text-[10px]">
                            {{ $nombreCompleto }}
                        </td>
                        @foreach($productos as $prod)
                            <td class="border border-gray-300 text-center px-1 py-1 text-gray-700 font-bold">
                                {{ $porAlumno[$prod['id']] > 0 ? $porAlumno[$prod['id']] : '' }}
                            </td>
                        @endforeach
                        <td class="border border-gray-300 text-center px-1 py-1 text-gray-700 font-bold">1</td>
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr class="bg-gray-700 text-white font-bold">
                    <td colspan="2" class="border border-gray-500 text-center px-3 py-2 uppercase text-xs font-black tracking-wider">
                        TOTAL
                    </td>
                    @foreach($productos as $prod)
                        <td class="border border-gray-500 text-center px-1 py-2 text-sm">
                            {{ number_format($totalesCol[$prod['id']]) }}
                        </td>
                    @endforeach
                    <td class="border border-gray-500 text-center px-1 py-2 text-sm">
                        {{ $totalAlumnos }}
                    </td>
                </tr>
            </tfoot>
        </table>

        {{-- Total general --}}
        <div class="mt-2 flex justify-end no-print">
            @php $granTotal = array_sum($totalesCol) + $totalAlumnos; @endphp
            <span class="text-xs text-gray-500">Total unidades + bolsas: <strong>{{ number_format($granTotal) }}</strong></span>
        </div>


    </div>
</div>

<style>
    @media print {
        aside, header, .no-print { display: none !important; }
        html, body { background: white !important; margin: 0 !important; padding: 0 !important; }
        .flex.h-screen { display: block !important; height: auto !important; overflow: visible !important; }
        .flex-1.flex.flex-col { display: block !important; overflow: visible !important; }
        main { padding: 0 !important; overflow: visible !important; }
        .bg-white.rounded-2xl { border-radius: 0 !important; box-shadow: none !important; border: none !important; }
        .overflow-x-auto { overflow: visible !important; }
        .p-4 { padding: 4mm !important; }
        .print-title { display: block !important; }

        table { width: 100% !important; border-collapse: collapse !important; font-size: 7.5px !important; }
        th, td { padding: 2px 3px !important; border: 0.5px solid #444 !important; }
        .sticky { position: static !important; }
        thead { display: table-header-group !important; }
        tfoot { display: table-footer-group !important; }
@page { size: A4 landscape; margin: 8mm; }
    }
</style>
@endsection
