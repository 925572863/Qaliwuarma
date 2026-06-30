@extends('layouts.app')
@section('title', 'Usuarios')
@section('page-title', 'Gestión de Usuarios')
@section('breadcrumb', 'Administra los usuarios del sistema')

@section('header-actions')
    <a href="{{ route('users.create') }}"
       class="inline-flex items-center space-x-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Nuevo Usuario</span>
    </a>
@endsection

@section('content')

@if(session('success'))
<div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
    {{ session('error') }}
</div>
@endif

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="p-5 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-transparent">
        <h2 class="text-lg font-bold text-gray-800">Usuarios del Sistema</h2>
        <p class="text-xs text-gray-500 mt-0.5">{{ $users->count() }} usuario(s) registrado(s)</p>
    </div>

    @if($users->isEmpty())
        <div class="py-16 text-center text-gray-400 text-sm">No hay usuarios registrados.</div>
    @else
        <div class="divide-y divide-gray-100">
            @foreach($users as $user)
                <div class="flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-blue-700 font-bold text-sm">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $user->name }}
                                @if($user->id === auth()->id())
                                    <span class="ml-2 text-xs bg-blue-100 text-blue-600 px-2 py-0.5 rounded-full">Tú</span>
                                @endif
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $user->email }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <a href="{{ route('users.edit', $user) }}"
                           class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-xs font-medium transition-colors flex items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            <span>Editar</span>
                        </a>
                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                  onsubmit="return confirm('¿Eliminar al usuario {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg text-xs font-medium transition-colors flex items-center space-x-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>Eliminar</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
