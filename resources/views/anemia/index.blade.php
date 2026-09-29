@extends('layouts.app')
@section('title', 'Orientación a Familias — Anemia')
@section('page-title', 'Orientación a Familias')
@section('breadcrumb', 'Estudiantes con anemia y recomendaciones de preparación')

@section('header-actions')
    <div class="flex items-center space-x-2">
        <a href="{{ route('anemia.resultados') }}"
           class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span>Resultados del cuestionario</span>
        </a>
        <button type="button" onclick="document.getElementById('modal-registrar').classList.remove('hidden')"
                class="inline-flex items-center space-x-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Registrar estudiante</span>
        </button>
        <button type="button" onclick="document.getElementById('modal-recomendacion').classList.remove('hidden')"
                class="inline-flex items-center space-x-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <span>Nueva recomendación</span>
        </button>
    </div>
@endsection

@section('content')

@if(session('success'))
<div class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 mb-5 text-green-700 text-sm font-medium">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="bg-red-50 border border-red-200 rounded-xl px-5 py-3 mb-5 text-red-700 text-sm font-medium">{{ session('error') }}</div>
@endif

<div class="bg-blue-50 border border-blue-200 rounded-xl px-5 py-3 mb-6 text-blue-800 text-sm">
    Cada estudiante registrado tiene un enlace único para que sus padres vean las recomendaciones de preparación
    y respondan el cuestionario, sin necesidad de una cuenta en el sistema.
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="p-5 border-b border-gray-100">
        <h2 class="text-lg font-bold text-gray-800">Estudiantes con anemia registrados</h2>
        <p class="text-xs text-gray-500 mt-0.5">{{ $casos->count() }} estudiante(s)</p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Alumno</th>
                    <th class="px-4 py-3 text-left">Nivel / Sección</th>
                    <th class="px-4 py-3 text-left">Fecha tamizaje</th>
                    <th class="px-4 py-3 text-left">Establecimiento de salud</th>
                    <th class="px-4 py-3 text-left">Enlace para padres</th>
                    <th class="px-4 py-3 text-right">Acción</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($casos as $caso)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ $caso->alumno->apellido_paterno }} {{ $caso->alumno->apellido_materno }}, {{ $caso->alumno->nombre }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $caso->alumno->nivel_label }} · {{ $caso->alumno->carrera }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ \Carbon\Carbon::parse($caso->fecha_tamizaje)->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $caso->establecimiento_salud ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @php $link = route('orientacion.show', $caso->alumno->matricula); @endphp
                        <input type="text" readonly value="{{ $link }}" onclick="this.select()"
                               class="text-xs text-blue-600 border border-gray-200 rounded px-2 py-1 w-56">
                    </td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="{{ route('anemia.destroy', $caso) }}"
                              onsubmit="return confirm('¿Quitar a este estudiante del módulo de orientación?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Quitar</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Aún no se ha registrado ningún estudiante.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100">
        <h2 class="text-lg font-bold text-gray-800">Recomendaciones de preparación por producto</h2>
        <p class="text-xs text-gray-500 mt-0.5">Validadas por un profesional en nutrición</p>
    </div>
    <div class="divide-y divide-gray-100">
        @forelse($recomendaciones as $rec)
        <div class="p-4 flex items-start justify-between gap-4">
            <div>
                <p class="text-sm font-bold text-gray-800">{{ $rec->producto }} <span class="text-xs font-normal text-gray-400 uppercase">({{ $rec->nivel }})</span></p>
                <p class="text-sm text-gray-600 mt-1">{{ $rec->recomendacion }}</p>
                @if($rec->validado_por)
                    <p class="text-xs text-gray-400 mt-1">Validado por: {{ $rec->validado_por }}</p>
                @endif
            </div>
            <form method="POST" action="{{ route('anemia.recomendaciones.destroy', $rec) }}" onsubmit="return confirm('¿Eliminar esta recomendación?')">
                @csrf @method('DELETE')
                <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium whitespace-nowrap">Eliminar</button>
            </form>
        </div>
        @empty
        <p class="p-6 text-center text-gray-400 text-sm">Aún no hay recomendaciones registradas.</p>
        @endforelse
    </div>
</div>

{{-- Modal: registrar estudiante --}}
<div id="modal-registrar" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">Registrar estudiante con anemia</h3>
        <form method="POST" action="{{ route('anemia.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Buscar alumno (nombre o matrícula)</label>
                <input type="text" id="buscar-alumno" autocomplete="off"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400"
                       placeholder="Escribe para buscar...">
                <div id="resultados-alumno" class="mt-1 border border-gray-200 rounded-lg divide-y divide-gray-100 max-h-40 overflow-y-auto hidden"></div>
                <input type="hidden" name="alumno_id" id="alumno_id" required>
                <p id="alumno-seleccionado" class="text-xs text-green-600 mt-1"></p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Fecha del tamizaje</label>
                <input type="date" name="fecha_tamizaje" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Establecimiento de salud</label>
                <input type="text" name="establecimiento_salud" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Ej. C.S. Piura">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Observaciones</label>
                <textarea name="observaciones" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-registrar').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg">Registrar</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: nueva recomendación --}}
<div id="modal-recomendacion" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-base font-bold text-gray-800 mb-4">Nueva recomendación de preparación</h3>
        <form method="POST" action="{{ route('anemia.recomendaciones.store') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nivel</label>
                <select name="nivel" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <option value="inicial">Inicial</option>
                    <option value="primaria">Primaria</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Producto</label>
                <input type="text" name="producto" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" placeholder="Ej. Conserva de pescado">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Recomendación</label>
                <textarea name="recomendacion" required rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Validado por (nutricionista)</label>
                <input type="text" name="validado_por" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="document.getElementById('modal-recomendacion').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-600">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg">Guardar</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const inputBuscar = document.getElementById('buscar-alumno');
const resultadosDiv = document.getElementById('resultados-alumno');
let timeoutBusqueda;

inputBuscar.addEventListener('input', function () {
    clearTimeout(timeoutBusqueda);
    const q = this.value.trim();
    if (q.length < 2) { resultadosDiv.classList.add('hidden'); return; }
    timeoutBusqueda = setTimeout(() => {
        fetch(`{{ route('anemia.buscar-alumno') }}?q=${encodeURIComponent(q)}`)
            .then(r => r.json())
            .then(data => {
                resultadosDiv.innerHTML = '';
                if (data.length === 0) { resultadosDiv.classList.add('hidden'); return; }
                data.forEach(a => {
                    const div = document.createElement('div');
                    div.className = 'px-3 py-2 text-sm hover:bg-gray-50 cursor-pointer';
                    div.textContent = `${a.apellido_paterno} ${a.apellido_materno}, ${a.nombre} — ${a.matricula} (${a.nivel})`;
                    div.onclick = () => {
                        document.getElementById('alumno_id').value = a.id;
                        document.getElementById('alumno-seleccionado').textContent = `Seleccionado: ${a.nombre} ${a.apellido_paterno}`;
                        inputBuscar.value = div.textContent;
                        resultadosDiv.classList.add('hidden');
                    };
                    resultadosDiv.appendChild(div);
                });
                resultadosDiv.classList.remove('hidden');
            });
    }, 300);
});
</script>
@endpush
