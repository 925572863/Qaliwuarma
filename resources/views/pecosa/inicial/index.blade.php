@extends('layouts.app')
@section('title', 'Pecosa Inicial')
@section('page-title', 'Pecosa — Nivel Inicial')
@section('breadcrumb', 'Registro de productos alimentarios')

@section('header-actions')
    <div class="flex items-center gap-2">
        {{-- Importar Excel --}}
        <button type="button" onclick="document.getElementById('modal-excel-inicial').classList.remove('hidden')"
                class="inline-flex items-center space-x-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <span>Importar Excel</span>
        </button>
<a href="{{ route('pecosa.inicial.create') }}"
           class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Agregar Producto</span>
        </a>
    </div>

    {{-- Modal importar Excel --}}
    <div id="modal-excel-inicial" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-base font-bold text-gray-800 mb-1">Importar desde Excel</h3>
            <p class="text-xs text-gray-500 mb-4">El archivo debe tener las columnas en este orden:<br>
                <span class="font-mono text-gray-700">CANT | UNID | DESCRIPCIÓN | MARCA | PRESENTACIÓN | LOTE</span>
            </p>
            <form method="POST" action="{{ route('pecosa.inicial.importar') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Archivo Excel (.xlsx, .xls)</label>
                    <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required
                           class="w-full text-sm text-gray-600 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-excel-inicial').classList.add('hidden')"
                            class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                        Cancelar
                    </button>
                    <button type="submit"
                            class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-bold">
                        Importar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('content')

