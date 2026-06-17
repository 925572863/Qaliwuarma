@extends('layouts.app')
@section('title', $alumno->nombre_completo)
@section('page-title', $alumno->nombre_completo)
@section('breadcrumb', 'Matrícula: ' . $alumno->matricula)

@section('header-actions')
    <div class="flex items-center space-x-2">
        <a href="{{ route('alumnos.edit', $alumno) }}"
           class="inline-flex items-center space-x-1.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            <span>Editar</span>
        </a>
        <form method="POST" action="{{ route('alumnos.destroy', $alumno) }}"
              onsubmit="return confirm('¿Eliminar a {{ addslashes($alumno->nombre_completo) }}? Esta acción no se puede deshacer.')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center space-x-1.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Eliminar</span>
            </button>
        </form>
        <a href="{{ route('alumnos.index') }}"
           class="text-sm text-gray-600 hover:text-gray-800 font-medium px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
            ← Volver
        </a>
    </div>
@endsection

@section('content')
<div class="max-w-4xl space-y-5">

    {{-- Profile header --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <div class="flex items-center space-x-5">
            <div class="w-16 h-16 rounded-2xl bg-blue-600 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
                {{ strtoupper(substr($alumno->nombre, 0, 1)) }}{{ strtoupper(substr($alumno->apellido_paterno, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <h2 class="text-xl font-bold text-gray-900">{{ $alumno->nombre_completo }}</h2>
                <p class="text-gray-500 text-sm mt-0.5">{{ $alumno->matricula }} · {{ $alumno->carrera }}</p>
                <p class="text-gray-400 text-xs mt-0.5">Nivel: <span class="font-semibold {{ $alumno->nivel === 'inicial' ? 'text-yellow-600' : 'text-blue-600' }}">{{ $alumno->nivel_label }}</span></p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $alumno->estado_badge }}">
                        {{ $alumno->estado_label }}
                    </span>
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                        {{ $alumno->semestre }}° Semestre
                    </span>
                    @if($alumno->genero)
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                            {{ $alumno->genero === 'M' ? 'Masculino' : ($alumno->genero === 'F' ? 'Femenino' : 'Otro') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Datos personales --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos Personales</h3>
            </div>
            <dl class="px-6 py-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Nombre completo</dt>
                    <dd class="text-gray-800 font-medium text-right">{{ $alumno->nombre_completo }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Fecha de nacimiento</dt>
                    <dd class="text-gray-800">{{ $alumno->fecha_nacimiento ? $alumno->fecha_nacimiento->format('d/m/Y') : '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Género</dt>
                    <dd class="text-gray-800">
                        @if($alumno->genero === 'M') Masculino
                        @elseif($alumno->genero === 'F') Femenino
                        @elseif($alumno->genero === 'Otro') Otro
                        @else —
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">CURP</dt>
                    <dd class="text-gray-800 font-mono text-xs">{{ $alumno->curp ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Contacto --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Contacto</h3>
            </div>
            <dl class="px-6 py-4 space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-500">Teléfono</dt>
                    <dd class="text-gray-800">{{ $alumno->telefono ?? '—' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-500">Correo</dt>
                    <dd class="text-gray-800">
                        @if($alumno->email)
                            <a href="mailto:{{ $alumno->email }}" class="text-blue-600 hover:underline">{{ $alumno->email }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between items-start">
                    <dt class="text-gray-500">Dirección</dt>
                    <dd class="text-gray-800 text-right max-w-xs">{{ $alumno->direccion ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Datos académicos --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden md:col-span-2">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos Académicos</h3>
            </div>
            <dl class="px-6 py-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <dt class="text-gray-500 mb-1">Matrícula</dt>
                    <dd class="text-gray-800 font-semibold">{{ $alumno->matricula }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 mb-1">Carrera</dt>
                    <dd class="text-gray-800 font-medium">{{ $alumno->carrera }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 mb-1">Semestre</dt>
                    <dd class="text-gray-800">{{ $alumno->semestre }}°</dd>
                </div>
                <div>
                    <dt class="text-gray-500 mb-1">Inscripción</dt>
                    <dd class="text-gray-800">{{ $alumno->fecha_inscripcion->format('d/m/Y') }}</dd>
                </div>
                @if($alumno->observaciones)
                <div class="col-span-2 md:col-span-4">
                    <dt class="text-gray-500 mb-1">Observaciones</dt>
                    <dd class="text-gray-700 bg-gray-50 rounded-lg p-3">{{ $alumno->observaciones }}</dd>
                </div>
                @endif
            </dl>
        </div>

    </div>

    {{-- Meta --}}
    <p class="text-xs text-gray-400 text-right">
        Registrado: {{ $alumno->created_at->format('d/m/Y H:i') }}
        @if($alumno->updated_at->ne($alumno->created_at))
            · Actualizado: {{ $alumno->updated_at->format('d/m/Y H:i') }}
        @endif
    </p>

</div>
@endsection
