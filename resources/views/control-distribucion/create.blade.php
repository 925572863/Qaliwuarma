@extends('layouts.app')
@section('title', 'Nuevo Control de Distribución')
@section('page-title', 'Registrar Control de Distribución')
@section('breadcrumb', 'Ficha 6 (VD)')

@section('content')

<div class="bg-white rounded-xl border border-gray-100 shadow-sm max-w-2xl">
    <div class="px-6 py-4 border-b border-gray-100">
        <h2 class="text-base font-semibold text-gray-800">Datos de la jornada</h2>
    </div>

    <form method="POST" action="{{ route('control-distribucion.store') }}" class="p-6 space-y-5">
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

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Kg desperdiciados</label>
                <input type="number" step="0.01" name="kg_desperdiciados" value="{{ old('kg_desperdiciados') }}" required min="0" max="1000"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Kg distribuidos</label>
                <input type="number" step="0.01" name="kg_distribuidos" value="{{ old('kg_distribuidos') }}" required min="0.01" max="5000"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tiempo de distribución (minutos)</label>
            <input type="number" name="tiempo_distribucion_min" value="{{ old('tiempo_distribucion_min') }}" required min="0" max="600"
                   class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex items-center space-x-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors shadow-sm">
                Guardar
            </button>
            <a href="{{ route('control-distribucion.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancelar</a>
        </div>
    </form>
</div>

@endsection