{{-- Buscador --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 mb-5">
    <form method="GET" action="{{ route('pecosa.inicial.index') }}" class="flex gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Buscar por descripción, marca o lote…">
        </div>
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors">
            Buscar
        </button>
        @if(request('buscar'))
            <a href="{{ route('pecosa.inicial.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 font-medium py-2.5 px-3">Limpiar</a>
        @endif
    </form>
</div>

{{-- Tabla --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Productos PECOSA — Inicial</h2>
        <p class="text-xs text-gray-400 mt-0.5">{{ $items->total() }} producto(s) registrado(s)</p>
    </div>

    @if($items->isEmpty())
        <div class="py-16 text-center">
            <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-400 text-sm">No hay productos registrados.</p>
    <a href="{{ route('pecosa.inicial.create') }}"
               class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Agregar primer producto
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-yellow-500 text-white text-xs uppercase tracking-wider">
                        <th class="px-4 py-3 text-center border border-yellow-400 w-16">CANT.</th>
                        <th class="px-4 py-3 text-center border border-yellow-400 w-20">UNID.</th>
                        <th class="px-4 py-3 text-left border border-yellow-400">DESCRIPCIÓN DE PRODUCTOS</th>
                        <th class="px-4 py-3 text-left border border-yellow-400">MARCAS</th>
                        <th class="px-4 py-3 text-center border border-yellow-400 w-24">PRESENT.</th>
                        <th class="px-4 py-3 text-center border border-yellow-400 w-28">VOLUMEN</th>
                        <th class="px-4 py-3 text-center border border-yellow-400">LOTE / LOTES</th>
                        <th class="px-4 py-3 text-center border border-yellow-400 w-24">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr class="border-b border-gray-200 hover:bg-yellow-50 transition-colors">
                            <td class="px-4 py-2.5 text-center font-semibold text-gray-800 border border-gray-200">
                                {{ number_format($item->cant) }}
                            </td>
                            <td class="px-4 py-2.5 text-center text-gray-600 border border-gray-200">
                                {{ $item->unid }}
                            </td>
                            <td class="px-4 py-2.5 text-gray-800 font-medium border border-gray-200 uppercase">
                                {{ $item->descripcion }}
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 border border-gray-200 uppercase">
                                {{ $item->marca ?? '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-center text-gray-700 border border-gray-200">
                                {{ number_format($item->presentacion, 3) }}
                            </td>
                            <td class="px-4 py-2.5 text-center font-semibold text-gray-800 border border-gray-200">
                                {{ number_format($item->volumen, 3) }}
                            </td>
                            <td class="px-4 py-2.5 text-center text-gray-600 border border-gray-200 text-xs">
                                {{ $item->lote ?? '—' }}
                            </td>
                            <td class="px-4 py-2.5 text-center border border-gray-200">
                                <div class="inline-flex items-center space-x-2">
                                    <a href="{{ route('pecosa.inicial.edit', $item) }}"
                                       class="text-amber-600 hover:text-amber-800 font-medium text-xs">Editar</a>
                                    <form method="POST" action="{{ route('pecosa.inicial.destroy', $item) }}"
                                          onsubmit="return confirm('¿Eliminar este producto?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="text-red-500 hover:text-red-700 text-xs font-medium">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $items->links() }}
            </div>
        @endif
    @endif
</div>

{{-- Modal Análisis Nutricional IA --}}
<div id="modal-nutricion" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col">

        {{-- Paso 1: Selección --}}
        <div id="paso-seleccion" class="flex flex-col flex-1 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-800">Análisis Nutricional con IA</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Selecciona productos y describe la receta</p>
                </div>
                <button onclick="cerrarModalIA()" class="text-gray-400 hover:text-gray-600 text-xl font-bold leading-none">&times;</button>
            </div>
            <div class="flex-1 overflow-auto px-6 py-4 space-y-4">
                {{-- Selección de productos --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-sm font-semibold text-gray-700">Productos a analizar</label>
                        <button type="button" onclick="seleccionarTodos()" class="text-xs text-blue-600 hover:underline">Seleccionar todos</button>
                    </div>
                    <div class="border border-gray-200 rounded-lg max-h-48 overflow-y-auto divide-y divide-gray-100">
                        @foreach($items as $item)
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" class="producto-check w-4 h-4 text-purple-600 rounded"
                                   value="{{ $item->id }}" checked>
                            <span class="text-sm text-gray-800 uppercase">{{ $item->descripcion }}</span>
                            <span class="ml-auto text-xs text-gray-400">{{ $item->cant }} unid.</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                {{-- Receta --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Descripción de la receta <span class="text-gray-400 font-normal">(opcional)</span></label>
                    <textarea id="receta-texto" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                              placeholder="Ej: Mazamorra con leche y canela, hervir 20 minutos…"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                <button type="button" onclick="cerrarModalIA()"
                        class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">Cancelar</button>
                <button type="button" onclick="enviarAnalisis()"
                        class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-bold">
                    Analizar con IA
                </button>
            </div>
        </div>

        {{-- Paso 2: Resultado --}}
        <div id="paso-resultado" class="hidden flex flex-col flex-1 overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h3 class="text-base font-bold text-gray-800">Resultado Nutricional — IA</h3>
                    <p class="text-xs text-green-600 mt-0.5">✓ Guardado automáticamente para Predicciones</p>
                </div>
                <button onclick="cerrarModalIA()" class="text-gray-400 hover:text-gray-600 text-xl font-bold leading-none">&times;</button>
            </div>
            <div id="nutricion-loading" class="hidden flex-1 flex flex-col items-center justify-center py-16 text-gray-400">
                <svg class="animate-spin w-8 h-8 mb-3 text-purple-500" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span class="text-sm">Consultando IA…</span>
            </div>
            <div id="nutricion-error" class="hidden px-6 py-8 text-red-500 text-sm" style="white-space:pre-wrap"></div>
            <div id="nutricion-result" class="hidden flex-1 overflow-auto px-6 py-4">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-purple-600 text-white text-xs uppercase">
                            <th class="px-3 py-2 text-left border border-purple-500">Producto</th>
                            <th class="px-3 py-2 text-center border border-purple-500">g/ración</th>
                            <th class="px-3 py-2 text-center border border-purple-500">kcal/ración</th>
                            <th class="px-3 py-2 text-center border border-purple-500">Proteínas</th>
                            <th class="px-3 py-2 text-center border border-purple-500">Carboh.</th>
                            <th class="px-3 py-2 text-left border border-purple-500">Preparación</th>
                            <th class="px-3 py-2 text-center border border-purple-500">Tiempo</th>
                        </tr>
                    </thead>
                    <tbody id="nutricion-tbody"></tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 flex justify-between items-center">
                <button onclick="volverSeleccion()" class="text-sm text-gray-500 hover:text-gray-700">← Volver</button>
                <a href="{{ route('prediccion.index', ['nivel' => 'inicial']) }}"
                   class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold">
                    Ver en Predicciones →
                </a>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function calcularNutricion() {
    document.getElementById('modal-nutricion').classList.remove('hidden');
    document.getElementById('paso-seleccion').classList.remove('hidden');
    document.getElementById('paso-resultado').classList.add('hidden');
}
function cerrarModalIA() {
    document.getElementById('modal-nutricion').classList.add('hidden');
}
function volverSeleccion() {
    document.getElementById('paso-seleccion').classList.remove('hidden');
    document.getElementById('paso-resultado').classList.add('hidden');
}
function seleccionarTodos() {
    document.querySelectorAll('.producto-check').forEach(c => c.checked = true);
}
function enviarAnalisis() {
    const checks  = [...document.querySelectorAll('.producto-check:checked')].map(c => c.value);
    const receta  = document.getElementById('receta-texto').value;
    const loading = document.getElementById('nutricion-loading');
    const error   = document.getElementById('nutricion-error');
    const result  = document.getElementById('nutricion-result');
    const tbody   = document.getElementById('nutricion-tbody');

    document.getElementById('paso-seleccion').classList.add('hidden');
    document.getElementById('paso-resultado').classList.remove('hidden');
    loading.classList.remove('hidden');
    error.classList.add('hidden');
    result.classList.add('hidden');
    tbody.innerHTML = '';

    const body = new FormData();
    body.append('_token', '{{ csrf_token() }}');
    body.append('receta', receta);
    checks.forEach(id => body.append('productos[]', id));

    fetch('{{ route("pecosa.inicial.nutricion") }}', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
        body,
    })
    .then(r => r.json())
    .then(data => {
        loading.classList.add('hidden');
        if (!Array.isArray(data) || data.length === 0) {
            error.textContent = (data.error ?? 'Error') + (data.debug ? '\n\n' + JSON.stringify(data.debug, null, 2) : '');
            error.classList.remove('hidden');
            return;
        }
        data.forEach(item => {
            const tr = document.createElement('tr');
            tr.className = 'border-b border-gray-200 hover:bg-purple-50';
            tr.innerHTML = `
                <td class="px-3 py-2 font-medium text-gray-800 border border-gray-200 uppercase">${item.descripcion ?? '—'}</td>
                <td class="px-3 py-2 text-center font-semibold text-purple-700 border border-gray-200">${item.gramos_racion ?? '—'} g</td>
                <td class="px-3 py-2 text-center font-semibold text-orange-600 border border-gray-200">${item.calorias_racion ?? '—'} kcal</td>
                <td class="px-3 py-2 text-center text-gray-700 border border-gray-200">${item.proteinas_racion ?? '—'} g</td>
                <td class="px-3 py-2 text-center text-gray-700 border border-gray-200">${item.carbohidratos_racion ?? '—'} g</td>
                <td class="px-3 py-2 text-gray-700 border border-gray-200 text-xs leading-relaxed">${item.preparacion ?? '—'}</td>
                <td class="px-3 py-2 text-center text-gray-600 border border-gray-200">${item.tiempo_preparacion ?? '—'}</td>
            `;
            tbody.appendChild(tr);
        });
        result.classList.remove('hidden');
    })
    .catch(() => {
        loading.classList.add('hidden');
        error.textContent = 'Error de conexión.';
        error.classList.remove('hidden');
    });
}
</script>
@endpush

@endsection
