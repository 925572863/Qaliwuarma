@extends('layouts.app')
@section('title', $version->nombre)
@section('page-title', $version->nombre)
@section('breadcrumb', 'Distribución guardada el ' . \Carbon\Carbon::parse($version->created_at)->format('d/m/Y H:i'))

@section('content')

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gradient-to-r from-blue-50 to-transparent no-print">
        <div>
            <h2 class="text-lg font-bold text-gray-800">{{ $version->nombre }}</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                Guardada el {{ \Carbon\Carbon::parse($version->created_at)->format('d/m/Y \a \l\a\s H:i') }}
                &nbsp;·&nbsp; {{ number_format($totalAlumnos) }} alumnos
                &nbsp;·&nbsp; {{ number_format($totalGeneral) }} unidades
            </p>
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
            <button onclick="descargarExcel()" type="button"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium transition-colors flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                <span>Descargar Excel</span>
            </button>
            <a href="{{ route('pecosa.primaria.distribuciones') }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                Volver al historial
            </a>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse" id="tabla-version">
            <thead>
                <tr class="bg-gray-800 text-white">
                    <th rowspan="2" class="px-4 py-3 border border-gray-700 font-bold uppercase text-center sticky left-0 z-20 bg-gray-800 text-xs">
                        SECCIÓN<br><span class="font-normal text-gray-400">(alumnos)</span>
                    </th>
                    @foreach($productos as $prod)
                        <th class="px-2 py-2 border border-gray-700 text-center text-[9px] uppercase leading-tight">
                            {{ $prod['nombre'] }}
                        </th>
                    @endforeach
                    <th rowspan="2" class="px-3 py-3 border border-gray-700 font-bold uppercase text-center bg-gray-900 text-xs">TOTAL</th>
                </tr>
                <tr class="bg-gray-700 text-gray-300 text-[9px]">
                    @foreach($productos as $prod)
                        <th class="px-2 py-1 border border-gray-600 text-center italic">
                            {{ $prod['unid'] }} · {{ $prod['presentacion'] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $fila)
                    <tr class="hover:bg-blue-50 transition-colors">
                        <td class="px-3 py-2 border border-gray-200 font-bold text-gray-700 text-center sticky left-0 z-10 bg-white text-xs">
                            {{ $fila['seccion'] }}<br>
                            <span class="text-[10px] text-gray-400 font-normal">{{ $fila['alumnos'] }} alu</span>
                        </td>
                        @foreach($fila['items'] as $cant)
                            <td class="px-3 py-2 border border-gray-200 text-center text-gray-700">
                                {{ number_format($cant) }}
                            </td>
                        @endforeach
                        <td class="px-3 py-2 border border-gray-200 text-center font-bold text-gray-800 bg-gray-50">
                            {{ number_format($fila['total']) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 font-bold text-gray-800 text-xs">
                    <td class="px-4 py-3 border border-gray-300 uppercase text-center sticky left-0 z-20 bg-gray-100">
                        TOTAL
                    </td>
                    @foreach($totalesProductos as $i => $total)
                        <td class="px-2 py-3 border border-gray-300 text-center">
                            <div class="font-bold">{{ number_format($total) }}</div>
                            <div class="text-[9px] text-gray-400 font-normal">/ {{ number_format($productos[$i]['cant_total']) }}</div>
                        </td>
                    @endforeach
                    <td class="px-4 py-3 border border-gray-300 text-center font-black text-base bg-gray-200">
                        {{ number_format($totalGeneral) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Listados por aula --}}
    <div class="p-5 border-t border-gray-100 no-print">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Listado por aula</p>
        <div class="flex flex-wrap gap-2">
            @foreach($data as $fila)
                <a href="{{ route('pecosa.primaria.distribuciones.listado', ['version' => $version->id, 'seccion' => $fila['seccion']]) }}"
                   class="px-3 py-1.5 bg-yellow-400 hover:bg-yellow-500 text-gray-800 rounded-lg text-xs font-bold transition-colors">
                    {{ $fila['seccion'] }}
                    <span class="text-gray-600 font-normal">({{ $fila['alumnos'] }})</span>
                </a>
            @endforeach
        </div>
    </div>
</div>

@php
    $excelNombres  = array_column($productos, 'nombre');
    $excelUnidades = array_map(fn($p) => $p['unid'].' · '.$p['presentacion'], $productos);
    $excelCantPec  = array_column($productos, 'cant_total');
    $excelFecha    = \Carbon\Carbon::parse($version->created_at)->format('d-m-Y');
@endphp

<script src="https://cdn.sheetjs.com/xlsx-0.20.2/package/dist/xlsx.full.min.js"></script>
<script>
function descargarExcel() {
    const nombre    = @json($version->nombre);
    const fecha     = @json($excelFecha);
    const nombres   = @json($excelNombres);
    const unidades  = @json($excelUnidades);
    const cantPecosa = @json($excelCantPec);
    const filas     = @json($data);
    const totales   = @json($totalesProductos);

    const aoa = [];

    aoa.push([nombre]);
    aoa.push(['Fecha de guardado: ' + fecha]);
    aoa.push([]);
    aoa.push(['SECCIÓN', 'ALUMNOS', ...nombres, 'TOTAL']);
    aoa.push(['', '', ...unidades, '']);
    aoa.push(['CANT. PECOSA', '', ...cantPecosa, cantPecosa.reduce((a,b)=>a+b,0)]);
    aoa.push([]);

    filas.forEach(f => {
        aoa.push([f.seccion, f.alumnos, ...f.items, f.total]);
    });

    aoa.push([]);
    aoa.push(['TOTAL DISTRIBUIDO', '', ...totales, totales.reduce((a,b)=>a+b,0)]);

    const ws = XLSX.utils.aoa_to_sheet(aoa);
    ws['!cols'] = [{ wch: 14 }, { wch: 9 }, ...nombres.map(() => ({ wch: 22 })), { wch: 10 }];

    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Distribución');
    XLSX.writeFile(wb, 'Distribucion_' + fecha + '.xlsx');
}
</script>

<style>
    @media print {
        aside, header, .no-print { display: none !important; }
        html, body { background: white !important; margin: 0 !important; padding: 0 !important; }
        .flex.h-screen { display: block !important; height: auto !important; overflow: visible !important; }
        .flex-1.flex.flex-col { display: block !important; overflow: visible !important; }
        main { padding: 0 !important; overflow: visible !important; }
        .bg-white.rounded-2xl { border-radius: 0 !important; box-shadow: none !important; border: none !important; }
        .overflow-x-auto { overflow: visible !important; width: 100% !important; }
        table { width: 100% !important; table-layout: fixed !important; border-collapse: collapse !important; font-size: 6.5px !important; }
        th:first-child, td:first-child { width: 38px !important; }
        th, td { padding: 2px 1px !important; border: 0.5px solid #666 !important; word-break: break-word !important; }
        .sticky { position: static !important; }
        thead { display: table-header-group !important; }
        tfoot { display: table-footer-group !important; }
        @page { size: A3 landscape; margin: 6mm; }
    }
</style>
@endsection
