{{-- Modal detalle de aula --}}
@section('modals')
<div id="modal-detalle" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="cerrarDetalle()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md max-h-[80vh] flex flex-col">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-gray-800" id="modal-titulo">Asistencia</h3>
                <p class="text-xs text-gray-400 mt-0.5" id="modal-subtitulo"></p>
            </div>
            <button onclick="cerrarDetalle()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="modal-body" class="overflow-y-auto flex-1 px-6 py-4">
            <div id="modal-loading" class="flex items-center gap-3 text-blue-500 py-4">
                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/></svg>
                <span class="text-sm">Cargando…</span>
            </div>
            <div id="modal-lista" class="hidden space-y-1"></div>
            <div id="modal-sin-detalle" class="hidden text-center py-6 text-gray-400 text-sm">
                Este registro no tiene detalle por alumno.<br>
                <span class="text-xs">Solo disponible para registros nuevos.</span>
            </div>
        </div>
        <div id="modal-footer" class="hidden px-6 py-3 border-t border-gray-100 flex gap-6 text-sm">
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-green-500"></span><span class="text-gray-600">Presentes: <strong id="modal-presentes" class="text-green-700"></strong></span></span>
            <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-400"></span><span class="text-gray-600">Ausentes: <strong id="modal-ausentes" class="text-red-500"></strong></span></span>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('title', 'Módulo de Predicción')
@section('page-title', 'Módulo de Predicción de Raciones')
@section('breadcrumb', 'Regresión lineal · Métricas MAE, RMSE, R², MAPE')

