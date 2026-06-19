@extends('layouts.app')
@section('title', 'Alumnos')
@section('page-title', 'Registro de Alumnos')
@section('breadcrumb', 'Consulta y gestión de alumnos')

@section('header-actions')
    <a href="{{ route('alumnos.create') }}"
       class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Nuevo Alumno</span>
    </a>
@endsection

@section('content')

@php
    $coloresSecciones = [
        'A' => ['bg' => 'bg-blue-600',   'hover' => 'hover:bg-blue-700',   'light' => 'bg-blue-50',   'text' => 'text-blue-600'],
        'B' => ['bg' => 'bg-emerald-600','hover' => 'hover:bg-emerald-700','light' => 'bg-emerald-50','text' => 'text-emerald-600'],
        'C' => ['bg' => 'bg-violet-600', 'hover' => 'hover:bg-violet-700', 'light' => 'bg-violet-50', 'text' => 'text-violet-600'],
        'D' => ['bg' => 'bg-amber-500',  'hover' => 'hover:bg-amber-600',  'light' => 'bg-amber-50',  'text' => 'text-amber-600'],
        'E' => ['bg' => 'bg-rose-600',   'hover' => 'hover:bg-rose-700',   'light' => 'bg-rose-50',   'text' => 'text-rose-600'],
    ];
    $coloresDefault = ['bg' => 'bg-slate-600','hover' => 'hover:bg-slate-700','light' => 'bg-slate-50','text' => 'text-slate-600'];
@endphp

<script>
function abrirModal(id) { document.getElementById(id).classList.remove('hidden'); }
function cerrarModal(id) { document.getElementById(id).classList.add('hidden'); }
document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ cerrarModal('modal-primaria'); cerrarModal('modal-inicial'); }});

function toggleAcordeon(id) {
    var el = document.getElementById(id);
    var arrow = document.getElementById('arrow-' + id);
    el.classList.toggle('hidden');
    if (arrow) arrow.classList.toggle('rotate-180');
}
function cambiarTab(tab) {
    ['primaria','inicial'].forEach(function(t) {
        document.getElementById('tab-content-' + t).classList.toggle('hidden', t !== tab);
        document.getElementById('btn-tab-' + t).classList.toggle('bg-white', t === tab);
        document.getElementById('btn-tab-' + t).classList.toggle('shadow-sm', t === tab);
        document.getElementById('btn-tab-' + t).classList.toggle(t === 'primaria' ? 'text-blue-600' : 'text-amber-600', t === tab);
        document.getElementById('btn-tab-' + t).classList.toggle('text-gray-500', t !== tab);
    });
    document.getElementById('btn-importar-primaria').classList.toggle('hidden', tab !== 'primaria');
    document.getElementById('btn-importar-inicial').classList.toggle('hidden', tab !== 'inicial');
}
document.addEventListener('DOMContentLoaded', function(){ cambiarTab('primaria'); });
</script>

