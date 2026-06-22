@extends('layouts.app')
@section('title', 'Nuevo Alumno')
@section('page-title', 'Nuevo Alumno')
@section('breadcrumb', 'Completa el formulario para registrar un alumno')

@section('content')
<div class="max-w-4xl">
    <form method="POST" action="{{ route('alumnos.store') }}" class="space-y-6">
        @csrf

        {{-- Datos personales --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos Personales</h2>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-3 gap-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre(s) <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('nombre') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Apellido Paterno <span class="text-red-500">*</span></label>
                    <input type="text" name="apellido_paterno" value="{{ old('apellido_paterno') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('apellido_paterno') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('apellido_paterno')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Apellido Materno</label>
                    <input type="text" name="apellido_materno" value="{{ old('apellido_materno') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Fecha de Nacimiento</label>
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('fecha_nacimiento')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Género</label>
                    <select name="genero"
                            class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Seleccionar --</option>
                        <option value="M"    {{ old('genero') === 'M'    ? 'selected' : '' }}>Masculino</option>
                        <option value="F"    {{ old('genero') === 'F'    ? 'selected' : '' }}>Femenino</option>
                        <option value="Otro" {{ old('genero') === 'Otro' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">CURP</label>
                    <input type="text" name="curp" value="{{ old('curp') }}" maxlength="18"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm uppercase focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('curp') ? 'border-red-400' : 'border-gray-300' }}"
                           placeholder="18 caracteres">
                    @error('curp')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

            </div>
        </div>

        {{-- Contacto --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Contacto</h2>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Teléfono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="10 dígitos">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo Electrónico</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('email') ? 'border-red-400' : '' }}"
                           placeholder="correo@ejemplo.com">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Dirección</label>
                    <textarea name="direccion" rows="2"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('direccion') }}</textarea>
                </div>

            </div>
        </div>

        {{-- Datos académicos --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos Académicos</h2>
            </div>
            <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-3 gap-5">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nivel <span class="text-red-500">*</span></label>
                    <select name="nivel"
                            class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('nivel') ? 'border-red-400' : 'border-gray-300' }}">
                        <option value="inicial"  {{ old('nivel') === 'inicial'  ? 'selected' : '' }}>Inicial</option>
                        <option value="primaria" {{ old('nivel', 'primaria') === 'primaria' ? 'selected' : '' }}>Primaria</option>
                    </select>
                    @error('nivel')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Matrícula <span class="text-red-500">*</span></label>
                    <input type="text" name="matricula" value="{{ old('matricula') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('matricula') ? 'border-red-400' : 'border-gray-300' }}"
                           placeholder="Ej. 2024001">
                    @error('matricula')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Carrera / Programa <span class="text-red-500">*</span></label>
                    <input type="text" name="carrera" value="{{ old('carrera') }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('carrera') ? 'border-red-400' : 'border-gray-300' }}"
                           placeholder="Ej. Ingeniería en Sistemas">
                    @error('carrera')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Semestre <span class="text-red-500">*</span></label>
                    <select name="semestre"
                            class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('semestre') ? 'border-red-400' : 'border-gray-300' }}">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ old('semestre', 1) == $i ? 'selected' : '' }}>
                                {{ $i }}° Semestre
                            </option>
                        @endfor
                    </select>
                    @error('semestre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Fecha de Inscripción <span class="text-red-500">*</span></label>
                    <input type="date" name="fecha_inscripcion" value="{{ old('fecha_inscripcion', date('Y-m-d')) }}"
                           class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('fecha_inscripcion') ? 'border-red-400' : 'border-gray-300' }}">
                    @error('fecha_inscripcion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Estado <span class="text-red-500">*</span></label>
                    <select name="estado"
                            class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('estado') ? 'border-red-400' : 'border-gray-300' }}">
                        <option value="activo"   {{ old('estado', 'activo') === 'activo'   ? 'selected' : '' }}>Activo</option>
                        <option value="inactivo" {{ old('estado') === 'inactivo' ? 'selected' : '' }}>Trasladado</option>
                        <option value="egresado" {{ old('estado') === 'egresado' ? 'selected' : '' }}>Egresado</option>
                        <option value="baja"     {{ old('estado') === 'baja'     ? 'selected' : '' }}>Baja</option>
                    </select>
                    @error('estado')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Observaciones</label>
                    <textarea name="observaciones" rows="3"
                              class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none">{{ old('observaciones') }}</textarea>
                </div>

            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center space-x-3">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm px-6 py-2.5 rounded-lg transition-colors shadow-sm">
                Registrar Alumno
            </button>
            <a href="{{ route('alumnos.index') }}"
               class="text-sm text-gray-600 hover:text-gray-800 font-medium px-4 py-2.5 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                Cancelar
            </a>
        </div>
    </form>
</div>
@endsection
