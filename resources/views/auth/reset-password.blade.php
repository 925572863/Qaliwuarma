<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Contraseña — Qali Warma</title>
    <script src="/js/tailwind.min.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-green-50 to-blue-50 flex items-center justify-center p-4">

<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <img src="/images/escudo.png" alt="Logo" class="w-16 h-16 mx-auto mb-3 object-contain" onerror="this.style.display='none'">
        <h1 class="text-xl font-bold text-gray-800">Nueva Contraseña</h1>
        <p class="text-sm text-gray-500 mt-1">Escribe tu nueva contraseña</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">

        @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
                <input type="email" name="email" value="{{ old('email', request()->email) }}" required
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="tu@correo.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nueva contraseña</label>
                <input type="password" name="password" required minlength="8"
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Mínimo 8 caracteres">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" required
                       class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Repite la nueva contraseña">
            </div>

            <button type="submit"
                    class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-semibold transition-colors">
                Restablecer Contraseña
            </button>
        </form>
    </div>
</div>

</body>
</html>
