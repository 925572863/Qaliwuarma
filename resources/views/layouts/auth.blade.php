<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Acceso') — Qualiwuarma</title>
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <script src="{{ '/js/tailwind.min.js' }}"></script>
    <script>tailwind.config = { darkMode: 'class' };</script>
    @stack('styles')
</head>
<body class="min-h-screen overflow-hidden">
    @yield('content')

    @stack('scripts')
</body>
</html>
