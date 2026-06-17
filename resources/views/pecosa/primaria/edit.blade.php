@extends('layouts.app')
@section('title', 'Editar Producto — Primaria')
@section('page-title', 'Editar Producto')
@section('breadcrumb', 'Pecosa Primaria › ' . $item->descripcion)

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('pecosa.primaria.update', $item) }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Editar Producto</h2>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-3 gap-5">

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">CANT. <span class="text-red-500">*</span></label>
                    <input type="number" name="cant" id="cant" value="{{ old('cant', $item->cant) }}" min="1"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('cant') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('cant')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">UNID. <span class="text-red-500">*</span></label>
                    <select name="unid"
                            class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 border-gray-300">
                        @foreach(['BOLSA','BOTELLA','HOJALATA','LATA','CAJA','UNIDAD','SACO'] as $u)
                            <option value="{{ $u }}" {{ old('unid', $item->unid) === $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">PRESENT. <span class="text-red-500">*</span></label>
                    <input type="number" name="presentacion" id="presentacion"
                           value="{{ old('presentacion', $item->presentacion) }}" step="0.001" min="0.001"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 border-gray-300">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">DESCRIPCIÓN <span class="text-red-500">*</span></label>
                    <input type="text" name="descripcion" value="{{ old('descripcion', $item->descripcion) }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('descripcion') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('descripcion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">VOLUMEN (auto)</label>
                    <input type="text" id="volumen-preview" readonly
                           value="{{ number_format($item->volumen, 3) }}"
                           class="w-full px-3 py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-sm text-blue-700 font-bold">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">MARCAS</label>
                    <input type="text" name="marca" value="{{ old('marca', $item->marca) }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">LOTE / LOTES</label>
                    <input type="text" name="lote" value="{{ old('lote', $item->lote) }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">FECHA DE VENCIMIENTO</label>
                    <input type="date" name="fecha_vencimiento"
                           value="{{ old('fecha_vencimiento', $item->fecha_vencimiento?->format('Y-m-d')) }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

            </div>
        </div>

        <div class="flex items-center space-x-3">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-6 py-2.5 rounded-lg transition-colors shadow-sm">
                Guardar Cambios
            </button>
            <a href="{{ route('pecosa.primaria.index') }}"
               class="text-sm text-gray-600 hover:text-gray-800 font-medium px-4 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function calcularVolumen() {
        const cant = parseFloat(document.getElementById('cant').value) || 0;
        const pres = parseFloat(document.getElementById('presentacion').value) || 0;
        document.getElementById('volumen-preview').value = (cant * pres).toFixed(3);
    }
    document.getElementById('cant').addEventListener('input', calcularVolumen);
    document.getElementById('presentacion').addEventListener('input', calcularVolumen);
</script>
@endpush
@endsection
