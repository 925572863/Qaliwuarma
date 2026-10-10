{{-- Opciones avanzadas de Pecosa: renombrar, poner fecha, eliminar por lote.
     Variables esperadas: $pecosasSubidas, $rutaRenombrar, $rutaCambiarFecha, $rutaDestroyPecosa --}}
@if($pecosasSubidas->isNotEmpty())
<details class="mt-3 pt-3 border-t border-gray-100 group">
    <summary class="flex items-center gap-1.5 text-xs text-gray-400 hover:text-gray-600 cursor-pointer select-none w-fit">
        <svg class="w-3.5 h-3.5 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        Opciones avanzadas de Pecosa
    </summary>

    <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3">
        <form method="POST" action="{{ $rutaRenombrar }}"
              onsubmit="return confirm('¿Renombrar todos los productos de esa Pecosa?')"
              class="bg-gray-50 rounded-lg p-3 flex flex-col gap-2">
            @csrf
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Renombrar</span>
            <select name="nombre_actual" required
                    class="border border-gray-300 rounded-lg text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- elegir Pecosa --</option>
                @foreach($pecosasSubidas as $nombrePecosa)
                    <option value="{{ $nombrePecosa }}">{{ $nombrePecosa }}</option>
                @endforeach
            </select>
            <input type="text" name="nombre_nuevo" required placeholder="Nuevo nombre, ej: 20/08/2026"
                   class="border border-gray-300 rounded-lg text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors self-start">
                Renombrar
            </button>
        </form>

        <form method="POST" action="{{ $rutaCambiarFecha }}"
              onsubmit="return confirm('¿Poner esa fecha a todos los productos de esa Pecosa?')"
              class="bg-gray-50 rounded-lg p-3 flex flex-col gap-2">
            @csrf
            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Poner fecha</span>
            <select name="nombre_pecosa" required
                    class="border border-gray-300 rounded-lg text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">-- elegir Pecosa --</option>
                @foreach($pecosasSubidas as $nombrePecosa)
                    <option value="{{ $nombrePecosa }}">{{ $nombrePecosa }}</option>
                @endforeach
            </select>
            <input type="date" name="fecha_entrega" required
                   class="border border-gray-300 rounded-lg text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors self-start">
                Poner fecha
            </button>
        </form>

        <form method="POST" action="{{ $rutaDestroyPecosa }}"
              onsubmit="return confirm('¿Eliminar TODOS los productos de esa Pecosa? Esta accion no se puede deshacer.')"
              class="bg-red-50 rounded-lg p-3 flex flex-col gap-2">
            @csrf
            @method('DELETE')
            <span class="text-xs font-semibold text-red-500 uppercase tracking-wide">Eliminar</span>
            <select name="nombre_pecosa" required
                    class="border border-gray-300 rounded-lg text-xs px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-red-500">
                <option value="">-- elegir Pecosa --</option>
                @foreach($pecosasSubidas as $nombrePecosa)
                    <option value="{{ $nombrePecosa }}">{{ $nombrePecosa }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-1.5 rounded-lg transition-colors self-start">
                Eliminar Pecosa
            </button>
        </form>
    </div>
</details>
@endif
