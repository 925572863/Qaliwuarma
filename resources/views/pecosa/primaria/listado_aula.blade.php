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

        {{-- Productos por aula --}}
        @if(count($productosAula) > 0)
        <div class="mt-5 border-2 border-orange-400 rounded-xl overflow-hidden">
            <div class="bg-orange-400 px-4 py-2 flex items-center space-x-2">
                <svg class="w-4 h-4 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
                </svg>
                <span class="text-white font-black text-xs uppercase tracking-widest">
                    Productos entregados por aula (no por alumno individual)
                </span>
            </div>
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-orange-50">
                        <th class="border border-orange-200 px-3 py-2 text-left font-bold text-orange-800 uppercase text-[9px]">Producto</th>
                        <th class="border border-orange-200 px-3 py-2 text-center font-bold text-orange-800 uppercase text-[9px]">Unidad</th>
                        <th class="border border-orange-200 px-3 py-2 text-center font-bold text-orange-800 uppercase text-[9px]">Presentación</th>
                        <th class="border border-orange-200 px-3 py-2 text-center font-bold text-orange-800 uppercase text-[9px]">Cantidad para el aula</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productosAula as $prod)
                    <tr class="hover:bg-orange-50">
                        <td class="border border-orange-200 px-3 py-2 font-semibold text-gray-800">{{ $prod['nombre'] }}</td>
                        <td class="border border-orange-200 px-3 py-2 text-center text-gray-600">{{ $prod['unid'] }}</td>
                        <td class="border border-orange-200 px-3 py-2 text-center text-gray-600">{{ $prod['presentacion'] }}</td>
                        <td class="border border-orange-200 px-3 py-2 text-center font-black text-orange-700 text-sm">
                            {{ number_format($prod['total_aula']) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="text-[9px] text-orange-700 px-4 py-2 bg-orange-50 border-t border-orange-200">
                Estos productos se entregan en cantidad total al aula, no individualmente por alumno.
            </p>
        </div>
        @endif

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
        .border-orange-400 { border: 1.5px solid #f97316 !important; border-radius: 4px !important; margin-top: 6mm !important; }
        .bg-orange-400 { background: #f97316 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .bg-orange-50 { background: #fff7ed !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        @page { size: A4 landscape; margin: 8mm; }
    }
</style>
@endsection
