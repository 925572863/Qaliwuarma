<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Productos Vencidos</title>
    <script src="{{ '/js/tailwind.min.js' }}"></script>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; background: white; }

        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 0; }
        }
    </style>
</head>
<body class="p-8 bg-white">

    {{-- Botón imprimir (solo en pantalla) --}}
    <div class="no-print flex justify-end mb-4 gap-3">
        <button onclick="window.print()"
                class="px-5 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg hover:bg-gray-900 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimir
        </button>
        <a href="{{ route('vencidos.index') }}"
           class="px-5 py-2 border border-gray-300 text-gray-600 text-sm font-medium rounded-lg hover:bg-gray-50">
            ← Volver
        </a>
    </div>

    {{-- Encabezado del reporte --}}
    <div class="text-center border-b-2 border-gray-800 pb-4 mb-6">
        <p class="text-xs uppercase tracking-widest text-gray-500">Comité de Alimentación Escolar — CAE</p>
        <h2 class="text-sm font-bold text-gray-700 mt-0.5">I.E. 14008 Leonor Cerna de Valdiviezo — Piura</h2>
        <h1 class="text-xl font-black text-gray-900 mt-2 uppercase tracking-wide">Reporte de Control de Vencimientos</h1>
        <p class="text-xs text-gray-500 mt-1">Programa PAE &nbsp;|&nbsp; Fecha de emisión: {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    {{-- Resumen general --}}
    <div class="flex gap-6 mb-6 justify-center">
        <div class="text-center border border-red-300 bg-red-50 rounded-lg px-6 py-3">
            <p class="text-2xl font-black text-red-700">{{ $vencidos->count() }}</p>
            <p class="text-[10px] uppercase font-bold text-red-500 tracking-wide">Vencidos</p>
        </div>
        <div class="text-center border border-yellow-300 bg-yellow-50 rounded-lg px-6 py-3">
            <p class="text-2xl font-black text-yellow-700">{{ $porVencer->count() }}</p>
            <p class="text-[10px] uppercase font-bold text-yellow-500 tracking-wide">Por vencer (30 días)</p>
        </div>
        <div class="text-center border border-green-300 bg-green-50 rounded-lg px-6 py-3">
            <p class="text-2xl font-black text-green-700">{{ $vigentes->count() }}</p>
            <p class="text-[10px] uppercase font-bold text-green-500 tracking-wide">Vigentes</p>
        </div>
    </div>

    {{-- TABLA VENCIDOS --}}
    @if($vencidos->count())
    <div class="mb-6">
        <div class="bg-red-700 text-white text-xs font-bold uppercase px-3 py-1.5 tracking-wide rounded-t">
            ● Productos Vencidos ({{ $vencidos->count() }})
        </div>
        <table class="w-full border-collapse border border-red-300 text-xs">
            <thead>
                <tr class="bg-red-100 text-gray-700 text-[10px] uppercase">
                    <th class="border border-red-300 px-3 py-2 text-left">N°</th>
                    <th class="border border-red-300 px-3 py-2 text-left">Descripción</th>
                    <th class="border border-red-300 px-3 py-2 text-center">Nivel</th>
                    <th class="border border-red-300 px-3 py-2 text-left">Marca</th>
                    <th class="border border-red-300 px-3 py-2 text-left">Lote</th>
                    <th class="border border-red-300 px-3 py-2 text-center">Cant.</th>
                    <th class="border border-red-300 px-3 py-2 text-center">Unid.</th>
                    <th class="border border-red-300 px-3 py-2 text-center">Fecha Venc.</th>
                    <th class="border border-red-300 px-3 py-2 text-center">Días vencido</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vencidos as $i => $p)
                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-red-50' }}">
                    <td class="border border-red-200 px-3 py-1.5 text-center text-gray-400">{{ $i + 1 }}</td>
                    <td class="border border-red-200 px-3 py-1.5 font-semibold">{{ $p['descripcion'] }}</td>
                    <td class="border border-red-200 px-3 py-1.5 text-center">{{ $p['nivel'] }}</td>
                    <td class="border border-red-200 px-3 py-1.5 text-gray-500">{{ $p['marca'] ?? '—' }}</td>
                    <td class="border border-red-200 px-3 py-1.5 text-gray-500">{{ $p['lote'] ?? '—' }}</td>
                    <td class="border border-red-200 px-3 py-1.5 text-center font-bold">{{ $p['cant'] }}</td>
                    <td class="border border-red-200 px-3 py-1.5 text-center">{{ $p['unid'] }}</td>
                    <td class="border border-red-200 px-3 py-1.5 text-center font-bold text-red-700">{{ $p['fecha_vencimiento']->format('d/m/Y') }}</td>
                    <td class="border border-red-200 px-3 py-1.5 text-center font-bold text-red-700">{{ $hoy->diffInDays($p['fecha_vencimiento']) }} días</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- TABLA POR VENCER --}}
    @if($porVencer->count())
    <div class="mb-6">
        <div class="bg-yellow-500 text-white text-xs font-bold uppercase px-3 py-1.5 tracking-wide rounded-t">
            ● Por Vencer en 30 días ({{ $porVencer->count() }})
        </div>
        <table class="w-full border-collapse border border-yellow-300 text-xs">
            <thead>
                <tr class="bg-yellow-100 text-gray-700 text-[10px] uppercase">
                    <th class="border border-yellow-300 px-3 py-2 text-left">N°</th>
                    <th class="border border-yellow-300 px-3 py-2 text-left">Descripción</th>
                    <th class="border border-yellow-300 px-3 py-2 text-center">Nivel</th>
                    <th class="border border-yellow-300 px-3 py-2 text-left">Marca</th>
                    <th class="border border-yellow-300 px-3 py-2 text-left">Lote</th>
                    <th class="border border-yellow-300 px-3 py-2 text-center">Cant.</th>
                    <th class="border border-yellow-300 px-3 py-2 text-center">Unid.</th>
                    <th class="border border-yellow-300 px-3 py-2 text-center">Fecha Venc.</th>
                    <th class="border border-yellow-300 px-3 py-2 text-center">Días restantes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($porVencer as $i => $p)
                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-yellow-50' }}">
                    <td class="border border-yellow-200 px-3 py-1.5 text-center text-gray-400">{{ $i + 1 }}</td>
                    <td class="border border-yellow-200 px-3 py-1.5 font-semibold">{{ $p['descripcion'] }}</td>
                    <td class="border border-yellow-200 px-3 py-1.5 text-center">{{ $p['nivel'] }}</td>
                    <td class="border border-yellow-200 px-3 py-1.5 text-gray-500">{{ $p['marca'] ?? '—' }}</td>
                    <td class="border border-yellow-200 px-3 py-1.5 text-gray-500">{{ $p['lote'] ?? '—' }}</td>
                    <td class="border border-yellow-200 px-3 py-1.5 text-center font-bold">{{ $p['cant'] }}</td>
                    <td class="border border-yellow-200 px-3 py-1.5 text-center">{{ $p['unid'] }}</td>
                    <td class="border border-yellow-200 px-3 py-1.5 text-center font-bold text-yellow-700">{{ $p['fecha_vencimiento']->format('d/m/Y') }}</td>
                    <td class="border border-yellow-200 px-3 py-1.5 text-center font-bold text-yellow-700">{{ $hoy->diffInDays($p['fecha_vencimiento']) }} días</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- TABLA VIGENTES --}}
    @if($vigentes->count())
    <div class="mb-6">
        <div class="bg-green-700 text-white text-xs font-bold uppercase px-3 py-1.5 tracking-wide rounded-t">
            ● Vigentes ({{ $vigentes->count() }})
        </div>
        <table class="w-full border-collapse border border-green-300 text-xs">
            <thead>
                <tr class="bg-green-100 text-gray-700 text-[10px] uppercase">
                    <th class="border border-green-300 px-3 py-2 text-left">N°</th>
                    <th class="border border-green-300 px-3 py-2 text-left">Descripción</th>
                    <th class="border border-green-300 px-3 py-2 text-center">Nivel</th>
                    <th class="border border-green-300 px-3 py-2 text-left">Marca</th>
                    <th class="border border-green-300 px-3 py-2 text-left">Lote</th>
                    <th class="border border-green-300 px-3 py-2 text-center">Cant.</th>
                    <th class="border border-green-300 px-3 py-2 text-center">Unid.</th>
                    <th class="border border-green-300 px-3 py-2 text-center">Fecha Venc.</th>
                    <th class="border border-green-300 px-3 py-2 text-center">Días restantes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vigentes as $i => $p)
                <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-green-50' }}">
                    <td class="border border-green-200 px-3 py-1.5 text-center text-gray-400">{{ $i + 1 }}</td>
                    <td class="border border-green-200 px-3 py-1.5 font-semibold">{{ $p['descripcion'] }}</td>
                    <td class="border border-green-200 px-3 py-1.5 text-center">{{ $p['nivel'] }}</td>
                    <td class="border border-green-200 px-3 py-1.5 text-gray-500">{{ $p['marca'] ?? '—' }}</td>
                    <td class="border border-green-200 px-3 py-1.5 text-gray-500">{{ $p['lote'] ?? '—' }}</td>
                    <td class="border border-green-200 px-3 py-1.5 text-center font-bold">{{ $p['cant'] }}</td>
                    <td class="border border-green-200 px-3 py-1.5 text-center">{{ $p['unid'] }}</td>
                    <td class="border border-green-200 px-3 py-1.5 text-center font-bold text-green-700">{{ $p['fecha_vencimiento']->format('d/m/Y') }}</td>
                    <td class="border border-green-200 px-3 py-1.5 text-center font-bold text-green-700">{{ $hoy->diffInDays($p['fecha_vencimiento']) }} días</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($vencidos->count() === 0 && $porVencer->count() === 0 && $vigentes->count() === 0)
    <p class="text-center text-gray-400 text-sm py-10">No hay productos con fecha de vencimiento registrada.</p>
    @endif

    {{-- Firma --}}
    <div class="mt-12 grid grid-cols-2 gap-16 text-center text-xs text-gray-500">
        <div>
            <div class="border-t border-gray-400 pt-2 mt-8">Responsable CAE</div>
        </div>
        <div>
            <div class="border-t border-gray-400 pt-2 mt-8">Docente / Director</div>
        </div>
    </div>

</body>
</html>

