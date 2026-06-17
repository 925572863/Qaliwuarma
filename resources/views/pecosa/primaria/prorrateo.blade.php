@extends('layouts.app')
@section('title', 'Distribución Primaria')
@section('page-title', 'Cuadro de Distribución de Alimentos')
@section('breadcrumb', 'Distribución de PECOSA — Nivel Primaria')

@section('content')

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    {{-- Cabecera --}}
    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-green-50 to-transparent no-print">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Distribución de Productos por Sección</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                {{ $totalAlumnos }} alumnos activos ·
                @if($hayGuardado)
                    <span class="text-green-600 font-medium">Guardado el {{ \Carbon\Carbon::parse($ultimaActualizacion)->format('d/m/Y H:i') }}</span>
                @else
                    <span class="text-amber-500 font-medium">Calculado automáticamente — aún no guardado</span>
                @endif
            </p>
        </div>
        <div class="flex space-x-2">
            <button type="button" onclick="limpiarTabla()"
                    class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Borrar tabla</span>
            </button>
            <a href="{{ route('pecosa.primaria.index') }}"
               class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors">
                Volver al Inventario
            </a>
        </div>
    </div>

    {{-- Formulario --}}
    <form method="POST" action="{{ route('pecosa.primaria.prorrateo.guardar') }}" id="form-distribucion">
        @csrf

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse" id="tabla-distribucion">
                <thead>
                    {{-- Fila 1: nombres de productos --}}
                    <tr class="bg-gray-800 text-white">
                        <th rowspan="3" class="px-4 py-3 border border-gray-700 font-bold uppercase text-center sticky left-0 z-20 bg-gray-800 text-xs">
                            SECCIÓN<br><span class="font-normal text-gray-400">(alumnos)</span>
                        </th>
                        @foreach($productos as $prod)
                            <th class="px-2 py-2 border border-gray-700 text-center text-[9px] uppercase leading-tight">
                                {{ $prod['nombre'] }}
                            </th>
                        @endforeach
                        <th rowspan="3" class="px-3 py-3 border border-gray-700 font-bold uppercase text-center bg-gray-900 text-xs">TOTAL</th>
                    </tr>
                    {{-- Fila 2: unidad y presentación --}}
                    <tr class="bg-gray-700 text-gray-300 text-[9px]">
                        @foreach($productos as $prod)
                            <th class="px-2 py-1 border border-gray-600 text-center italic">
                                {{ $prod['unid'] }} · {{ $prod['presentacion'] }}
                            </th>
                        @endforeach
                    </tr>
                    {{-- Fila 3: cantidad total PECOSA --}}
                    <tr class="bg-blue-700 text-white text-[9px] font-bold">
                        @foreach($productos as $prod)
                            <td class="px-2 py-1 border border-blue-600 text-center">
                                {{ number_format($prod['cant_total']) }}
                            </td>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach($data as $fila)
                        <tr class="hover:bg-green-50 transition-colors group">
                            {{-- Celda sección --}}
                            <td class="px-3 py-2 border border-gray-200 font-bold text-gray-700 text-center sticky left-0 z-10 bg-white group-hover:bg-green-50 text-xs">
                                {{ $fila['seccion'] }}<br>
                                <span class="text-[10px] text-gray-400 font-normal">{{ $fila['alumnos'] }} alu</span>
                                <input type="hidden" name="alumnos[{{ $fila['seccion'] }}]" value="{{ $fila['alumnos'] }}">
                            </td>
                            {{-- Inputs de cantidad por producto --}}
                            @foreach($fila['items'] as $index => $cant)
                                <td class="border border-gray-200 p-0 text-center">
                                    <input type="number" min="0"
                                           name="cantidades[{{ $fila['seccion'] }}][{{ $productos[$index]['id'] }}]"
                                           value="{{ $cant }}"
                                           class="w-full text-center text-sm py-2 px-1 focus:outline-none focus:bg-yellow-50 focus:ring-1 focus:ring-yellow-400 print-value"
                                           data-col="{{ $index }}"
                                           data-row="{{ $loop->parent->index }}"
                                           onchange="recalcular()">
                                </td>
                            @endforeach
                            {{-- Total fila --}}
                            <td class="px-3 py-2 border border-gray-200 text-center font-bold text-gray-800 bg-gray-50 text-sm row-total">
                                {{ $fila['total'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr class="bg-gray-100 font-bold text-gray-800 text-xs">
                        <td class="px-4 py-3 border border-gray-300 uppercase text-center sticky left-0 z-20 bg-gray-100">
                            TOTAL DISTRIBUIDO
                        </td>
                        @foreach($totalesProductos as $i => $total)
                            <td class="px-2 py-3 border border-gray-300 text-center col-total" data-col="{{ $i }}">
                                <div class="font-bold">{{ number_format($total) }}</div>
                                <div class="text-[9px] text-gray-400 font-normal">/ {{ number_format($productos[$i]['cant_total']) }}</div>
                            </td>
                        @endforeach
                        <td class="px-4 py-3 border border-gray-300 text-center font-black text-base bg-gray-200" id="gran-total">
                            {{ number_format($totalGeneral) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Pie: nombre + botones --}}
        <div class="p-5 border-t border-gray-100 no-print">
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <input type="text" name="nombre"
                       placeholder="Nombre de esta distribución (opcional, ej: Mayo 2026)"
                       class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                <div class="flex gap-2">
                    <a href="{{ route('pecosa.primaria.distribuciones') }}"
                       class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold transition-colors flex items-center space-x-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        <span>Ver distribuciones guardadas</span>
                        @if($totalVersiones > 0)
                            <span class="bg-white text-blue-600 text-xs font-bold rounded-full px-1.5 py-0.5">{{ $totalVersiones }}</span>
                        @endif
                    </a>
                    <button type="submit"
                            class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition-colors flex items-center space-x-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Guardar</span>
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>

@push('scripts')
<script>
function limpiarTabla() {
    if (!confirm('¿Borrar todas las cantidades de la tabla? Esta acción no se puede deshacer.')) return;
    document.querySelectorAll('input[data-col]').forEach(input => input.value = '');
    recalcular();
}

function recalcular() {
    const inputs = document.querySelectorAll('input[data-col]');
    const colTotales = {};
    const rowTotales = {};

    inputs.forEach(input => {
        const col = input.dataset.col;
        const row = input.dataset.row;
        const val = parseInt(input.value) || 0;

        colTotales[col] = (colTotales[col] || 0) + val;
        rowTotales[row] = (rowTotales[row] || 0) + val;
    });

    // Actualizar totales por columna
    document.querySelectorAll('.col-total').forEach(td => {
        const col = td.dataset.col;
        const pecosaTotal = td.querySelector('div:last-child').textContent.replace('/ ','').replace(/,/g,'');
        td.querySelector('div:first-child').textContent = (colTotales[col] || 0).toLocaleString();
    });

    // Actualizar totales por fila
    const rowCells = document.querySelectorAll('.row-total');
    rowCells.forEach((td, i) => {
        td.textContent = (rowTotales[i] || 0).toLocaleString();
    });

    // Gran total
    const gran = Object.values(rowTotales).reduce((a, b) => a + b, 0);
    document.getElementById('gran-total').textContent = gran.toLocaleString();
}
</script>
@endpush

<style>
    @media print {
        /* Ocultar todo el sistema */
        aside, header, .no-print { display: none !important; }

        /* Resetear layout */
        *, *::before, *::after { box-sizing: border-box; }
        html, body { width: 100% !important; height: auto !important; background: white !important; margin: 0 !important; padding: 0 !important; }
        .flex.h-screen  { display: block !important; height: auto !important; overflow: visible !important; }
        .flex-1.flex.flex-col { display: block !important; overflow: visible !important; }
        main { padding: 0 !important; overflow: visible !important; }

        /* Tarjeta sin bordes decorativos */
        .bg-white.rounded-2xl { border-radius: 0 !important; box-shadow: none !important; border: none !important; }

        /* Contenedor de la tabla sin scroll — ocupa todo el ancho */
        .overflow-x-auto { overflow: visible !important; width: 100% !important; }

        /* Tabla compacta que ocupa el ancho completo de la hoja */
        table {
            width: 100% !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
            font-size: 6.5px !important;
        }

        /* Columna de sección más ancha, resto igual */
        th:first-child, td:first-child { width: 38px !important; }

        th, td {
            padding: 2px 1px !important;
            border: 0.5px solid #666 !important;
            word-break: break-word !important;
            overflow: hidden !important;
        }

        /* Sin sticky en impresión */
        .sticky { position: static !important; }

        /* Inputs: mostrar solo el número sin cuadro */
        input[type=number] {
            border: none !important;
            background: transparent !important;
            text-align: center !important;
            width: 100% !important;
            font-size: 6.5px !important;
            padding: 0 !important;
            -moz-appearance: textfield;
        }

        /* Encabezado y pie en cada página */
        thead { display: table-header-group !important; }
        tfoot { display: table-footer-group !important; }

        /* Página horizontal A4/carta con márgenes mínimos */
        @page { size: A3 landscape; margin: 6mm; }
    }
</style>
@endsection
