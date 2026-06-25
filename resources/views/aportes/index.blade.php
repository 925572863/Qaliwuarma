@extends('layouts.app')
@section('title', 'Aportes PAE')
@section('page-title', '')

@push('styles')
<style>
    header.shadow-sm { display: none !important; }
</style>
@endpush

@section('content')
<div id="aportes-root">

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm mb-4 no-print">
            {{ session('success') }}
        </div>
    @endif

    {{-- ── Filtros ── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-5 no-print">
        <div class="p-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3 bg-gradient-to-r from-yellow-50 to-transparent">
            <form method="GET" id="filtros-form" class="flex flex-wrap items-center gap-3">
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase">Año</label>
                    <select name="anio" onchange="document.getElementById('filtros-form').submit()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        @foreach(range(now()->year - 1, now()->year + 1) as $y)
                            <option value="{{ $y }}" {{ $y == $anio ? 'selected' : '' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center space-x-2">
                    <label class="text-xs font-semibold text-gray-500 uppercase">Mes</label>
                    <select name="mes" onchange="document.getElementById('filtros-form').submit()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        @foreach($meses as $n => $nombre)
                            <option value="{{ $n }}" {{ $n == $mes ? 'selected' : '' }}>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <div class="flex items-center space-x-2">
                <button onclick="window.print()" type="button"
                        class="px-4 py-2 bg-white border border-gray-200 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Imprimir</span>
                </button>
            </div>
        </div>
    </div>

    @if($aulas->isEmpty())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm px-6 py-14 text-center">
            <p class="text-gray-400 text-sm">No hay alumnos de nivel inicial registrados.</p>
        </div>
    @else

    {{-- Título imprimible --}}
    <div class="print-title hidden mb-3 text-center">
        <p class="font-black text-xs uppercase tracking-wider border border-gray-700 inline-block px-4 py-1">
            Control de Aportes para la Preparación de Alimentos del Programa de Alimentación Escolar (PAE) – Docentes – {{ $anio }}
        </p>
    </div>

    <form method="POST" action="{{ route('aportes.pagos.store') }}">
        @csrf
        <input type="hidden" name="anio" value="{{ $anio }}">
        <input type="hidden" name="mes" value="{{ $mes }}">

        @php
            $aulasPorGrado = $aulas->groupBy(fn($a) => $a['config']->grado);
            $hojaNum = 0;
        @endphp

        @foreach($aulasPorGrado as $grado => $aulasGrado)

        {{-- Cabecera de grupo colapsable --}}
        @php $gradoId = 'grupo-' . Str::slug($grado); @endphp
        <div class="mt-6 mb-2 no-print">
            <button type="button" onclick="toggleGrupo('{{ $gradoId }}')"
                    class="w-full flex items-center gap-3 group">
                <div class="h-px flex-1 bg-yellow-300"></div>
                <span class="flex items-center gap-2 px-4 py-1.5 bg-yellow-400 hover:bg-yellow-500 text-gray-900 text-xs font-black uppercase rounded-full tracking-widest transition-colors cursor-pointer select-none">
                    {{ strtoupper($grado) }}
                    <svg id="{{ $gradoId }}-icon" class="w-3.5 h-3.5 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"/>
                    </svg>
                </span>
                <div class="h-px flex-1 bg-yellow-300"></div>
            </button>
        </div>

        <div id="{{ $gradoId }}" class="space-y-8">
        @foreach($aulasGrado as $aula)
        @php
            $hojaNum++;
            $config     = $aula['config'];
            $alumnos    = $aula['alumnos'];
            $semanas    = $aula['semanas'];
            $pagosMap   = $aula['pagosMap'];
            $firmadoMap = $aula['firmadoMap'];
            $cuota      = $config->cuota_por_dia;
            $semanasHabiles = $semanas->filter(fn($s) => !$s->es_vacaciones);
            $totalMesEsperado = $semanasHabiles->sum(fn($s) => $s->dias_habiles * $cuota);
        @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

            {{-- ── Cabecera estilo Excel ── --}}
            <div class="border-b-2 border-gray-700">
                {{-- Fila título del aula --}}
                <div class="grid grid-cols-12 border-b border-gray-400 text-xs font-black uppercase">
                    <div class="col-span-1 bg-yellow-400 text-gray-900 px-3 py-2 flex items-center justify-center border-r border-gray-400">
                        HOJA {{ $hojaNum }}
                    </div>
                    <div class="col-span-3 bg-yellow-300 text-gray-900 px-3 py-2 flex items-center justify-center border-r border-gray-400">
                        {{ $config->grado }} "{{ $config->seccion }}"
                    </div>
                    <div class="col-span-2 bg-yellow-300 text-gray-700 px-3 py-2 flex items-center justify-center text-[10px] border-r border-gray-400">
                        Nº Est. por día
                    </div>
                    <div class="col-span-4 bg-white text-gray-800 px-3 py-2 flex items-center border-r border-gray-400 normal-case font-semibold text-[10px]">
                        @if($config->profesora)
                            PROFESOR(A): {{ strtoupper($config->profesora) }}
                        @else
                            <span class="text-gray-400 italic">Sin docente registrado</span>
                        @endif
                    </div>
                    <div class="col-span-2 bg-yellow-100 text-gray-700 px-3 py-2 flex items-center justify-center text-[10px]">
                        Cuota: <span class="text-yellow-700 font-black ml-1">S/ {{ number_format($cuota, 2) }}/día</span>
                    </div>
                </div>

                {{-- Fila calendario de semanas --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-[9px] border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-400 px-2 py-1 text-left font-bold text-gray-700 uppercase w-32">Nº de días</th>
                                <th class="border border-gray-400 px-2 py-1 text-center font-bold text-gray-700 uppercase">Días por semana</th>
                                <th class="border border-gray-400 px-2 py-1 text-center font-bold text-yellow-700 uppercase w-8">L</th>
                                <th class="border border-gray-400 px-2 py-1 text-center font-bold text-yellow-700 uppercase w-8">M</th>
                                <th class="border border-gray-400 px-2 py-1 text-center font-bold text-yellow-700 uppercase w-8">MI</th>
                                <th class="border border-gray-400 px-2 py-1 text-center font-bold text-yellow-700 uppercase w-8">J</th>
                                <th class="border border-gray-400 px-2 py-1 text-center font-bold text-yellow-700 uppercase w-8">V</th>
                                <th class="border border-gray-400 px-2 py-1 text-center font-bold text-gray-600 uppercase no-print">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $diasTotales = 0; @endphp
                            @foreach($semanas as $semana)
                            @php $diasTotales += $semana->dias_habiles; @endphp
                            <tr class="{{ $semana->es_vacaciones ? 'bg-red-50' : 'bg-white' }} hover:bg-yellow-50">
                                <td class="border border-gray-300 px-2 py-1 font-black text-gray-800 text-center text-sm">
                                    @if(!$semana->es_vacaciones) {{ $semana->dias_habiles }} @else — @endif
                                </td>
                                <td class="border border-gray-300 px-2 py-1 text-gray-700 font-semibold">
                                    @if($semana->es_vacaciones)
                                        <span class="text-red-500 font-bold uppercase">Vacaciones</span>
                                    @else
                                        {{ $semana->fecha_inicio->format('d') }}–{{ $semana->fecha_fin->format('d') }} de {{ $meses[$mes] }}
                                    @endif
                                </td>
                                @foreach(['lunes','martes','miercoles','jueves','viernes'] as $dia)
                                <td class="border border-gray-300 px-1 py-1 text-center font-bold
                                           {{ $semana->$dia && !$semana->es_vacaciones ? 'text-green-700 bg-green-50' : 'text-gray-300' }}">
                                    {{ ($semana->$dia && !$semana->es_vacaciones) ? $alumnos->count() : '—' }}
                                </td>
                                @endforeach
                                <td class="border border-gray-300 px-2 py-1 text-center no-print">
                                    <button type="button"
                                            onclick="abrirModalSemana({{ $config->id }}, '{{ addslashes($config->grado) }}', '{{ $config->seccion }}')"
                                            class="text-[9px] text-blue-500 hover:underline">editar</button>
                                </td>
                            </tr>
                            @endforeach
                            <tr class="bg-yellow-100 font-black">
                                <td class="border border-gray-400 px-2 py-1 text-center text-sm text-yellow-800">{{ $diasTotales }}</td>
                                <td class="border border-gray-400 px-2 py-1 text-yellow-800 uppercase text-[9px]">Total días hábiles del mes</td>
                                <td colspan="5" class="border border-gray-400 px-2 py-1 text-center text-yellow-800">
                                    Total a cancelar: <span class="text-yellow-900">S/ {{ number_format($totalMesEsperado, 2) }}</span>
                                </td>
                                <td class="border border-gray-400 no-print"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── Tabla principal de pagos por alumno ── --}}
            @if($alumnos->isEmpty())
                <div class="px-5 py-8 text-center text-sm text-gray-400">
                    No hay alumnos activos en esta aula.
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-[10px] border-collapse">
                    <thead>
                        <tr class="bg-gray-800 text-white">
                            <th rowspan="2" class="border border-gray-600 px-2 py-2 text-center font-black uppercase text-[9px] w-8">Nº</th>
                            <th rowspan="2" class="border border-gray-600 px-3 py-2 text-left font-black uppercase text-[9px]" style="min-width:170px">Apellidos y Nombres</th>
                            @foreach($semanas as $semana)
                                @if(!$semana->es_vacaciones)
                                <th colspan="4" class="border border-gray-600 px-2 py-1 text-center font-black text-[9px] bg-yellow-700 uppercase">
                                    Semana {{ $semana->semana_num }}
                                    <span class="font-normal text-yellow-200 block">{{ $semana->fecha_inicio->format('d/m') }}–{{ $semana->fecha_fin->format('d/m') }}</span>
                                </th>
                                @else
                                <th class="border border-gray-600 px-2 py-1 text-center font-black text-[9px] bg-red-800 uppercase">
                                    VAC<br><span class="font-normal text-red-300 text-[8px]">{{ $semana->fecha_inicio->format('d/m') }}</span>
                                </th>
                                @endif
                            @endforeach
                            <th rowspan="2" class="border border-gray-600 px-2 py-2 text-center font-black text-[9px] bg-yellow-700 uppercase">Total<br>Cancelar</th>
                            <th rowspan="2" class="border border-gray-600 px-2 py-2 text-center font-black text-[9px] bg-green-700 uppercase">Total<br>Aportó</th>
                            <th rowspan="2" class="border border-gray-600 px-2 py-2 text-center font-black text-[9px] bg-red-700 uppercase">Total<br>Debe</th>
                        </tr>
                        <tr class="bg-gray-700 text-white text-[8px]">
                            @foreach($semanas as $semana)
                                @if(!$semana->es_vacaciones)
                                <th class="border border-gray-500 px-1 py-1 text-center bg-yellow-600 uppercase">Cuota<br>S/{{ number_format($semana->dias_habiles * $cuota, 2) }}</th>
                                <th class="border border-gray-500 px-1 py-1 text-center bg-green-700 uppercase">Aporta</th>
                                <th class="border border-gray-500 px-1 py-1 text-center bg-red-700 uppercase">Debe</th>
                                <th class="border border-gray-500 px-1 py-1 text-center bg-gray-600 uppercase">Firma</th>
                                @else
                                <th class="border border-gray-500 px-1 py-1 text-center bg-red-900 text-[8px]">—</th>
                                @endif
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $totalEsperadoAula  = 0;
                            $totalAportadoAula  = 0;
                        @endphp
                        @foreach($alumnos as $i => $alumno)
                        @php
                            $totalEsperadoAlumno  = 0;
                            $totalAportadoAlumno  = 0;
                            foreach ($semanasHabiles as $s) {
                                $totalEsperadoAlumno += $s->dias_habiles * $cuota;
                                $totalAportadoAlumno += $pagosMap[$s->id][$alumno->id] ?? 0;
                            }
                            $debeAlumno = max(0, $totalEsperadoAlumno - $totalAportadoAlumno);
                            $totalEsperadoAula  += $totalEsperadoAlumno;
                            $totalAportadoAula  += $totalAportadoAlumno;
                            $ap = strtoupper(trim(($alumno->apellido_paterno ?? '') . ' ' . ($alumno->apellido_materno ?? '')));
                            $nm = strtoupper($alumno->nombre ?? '');
                        @endphp
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-yellow-50 transition-colors">
                            <td class="border border-gray-300 text-center px-2 py-1.5 text-gray-600 font-medium">{{ $i + 1 }}</td>
                            <td class="border border-gray-300 px-3 py-1.5 text-gray-900 font-semibold uppercase text-[10px]">
                                {{ $ap }}, {{ $nm }}
                            </td>

                            @foreach($semanas as $semana)
                            @if(!$semana->es_vacaciones)
                            @php
                                $cuotaSem   = $semana->dias_habiles * $cuota;
                                $aportado   = $pagosMap[$semana->id][$alumno->id] ?? 0;
                                $debeSem    = max(0, $cuotaSem - $aportado);
                                $firmado    = $firmadoMap[$semana->id][$alumno->id] ?? false;
                            @endphp
                            <td class="border border-gray-300 text-center px-1 py-1 bg-yellow-50 text-yellow-800 font-bold text-[9px]">
                                S/ {{ number_format($cuotaSem, 2) }}
                            </td>
                            <td class="border border-gray-300 text-center px-1 py-1 bg-green-50">
                                <input type="number"
                                       name="pagos[{{ $semana->id }}][{{ $alumno->id }}]"
                                       value="{{ $aportado > 0 ? number_format($aportado, 2, '.', '') : '' }}"
                                       min="0" step="0.10" placeholder="0.00"
                                       class="w-14 px-1 py-0.5 border border-green-300 rounded text-[10px] text-center
                                              focus:outline-none focus:ring-1 focus:ring-green-400 bg-white no-print">
                                <span class="print-only text-[10px] font-bold text-green-800">
                                    {{ $aportado > 0 ? 'S/ '.number_format($aportado, 2) : '' }}
                                </span>
                            </td>
                            <td class="border border-gray-300 text-center px-1 py-1 text-[10px] font-bold
                                       {{ $debeSem > 0 ? 'text-red-600 bg-red-50' : 'text-green-600 bg-green-50' }}">
                                {{ $debeSem > 0 ? 'S/ '.number_format($debeSem, 2) : '—' }}
                            </td>
                            <td class="border border-gray-300 text-center px-1 py-1 bg-gray-50">
                                <label class="flex items-center justify-center cursor-pointer no-print">
                                    <input type="checkbox"
                                           name="firmado[{{ $semana->id }}][{{ $alumno->id }}]"
                                           value="1"
                                           {{ $firmado ? 'checked' : '' }}
                                           class="w-4 h-4 rounded text-green-600 border-gray-400 focus:ring-green-400">
                                </label>
                                <span class="print-only text-[11px] text-center block">{{ $firmado ? '✓' : '' }}</span>
                            </td>
                            @else
                            <td class="border border-gray-300 text-center px-1 py-1 bg-red-50 text-red-300 text-[9px]">VAC</td>
                            @endif
                            @endforeach

                            <td class="border border-gray-300 text-center px-2 py-1.5 font-bold text-[10px] text-yellow-800 bg-yellow-50">
                                S/ {{ number_format($totalEsperadoAlumno, 2) }}
                            </td>
                            <td class="border border-gray-300 text-center px-2 py-1.5 font-bold text-[10px] text-green-700 bg-green-50">
                                S/ {{ number_format($totalAportadoAlumno, 2) }}
                            </td>
                            <td class="border border-gray-300 text-center px-2 py-1.5 font-bold text-[10px]
                                       {{ $debeAlumno > 0 ? 'text-red-600 bg-red-50' : 'text-green-600 bg-green-50' }}">
                                {{ $debeAlumno > 0 ? 'S/ '.number_format($debeAlumno, 2) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        @php $debeAula = max(0, $totalEsperadoAula - $totalAportadoAula); @endphp
                        <tr class="bg-gray-800 text-white font-black text-[10px]">
                            <td colspan="2" class="border border-gray-600 text-center px-3 py-2 uppercase tracking-wider">
                                TOTAL ({{ $alumnos->count() }} alumnos)
                            </td>
                            @foreach($semanas as $semana)
                                @if(!$semana->es_vacaciones)
                                @php $totalSem = collect($pagosMap[$semana->id] ?? [])->sum(); @endphp
                                <td class="border border-gray-600 text-center px-1 py-2 text-[9px] bg-yellow-700">
                                    S/ {{ number_format($semana->dias_habiles * $cuota * $alumnos->count(), 2) }}
                                </td>
                                <td class="border border-gray-600 text-center px-1 py-2 text-[9px] bg-green-700">
                                    S/ {{ number_format($totalSem, 2) }}
                                </td>
                                <td class="border border-gray-600 text-center px-1 py-2 text-[9px] bg-red-700">
                                    S/ {{ number_format(max(0, $semana->dias_habiles * $cuota * $alumnos->count() - $totalSem), 2) }}
                                </td>
                                <td class="border border-gray-600 text-center px-1 py-2 text-[9px] bg-gray-600">
                                    {{ collect($firmadoMap[$semana->id] ?? [])->filter()->count() }}/{{ $alumnos->count() }}
                                </td>
                                @else
                                <td class="border border-gray-600 px-1 py-2 bg-red-900 text-center text-[9px]">VAC</td>
                                @endif
                            @endforeach
                            <td class="border border-gray-600 text-center px-2 py-2 bg-yellow-700">
                                S/ {{ number_format($totalEsperadoAula, 2) }}
                            </td>
                            <td class="border border-gray-600 text-center px-2 py-2 bg-green-700">
                                S/ {{ number_format($totalAportadoAula, 2) }}
                            </td>
                            <td class="border border-gray-600 text-center px-2 py-2 {{ $debeAula > 0 ? 'bg-red-700' : 'bg-green-700' }}">
                                S/ {{ number_format($debeAula, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif

            {{-- Botón agregar semana --}}
            <div class="px-4 py-3 border-t border-gray-100 flex justify-end no-print">
                <button type="button"
                        onclick="abrirModalSemana({{ $config->id }}, '{{ addslashes($config->grado) }}', '{{ $config->seccion }}')"
                        class="px-3 py-1.5 bg-white border border-gray-200 text-gray-600 rounded-lg text-xs font-medium hover:bg-gray-50 transition-colors">
                    + Agregar / editar semana
                </button>
            </div>

        </div>
        @endforeach
        </div>{{-- fin aulasGrado --}}

        @endforeach{{-- fin aulasPorGrado --}}

        <div class="flex justify-end pt-4 no-print">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-8 py-3 rounded-xl shadow-md transition-colors">
                Guardar todos los aportes
            </button>
        </div>
    </form>
    @endif

</div>

{{-- ── Modal agregar semana (JS puro) ── --}}
<div id="modal-semana" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Agregar Semana – <span id="modal-aula-label"></span></h3>
            <button type="button" onclick="cerrarModalSemana()" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
        </div>
        <form id="form-semana" method="POST" class="px-6 py-5 space-y-4">
            @csrf
            <input type="hidden" name="mes" value="{{ $mes }}">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">N° Semana</label>
                    <select name="semana_num" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        @foreach(range(1,6) as $n)
                            <option value="{{ $n }}">Semana {{ $n }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="es_vacaciones" value="1" class="w-4 h-4 rounded text-red-500">
                        <span class="text-sm text-red-600 font-semibold">Vacaciones</span>
                    </label>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" required
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fecha fin</label>
                    <input type="date" name="fecha_fin" required
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Días hábiles</label>
                <div class="flex items-center space-x-4">
                    @foreach(['lunes'=>'Lun','martes'=>'Mar','miercoles'=>'Mié','jueves'=>'Jue','viernes'=>'Vie'] as $campo => $letra)
                    <label class="flex flex-col items-center space-y-1 cursor-pointer">
                        <input type="checkbox" name="{{ $campo }}" value="1" checked class="w-4 h-4 rounded text-yellow-500">
                        <span class="text-xs font-bold text-gray-600">{{ $letra }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <div class="flex items-center space-x-3 pt-1">
                <button type="submit" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white font-semibold text-sm py-2.5 rounded-lg transition-colors">
                    Guardar Semana
                </button>
                <button type="button" onclick="cerrarModalSemana()"
                        class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    .print-only { display: none; }
    @media print {
        aside, header, .no-print { display: none !important; }
        html, body { background: white !important; margin: 0 !important; padding: 0 !important; }
        .flex.h-screen { display: block !important; height: auto !important; overflow: visible !important; }
        .flex-1.flex.flex-col { display: block !important; overflow: visible !important; }
        main { padding: 0 !important; overflow: visible !important; }
        .bg-white.rounded-2xl { border-radius: 0 !important; box-shadow: none !important; border: none !important; }
        .overflow-x-auto { overflow: visible !important; }
        .print-title { display: block !important; }
        .print-only { display: inline !important; }
        table { width: 100% !important; border-collapse: collapse !important; font-size: 7px !important; }
        th, td { padding: 2px 3px !important; border: 0.5px solid #444 !important; }
        thead { display: table-header-group !important; }
        tfoot { display: table-footer-group !important; }
        .space-y-8 > div { margin-bottom: 10mm !important; page-break-inside: avoid; }
        @page { size: A4 landscape; margin: 8mm; }
    }
</style>

@push('scripts')
<script>
function toggleGrupo(id) {
    const el   = document.getElementById(id);
    const icon = document.getElementById(id + '-icon');
    const hidden = el.classList.toggle('hidden');
    icon.style.transform = hidden ? 'rotate(180deg)' : 'rotate(0deg)';
}

function abrirModalSemana(configId, grado, seccion) {
    document.getElementById('modal-aula-label').textContent = grado + ' "' + seccion + '"';
    document.getElementById('form-semana').action = '/aportes/config/' + configId + '/semana';
    document.getElementById('modal-semana').classList.remove('hidden');
}

function cerrarModalSemana() {
    document.getElementById('modal-semana').classList.add('hidden');
}

document.getElementById('modal-semana').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalSemana();
});
</script>
@endpush
@endsection
