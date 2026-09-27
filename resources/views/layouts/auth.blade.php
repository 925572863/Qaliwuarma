<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema PAE — I.E. 14008 Leonor Cerna de Valdiviezo</title>
    <meta name="description" content="Sistema PAE (Programa de Alimentación Escolar / Qali Warma) de la I.E. 14008 Leonor Cerna de Valdiviezo, Piura. Gestión de PECOSA, alumnos, distribución de alimentos y predicción de raciones.">
    <meta name="robots" content="index, follow">
    <meta name="google-site-verification" content="6o1X3lRA3P9ryA7r5R6m1jJqHdnsyEHL_4m6vOkN3pw" />
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