@section('header-actions')
    <div class="flex items-center space-x-2" x-data="{ modalImport: false }">
        {{-- Selector de nivel --}}
        <form method="GET" action="{{ route('prediccion.index') }}" class="flex items-center space-x-2">
            <select name="nivel" onchange="this.form.submit()"
                    class="text-sm border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="inicial" {{ $nivel === 'inicial' ? 'selected' : '' }}>Inicial</option>
            </select>
        </form>

        {{-- Botón importar histórico --}}
        <button @click="modalImport = true"
                class="inline-flex items-center space-x-1.5 bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            <span>Importar Histórico</span>
        </button>

        <a href="{{ route('prediccion.create', ['nivel' => $nivel]) }}"
           class="inline-flex items-center space-x-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Registrar Asistencia</span>
        </a>

        {{-- Modal importar --}}
        <div x-show="modalImport" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" x-cloak @click.self="modalImport = false">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-5" @click.stop>
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-gray-800">Importar Histórico de Asistencia</h2>
                        <p class="text-sm text-gray-400 mt-0.5">Sube un Excel con los datos anteriores</p>
                    </div>
                    <button @click="modalImport = false" class="p-1.5 hover:bg-gray-100 rounded-lg">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-sm text-blue-700 space-y-1">
                    <p class="font-bold">Columnas requeridas en el Excel:</p>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        @foreach(['fecha','grado','seccion','presentes','total_alumnos'] as $col)
                            <span class="font-mono text-xs bg-white border border-blue-200 px-2 py-0.5 rounded">{{ $col }}</span>
                        @endforeach
                    </div>
                    <p class="text-xs text-blue-500 mt-2">Ejemplo fila: 02/04/2026 · 3 Años · A · 18 · 18</p>
                </div>

                <form action="{{ route('prediccion.importar') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <input type="hidden" name="nivel" value="{{ $nivel }}">

                    <div x-data="{ fileName: '' }">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Archivo Excel / CSV</label>
                        <div class="relative border-2 border-dashed border-gray-200 rounded-xl p-6 text-center hover:border-blue-400 transition-colors cursor-pointer"
                             @dragover.prevent
                             @drop.prevent="const f=$event.dataTransfer.files[0]; if(f){fileName=f.name; $refs.fileHist.files=$event.dataTransfer.files;}">
                            <input type="file" name="archivo" accept=".xlsx,.xls,.csv"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                   x-ref="fileHist"
                                   @change="fileName = $event.target.files[0]?.name || ''">
                            <div x-show="!fileName">
                                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm text-gray-500">Arrastra o <span class="text-blue-600 font-semibold">elige un archivo</span></p>
                                <p class="text-xs text-gray-400 mt-1">.xlsx · .xls · .csv</p>
                            </div>
                            <div x-show="fileName" class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span class="text-sm font-semibold text-gray-700" x-text="fileName"></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-1">
                        <button type="button" @click="modalImport = false"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                            Cancelar
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors">
                            Importar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="space-y-5">

    {{-- Mensajes flash --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-5 py-3 text-sm">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-5 py-3 text-sm">
        ⚠️ {{ session('error') }}
    </div>
    @endif

    {{-- Receta para la IA --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ abierto: false }">
        <button @click="abierto = !abierto"
                class="w-full px-6 py-4 bg-gray-50 flex items-center justify-between hover:bg-gray-100 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div class="text-left">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Receta del día para la IA</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Escribe los ingredientes y la IA calculará las cantidades según los alumnos predichos</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform duration-300 flex-shrink-0" :class="abierto ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="abierto" x-transition.opacity.duration.200ms class="px-6 py-5 space-y-3">
            <form method="POST" action="{{ route('prediccion.guardar-receta') }}">
                @csrf
                <input type="hidden" name="nivel" value="{{ $nivel }}">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        Escribe la receta (ingrediente y gramos por ración)
                    </label>
                    <textarea name="receta_texto" rows="3"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-purple-400 resize-none"
                              placeholder="Ej: arroz 130g, aceite 6g, conserva de pescado 85g, frijol 40g...">{{ session('receta_ia_' . $nivel) }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">Puedes escribirlo como quieras — la IA lo interpreta automáticamente</p>
                </div>
                <div class="flex gap-3 mt-3">
                    <button type="submit"
                            class="bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold px-5 py-2 rounded-lg transition-colors">
                        Guardar y actualizar análisis
                    </button>
                    @if(session('receta_ia_' . $nivel))
                        <span class="text-xs text-green-600 self-center">✓ Receta guardada — la IA la usa en el análisis</span>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- Análisis IA automático --}}
    @if(!empty($analisisIA))
    @php
        // Separar secciones por número + punto
        preg_match_all('/(\d+\.\s[^\n:]+[:])([^1-9]*)/u', $analisisIA, $matches, PREG_SET_ORDER);
        $secciones = [];
        foreach ($matches as $m) {
            $secciones[] = [
                'titulo' => trim(rtrim($m[1], ':')),
                'texto'  => trim($m[2]),
            ];
        }
        $iconos = ['📊','🍽️','📅','🥘','✅'];
        $colores = [
            'bg-blue-50 border-blue-200 text-blue-800',
            'bg-green-50 border-green-200 text-green-800',
            'bg-yellow-50 border-yellow-200 text-yellow-800',
            'bg-orange-50 border-orange-200 text-orange-800',
            'bg-purple-50 border-purple-200 text-purple-800',
        ];
    @endphp
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ abierto: false }">
        <button @click="abierto = !abierto" class="w-full px-6 py-4 border-b border-purple-700 bg-purple-600 flex items-center justify-between gap-3 hover:bg-purple-700 transition-colors">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="text-left">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wide">Análisis IA — Recomendaciones para el CAE</h3>
                    <p class="text-xs text-purple-200 mt-0.5">Basado en tus registros · se actualiza al registrar nueva asistencia</p>
                </div>
            </div>
            <svg class="w-5 h-5 text-white/70 transition-transform duration-300 flex-shrink-0"
                 :class="abierto ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="abierto" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="p-5 space-y-3">
            @if(count($secciones) >= 2)
                @foreach($secciones as $i => $sec)
                @php $color = $colores[$i] ?? 'bg-gray-50 border-gray-200 text-gray-800'; @endphp
                <div class="border rounded-xl p-4 {{ $color }}">
                    <p class="text-xs font-bold uppercase tracking-wide mb-2 opacity-70">
                        {{ $iconos[$i] ?? '•' }} {{ $sec['titulo'] }}
                    </p>
                    <p class="text-sm leading-relaxed">{{ $sec['texto'] }}</p>
                </div>
                @endforeach
            @else
                <p class="text-sm text-gray-700 leading-relaxed whitespace-pre-wrap px-2">{{ $analisisIA }}</p>
            @endif
        </div>

        {{-- Botón descontar stock PECOSA --}}
        @if(session('receta_ia_' . $nivel))
        <div class="px-5 pb-5">
            <form method="POST" action="{{ route('prediccion.descontar-stock') }}"
                  onsubmit="return confirm('¿Confirmas descontar los ingredientes calculados por la IA del stock PECOSA?')">
                @csrf
                <input type="hidden" name="nivel" value="{{ $nivel }}">
                <button type="submit"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                    </svg>
                    Descontar ingredientes del stock PECOSA
                </button>
            </form>
        </div>
        @endif
    </div>
    @endif

    {{-- Badge de nivel --}}
    <div class="flex items-center space-x-2">
        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
            {{ $nivel === 'inicial' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' }}">
            {{ $nivel === 'inicial' ? 'Nivel Inicial' : 'Nivel Primaria' }}
        </span>
        <span class="text-sm text-gray-500">· Datos históricos de los últimos 60 días</span>
    </div>

    {{-- Resumen del mes --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Raciones este mes</p>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($resumenMes->total_raciones ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total presentes este mes</p>
            <p class="text-3xl font-bold text-gray-900">{{ number_format($resumenMes->total_presentes ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Días registrados</p>
            <p class="text-3xl font-bold text-gray-900">{{ $resumenMes->dias_registrados ?? 0 }}</p>
        </div>
    </div>

    {{-- Tabla por aula (solo inicial) — colapsable --}}
    @if($nivel === 'inicial' && count($aulas) > 0 && count($porFecha) > 0)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ abierto: false }">
        <button @click="abierto = !abierto"
                class="w-full px-6 py-4 bg-gray-50 flex items-center justify-between hover:bg-gray-100 transition-colors">
            <div class="text-left">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Asistencia por Aula — Últimos 60 días</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ count($aulas) }} aulas · {{ count($porFecha) }} días registrados · click para ver</p>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform duration-300 flex-shrink-0" :class="abierto ? 'rotate-180' : ''"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="abierto" x-transition.opacity.duration.200ms class="overflow-x-auto">
            <table class="text-xs border-collapse" style="min-width: max-content">
                <thead>
                    <tr class="bg-yellow-500 text-white uppercase">
                        <th class="px-3 py-2 text-left border border-gray-600 whitespace-nowrap sticky left-0 bg-yellow-500">Fecha</th>
                        @foreach($aulas as $aula)
                            <th class="px-2 py-2 text-center border border-gray-600 whitespace-nowrap font-semibold">{{ $aula }}</th>
                        @endforeach
                        <th class="px-3 py-2 text-center border border-gray-600 bg-yellow-600 whitespace-nowrap">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($porFecha as $fila)
                    @php
                        $diaSemana = \Carbon\Carbon::parse($fila['fecha'])->locale('es')->isoFormat('ddd');
                    @endphp
                    <tr class="border-b border-gray-100 hover:bg-yellow-50 transition-colors">
                        <td class="px-3 py-1.5 border border-gray-200 whitespace-nowrap sticky left-0 bg-white">
                            <span class="font-medium text-gray-700">{{ \Carbon\Carbon::parse($fila['fecha'])->format('d/m/Y') }}</span>
                            <span class="text-gray-400 ml-1">{{ $diaSemana }}</span>
                        </td>
                        @foreach($aulas as $aula)
                            @php
                                $dat = $fila['por_aula'][$aula] ?? null;
                                $aulaPartes = explode(' ', $aula);
                                $secAula    = array_pop($aulaPartes);
                                $gradoAula  = implode(' ', $aulaPartes);
                            @endphp
                            <td class="px-2 py-1.5 text-center border border-gray-200 whitespace-nowrap {{ $dat ? 'cursor-pointer hover:bg-yellow-100' : '' }}"
                                @if($dat) onclick="verDetalle('{{ $fila['fecha'] }}','{{ $nivel }}','{{ $gradoAula }}','{{ $secAula }}')" @endif>
                                @if($dat)
                                    <span class="font-semibold text-yellow-700">{{ $dat['presentes'] }}/{{ $dat['raciones'] }}</span>
                                @else
                                    <span class="text-gray-200">—</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="px-3 py-1.5 text-center border border-gray-200 bg-yellow-50 whitespace-nowrap font-bold text-yellow-700">
                            {{ $fila['presentes'] }}/{{ $fila['raciones'] }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif


    @if(count($historico) >= 1)

    {{-- Métricas del modelo --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Métricas del Modelo (Regresión Lineal)</h3>
            <p class="text-xs text-gray-400 mt-0.5">Calculadas sobre {{ $metricas['n'] }} días de datos históricos</p>
        </div>
        <div class="px-6 py-5 grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs text-gray-500 mb-1 font-medium">MAE</p>
                <p class="text-2xl font-bold text-gray-800">{{ $metricas['mae'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Error absoluto medio</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1 font-medium">RMSE</p>
                <p class="text-2xl font-bold text-gray-800">{{ $metricas['rmse'] }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Raíz del error cuadrático</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1 font-medium">R²</p>
                <p class="text-2xl font-bold {{ $metricas['r2'] >= 0.7 ? 'text-green-600' : ($metricas['r2'] >= 0.4 ? 'text-yellow-600' : 'text-red-500') }}">
                    {{ $metricas['r2'] }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">Coeficiente determinación</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1 font-medium">MAPE</p>
                <p class="text-2xl font-bold text-gray-800">{{ $metricas['mape'] }}%</p>
                <p class="text-xs text-gray-400 mt-0.5">Error porcentual absoluto</p>
            </div>
        </div>
        <div class="px-6 pb-4">
            <p class="text-xs text-gray-400">
                Ecuación: <span class="font-mono text-gray-600">y = {{ round(is_array($m) ? 0 : (float)$m, 4) }} · x + {{ round(is_array($b) ? 0 : (float)$b, 2) }}</span>
                <span class="ml-2 text-gray-400">(x = índice de día, y = raciones)</span>
            </p>
        </div>
    </div>

    {{-- Gráfico histórico vs predicción --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Raciones Históricas vs Tendencia</h3>
        </div>
        <div class="p-6">
            <canvas id="graficoHistorico" width="900" height="350" style="width:100%;max-width:100%;height:350px;"></canvas>
        </div>
    </div>

    {{-- Predicción próximos días --}}
    @if(count($predicciones) > 0)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Predicción Próximos Días Hábiles</h3>
            <span class="text-xs text-gray-400">Basada en regresión lineal · sin fines de semana</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3 text-right">Raciones predichas</th>
                        <th class="px-6 py-3">Indicador</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($predicciones as $pred)
                        @php
                            $avg = count($historico) > 0 ? array_sum(array_column($historico, 'raciones')) / count($historico) : 0;
                            $diff = $avg > 0 ? (($pred['raciones_predichas'] - $avg) / $avg) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3.5 text-gray-700">{{ $pred['fecha_legible'] }}</td>
                            <td class="px-6 py-3.5 text-right font-bold text-gray-900 text-base">{{ $pred['raciones_predichas'] }}</td>
                            <td class="px-6 py-3.5">
                                @if($diff > 5)
                                    <span class="inline-flex items-center space-x-1 text-green-600 text-xs font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                                        <span>+{{ round($diff, 1) }}% vs promedio</span>
                                    </span>
                                @elseif($diff < -5)
                                    <span class="inline-flex items-center space-x-1 text-red-500 text-xs font-medium">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                                        <span>{{ round($diff, 1) }}% vs promedio</span>
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">≈ promedio</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif


    @endif

    {{-- Ingredientes necesarios (solo inicial) --}}
    @if($nivel === 'inicial' && count($ingredientes ?? []) > 0)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-purple-50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-purple-800 uppercase tracking-wide">Ingredientes Necesarios — IA</h3>
                <p class="text-xs text-purple-500 mt-0.5">Calculado según raciones predichas × gramos por ración</p>
            </div>
            <a href="{{ route('pecosa.inicial.index') }}"
               class="text-xs text-purple-600 hover:underline">Actualizar análisis →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-purple-600 text-white text-xs uppercase">
                        <th class="px-4 py-2 text-left border border-purple-500">Ingrediente</th>
                        @foreach($ingredientes as $dia)
                            <th class="px-4 py-2 text-center border border-purple-500 whitespace-nowrap">
                                {{ $dia['fecha'] }}<br>
                                <span class="font-normal text-purple-200">{{ $dia['raciones'] }} rac.</span>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($ingredientes[0]['items'] as $idx => $ing)
                    <tr class="border-b border-gray-100 hover:bg-purple-50">
                        <td class="px-4 py-2 font-medium text-gray-800 border border-gray-200 uppercase text-xs">{{ $ing['producto'] }}</td>
                        @foreach($ingredientes as $dia)
                            <td class="px-4 py-2 text-center border border-gray-200">
                                <span class="font-semibold text-purple-700">{{ $dia['items'][$idx]['kg_total'] }} kg</span>
                                <span class="block text-xs text-gray-400">{{ number_format($dia['items'][$idx]['calorias_total']) }} kcal</span>
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Tabla de registros --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Registros de Asistencia</h3>
            <span class="text-xs text-gray-400">{{ $registros->total() }} registros</span>
        </div>

        @if($registros->isEmpty())
            <div class="py-10 text-center text-gray-400 text-sm">No hay registros aún.</div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-3">Fecha</th>
                        <th class="px-6 py-3">Grado</th>
                        <th class="px-6 py-3">Sección</th>
                        <th class="px-6 py-3 text-center">Total</th>
                        <th class="px-6 py-3 text-center">Presentes</th>
                        <th class="px-6 py-3 text-center">% Asist.</th>
                        <th class="px-6 py-3 text-center">Raciones</th>
                        <th class="px-6 py-3 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($registros as $reg)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3 text-gray-700">{{ $reg->fecha->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 text-gray-700">{{ $reg->grado }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $reg->seccion }}</td>
                            <td class="px-6 py-3 text-center text-gray-600">{{ $reg->total_alumnos }}</td>
                            <td class="px-6 py-3 text-center font-medium text-gray-800">{{ $reg->presentes }}</td>
                            <td class="px-6 py-3 text-center">
                                <span class="text-xs font-semibold {{ $reg->porcentaje_asistencia >= 85 ? 'text-green-600' : ($reg->porcentaje_asistencia >= 70 ? 'text-yellow-600' : 'text-red-500') }}">
                                    {{ $reg->porcentaje_asistencia }}%
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center font-bold text-gray-900">{{ $reg->raciones }}</td>
                            <td class="px-6 py-3 text-right">
                                <form method="POST" action="{{ route('prediccion.destroy', $reg) }}"
                                      onsubmit="return confirm('¿Eliminar este registro?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-medium">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($registros->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $registros->links() }}
            </div>
        @endif
        @endif
    </div>

</div>
@endsection

@push('scripts')
@if(count($historico) >= 2)
<script src="{{ '/js/chart.min.js' }}"></script>
<script>
(function () {
    const historico   = @json($historico);
    const predicciones = @json($predicciones);
    const m = {{ is_array($m) ? 0 : (float)$m }};
    const b = {{ is_array($b) ? 0 : (float)$b }};
    const esInicial = {{ $nivel === 'inicial' ? 'true' : 'false' }};

    const colorPrimario = esInicial ? '#eab308' : '#3b82f6';
    const colorFondo    = esInicial ? 'rgba(234,179,8,0.12)' : 'rgba(59,130,246,0.12)';
    const colorPrediccion = '#10b981';

    // Labels: histórico + predicción
    const labelsHist = historico.map(r => r.fecha);
    const labelsPred = predicciones.map(p => p.fecha_legible);
    const todosLabels = [...labelsHist, ...labelsPred];

    const actuales  = historico.map(r => r.raciones);
    const tendencia = historico.map((_, i) => Math.round(Math.max(0, m * i + b)));

    // Dataset predicción: null para los días históricos, valores para los futuros
    const datosPred = [
        ...historico.map(() => null),
        ...predicciones.map(p => p.raciones_predichas)
    ];

    // Unir último punto histórico con primer punto predicho para que la línea conecte
    const nHist = historico.length;
    const ultimoReal = actuales[nHist - 1] ?? null;
    if (datosPred.length > nHist) {
        datosPred[nHist - 1] = ultimoReal;
    }

    const ctx = document.getElementById('graficoHistorico');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: todosLabels,
            datasets: [
                {
                    label: 'Raciones reales',
                    data: [...actuales, ...predicciones.map(() => null)],
                    borderColor: colorPrimario,
                    backgroundColor: colorFondo,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: colorPrimario,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    fill: true,
                    tension: 0.35,
                    order: 2,
                },
                {
                    label: 'Predicción (7 días)',
                    data: datosPred,
                    borderColor: colorPrediccion,
                    backgroundColor: 'rgba(16,185,129,0.08)',
                    borderWidth: 2.5,
                    borderDash: [7, 4],
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: colorPrediccion,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointStyle: 'rectRot',
                    fill: true,
                    tension: 0.2,
                    order: 1,
                },
                {
                    label: 'Tendencia (regresión)',
                    data: [...tendencia, ...predicciones.map(() => null)],
                    borderColor: 'rgba(156,163,175,0.7)',
                    borderWidth: 1.5,
                    borderDash: [4, 4],
                    pointRadius: 0,
                    fill: false,
                    tension: 0,
                    order: 3,
                },
            ],
        },
        options: {
            responsive: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { size: 12 },
                    },
                },
                tooltip: {
                    backgroundColor: 'rgba(17,24,39,0.92)',
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => {
                            if (ctx.parsed.y === null) return null;
                            return ` ${ctx.dataset.label}: ${ctx.parsed.y} raciones`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    ticks: {
                        maxTicksLimit: 16,
                        maxRotation: 40,
                        font: { size: 10 },
                        color: '#6b7280',
                    },
                    grid: { color: 'rgba(229,231,235,0.6)' },
                },
                y: {
                    beginAtZero: false,
                    title: {
                        display: true,
                        text: 'Raciones',
                        font: { size: 11 },
                        color: '#9ca3af',
                    },
                    ticks: { font: { size: 11 }, color: '#6b7280' },
                    grid: { color: 'rgba(229,231,235,0.6)' },
                },
            },
            animation: false,
        },
    });
})();
</script>
@endif

<script>
function verDetalle(fecha, nivel, grado, seccion) {
    const modal    = document.getElementById('modal-detalle');
    const loading  = document.getElementById('modal-loading');
    const lista    = document.getElementById('modal-lista');
    const sinDet   = document.getElementById('modal-sin-detalle');
    const footer   = document.getElementById('modal-footer');

    document.getElementById('modal-titulo').textContent   = grado + ' — Sección ' + seccion;
    document.getElementById('modal-subtitulo').textContent = fecha.split('-').reverse().join('/');

    modal.classList.remove('hidden');
    loading.classList.remove('hidden');
    lista.classList.add('hidden');
    sinDet.classList.add('hidden');
    footer.classList.add('hidden');

    const url = '{{ route("prediccion.detalle-aula") }}'
        + '?fecha='   + encodeURIComponent(fecha)
        + '&nivel='   + encodeURIComponent(nivel)
        + '&grado='   + encodeURIComponent(grado)
        + '&seccion=' + encodeURIComponent(seccion);

    fetch(url, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            loading.classList.add('hidden');
            if (data.error || !data.detalle || data.detalle.length === 0) {
                sinDet.classList.remove('hidden');
                document.getElementById('modal-subtitulo').textContent =
                    (data.fecha || '') + ' · ' + data.presentes + ' presentes / ' + data.total + ' total';
                footer.classList.remove('hidden');
                document.getElementById('modal-presentes').textContent = data.presentes ?? '—';
                document.getElementById('modal-ausentes').textContent  = (data.total - data.presentes) ?? '—';
                return;
            }
            lista.innerHTML = '';
            data.detalle.forEach((a, i) => {
                const div = document.createElement('div');
                div.className = 'flex items-center gap-3 py-1.5 border-b border-gray-50 last:border-0';
                div.innerHTML = `
                    <span class="text-xs text-gray-400 w-5 text-right">${i+1}</span>
                    <span class="flex-1 text-sm text-gray-800">${a.nombre}</span>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full ${a.presente ? 'bg-green-50 text-green-600' : 'bg-red-50 text-red-400'}">
                        ${a.presente ? 'Presente' : 'Ausente'}
                    </span>`;
                lista.appendChild(div);
            });
            const ausentes = data.detalle.filter(a => !a.presente).length;
            document.getElementById('modal-subtitulo').textContent = data.fecha + ' · ' + data.grado + ' ' + data.seccion;
            document.getElementById('modal-presentes').textContent = data.detalle.filter(a => a.presente).length;
            document.getElementById('modal-ausentes').textContent  = ausentes;
            lista.classList.remove('hidden');
            footer.classList.remove('hidden');
        })
        .catch(() => { loading.classList.add('hidden'); sinDet.classList.remove('hidden'); });
}

function cerrarDetalle() {
    document.getElementById('modal-detalle').classList.add('hidden');
}

function analizarConIA() {
    const btn      = document.getElementById('btn-ia-pred');
    const loading  = document.getElementById('ia-loading');
    const resultado= document.getElementById('ia-resultado');
    const inicial  = document.getElementById('ia-inicial');

    btn.disabled = true;
    btn.textContent = 'Analizando…';
    loading.classList.remove('hidden');
    resultado.classList.add('hidden');
    inicial.classList.add('hidden');

    fetch('{{ route("prediccion.ia") }}?nivel={{ $nivel }}', {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => {
        loading.classList.add('hidden');
        btn.disabled = false;
        btn.innerHTML = '⚡ Analizar con IA';
        if (data.error) {
            resultado.textContent = '❌ ' + data.error;
        } else {
            resultado.textContent = data.analisis;
        }
        resultado.classList.remove('hidden');
    })
    .catch(() => {
        loading.classList.add('hidden');
        btn.disabled = false;
        btn.innerHTML = '⚡ Analizar con IA';
        resultado.textContent = '❌ Error de conexión.';
        resultado.classList.remove('hidden');
    });
}
</script>
@endpush
