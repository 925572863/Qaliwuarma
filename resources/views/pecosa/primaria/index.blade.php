@extends('layouts.app')
@section('title', 'Pecosa Primaria')
@section('page-title', 'Pecosa — Nivel Primaria')
@section('breadcrumb', 'Registro de productos alimentarios')

@section('header-actions')
    <div class="flex items-center gap-2">
        {{-- Importar Excel --}}
        <button type="button" onclick="document.getElementById('modal-excel-primaria').classList.remove('hidden')"
                class="inline-flex items-center space-x-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <span>Importar Excel</span>
        </button>
        <a href="{{ route('pecosa.primaria.create') }}"
           class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Agregar Producto</span>
        </a>
    </div>

    {{-- Modal importar Excel --}}
    <div id="modal-excel-primaria" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
            <h3 class="text-base font-bold text-gray-800 mb-1">Importar desde Excel</h3>
            <p class="text-xs text-gray-500 mb-4">El archivo debe tener las columnas en este orden:<br>
                <span class="font-mono text-gray-700">CANT | UNID | DESCRIPCIÓN | MARCA | PRESENTACIÓN | LOTE</span>
            </p>
            <form method="POST" action="{{ route('pecosa.primaria.importar') }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Archivo Excel (.xlsx, .xls)</label>
                    <input type="file" name="archivo" accept=".xlsx,.xls,.csv" required
                           class="w-full text-sm text-gray-600 border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-excel-primaria').classList.add('hidden')"
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
    <form method="GET" action="{{ route('pecosa.primaria.index') }}" class="flex gap-3">
        <div class="flex-1 relative">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
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
            <a href="{{ route('pecosa.primaria.index') }}"
               class="text-sm text-gray-500 hover:text-gray-700 font-medium py-2.5 px-3">Limpiar</a>
        @endif
    </form>
</div>

{{-- Tabla principal --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
        <div>
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Productos PECOSA — Primaria</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ $items->total() }} producto(s) registrado(s)</p>
        </div>
    </div>

    @if($items->isEmpty())
        <div class="py-16 text-center">
            <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-400 text-sm">No hay productos registrados.</p>
            <a href="{{ route('pecosa.primaria.create') }}"
               class="mt-3 inline-block bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                Agregar primer producto
            </a>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-800 text-white text-xs uppercase tracking-wider">
                        <th class="px-4 py-3 text-center border border-gray-600 w-16">CANT.</th>
                        <th class="px-4 py-3 text-center border border-gray-600 w-20">UNID.</th>
                        <th class="px-4 py-3 text-left border border-gray-600">DESCRIPCIÓN DE PRODUCTOS</th>
                        <th class="px-4 py-3 text-left border border-gray-600">MARCAS</th>
                        <th class="px-4 py-3 text-center border border-gray-600 w-24">PRESENT.</th>
                        <th class="px-4 py-3 text-center border border-gray-600 w-28">VOLUMEN</th>
                        <th class="px-4 py-3 text-center border border-gray-600">LOTE / LOTES</th>
                        <th class="px-4 py-3 text-center border border-gray-600 w-24">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr class="border-b border-gray-200 hover:bg-blue-50 transition-colors">
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
                                    <a href="{{ route('pecosa.primaria.edit', $item) }}"
                                       class="text-amber-600 hover:text-amber-800 font-medium text-xs">Editar</a>
                                    <form method="POST" action="{{ route('pecosa.primaria.destroy', $item) }}"
                                          onsubmit="return confirm('¿Eliminar este producto?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">
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
@endsection
