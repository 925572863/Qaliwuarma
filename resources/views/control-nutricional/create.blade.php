@extends('layouts.app')
@section('title', 'Nuevo Control Nutricional')
@section('page-title', 'Registrar Control Nutricional')
@section('breadcrumb', 'Ficha 5 (VD)')

@section('content')

<div class="bg-white rounded-xl border border-gray-100 shadow-sm max-w-2xl">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Datos de la ración</h2>
    </div>

    <form method="POST" action="{{ route('control-nutricional.store') }}" class="p-6 space-y-5">
        @csrf

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-red-700 text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Fecha</label>
                <input type="date" name="fecha" value="{{ old('fecha', date('Y-m-d')) }}" required
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nivel</label>
                <select name="nivel" required
                        class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="inicial" @selected(old('nivel')=='inicial')>Inicial</option>
                    <option value="primaria" @selected(old('nivel')=='primaria')>Primaria</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Fase de medición</label>
            <select name="fase" required
                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="pretest" @selected(old('fase','pretest')=='pretest')>Pretest (antes de la intervención)</option>
                <option value="postest" @selected(old('fase')=='postest')>Postest (después de la intervención)</option>
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Menú del día</label>
            <input type="text" name="menu_dia" value="{{ old('menu_dia') }}" required maxlength="150"
                   placeholder="Ej. Arroz con pollo y menestra"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Gramos planificados</label>
                <input type="number" step="0.01" name="gramos_planificados" value="{{ old('gramos_planificados') }}" required min="0" max="2000"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Gramos servidos</label>
                <input type="number" step="0.01" name="gramos_servidos" value="{{ old('gramos_servidos') }}" required min="0" max="2000"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="flex items-center space-x-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors shadow-sm">
                Guardar
            </button>
            <a href="{{ route('control-nutricional.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancelar</a>
        </div>
    </form>
</div>

@endsection