<div>
    {{-- Tabs + botón importar --}}
    <div class="flex items-center justify-between mb-6">
    <div class="flex items-center space-x-1 bg-gray-100 p-1 rounded-xl w-fit">
        <button id="btn-tab-primaria"
                onclick="cambiarTab('primaria')"
                class="px-6 py-2 rounded-lg text-sm font-bold transition-all flex items-center space-x-2">
            <div class="w-2 h-2 rounded-full bg-blue-500"></div>
            <span>PRIMARIA</span>
            <span class="bg-gray-200 text-gray-600 px-2 py-0.5 rounded-md text-[10px]">
                {{ $seccionesPorNivel->has('primaria') ? $seccionesPorNivel['primaria']->flatten()->count() : 0 }}
            </span>
        </button>
        <button id="btn-tab-inicial"
                onclick="cambiarTab('inicial')"
                class="px-6 py-2 rounded-lg text-sm font-bold transition-all flex items-center space-x-2">
            <div class="w-2 h-2 rounded-full bg-amber-500"></div>
            <span>INICIAL</span>
            <span class="bg-gray-200 text-gray-600 px-2 py-0.5 rounded-md text-[10px]">
                {{ $seccionesPorNivel->has('inicial') ? $seccionesPorNivel['inicial']->flatten()->count() : 0 }}
            </span>
        </button>
    </div>

    {{-- Botón importar Primaria --}}
    <button id="btn-importar-primaria"
            onclick="abrirModal('modal-primaria')"
            class="inline-flex items-center space-x-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        <span>Importar Excel Primaria</span>
    </button>
    {{-- Botón importar Inicial --}}
    <button id="btn-importar-inicial"
            onclick="abrirModal('modal-inicial')"
            class="hidden inline-flex items-center space-x-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold px-4 py-2 rounded-xl transition-colors shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        <span>Importar Excel Inicial</span>
    </button>
    </div>{{-- cierra flex justify-between --}}

    {{-- Modal importar Primaria --}}
    <div id="modal-primaria"
         class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
         onclick="if(event.target===this) cerrarModal('modal-primaria')">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-5" onclick="event.stopPropagation()">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-black text-gray-800">Importar Alumnos de Primaria</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Sube el Excel o CSV con la lista actualizada</p>
                </div>
                <button onclick="cerrarModal('modal-primaria')" class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 flex items-start space-x-3">
                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-sm font-bold text-blue-700">Reemplaza todos los alumnos de primaria</p>
                    <p class="text-xs text-blue-600 mt-0.5">Se borrarán todos los alumnos anteriores de primaria y se cargarán los nuevos.</p>
                </div>
            </div>
            <a href="{{ route('alumnos.plantilla-primaria') }}"
               class="flex items-center space-x-3 p-3 border border-dashed border-green-300 rounded-xl bg-green-50 hover:bg-green-100 transition-colors group">
                <div class="w-9 h-9 bg-green-100 group-hover:bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-green-700">Descargar plantilla CSV</p>
                    <p class="text-xs text-green-600">Abre en Excel y rellena con tus datos</p>
                </div>
            </a>
            <form action="{{ route('alumnos.importar-primaria') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div x-data="{ fileName: '' }">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Archivo Excel / CSV</label>
                        <div class="relative border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-blue-400 transition-colors cursor-pointer"
                             @dragover.prevent
                             @drop.prevent="const file = $event.dataTransfer.files[0]; if(file){ fileName = file.name; $refs.fileInputPri.files = $event.dataTransfer.files; }">
                            <input type="file" name="archivos[]" accept=".xlsx,.xls,.csv"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                   x-ref="fileInputPri"
                                   @change="fileName = $event.target.files[0]?.name || ''">
                            <div x-show="!fileName">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm text-gray-500">Arrastra aquí o <span class="text-blue-600 font-bold">elige un archivo</span></p>
                                <p class="text-xs text-gray-400 mt-1">.xlsx · .xls · .csv &mdash; máx 5 MB</p>
                            </div>
                            <div x-show="fileName" class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm font-bold text-gray-700" x-text="fileName"></span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1.5">
                            Sección por defecto
                            <span class="normal-case font-normal text-gray-400">(si el Excel no tiene columna "seccion")</span>
                        </label>
                        <input type="text" name="seccion_default" placeholder="Ej: 1° A"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-300 focus:border-blue-400 transition-colors uppercase">
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Columnas que detecta automáticamente</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['apellido_paterno','apellido_materno','nombres','dni','fecha_nacimiento','sexo','seccion'] as $col)
                                <span class="font-mono text-[10px] bg-white border border-gray-200 text-gray-600 px-2 py-0.5 rounded-md">{{ $col }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 pt-1">
                        <button type="button" onclick="cerrarModal('modal-primaria')"
                                class="flex-1 px-4 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors shadow-sm">
                            Importar y Reemplazar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal de importación Inicial --}}
    <div id="modal-inicial"
         class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm"
         onclick="if(event.target===this) cerrarModal('modal-inicial')">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-5" onclick="event.stopPropagation()">

            {{-- Header del modal --}}
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-black text-gray-800">Importar Alumnos de Inicial</h2>
                    <p class="text-sm text-gray-400 mt-0.5">Sube el Excel o CSV con la lista actualizada</p>
                </div>
                <button onclick="cerrarModal('modal-inicial')" class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Advertencia --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start space-x-3">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-sm font-bold text-amber-700">Reemplaza todos los alumnos de inicial</p>
                    <p class="text-xs text-amber-600 mt-0.5">Las secciones que no estén en los archivos no se tocan. Puedes subir un archivo por sección o todos a la vez.</p>
                </div>
            </div>

            {{-- Descargar plantilla --}}
            <a href="{{ route('alumnos.plantilla-inicial') }}"
               class="flex items-center space-x-3 p-3 border border-dashed border-green-300 rounded-xl bg-green-50 hover:bg-green-100 transition-colors group">
                <div class="w-9 h-9 bg-green-100 group-hover:bg-green-200 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-green-700">Descargar plantilla CSV</p>
                    <p class="text-xs text-green-600">Abre en Excel y rellena con tus datos</p>
                </div>
            </a>

            {{-- Formulario --}}
            <form action="{{ route('alumnos.importar-inicial') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">

                    {{-- Input de archivo con drag & drop --}}
                    <div x-data="{ fileName: '' }">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Archivo Excel / CSV</label>
                        <div class="relative border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-amber-400 transition-colors cursor-pointer"
                             @dragover.prevent
                             @drop.prevent="
                                const file = $event.dataTransfer.files[0];
                                if(file){ fileName = file.name; $refs.fileInput.files = $event.dataTransfer.files; }
                             ">
                            <input type="file"
                                   name="archivos[]"
                                   accept=".xlsx,.xls,.csv"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                   x-ref="fileInput"
                                   @change="fileName = $event.target.files[0]?.name || ''">
                            <div x-show="!fileName">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-sm text-gray-500">Arrastra aquí o <span class="text-amber-600 font-bold">elige un archivo</span></p>
                                <p class="text-xs text-gray-400 mt-1">.xlsx · .xls · .csv &mdash; máx 5 MB</p>
                            </div>
                            <div x-show="fileName" class="flex items-center justify-center space-x-2">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm font-bold text-gray-700" x-text="fileName"></span>
                            </div>
                        </div>
                        @error('archivos')
                            <p class="text-xs text-red-600 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Sección por defecto --}}
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-wider mb-1.5">
                            Sección por defecto
                            <span class="normal-case font-normal text-gray-400">(si el Excel no tiene columna "seccion")</span>
                        </label>
                        <input type="text"
                               name="seccion_default"
                               placeholder="Ej: 3 AÑOS A"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-300 focus:border-amber-400 transition-colors uppercase">
                    </div>

                    {{-- Columnas detectadas automáticamente --}}
                    <div class="bg-gray-50 rounded-xl p-3">
                        <p class="text-xs font-black text-gray-400 uppercase tracking-wider mb-2">Columnas que detecta automáticamente</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['apellido_paterno','apellido_materno','nombres','dni','fecha_nacimiento','sexo','seccion'] as $col)
                                <span class="font-mono text-[10px] bg-white border border-gray-200 text-gray-600 px-2 py-0.5 rounded-md">{{ $col }}</span>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2">También acepta variantes como: <span class="font-mono">paterno · materno · nombres · dni_del_alumno · sexo · aula</span></p>
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center space-x-3 pt-1">
                        <button type="button" onclick="cerrarModal('modal-inicial')"
                                class="flex-1 px-4 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 rounded-xl transition-colors shadow-sm">
                            Importar y Reemplazar
                        </button>
                    </div>

                </div>
            </form>

        </div>
    </div>

    {{-- Content --}}
    <div class="space-y-6">
        @foreach(['primaria', 'inicial'] as $nivel)
            <div id="tab-content-{{ $nivel }}" class="hidden">
                    @php
                        $secciones = $seccionesPorNivel->get($nivel, collect());
                        $grados = [];
                        foreach ($secciones as $carrera => $lista) {
                            $partes      = explode(' ', trim($carrera));
                            $secLetra    = array_pop($partes);
                            $gradoNombre = implode(' ', $partes);
                            $grados[$gradoNombre][$carrera] = $lista;
                        }
                    @endphp

                    @if($secciones->isEmpty())
                        <div class="bg-white rounded-2xl border border-dashed border-gray-200 py-16 text-center">
                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <h3 class="text-gray-900 font-bold">Sin alumnos registrados</h3>
                            <p class="text-gray-500 text-sm max-w-xs mx-auto mt-1">Todavía no hay alumnos registrados en el nivel de {{ $nivel }}.</p>
                        </div>
                    @else
                        @php $gi = 0; @endphp
                        <div class="space-y-4">
                            @foreach($grados as $gradoNombre => $seccionesDelGrado)
                                @php $gi++; $gradoId = 'grado-' . $nivel . '-' . $gi; $si = 0; @endphp
                                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all hover:shadow-md">
                                    {{-- Grado Header --}}
                                    <button onclick="toggleAcordeon('{{ $gradoId }}')"
                                            class="w-full flex items-center justify-between px-6 py-4 {{ $nivel === 'primaria' ? 'bg-slate-800' : 'bg-amber-700' }} hover:opacity-90 transition-all focus:outline-none">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0 backdrop-blur-sm">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                            </div>
                                            <div class="text-left">
                                                <span class="text-white font-black text-sm tracking-widest uppercase block">{{ $gradoNombre }}</span>
                                                <span class="text-white/60 text-[10px] font-bold uppercase">{{ count($seccionesDelGrado) }} Secciones &middot; {{ collect($seccionesDelGrado)->flatten()->count() }} Alumnos</span>
                                            </div>
                                        </div>
                                        <svg id="arrow-{{ $gradoId }}" class="w-5 h-5 text-white/50 transition-transform duration-300"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    {{-- Secciones --}}
                                    <div id="{{ $gradoId }}" class="hidden divide-y divide-gray-50">
                                        @foreach($seccionesDelGrado as $carrera => $lista)
                                            @php
                                                $si++;
                                                $secId = 'sec-' . $nivel . '-' . $gi . '-' . $si;
                                                $letra = trim(substr($carrera, strrpos($carrera, ' ') + 1));
                                                $color = $coloresSecciones[$letra] ?? $coloresDefault;
                                            @endphp
                                            <div class="bg-white">
                                                <button onclick="toggleAcordeon('{{ $secId }}')"
                                                        class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50/80 transition-colors focus:outline-none">
                                                    <div class="flex items-center space-x-4">
                                                        <div class="w-10 h-10 rounded-xl {{ $color['light'] }} flex items-center justify-center flex-shrink-0 border border-{{ str_replace('bg-', '', $color['text']) }}/10">
                                                            <span class="text-base font-black {{ $color['text'] }}">{{ $letra }}</span>
                                                        </div>
                                                        <div class="text-left">
                                                            <p class="text-sm font-black text-gray-800 uppercase tracking-tight">{{ $carrera }}</p>
                                                            <p class="text-[11px] font-bold text-gray-400 uppercase">Listado oficial de matriculados</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-4">
                                                        <div class="text-right mr-2">
                                                            <span class="text-xl font-black {{ $color['text'] }} block leading-none">{{ $lista->count() }}</span>
                                                            <span class="text-[10px] font-bold text-gray-400 uppercase">Total</span>
                                                        </div>
                                                        <div class="p-1.5 rounded-lg bg-gray-50 transition-colors">
                                                            <svg id="arrow-{{ $secId }}" class="w-4 h-4 text-gray-400 transition-transform duration-300"
                                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </button>

                                                <div id="{{ $secId }}" class="hidden border-t border-gray-50 bg-gray-50/30">
                                                    <div class="overflow-x-auto">
                                                        <table class="w-full text-left">
                                                            <thead>
                                                                <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-100">
                                                                    <th class="px-6 py-3 w-12 text-center">N°</th>
                                                                    <th class="px-6 py-3">Estudiante</th>
                                                                    <th class="px-6 py-3">Documento</th>
                                                                    <th class="px-6 py-3">Estado</th>
                                                                    <th class="px-6 py-3 text-right">Gestión</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-gray-50">
                                                                @foreach($lista as $i => $alumno)
                                                                    <tr class="group hover:bg-white transition-all">
                                                                        <td class="px-6 py-4 text-center text-gray-400 font-bold text-xs">{{ $i + 1 }}</td>
                                                                        <td class="px-6 py-4">
                                                                            <div class="flex flex-col">
                                                                                <span class="text-sm font-black text-gray-800 leading-tight">{{ $alumno->apellido_paterno }} {{ $alumno->apellido_materno }}</span>
                                                                                <span class="text-xs font-bold text-gray-500">{{ $alumno->nombre }}</span>
                                                                            </div>
                                                                        </td>
                                                                        <td class="px-6 py-4">
                                                                            <div class="flex items-center space-x-2">
                                                                                <div class="w-1.5 h-1.5 rounded-full bg-gray-300"></div>
                                                                                <span class="text-xs font-mono font-bold text-gray-600">{{ $alumno->matricula }}</span>
                                                                            </div>
                                                                        </td>
                                                                        <td class="px-6 py-4">
                                                                            <span class="inline-flex px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider {{ $alumno->estado_badge }}">
                                                                                {{ $alumno->estado_label }}
                                                                            </span>
                                                                        </td>
                                                                        <td class="px-6 py-4 text-right">
                                                                            <div class="inline-flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                                <a href="{{ route('alumnos.show', $alumno) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Ver detalle">
                                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                                                </a>
                                                                                <a href="{{ route('alumnos.edit', $alumno) }}" class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Editar">
                                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                                                </a>
                                                                                <form method="POST" action="{{ route('alumnos.destroy', $alumno) }}" onsubmit="return confirm('¿Eliminar a este alumno?')">
                                                                                    @csrf @method('DELETE')
                                                                                    <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                                                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                                                    </button>
                                                                                </form>
                                                                            </div>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
        @endforeach
    </div>
</div>

@endsection
