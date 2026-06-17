@extends('layouts.app')
@section('title', 'Lista de Compras — Inicial')
@section('page-title', 'Lista de Compras Adicionales')
@section('breadcrumb', 'Productos a comprar para cocinar — Nivel Inicial')

@section('content')

{{-- Tarjetas resumen --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center space-x-3">
        <div class="w-10 h-10 bg-orange-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-orange-600">{{ $pendientes }}</p>
            <p class="text-xs text-gray-500">Pendientes</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center space-x-3">
        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-green-600">{{ $comprados }}</p>
            <p class="text-xs text-gray-500">Comprados</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center space-x-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-blue-600">S/ {{ number_format($totalPresupuesto, 2) }}</p>
            <p class="text-xs text-gray-500">Presupuesto total</p>
        </div>
    </div>
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center space-x-3">
        <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-2xl font-black text-red-600">S/ {{ number_format($totalPendiente, 2) }}</p>
            <p class="text-xs text-gray-500">Por gastar</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

    {{-- Cabecera --}}
    <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-gradient-to-r from-green-50 to-transparent">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Lista de Compras Adicionales</h2>
            <p class="text-xs text-gray-500 mt-0.5">Ingredientes y productos para preparar los alimentos del nivel Inicial</p>
        </div>
        <div class="flex items-center gap-2">
            {{-- Buscador --}}
            <form method="GET" action="{{ route('pecosa.inicial.compras') }}" class="flex gap-2">
                <input type="text" name="buscar" value="{{ $buscar }}" placeholder="Buscar producto..."
                       class="px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-300 w-48">
                <button type="submit" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm text-gray-600 transition-colors">
                    Buscar
                </button>
            </form>
            {{-- Limpiar comprados --}}
            @if($comprados > 0)
            <form method="POST" action="{{ route('pecosa.inicial.compras.limpiar') }}"
                  onsubmit="return confirm('¿Eliminar todos los productos ya comprados?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg text-sm font-medium transition-colors flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Limpiar comprados
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Formulario agregar --}}
    <div class="p-5 border-b border-gray-100 bg-gray-50">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Agregar producto a comprar</p>
        <form method="POST" action="{{ route('pecosa.inicial.compras.store') }}">
            @csrf
            @if($errors->any())
                <div class="mb-3 text-xs text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-2">
                    {{ $errors->first() }}
                </div>
            @endif
            <div class="flex flex-wrap gap-2 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-[10px] text-gray-500 uppercase mb-1">Descripción *</label>
                    <input type="text" name="descripcion" value="{{ old('descripcion') }}" required
                           placeholder="Ej: SAL, CEBOLLA, AJO..."
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-300 uppercase"
                           style="text-transform:uppercase">
                </div>
                <div class="w-28">
                    <label class="block text-[10px] text-gray-500 uppercase mb-1">Cantidad *</label>
                    <input type="number" name="cantidad" value="{{ old('cantidad', 1) }}" required min="0.01" step="0.01"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-300 text-center">
                </div>
                <div class="w-32">
                    <label class="block text-[10px] text-gray-500 uppercase mb-1">Unidad *</label>
                    <select name="unidad" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                        @foreach(['KG','BOLSA','LITRO','UNIDAD','DOCENA','LATA','BOTELLA','SACO','CAJA','ATADO'] as $u)
                            <option value="{{ $u }}" {{ old('unidad') === $u ? 'selected' : '' }}>{{ $u }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-36">
                    <label class="block text-[10px] text-gray-500 uppercase mb-1">Precio unit. (S/)</label>
                    <input type="number" name="precio_unitario" value="{{ old('precio_unitario') }}" min="0" step="0.01"
                           placeholder="0.00"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-300 text-center">
                </div>
                <div class="flex-1 min-w-[160px]">
                    <label class="block text-[10px] text-gray-500 uppercase mb-1">Nota (opcional)</label>
                    <input type="text" name="nota" value="{{ old('nota') }}"
                           placeholder="Ej: Para preparar arroz con frijol"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                </div>
                <div>
                    <button type="submit"
                            class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Agregar
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Lista de productos --}}
    @if($items->isEmpty())
        <div class="py-16 text-center">
            <svg class="w-12 h-12 text-gray-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <p class="text-gray-400 text-sm">No hay productos en la lista aún.</p>
            <p class="text-gray-300 text-xs mt-1">Agrega los ingredientes que necesitas comprar.</p>
        </div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($items as $item)
            <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition-colors group {{ $item->estado === 'comprado' ? 'opacity-60' : '' }}"
                 id="item-{{ $item->id }}">

                {{-- Checkbox estado --}}
                <button onclick="toggleEstado({{ $item->id }})"
                        class="flex-shrink-0 w-6 h-6 rounded-full border-2 flex items-center justify-center transition-all
                               {{ $item->estado === 'comprado' ? 'bg-green-500 border-green-500' : 'border-gray-300 hover:border-green-400' }}"
                        title="{{ $item->estado === 'comprado' ? 'Marcar como pendiente' : 'Marcar como comprado' }}">
                    @if($item->estado === 'comprado')
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                    @endif
                </button>

                {{-- Descripción + nota --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 {{ $item->estado === 'comprado' ? 'line-through text-gray-400' : '' }}">
                        {{ $item->descripcion }}
                    </p>
                    @if($item->nota)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $item->nota }}</p>
                    @endif
                </div>

                {{-- Cantidad + unidad --}}
                <div class="text-center flex-shrink-0 w-24">
                    <span class="text-sm font-bold text-gray-700">{{ number_format($item->cantidad, 2) }}</span>
                    <span class="text-xs text-gray-400 ml-1">{{ $item->unidad }}</span>
                </div>

                {{-- Precio --}}
                <div class="text-center flex-shrink-0 w-28">
                    @if($item->precio_unitario)
                        <p class="text-xs text-gray-400">S/ {{ number_format($item->precio_unitario, 2) }} c/u</p>
                        <p class="text-sm font-bold text-blue-600">S/ {{ number_format($item->cantidad * $item->precio_unitario, 2) }}</p>
                    @else
                        <span class="text-xs text-gray-300">—</span>
                    @endif
                </div>

                {{-- Estado badge --}}
                <div class="flex-shrink-0 w-24 text-center">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase
                        {{ $item->estado === 'comprado' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                        {{ $item->estado === 'comprado' ? 'Comprado' : 'Pendiente' }}
                    </span>
                </div>

                {{-- Acciones --}}
                <div class="flex-shrink-0 flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onclick="abrirEditar({{ json_encode($item) }})"
                            class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <form method="POST" action="{{ route('pecosa.inicial.compras.destroy', $item->id) }}"
                          onsubmit="return confirm('¿Eliminar este producto?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        <div class="p-4 border-t border-gray-100">
            {{ $items->links() }}
        </div>
    @endif

    @if(session('success'))
    <div id="toast" class="fixed bottom-5 right-5 bg-green-600 text-white px-5 py-3 rounded-xl shadow-lg text-sm font-medium z-50 flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ session('success') }}
    </div>
    <script>setTimeout(() => document.getElementById('toast')?.remove(), 3000);</script>
    @endif
</div>

{{-- Modal editar --}}
<div id="modal-editar" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center">
            <h3 class="text-base font-bold text-gray-800">Editar producto</h3>
            <button onclick="cerrarEditar()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="form-editar" method="POST">
            @csrf @method('PUT')
            <div class="p-5 space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1 uppercase">Descripción</label>
                    <input type="text" name="descripcion" id="edit-descripcion" required
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-300 uppercase"
                           style="text-transform:uppercase">
                </div>
                <div class="flex gap-3">
                    <div class="w-1/3">
                        <label class="block text-xs text-gray-500 mb-1 uppercase">Cantidad</label>
                        <input type="number" name="cantidad" id="edit-cantidad" required min="0.01" step="0.01"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-300 text-center">
                    </div>
                    <div class="w-1/3">
                        <label class="block text-xs text-gray-500 mb-1 uppercase">Unidad</label>
                        <select name="unidad" id="edit-unidad" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                            @foreach(['KG','BOLSA','LITRO','UNIDAD','DOCENA','LATA','BOTELLA','SACO','CAJA','ATADO'] as $u)
                                <option value="{{ $u }}">{{ $u }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-1/3">
                        <label class="block text-xs text-gray-500 mb-1 uppercase">Precio (S/)</label>
                        <input type="number" name="precio_unitario" id="edit-precio" min="0" step="0.01"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-300 text-center">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1 uppercase">Nota</label>
                    <input type="text" name="nota" id="edit-nota"
                           class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-300">
                </div>
            </div>
            <div class="p-5 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" onclick="cerrarEditar()"
                        class="px-4 py-2 border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition-colors">
                    Guardar cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleEstado(id) {
    fetch(`/pecosa/inicial/compras/${id}/estado`, {
        method: 'PATCH',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(() => location.reload());
}

function abrirEditar(item) {
    document.getElementById('form-editar').action = `/pecosa/inicial/compras/${item.id}`;
    document.getElementById('edit-descripcion').value = item.descripcion;
    document.getElementById('edit-cantidad').value    = item.cantidad;
    document.getElementById('edit-unidad').value      = item.unidad;
    document.getElementById('edit-precio').value      = item.precio_unitario ?? '';
    document.getElementById('edit-nota').value        = item.nota ?? '';
    document.getElementById('modal-editar').classList.remove('hidden');
}

function cerrarEditar() {
    document.getElementById('modal-editar').classList.add('hidden');
}
</script>

@endsection
