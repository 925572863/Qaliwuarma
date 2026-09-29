<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orientación nutricional — {{ $alumno->nombre }}</title>
    <script src="/js/tailwind.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen py-8 px-4">
<div class="max-w-2xl mx-auto">

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex items-center space-x-3 mb-1">
            <img src="/images/escudo.png" alt="Escudo" class="w-10 h-10">
            <div>
                <p class="text-xs font-bold text-red-600 uppercase tracking-wide">Orientación nutricional — PAE</p>
                <h1 class="text-lg font-bold text-gray-800">{{ $alumno->nombre }} {{ $alumno->apellido_paterno }}</h1>
            </div>
        </div>
        <p class="text-sm text-gray-500 mt-2">
            Esta página tiene recomendaciones para preparar mejor los alimentos que su hijo/a recibe del programa,
            y así aprovechar su valor nutricional para combatir la anemia. No reemplaza la atención médica.
        </p>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-3 mb-6 text-green-700 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-3 mb-6 text-red-700 text-sm font-medium">{{ session('error') }}</div>
    @endif

    {{-- Recomendaciones --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-6">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-800">Recomendaciones de preparación</h2>
            <p class="text-xs text-gray-500 mt-0.5">Validadas por un profesional en nutrición</p>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse($recomendaciones as $rec)
                <div class="p-4">
                    <p class="text-sm font-bold text-gray-800">{{ $rec->producto }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $rec->recomendacion }}</p>
                </div>
            @empty
                <p class="p-6 text-center text-gray-400 text-sm">Todavía no hay recomendaciones cargadas para este nivel.</p>
            @endforelse
        </div>
    </div>

    {{-- Cuestionario --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-800">Cuestionario de conocimientos</h2>
            <p class="text-xs text-gray-500 mt-0.5">Nos ayuda a saber si esta orientación es útil para usted</p>
        </div>

        <div class="p-5">
        @if(!$pretest)
            <p class="text-sm text-gray-600 mb-4">Responda estas preguntas <strong>antes</strong> de leer las recomendaciones (si ya las leyó, no hay problema, responda igual).</p>
            @include('orientacion._cuestionario', ['tipo' => 'pretest'])
        @elseif(!$postest)
            <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 mb-4 text-blue-800 text-sm">
                Pretest ya registrado ({{ $pretest->puntaje }}/20 el {{ $pretest->created_at->format('d/m/Y') }}).
                El postest se responde después de usar esta orientación por unas semanas.
            </div>
            @include('orientacion._cuestionario', ['tipo' => 'postest'])
        @else
            <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-green-800 text-sm">
                <p>Pretest: <strong>{{ $pretest->puntaje }}/20</strong> ({{ $pretest->created_at->format('d/m/Y') }})</p>
                <p>Postest: <strong>{{ $postest->puntaje }}/20</strong> ({{ $postest->created_at->format('d/m/Y') }})</p>
                <p class="mt-2 font-medium">¡Gracias por completar el cuestionario!</p>
            </div>
        @endif
        </div>
    </div>

</div>
</body>
</html>
