@extends('layouts.app')
@section('title', 'Aportes PAE')
@section('page-title', '')

@push('styles')
<style>
    header.shadow-sm { display: none !important; }
</style>
@endpush

@section('content')
<div x-data="aporteIndex()">

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
    <div class="print-title hidden mb-4 text-center">
        <p class="font-bold text-sm uppercase">Control de Aportes PAE – Nivel Inicial</p>
        <p class="text-xs text-gray-600">{{ $meses[$mes] }} {{ $anio }}</p>
    </div>

    <form method="POST" action="{{ route('aportes.pagos.store') }}">
        @csrf
        <input type="hidden" name="anio" value="{{ $anio }}">
        <input type="hidden" name="mes" value="{{ $mes }}">

        <div class="space-y-6">
        @foreach($aulas as $aula)
        @php
            $config   = $aula['config'];
            $alumnos  = $aula['alumnos'];
            $semanas  = $aula['semanas'];
            $pagosMap = $aula['pagosMap'];
            $cuota    = $config->cuota_por_dia;
            $semanasHabiles = $semanas->filter(fn($s) => !$s->es_vacaciones);
        @endphp

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            {{-- Cabecera aula --}}
            <div class="p-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2 bg-gradient-to-r from-yellow-50 to-transparent no-print">
                <div>
                    <h2 class="text-base font-bold text-gray-800">
                        {{ $config->grado }} "{{ $config->seccion }}"
                        @if($config->profesora)
                            <span class="text-sm font-normal text-gray-500">· {{ $config->profesora }}</span>
                        @endif
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $alumnos->count() }} alumnos
                        · {{ $semanas->count() }} semana(s) configurada(s)
                        · Cuota S/ {{ number_format($cuota, 2) }}/día
                    </p>
                </div>
                <button type="button"
                        @click="abrirModalSemana({{ $config->id }}, '{{ addslashes($config->grado) }}', '{{ $config->seccion }}')"
                        class="px-3 py-1.5 bg-white border border-gray-200 text-gray-600 rounded-lg text-xs font-medium hover:bg-gray-50 transition-colors">
                    + Agregar semana
                </button>
            </div>

            @if($alumnos->isEmpty())
                <div class="px-5 py-8 text-center text-sm text-gray-400 no-print">
                    No hay alumnos activos en esta aula.
                </div>
            @else
            <div class="overflow-x-auto p-4">
                <table class="w-full text-xs border-collapse">
                    <thead>
                        {{-- Fila 1 --}}
                        <tr>
                            <th rowspan="2" colspan="2"
                                class="border-2 border-gray-700 bg-yellow-300 text-gray-800 font-bold text-center px-3 py-2 uppercase text-xs">
                                {{ $config->grado }} "{{ $config->seccion }}"<br>
                                <span class="font-normal text-[10px] text-gray-600 normal-case">{{ $meses[$mes] }} {{ $anio }}</span>
                            </th>
                            @foreach($semanas as $semana)
                                <th class="border-2 border-gray-700 {{ $semana->es_vacaciones ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700' }}
                                           font-bold text-center px-2 py-1 text-[9px] uppercase tracking-wide">
                                    @if($semana->es_vacaciones)
                                        VAC
                                    @else
                                        Sem {{ $semana->semana_num }}<br>
                                        <span class="font-normal text-gray-500">{{ $semana->fecha_inicio->format('d/m') }}–{{ $semana->fecha_fin->format('d/m') }}</span>
                                    @endif
                                </th>
                            @endforeach
                            <th class="border-2 border-gray-700 bg-yellow-200 text-gray-800 font-bold text-center px-2 py-1 text-[9px] uppercase">
                                Total<br>Cancelar
                            </th>
                            <th class="border-2 border-gray-700 bg-green-100 text-green-800 font-bold text-center px-2 py-1 text-[9px] uppercase">
                                Aportado
                            </th>
                            <th class="border-2 border-gray-700 bg-red-100 text-red-800 font-bold text-center px-2 py-1 text-[9px] uppercase">
                                Debe
                            </th>
                        </tr>
                        {{-- Fila 2: cuota por semana --}}
                        <tr>
                            @foreach($semanas as $semana)
                                <th class="border border-gray-400 text-center px-1 py-0.5 text-[9px] font-bold
                                           {{ $semana->es_vacaciones ? 'bg-red-50 text-red-400' : 'bg-yellow-50 text-gray-700' }}">
                                    @if(!$semana->es_vacaciones)
                                        <span class="text-yellow-700 font-black">S/ {{ number_format($cuota, 2) }}</span>/día<br>
                                        <span class="text-gray-500">Total: S/ {{ number_format($semana->dias_habiles * $cuota, 2) }}</span>
                                    @endif
                                </th>
                            @endforeach
                            <th class="border border-gray-400 bg-yellow-50 text-[9px] text-center px-1 py-0.5 font-bold text-gray-600">
                                S/ {{ number_format($semanasHabiles->sum(fn($s) => $s->dias_habiles * $cuota), 2) }}
                            </th>
                            <th class="border border-gray-400 bg-green-50 text-[9px] text-center"></th>
                            <th class="border border-gray-400 bg-red-50 text-[9px] text-center"></th>
                        </tr>
                        {{-- Fila 3: N° / Apellidos --}}
                        <tr class="bg-gray-700 text-white">
                            <th class="border border-gray-500 px-2 py-1.5 text-center font-bold uppercase text-[9px] w-8">Nº</th>
                            <th class="border border-gray-500 px-3 py-1.5 text-left font-bold uppercase text-[9px]">Apellidos y Nombres</th>
                            @foreach($semanas as $semana)
                                <th class="border border-gray-500 px-1 py-1.5 text-center font-bold text-[9px] w-16
                                           {{ $semana->es_vacaciones ? 'bg-red-800' : '' }}">
                                    {{ $semana->es_vacaciones ? 'VAC' : 'S'.$semana->semana_num }}
                                </th>
                            @endforeach
                            <th class="border border-gray-500 px-1 py-1.5 text-center font-bold text-[9px] bg-yellow-700">Total</th>
                            <th class="border border-gray-500 px-1 py-1.5 text-center font-bold text-[9px] bg-green-700">Aportó</th>
                            <th class="border border-gray-500 px-1 py-1.5 text-center font-bold text-[9px] bg-red-700">Debe</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $totalEsperadoAula = 0;
                            $totalAportadoAula = 0;
                        @endphp
                        @foreach($alumnos as $i => $alumno)
                        @php
                            $totalEsperadoAlumno = 0;
                            $totalAportadoAlumno = 0;
                            foreach ($semanasHabiles as $s) {
                                $totalEsperadoAlumno += $s->dias_habiles * $cuota;
                                $totalAportadoAlumno += $pagosMap[$s->id][$alumno->id] ?? 0;
                            }
                            $debeAlumno = max(0, $totalEsperadoAlumno - $totalAportadoAlumno);
                            $totalEsperadoAula += $totalEsperadoAlumno;
                            $totalAportadoAula += $totalAportadoAlumno;
                        @endphp
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-gray-50' }} hover:bg-yellow-50 transition-colors">
                            <td class="border border-gray-300 text-center px-2 py-1.5 text-gray-700 font-medium">{{ $i + 1 }}</td>
                            <td class="border border-gray-300 px-3 py-1.5 text-gray-800 font-medium uppercase text-[10px]" style="min-width:160px">
                                @php
                                    $ap = strtoupper(trim(($alumno->apellido_paterno ?? '') . ' ' . ($alumno->apellido_materno ?? '')));
                                    $nm = strtoupper($alumno->nombre ?? '');
                                @endphp
                                {{ $ap }}, {{ $nm }}
                            </td>
                            @foreach($semanas as $semana)
                            <td class="border border-gray-300 text-center px-1 py-1
                                       {{ $semana->es_vacaciones ? 'bg-red-50' : 'bg-yellow-50/40' }}">
                                @if(!$semana->es_vacaciones)
                                    @php $val = $pagosMap[$semana->id][$alumno->id] ?? 0; @endphp
                                    <input type="number"
                                           name="pagos[{{ $semana->id }}][{{ $alumno->id }}]"
                                           value="{{ $val > 0 ? number_format($val, 2, '.', '') : '' }}"
                                           min="0" step="0.10" placeholder="0"
                                           class="w-16 px-1 py-0.5 border border-yellow-300 rounded text-[10px] text-center
                                                  focus:outline-none focus:ring-1 focus:ring-yellow-400 bg-white no-print">
                                    <span class="print-only text-[10px] font-bold text-gray-800">
                                        {{ $val > 0 ? number_format($val, 2) : '' }}
                                    </span>
                                @else
                                    <span class="text-red-300 text-[9px]">—</span>
                                @endif
                            </td>
                            @endforeach
                            <td class="border border-gray-300 text-center px-2 py-1.5 font-bold text-[10px] text-gray-700 bg-yellow-50">
                                S/ {{ number_format($totalEsperadoAlumno, 2) }}
                            </td>
                            <td class="border border-gray-300 text-center px-2 py-1.5 font-bold text-[10px] text-green-700 bg-green-50">
                                S/ {{ number_format($totalAportadoAlumno, 2) }}
                            </td>
                            <td class="border border-gray-300 text-center px-2 py-1.5 font-bold text-[10px]
                                       {{ $debeAlumno > 0 ? 'text-red-600 bg-red-50' : 'text-green-600 bg-green-50' }}">
                                {{ $debeAlumno > 0 ? 'S/ '.number_format($debeAlumno,2) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>

                    <tfoot>
                        @php $debeAula = max(0, $totalEsperadoAula - $totalAportadoAula); @endphp
                        <tr class="bg-gray-700 text-white font-bold">
                            <td colspan="2" class="border border-gray-500 text-center px-3 py-2 uppercase text-xs font-black tracking-wider">
                                TOTAL ({{ $alumnos->count() }} alumnos)
                            </td>
                            @foreach($semanas as $semana)
                            <td class="border border-gray-500 text-center px-1 py-2 text-xs {{ $semana->es_vacaciones ? 'bg-red-900' : '' }}">
                                @if(!$semana->es_vacaciones)
                                    @php $totalSem = collect($pagosMap[$semana->id] ?? [])->sum(); @endphp
                                    S/ {{ number_format($totalSem, 2) }}
                                @endif
                            </td>
                            @endforeach
                            <td class="border border-gray-500 text-center px-2 py-2 text-xs bg-yellow-700">
                                S/ {{ number_format($totalEsperadoAula, 2) }}
                            </td>
                            <td class="border border-gray-500 text-center px-2 py-2 text-xs bg-green-700">
                                S/ {{ number_format($totalAportadoAula, 2) }}
                            </td>
                            <td class="border border-gray-500 text-center px-2 py-2 text-xs {{ $debeAula > 0 ? 'bg-red-700' : 'bg-green-700' }}">
                                S/ {{ number_format($debeAula, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @endif
        </div>
        @endforeach
        </div>

        <div class="flex justify-end pt-4 no-print">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-8 py-3 rounded-xl shadow-md transition-colors">
                Guardar todos los aportes
            </button>
        </div>
    </form>
    @endif

    {{-- ── Modal agregar semana ── --}}
    <div x-show="modalSemana" x-cloak class="fixed inset-0 bg-black/40 z-50 flex items-center justify-center px-4">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md" @click.outside="modalSemana = false">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Agregar Semana – <span x-text="aulaLabel"></span></h3>
                <button @click="modalSemana = false" class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
            </div>
            <form :action="'/aportes/config/' + configId + '/semana'" method="POST" class="px-6 py-5 space-y-4">
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
                    <button type="button" @click="modalSemana = false"
                            class="px-4 py-2.5 border border-gray-300 text-gray-600 rounded-lg text-sm hover:bg-gray-50">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
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
        table { width: 100% !important; border-collapse: collapse !important; font-size: 7.5px !important; }
        th, td { padding: 2px 3px !important; border: 0.5px solid #444 !important; }
        thead { display: table-header-group !important; }
        tfoot { display: table-footer-group !important; }
        .space-y-6 > div { margin-bottom: 8mm !important; }
        @page { size: A4 landscape; margin: 8mm; }
    }
</style>

@endsection

@push('scripts')
<script>
function aporteIndex() {
    return {
        modalSemana: false,
        configId: null,
        aulaLabel: '',
        abrirModalSemana(id, grado, seccion) {
            this.configId   = id;
            this.aulaLabel  = grado + ' "' + seccion + '"';
            this.modalSemana = true;
        },
    };
}
</script>
@endpush
