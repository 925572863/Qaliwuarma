@extends('layouts.app')
@section('title', 'Registrar PECOSA — Primaria')
@section('page-title', 'Registrar Productos PECOSA')
@section('breadcrumb', 'Pecosa Primaria › Nuevo registro')

@section('content')

@if($errors->any())
<div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-5 py-3 text-sm">
    <strong>Corrige los siguientes errores:</strong>
    <ul class="mt-1 list-disc list-inside">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('pecosa.primaria.store') }}" id="form-pecosa">
@csrf

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    {{-- Cabecera estilo PECOSA --}}
    <div class="bg-blue-600 px-6 py-3 flex items-center justify-between">
        <div>
            <p class="text-xs font-black text-white uppercase tracking-widest">Pecosa — Nivel Primaria</p>
            <p class="text-[10px] text-blue-200 mt-0.5">Rellena fila por fila igual que el documento físico</p>
        </div>
        <button type="button" onclick="agregarFila()"
                class="px-3 py-1.5 bg-white/20 hover:bg-white/30 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 transition-colors">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Agregar fila
        </button>
    </div>

    {{-- Tabla --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm border-collapse" id="tabla-pecosa">
            <thead>
                <tr class="bg-gray-800 text-white text-[10px] uppercase tracking-wide">
                    <th class="border border-gray-600 px-3 py-2.5 text-center w-8">#</th>
                    <th class="border border-gray-600 px-3 py-2.5 text-center w-20">CANT.</th>
                    <th class="border border-gray-600 px-3 py-2.5 text-center w-28">UNID.</th>
                    <th class="border border-gray-600 px-3 py-2.5 text-left min-w-[260px]">DESCRIPCIÓN DE PRODUCTOS</th>
                    <th class="border border-gray-600 px-3 py-2.5 text-left w-40">MARCAS</th>
                    <th class="border border-gray-600 px-3 py-2.5 text-center w-24">PRESENT.</th>
                    <th class="border border-gray-600 px-3 py-2.5 text-center w-24 bg-blue-800">VOLUMEN</th>
                    <th class="border border-gray-600 px-3 py-2.5 text-center w-32">LOTE / LOTES</th>
                    <th class="border border-gray-600 px-3 py-2.5 text-center w-36">VENC.</th>
                    <th class="border border-gray-600 px-2 py-2.5 text-center w-10"></th>
                </tr>
            </thead>
            <tbody id="cuerpo-tabla">
                {{-- Filas generadas por JS --}}
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="px-6 py-4 border-t border-gray-100 flex items-center justify-between bg-gray-50">
        <p class="text-xs text-gray-400">
            <span id="contador-filas">0</span> fila(s) · El volumen se calcula automáticamente (CANT × PRESENT.)
        </p>
        <div class="flex items-center gap-3">
            <a href="{{ route('pecosa.primaria.index') }}"
               class="px-4 py-2 border border-gray-300 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Guardar todos
            </button>
        </div>
    </div>
</div>
</form>

<script>
const UNIDADES = ['BOLSA','BOTELLA','HOJALATA','LATA','CAJA','UNIDAD','SACO'];
let contador = 0;

function opcionesUnid(seleccionada = '') {
    return UNIDADES.map(u =>
        `<option value="${u}" ${u === seleccionada ? 'selected' : ''}>${u}</option>`
    ).join('');
}

function agregarFila(data = {}) {
    contador++;
    const i = contador;
    const tr = document.createElement('tr');
    tr.id = `fila-${i}`;
    tr.className = 'hover:bg-blue-50 transition-colors';
    tr.innerHTML = `
        <td class="border border-gray-200 px-2 py-1.5 text-center text-xs text-gray-400 font-bold">${i}</td>
        <td class="border border-gray-200 px-1 py-1">
            <input type="number" name="filas[${i}][cant]" value="${data.cant ?? ''}"
                   min="1" required oninput="calcVol(${i})"
                   class="w-full px-2 py-1.5 border-0 text-center text-sm font-bold focus:outline-none focus:ring-2 focus:ring-blue-400 rounded bg-transparent"
                   placeholder="0">
        </td>
        <td class="border border-gray-200 px-1 py-1">
            <select name="filas[${i}][unid]" required
                    class="w-full px-2 py-1.5 border-0 text-center text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 rounded bg-transparent">
                <option value="">—</option>
                ${opcionesUnid(data.unid ?? '')}
            </select>
        </td>
        <td class="border border-gray-200 px-1 py-1">
            <input type="text" name="filas[${i}][descripcion]" value="${data.descripcion ?? ''}"
                   required maxlength="300"
                   class="w-full px-2 py-1.5 border-0 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-400 rounded bg-transparent"
                   style="text-transform:uppercase" placeholder="Nombre del producto">
        </td>
        <td class="border border-gray-200 px-1 py-1">
            <input type="text" name="filas[${i}][marca]" value="${data.marca ?? ''}"
                   maxlength="150"
                   class="w-full px-2 py-1.5 border-0 text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-400 rounded bg-transparent"
                   style="text-transform:uppercase" placeholder="Marca">
        </td>
        <td class="border border-gray-200 px-1 py-1">
            <input type="number" name="filas[${i}][presentacion]" value="${data.presentacion ?? '1.000'}"
                   step="0.001" min="0.001" required oninput="calcVol(${i})"
                   class="w-full px-2 py-1.5 border-0 text-center text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 rounded bg-transparent"
                   placeholder="1.000">
        </td>
        <td class="border border-gray-200 px-1 py-1 bg-blue-50">
            <input type="text" id="vol-${i}" readonly
                   class="w-full px-2 py-1.5 border-0 text-center text-sm font-bold text-blue-700 bg-transparent focus:outline-none"
                   placeholder="—">
        </td>
        <td class="border border-gray-200 px-1 py-1">
            <input type="text" name="filas[${i}][lote]" value="${data.lote ?? ''}"
                   maxlength="200"
                   class="w-full px-2 py-1.5 border-0 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 rounded bg-transparent"
                   placeholder="LT.00-0">
        </td>
        <td class="border border-gray-200 px-1 py-1">
            <input type="date" name="filas[${i}][fecha_vencimiento]" value="${data.fecha_vencimiento ?? ''}"
                   class="w-full px-2 py-1.5 border-0 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 rounded bg-transparent">
        </td>
        <td class="border border-gray-200 px-1 py-1 text-center">
            <button type="button" onclick="eliminarFila(${i})"
                    class="text-gray-300 hover:text-red-500 transition-colors p-1 rounded">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </td>
    `;
    document.getElementById('cuerpo-tabla').appendChild(tr);
    actualizarContador();
    calcVol(i);
    tr.querySelector('input[type=number]').focus();
}

function eliminarFila(i) {
    const tr = document.getElementById(`fila-${i}`);
    if (tr) tr.remove();
    actualizarContador();
}

function calcVol(i) {
    const cant = parseFloat(document.querySelector(`[name="filas[${i}][cant]"]`)?.value) || 0;
    const pres = parseFloat(document.querySelector(`[name="filas[${i}][presentacion]"]`)?.value) || 0;
    const vol  = document.getElementById(`vol-${i}`);
    if (vol) vol.value = cant > 0 && pres > 0 ? (cant * pres).toFixed(3) : '';
}

function actualizarContador() {
    const n = document.getElementById('cuerpo-tabla').querySelectorAll('tr').length;
    document.getElementById('contador-filas').textContent = n;
}

// Arrancar con 5 filas vacías
for (let k = 0; k < 5; k++) agregarFila();

// Tab en último campo → nueva fila automática
document.getElementById('tabla-pecosa').addEventListener('keydown', function(e) {
    if (e.key === 'Tab' && !e.shiftKey) {
        const inputs = Array.from(this.querySelectorAll('input:not([readonly]),select'));
        if (document.activeElement === inputs[inputs.length - 1]) {
            e.preventDefault();
            agregarFila();
        }
    }
});
</script>

@endsection
