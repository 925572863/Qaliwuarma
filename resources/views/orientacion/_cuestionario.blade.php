<form method="POST" action="{{ route('orientacion.cuestionario', $alumno->matricula) }}" class="space-y-5">
    @csrf
    <input type="hidden" name="tipo" value="{{ $tipo }}">

    @foreach($preguntas as $i => $preg)
        <div>
            <p class="text-sm font-semibold text-gray-800 mb-2">{{ $i + 1 }}. {{ $preg['pregunta'] }}</p>
            <div class="space-y-1.5">
                @foreach($preg['opciones'] as $j => $opcion)
                    <label class="flex items-center space-x-2 text-sm text-gray-600">
                        <input type="radio" name="respuestas[{{ $i }}]" value="{{ $j }}" required class="text-red-600 focus:ring-red-400">
                        <span>{{ $opcion }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg py-2.5">
        Enviar respuestas
    </button>
</form>
