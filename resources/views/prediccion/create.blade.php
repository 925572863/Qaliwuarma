@extends('layouts.app')
@section('title', 'Registrar Asistencia')
@section('page-title', 'Registrar Asistencia Diaria')
@section('breadcrumb', 'Datos de entrada para el modelo de predicción')

@section('content')
<div class="max-w-3xl">
    <form method="POST" action="{{ route('prediccion.store') }}" class="space-y-6" id="form-asistencia">
        @csrf

        {{-- Datos del día --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos del Día</h2>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Fecha <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha" id="campo-fecha" value="{{ old('fecha', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('fecha') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('fecha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nivel <span class="text-red-500">*</span></label>
                    <select name="nivel" id="campo-nivel" onchange="onNivelChange()"
                            class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 border-gray-300">
                        <option value="inicial" {{ $nivel === 'inicial' ? 'selected' : '' }}>Inicial</option>
                        <option value="primaria" {{ $nivel === 'primaria' ? 'selected' : '' }}>Primaria</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Grado <span class="text-red-500">*</span></label>
                    <select name="grado" id="campo-grado" onchange="cargarSecciones()"
                            class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('grado') ? 'border-red-400' : 'border-gray-300' }}">
                        <option value="3 Años">3 Años</option>
                        <option value="4 Años">4 Años</option>
                        <option value="5 Años">5 Años</option>
                    </select>
                    @error('grado')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Sección <span class="text-red-500">*</span></label>
                    <select name="seccion" id="campo-seccion" onchange="cargarAlumnos()"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">— Selecciona grado primero —</option>
                    </select>
                    @error('seccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>

        {{-- Lista de alumnos --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden" id="bloque-alumnos" style="display:none">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Lista de Alumnos</h2>
                    <p class="text-xs text-gray-400 mt-0.5" id="sub-lista">Marca los alumnos <span class="text-green-600 font-semibold">presentes</span> hoy</p>
                </div>
                <div class="flex items-center gap-3" id="btns-marcar">
                    <button type="button" onclick="marcarTodos(true)"
                            class="text-xs text-green-700 border border-green-300 bg-green-50 hover:bg-green-100 rounded-lg px-3 py-1.5 transition-colors font-medium">
                        Todos presentes
                    </button>
                    <button type="button" onclick="marcarTodos(false)"
                            class="text-xs text-red-600 border border-red-300 bg-red-50 hover:bg-red-100 rounded-lg px-3 py-1.5 transition-colors font-medium">
                        Todos ausentes
                    </button>
                </div>
            </div>

            <div id="bloque-cargando" class="px-6 py-8 flex items-center gap-3 text-blue-500" style="display:none">
                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                </svg>
                <span class="text-sm">Cargando alumnos…</span>
            </div>

            <div id="lista-alumnos" class="divide-y divide-gray-50"></div>

            <div id="bloque-resumen" class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center gap-8 text-sm" style="display:none">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
                    <span class="text-gray-600">Presentes:</span>
                    <span class="font-bold text-green-700 text-lg" id="cnt-presentes">0</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-red-400 inline-block"></span>
                    <span class="text-gray-600">Ausentes:</span>
                    <span class="font-bold text-red-500 text-lg" id="cnt-ausentes">0</span>
                </div>
                <div class="ml-auto">
                    <span class="text-gray-500">% Asistencia: </span>
                    <span class="font-bold text-lg" id="cnt-pct">0%</span>
                </div>
            </div>
        </div>

        {{-- Observaciones --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Resumen y Observaciones</h2>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div id="resumen-vacio" class="grid grid-cols-3 gap-4 text-center">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Total matriculados</p>
                        <p class="text-2xl font-bold text-gray-400" id="res-total">—</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Presentes</p>
                        <p class="text-2xl font-bold text-gray-400" id="res-presentes">—</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Ausentes</p>
                        <p class="text-2xl font-bold text-gray-400" id="res-ausentes">—</p>
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

        <input type="hidden" name="total_alumnos" id="hid-total" value="0">
        <input type="hidden" name="presentes" id="hid-presentes" value="0">
        <input type="hidden" name="raciones" id="hid-raciones" value="0">
        <input type="hidden" name="detalle_json" id="hid-detalle" value="[]">

        <div class="flex items-center space-x-3">
            <button type="submit" id="btn-guardar" disabled
                    class="opacity-50 cursor-not-allowed bg-blue-600 text-white font-semibold text-sm px-6 py-2.5 rounded-lg transition-colors shadow-sm">
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
var _alumnos = [];

function onNivelChange() {
    var nivel = document.getElementById('campo-nivel').value;
    var gradoSel = document.getElementById('campo-grado');
    gradoSel.innerHTML = '';
    var opciones = nivel === 'primaria'
        ? ['1°','2°','3°','4°','5°','6°']
        : ['3 Años','4 Años','5 Años'];
    opciones.forEach(function(g) {
        var opt = document.createElement('option');
        opt.value = g; opt.textContent = g;
        gradoSel.appendChild(opt);
    });
    cargarSecciones();
}

function cargarSecciones() {
    var nivel  = document.getElementById('campo-nivel').value;
    var grado  = document.getElementById('campo-grado').value;
    var secSel = document.getElementById('campo-seccion');
    secSel.innerHTML = '<option value="">Cargando…</option>';
    _alumnos = [];
    actualizarConteo();
    document.getElementById('bloque-alumnos').style.display = 'none';

    fetch('{{ route("prediccion.secciones-grado") }}?nivel=' + encodeURIComponent(nivel) + '&grado=' + encodeURIComponent(grado),
          { headers: { 'Accept': 'application/json' } })
        .then(function(r){ return r.json(); })
        .then(function(data) {
            secSel.innerHTML = '';
            var secs = data.secciones || [];
            if (secs.length === 0) {
                secSel.innerHTML = '<option value="">— Sin secciones —</option>';
                return;
            }
            secs.forEach(function(s) {
                var opt = document.createElement('option');
                opt.value = s; opt.textContent = 'Sección ' + s;
                secSel.appendChild(opt);
            });
            cargarAlumnos();
        })
        .catch(function(){ secSel.innerHTML = '<option value="">— Error —</option>'; });
}

function cargarAlumnos() {
    var nivel   = document.getElementById('campo-nivel').value;
    var grado   = document.getElementById('campo-grado').value;
    var seccion = document.getElementById('campo-seccion').value;
    if (!seccion) return;

    var bloque = document.getElementById('bloque-alumnos');
    var cargando = document.getElementById('bloque-cargando');
    var lista = document.getElementById('lista-alumnos');
    bloque.style.display = '';
    cargando.style.display = '';
    lista.innerHTML = '';
    document.getElementById('bloque-resumen').style.display = 'none';
    _alumnos = [];

    fetch('{{ route("prediccion.alumnos-aula") }}?nivel=' + encodeURIComponent(nivel) + '&grado=' + encodeURIComponent(grado) + '&seccion=' + encodeURIComponent(seccion),
          { headers: { 'Accept': 'application/json' } })
        .then(function(r){ return r.json(); })
        .then(function(data) {
            cargando.style.display = 'none';
            _alumnos = (data.alumnos || []).map(function(a){ return { id: a.id, nombre: a.nombre, presente: true }; });
            renderLista();
            actualizarConteo();
            document.getElementById('bloque-resumen').style.display = '';
        })
        .catch(function(){ cargando.style.display = 'none'; });
}

function renderLista() {
    var lista = document.getElementById('lista-alumnos');
    lista.innerHTML = '';
    _alumnos.forEach(function(alumno, i) {
        var label = document.createElement('label');
        label.className = 'flex items-center gap-4 px-6 py-3 hover:bg-gray-50 cursor-pointer transition-colors';
        label.innerHTML =
            '<input type="checkbox" ' + (alumno.presente ? 'checked' : '') + ' onchange="toggleAlumno(' + i + ', this.checked)" class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer">' +
            '<div class="flex items-center gap-3 flex-1">' +
                '<span class="text-xs font-semibold text-gray-400 w-6 text-right">' + (i+1) + '</span>' +
                '<span class="text-sm text-gray-800">' + alumno.nombre + '</span>' +
            '</div>' +
            '<span id="badge-' + i + '" class="text-xs font-semibold px-2 py-0.5 rounded-full ' + (alumno.presente ? 'text-green-600 bg-green-50' : 'text-red-400 bg-red-50') + '">' + (alumno.presente ? 'Presente' : 'Ausente') + '</span>';
        lista.appendChild(label);
    });
}

function toggleAlumno(i, val) {
    _alumnos[i].presente = val;
    var badge = document.getElementById('badge-' + i);
    if (badge) {
        badge.textContent = val ? 'Presente' : 'Ausente';
        badge.className = 'text-xs font-semibold px-2 py-0.5 rounded-full ' + (val ? 'text-green-600 bg-green-50' : 'text-red-400 bg-red-50');
    }
    actualizarConteo();
}

function marcarTodos(estado) {
    _alumnos.forEach(function(a, i){ a.presente = estado; });
    renderLista();
    actualizarConteo();
}

function actualizarConteo() {
    var total     = _alumnos.length;
    var presentes = _alumnos.filter(function(a){ return a.presente; }).length;
    var ausentes  = total - presentes;
    var pct       = total > 0 ? Math.round((presentes / total) * 1000) / 10 : 0;

    document.getElementById('cnt-presentes').textContent = presentes;
    document.getElementById('cnt-ausentes').textContent  = ausentes;
    var pctEl = document.getElementById('cnt-pct');
    pctEl.textContent = pct + '%';
    pctEl.className = 'font-bold text-lg ' + (pct >= 85 ? 'text-green-600' : (pct >= 70 ? 'text-yellow-600' : 'text-red-500'));

    document.getElementById('res-total').textContent     = total || '—';
    document.getElementById('res-presentes').textContent = presentes || '—';
    document.getElementById('res-ausentes').textContent  = ausentes || '—';

    document.getElementById('hid-total').value     = total;
    document.getElementById('hid-presentes').value = presentes;
    document.getElementById('hid-raciones').value  = presentes;
    document.getElementById('hid-detalle').value   = JSON.stringify(_alumnos.map(function(a){ return { alumno_id: a.id, presente: a.presente ? 1 : 0 }; }));

    var btn = document.getElementById('btn-guardar');
    if (total > 0) {
        btn.disabled = false;
        btn.classList.remove('opacity-50','cursor-not-allowed');
        btn.classList.add('hover:bg-blue-700');
    } else {
        btn.disabled = true;
        btn.classList.add('opacity-50','cursor-not-allowed');
        btn.classList.remove('hover:bg-blue-700');
    }
}

document.addEventListener('DOMContentLoaded', function(){ cargarSecciones(); });
</script>
@endpush
