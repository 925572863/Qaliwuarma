@extends('layouts.app')
@section('title', 'Nuevo Usuario')
@section('page-title', 'Crear Nuevo Usuario')
@section('breadcrumb', 'Registra un nuevo acceso al sistema')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Datos del Usuario</h2>
        </div>
        <form method="POST" action="{{ route('users.store') }}" class="px-6 py-5 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus
                       class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('name') ? 'border-red-400' : 'border-gray-300' }}"
                       placeholder="Ej: María García López">
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('email') ? 'border-red-400' : 'border-gray-300' }}"
                       placeholder="correo@ejemplo.com">
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña <span class="text-red-500">*</span></label>
                <input type="password" name="password" required minlength="8"
                       class="w-full px-3 py-2.5 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('password') ? 'border-red-400' : 'border-gray-300' }}"
                       placeholder="Mínimo 8 caracteres">
                @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar contraseña <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Repite la contraseña">
            </div>

            <div class="flex justify-end space-x-3 pt-2">
                <a href="{{ route('users.index') }}"
                   class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors">
                    Crear Usuario
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
