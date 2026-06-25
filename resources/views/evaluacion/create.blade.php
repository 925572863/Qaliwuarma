@extends('layouts.app')
@section('title', 'Nueva Evaluación de Usabilidad')
@section('page-title', 'Evaluación de Usabilidad')
@section('breadcrumb', 'Cuestionario de satisfacción del sistema')

@section('content')
<div class="max-w-2xl">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 mb-5 text-green-700 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('evaluacion.store') }}" class="space-y-6">
        @csrf

        {{-- Datos del evaluador --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos del Evaluador</h2>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo <span class="text-red-500">*</span></label>
                    <input type="text" name="evaluador" value="{{ old('evaluador') }}" placeholder="Ej. María García López"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('evaluador') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('evaluador')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Cargo / Función</label>
                    <input type="text" name="cargo" value="{{ old('cargo') }}" placeholder="Ej. Responsable CAE"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Fecha de evaluación <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" max="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('fecha') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('fecha')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Criterios --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Criterios de Evaluación</h2>
                <p class="text-xs text-gray-400 mt-0.5">Marque del 1 al 5 donde: 1 = Muy deficiente · 3 = Regular · 5 = Excelente</p>
            </div>
            <div class="divide-y divide-gray-50">

                @php
                $criterios = [
                    ['campo' => 'p1_facilidad',    'label' => '1. Facilidad de uso de la interfaz',          'desc' => '¿El sistema es fácil de navegar y usar?'],
                    ['campo' => 'p2_claridad',     'label' => '2. Claridad en la presentación de datos',     'desc' => '¿Los datos se muestran de forma comprensible?'],
                    ['campo' => 'p3_utilidad',     'label' => '3. Utilidad del módulo de predicción',        'desc' => '¿El módulo de predicción de raciones es útil?'],
                    ['campo' => 'p4_organizacion', 'label' => '4. Organización de la lista de alumnos',      'desc' => '¿La organización por grado y sección es adecuada?'],
                    ['campo' => 'p5_velocidad',    'label' => '5. Velocidad de respuesta del sistema',       'desc' => '¿El sistema responde con rapidez?'],
                ];
                @endphp

                @foreach($criterios as $c)
                <div class="px-6 py-5">
                    <p class="text-sm font-semibold text-gray-800">{{ $c['label'] }}</p>
                    <p class="text-xs text-gray-400 mb-3">{{ $c['desc'] }}</p>
                    <div class="flex gap-3" id="grupo-{{ $c['campo'] }}">
                        @for($v = 1; $v <= 5; $v++)
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="{{ $c['campo'] }}" value="{{ $v }}"
                                   {{ old($c['campo']) == $v ? 'checked' : '' }}
                                   class="sr-only peer"
                                   onchange="resaltarOpcion('{{ $c['campo'] }}', {{ $v }})">
                            <div id="opt-{{ $c['campo'] }}-{{ $v }}"
                                 class="text-center border-2 rounded-lg py-2.5 text-sm font-bold transition-all
                                        {{ old($c['campo']) == $v ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-500 hover:border-blue-300' }}">
                                {{ $v }}
                                <p class="text-xs font-normal mt-0.5">
                                    @if($v==1) Muy def. @elseif($v==2) Defic. @elseif($v==3) Regular @elseif($v==4) Bueno @else Excelen. @endif
                                </p>
                            </div>
                        </label>
                        @endfor
                    </div>
                    @error($c['campo'])<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                @endforeach

            </div>
        </div>

        {{-- Comentario --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Comentario (opcional)</h2>
            </div>
            <div class="px-6 py-5">
                <textarea name="comentario" rows="3" placeholder="Observaciones adicionales sobre el sistema…"
                          class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('comentario') }}</textarea>
            </div>
        </div>

        <div class="flex items-center space-x-3">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-6 py-2.5 rounded-lg transition-colors shadow-sm">
                Enviar Evaluación
            </button>
            <a href="{{ route('evaluacion.index') }}"
               class="text-sm text-gray-600 hover:text-gray-800 font-medium px-4 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function resaltarOpcion(campo, val) {
    for (var i = 1; i <= 5; i++) {
        var el = document.getElementById('opt-' + campo + '-' + i);
        if (!el) continue;
        if (i === val) {
            el.className = 'text-center border-2 rounded-lg py-2.5 text-sm font-bold transition-all border-blue-500 bg-blue-50 text-blue-700';
        } else {
            el.className = 'text-center border-2 rounded-lg py-2.5 text-sm font-bold transition-all border-gray-200 text-gray-500 hover:border-blue-300';
        }
    }
}
</script>
@endpush
