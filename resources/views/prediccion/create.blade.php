@extends('layouts.app')
@section('title', 'Registrar Asistencia')
@section('page-title', 'Registrar Asistencia Diaria')
@section('breadcrumb', 'Datos de entrada para el modelo de predicción')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('prediccion.store') }}" class="space-y-6" x-data="formAsistencia()">
        @csrf

        {{-- Datos del día --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos del Día</h2>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Fecha <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('fecha') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('fecha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nivel <span class="text-red-500">*</span></label>
                    <select name="nivel" x-model="nivel" @change="cargarSecciones()"
                            class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 border-gray-300">
                        <option value="inicial" selected>Inicial</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Grado <span class="text-red-500">*</span></label>
                    <select name="grado" x-model="grado" @change="cargarSecciones()"
                            class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('grado') ? 'border-red-400' : 'border-gray-300' }}">
                        <template x-for="g in (nivel === 'primaria' ? ['1°','2°','3°','4°','5°','6°'] : ['3 Años','4 Años','5 Años'])" :key="g">
                            <option :value="g" :selected="g === grado" x-text="g"></option>
                        </template>
                    </select>
                    @error('grado')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Sección <span class="text-red-500">*</span></label>
                    <select name="seccion" x-model="seccion" @change="cargarAlumnos()"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            :disabled="secciones.length === 0">
                        <template x-if="secciones.length === 0">
                            <option value="">— Selecciona grado primero —</option>
                        </template>
                        <template x-for="s in secciones" :key="s">
                            <option :value="s" :selected="s === seccion" x-text="'Sección ' + s"></option>
                        </template>
                    </select>
                    @error('seccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>

        {{-- Lista de alumnos --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden" x-show="alumnos.length > 0 || cargando">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Lista de Alumnos</h2>
                    <p class="text-xs text-gray-400 mt-0.5" x-show="alumnos.length > 0">
                        Marca los alumnos <span class="text-green-600 font-semibold">presentes</span> hoy
                    </p>
                </div>
                <div class="flex items-center gap-3" x-show="alumnos.length > 0">
                    <button type="button" @click="marcarTodos(true)"
                            class="text-xs text-green-700 border border-green-300 bg-green-50 hover:bg-green-100 rounded-lg px-3 py-1.5 transition-colors font-medium">
                        Todos presentes
                    </button>
                    <button type="button" @click="marcarTodos(false)"
                            class="text-xs text-red-600 border border-red-300 bg-red-50 hover:bg-red-100 rounded-lg px-3 py-1.5 transition-colors font-medium">
                        Todos ausentes
                    </button>
                </div>
            </div>

            {{-- Cargando --}}
            <div x-show="cargando" class="px-6 py-8 flex items-center gap-3 text-blue-500">
                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span class="text-sm">Cargando alumnos…</span>
            </div>

            {{-- Lista --}}
            <div x-show="!cargando && alumnos.length > 0" class="divide-y divide-gray-50">
                <template x-for="(alumno, i) in alumnos" :key="alumno.id">
                    <label class="flex items-center gap-4 px-6 py-3 hover:bg-gray-50 cursor-pointer transition-colors">
                        <input type="checkbox" x-model="alumno.presente" @change="actualizarConteo()"
                               class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer">
                        <div class="flex items-center gap-3 flex-1">
                            <span class="text-xs font-semibold text-gray-400 w-6 text-right" x-text="i + 1"></span>
                            <span class="text-sm text-gray-800" x-text="alumno.nombre"></span>
                        </div>
                        <span x-show="alumno.presente"
                              class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Presente</span>
                        <span x-show="!alumno.presente"
                              class="text-xs font-semibold text-red-400 bg-red-50 px-2 py-0.5 rounded-full">Ausente</span>
                    </label>
                </template>
            </div>

            {{-- Resumen al pie de la lista --}}
            <div x-show="!cargando && alumnos.length > 0"
                 class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center gap-8 text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
                    <span class="text-gray-600">Presentes:</span>
                    <span class="font-bold text-green-700 text-lg" x-text="presentes"></span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
                    <span class="text-gray-600">Ausentes:</span>
                    <span class="font-bold text-red-500 text-lg" x-text="ausentes"></span>
                </div>
                <div class="ml-auto">
                    <span class="text-gray-500">% Asistencia: </span>
                    <span class="font-bold text-lg"
                          :class="pct >= 85 ? 'text-green-600' : (pct >= 70 ? 'text-yellow-600' : 'text-red-500')"
                          x-text="pct + '%'"></span>
                </div>
            </div>
        </div>

        {{-- Campos ocultos y observaciones --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Resumen y Observaciones</h2>
            </div>
            <div class="px-6 py-5 space-y-4">

                <input type="hidden" name="total_alumnos" :value="total">
                <input type="hidden" name="presentes" :value="presentes">
                <input type="hidden" name="raciones" :value="presentes">

                {{-- Resumen compacto cuando no hay lista cargada --}}
                <div x-show="alumnos.length === 0 && !cargando"
                     class="grid grid-cols-3 gap-4 text-center">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Total matriculados</p>
                        <p class="text-2xl font-bold text-gray-400">—</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Presentes</p>
                        <p class="text-2xl font-bold text-gray-400">—</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Ausentes</p>
                        <p class="text-2xl font-bold text-gray-400">—</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Observaciones</label>
                    <textarea name="observaciones" rows="2"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"
                              placeholder="Ej. Lluvia, actividad escolar, etc.">{{ old('observaciones') }}</textarea>
                </div>

            </div>
        </div>

        {{-- Detalle individual como JSON --}}
        <input type="hidden" name="detalle_json" :value="JSON.stringify(alumnos.map(a => ({alumno_id: a.id, presente: a.presente ? 1 : 0})))">

        <div class="flex items-center space-x-3">
            <button type="submit" :disabled="alumnos.length === 0"
                    :class="alumnos.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                    class="bg-blue-600 text-white font-semibold text-sm px-6 py-2.5 rounded-lg transition-colors shadow-sm">
                Guardar Registro
            </button>
            <a href="{{ route('prediccion.index', ['nivel' => request('nivel', 'inicial')]) }}"
               class="text-sm text-gray-600 hover:text-gray-800 font-medium px-4 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
function formAsistencia() {
    return {
        nivel:    '{{ old('nivel', request('nivel', 'inicial')) }}',
        grado:    '{{ old('grado', request('nivel', 'inicial') === 'primaria' ? '1°' : '3 Años') }}',
        seccion:  '{{ old('seccion', '') }}',
        secciones: [],
        alumnos:  [],
        cargando: false,

        get total()    { return this.alumnos.length; },
        get presentes(){ return this.alumnos.filter(a => a.presente).length; },
        get ausentes() { return this.alumnos.filter(a => !a.presente).length; },
        get pct() {
            if (this.total <= 0) return 0;
            return Math.round((this.presentes / this.total) * 100 * 10) / 10;
        },

        marcarTodos(estado) {
            this.alumnos.forEach(a => a.presente = estado);
        },

        actualizarConteo() {},

        cargarSecciones() {
            this.secciones = [];
            this.seccion   = '';
            this.alumnos   = [];
            const url = '{{ route("prediccion.secciones-grado") }}'
                + '?nivel=' + encodeURIComponent(this.nivel)
                + '&grado=' + encodeURIComponent(this.grado);
            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.secciones = data.secciones || [];
                    if (this.secciones.length > 0) {
                        this.seccion = this.secciones[0];
                        this.cargarAlumnos();
                    }
                });
        },

        cargarAlumnos() {
            if (!this.seccion) return;
            this.alumnos  = [];
            this.cargando = true;
            const url = '{{ route("prediccion.alumnos-aula") }}'
                + '?nivel='   + encodeURIComponent(this.nivel)
                + '&grado='   + encodeURIComponent(this.grado)
                + '&seccion=' + encodeURIComponent(this.seccion);

            fetch(url, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.alumnos  = (data.alumnos || []).map(a => ({ ...a, presente: true }));
                    this.cargando = false;
                })
                .catch(() => { this.cargando = false; });
        },

        init() { this.$nextTick(() => this.cargarSecciones()); }
    };
}
</script>
@endpush
