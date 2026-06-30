<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PAE')</title>
    <script>
        (function(){
            var dark = localStorage.getItem('color-theme') === 'dark' ||
                       (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) {
                document.documentElement.classList.add('dark');
                document.documentElement.style.backgroundColor = '#111827';
            } else {
                document.documentElement.style.backgroundColor = '#f3f4f6';
            }
        })();
    </script>
    <script src="/js/tailwind.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <script defer src="/js/alpine.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="bg-gray-100 dark:bg-gray-900 font-sans antialiased">

<div class="flex h-screen overflow-hidden">

    {{-- Overlay móvil --}}
    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden" onclick="closeSidebar()"></div>

    {{-- ── Sidebar ── --}}
    <aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-30 w-64 bg-white dark:bg-slate-800 text-slate-800 dark:text-white border-r border-gray-200 dark:border-slate-700 flex flex-col flex-shrink-0 -translate-x-full lg:translate-x-0 transition-transform duration-300">

        {{-- Logo --}}
        <div class="px-6 py-5 flex items-center space-x-3 border-b border-gray-100 dark:border-slate-700">
            <div class="w-11 h-11 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center p-0.5">
                <img src="/images/escudo.png"
                     alt="Escudo"
                     class="w-full h-full object-contain">
            </div>
            <div>
                <p class="font-bold text-slate-800 dark:text-white text-sm leading-tight">PAE</p>
                <p class="text-xs text-slate-400">Gestión Escolar</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">

            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-widest mb-2">Principal</p>

            <a href="{{ route('dashboard') }}"
               class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
                      {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-widest mt-5 mb-2">Escolar</p>

            <a href="{{ route('alumnos.index') }}"
               class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
                      {{ request()->routeIs('alumnos.*') ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>Alumnos</span>
            </a>

            {{-- ── PREDICCIÓN ── --}}
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-widest mt-5 mb-2">Predicción</p>

            <a href="{{ route('prediccion.index') }}"
               class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
                      {{ request()->routeIs('prediccion.*') ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span>Predicción de Raciones</span>
            </a>

            {{-- ── PECOSA ── --}}
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-widest mt-5 mb-2">Pecosa</p>

            {{-- Pecosa Inicial --}}
            <div>
                <button onclick="toggleMenu('menu-inicial', this)"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
                               {{ request()->routeIs('pecosa.inicial.*') ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-white' }}">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Inicial</span>
                    </div>
                    <svg class="chevron w-4 h-4 transition-transform duration-150 {{ request()->routeIs('pecosa.inicial.*') ? 'rotate-180' : '' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="menu-inicial" class="mt-1 ml-8 space-y-1 {{ request()->routeIs('pecosa.inicial.*') ? '' : 'hidden' }}">
                    <a href="{{ route('pecosa.inicial.index') }}"
                       class="flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors
                              {{ request()->routeIs('pecosa.inicial.index') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('pecosa.inicial.index') ? 'bg-white' : 'bg-slate-400' }} flex-shrink-0"></span>
                        <span>Ver Registros</span>
                    </a>
                    <a href="{{ route('pecosa.inicial.create') }}"
                       class="flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors
                              {{ request()->routeIs('pecosa.inicial.create') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('pecosa.inicial.create') ? 'bg-white' : 'bg-slate-400' }} flex-shrink-0"></span>
                        <span>Nuevo Registro</span>
                    </a>
                    <a href="{{ route('pecosa.inicial.compras') }}"
                       class="flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors
                              {{ request()->routeIs('pecosa.inicial.compras*') ? 'bg-green-600 text-white' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-green-600 dark:hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('pecosa.inicial.compras*') ? 'bg-white' : 'bg-slate-400' }} flex-shrink-0"></span>
                        <span>Lista de Compras</span>
                    </a>
                    <a href="{{ route('pecosa.inicial.prorrateo') }}"
                       class="flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors
                              {{ request()->routeIs('pecosa.inicial.prorrateo') ? 'bg-orange-600 text-white' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-orange-600 dark:hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('pecosa.inicial.prorrateo') ? 'bg-white' : 'bg-slate-400' }} flex-shrink-0"></span>
                        <span>Nueva Distribución</span>
                    </a>
                    <a href="{{ route('pecosa.inicial.distribuciones') }}"
                       class="flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors
                              {{ request()->routeIs('pecosa.inicial.distribuciones*') ? 'bg-orange-600 text-white' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-orange-600 dark:hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('pecosa.inicial.distribuciones*') ? 'bg-white' : 'bg-slate-400' }} flex-shrink-0"></span>
                        <span>Ver guardadas</span>
                    </a>
                    <a href="{{ route('aportes.index') }}"
                       class="flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors
                              {{ request()->routeIs('aportes.*') ? 'bg-yellow-500 text-white' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-yellow-600 dark:hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('aportes.*') ? 'bg-white' : 'bg-slate-400' }} flex-shrink-0"></span>
                        <span>Aportes PAE</span>
                    </a>
                </div>
            </div>

            {{-- Pecosa Primaria --}}
            <div>
                <button onclick="toggleMenu('menu-primaria', this)"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
                               {{ request()->routeIs('pecosa.primaria.*') ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-white' }}">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span>Primaria</span>
                    </div>
                    <svg class="chevron w-4 h-4 transition-transform duration-150 {{ request()->routeIs('pecosa.primaria.*') ? 'rotate-180' : '' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="menu-primaria" class="mt-1 ml-8 space-y-1 {{ request()->routeIs('pecosa.primaria.*') ? '' : 'hidden' }}">
                    <a href="{{ route('pecosa.primaria.index') }}"
                       class="flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors
                              {{ request()->routeIs('pecosa.primaria.index') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('pecosa.primaria.index') ? 'bg-white' : 'bg-slate-400' }} flex-shrink-0"></span>
                        <span>Ver Registros</span>
                    </a>
                    <a href="{{ route('pecosa.primaria.create') }}"
                       class="flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors
                              {{ request()->routeIs('pecosa.primaria.create') ? 'bg-blue-600 text-white' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('pecosa.primaria.create') ? 'bg-white' : 'bg-slate-400' }} flex-shrink-0"></span>
                        <span>Nuevo Registro</span>
                    </a>
                </div>
            </div>

            {{-- Distribución Primaria --}}
            <div>
                @php $distActiva = request()->routeIs('pecosa.primaria.prorrateo*') || request()->routeIs('pecosa.primaria.distribuciones*'); @endphp
                <button onclick="toggleMenu('menu-dist-primaria', this)"
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
                               {{ $distActiva ? 'bg-green-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-green-600 dark:hover:text-white' }}">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>Distribución</span>
                    </div>
                    <svg class="chevron w-4 h-4 transition-transform duration-150 {{ $distActiva ? 'rotate-180' : '' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="menu-dist-primaria" class="mt-1 ml-8 space-y-1 {{ $distActiva ? '' : 'hidden' }}">
                    <a href="{{ route('pecosa.primaria.prorrateo') }}"
                       class="flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors
                              {{ request()->routeIs('pecosa.primaria.prorrateo') && !request()->routeIs('pecosa.primaria.prorrateo.guardar') ? 'bg-green-600 text-white' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-green-600 dark:hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('pecosa.primaria.prorrateo') ? 'bg-white' : 'bg-slate-400' }} flex-shrink-0"></span>
                        <span>Nueva Distribución</span>
                    </a>
                    <a href="{{ route('pecosa.primaria.distribuciones') }}"
                       class="flex items-center space-x-2 px-3 py-2 rounded-lg text-xs font-medium transition-colors
                              {{ request()->routeIs('pecosa.primaria.distribuciones*') ? 'bg-green-600 text-white' : 'text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-green-600 dark:hover:text-white' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('pecosa.primaria.distribuciones*') ? 'bg-white' : 'bg-slate-400' }} flex-shrink-0"></span>
                        <span>Ver guardadas</span>
                    </a>
                </div>
            </div>

            {{-- Stock Vigente --}}
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-widest mt-5 mb-2">Inventario</p>
            <a href="{{ route('vencidos.index') }}"
               class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
                      {{ request()->routeIs('vencidos.*') ? 'bg-red-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-red-600 dark:hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                <span>Stock Vigente</span>
            </a>

            {{-- Usuarios --}}
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-widest mt-5 mb-2">Administración</p>
            <a href="{{ route('users.index') }}"
               class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
                      {{ request()->routeIs('users.*') ? 'bg-blue-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Usuarios</span>
            </a>

            {{-- Evaluación --}}
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-widest mt-5 mb-2">Evaluación</p>
            <a href="{{ route('evaluacion.index') }}"
               class="flex items-center space-x-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors duration-150
                      {{ request()->routeIs('evaluacion.*') ? 'bg-purple-600 text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 hover:text-purple-600 dark:hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12l2 2 4-4M7 7h10M7 12h4m-4 5h10M5 3h14a2 2 0 012 2v14a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2z"/>
                </svg>
                <span>Usabilidad</span>
            </a>

        </nav>

        {{-- User info --}}
        <div class="px-4 py-4 border-t border-gray-100 dark:border-slate-700">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 bg-blue-500 rounded-full flex items-center justify-center text-sm font-bold text-white flex-shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-800 dark:text-white truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ Auth::user()->email }}</p>
                </div>
                <form method="GET" action="{{ route('logout') }}">
                    <button type="submit" title="Cerrar sesión"
                            class="text-slate-400 hover:text-slate-700 dark:hover:text-white transition-colors p-1 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ── Main area ── --}}
    <div class="flex-1 flex flex-col overflow-hidden">

        {{-- Top bar --}}
        <header class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 px-4 lg:px-6 py-4 flex items-center justify-between flex-shrink-0">
            <div class="flex items-center gap-3">
                {{-- Botón hamburguesa (solo móvil) --}}
                <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <div>
                <h1 class="text-lg font-semibold text-gray-800 dark:text-white">@yield('page-title', 'Dashboard')</h1>
                @hasSection('breadcrumb')
                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">@yield('breadcrumb')</p>
                @endif
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-400">{{ now()->isoFormat('D [de] MMMM [de] YYYY') }}</span>

                {{-- Botón de Modo Oscuro --}}
                <button id="theme-toggle" type="button" class="text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-4 focus:ring-gray-200 dark:focus:ring-gray-700 rounded-lg text-sm p-2.5">
                    <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                    <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                </button>

                <script>
                    var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
                    var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

                    function updateIcons() {
                        if (document.documentElement.classList.contains('dark')) {
                            themeToggleLightIcon.classList.remove('hidden');
                            themeToggleDarkIcon.classList.add('hidden');
                        } else {
                            themeToggleLightIcon.classList.add('hidden');
                            themeToggleDarkIcon.classList.remove('hidden');
                        }
                    }

                    updateIcons();

                    document.getElementById('theme-toggle').addEventListener('click', function() {
                        if (document.documentElement.classList.contains('dark')) {
                            document.documentElement.classList.remove('dark');
                            localStorage.setItem('color-theme', 'light');
                        } else {
                            document.documentElement.classList.add('dark');
                            localStorage.setItem('color-theme', 'dark');
                        }
                        updateIcons();
                    });
                </script>
                @yield('header-actions')
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 overflow-y-auto p-3 lg:p-6">
        <style>
            table { width: 100%; }
            .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        </style>

            @if(session('success'))
                <div class="mb-5 flex items-center space-x-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-5 flex items-center space-x-2 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

@yield('modals')
@stack('scripts')
<script>
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const open = !sidebar.classList.contains('-translate-x-full');
    if (open) {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    } else {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    }
}
function closeSidebar() {
    document.getElementById('sidebar').classList.add('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.add('hidden');
}
function toggleMenu(id, btn) {
    const el = document.getElementById(id);
    const chevron = btn.querySelector('.chevron');
    const hidden = el.classList.toggle('hidden');
    if (chevron) chevron.classList.toggle('rotate-180', !hidden);
}
</script>
</body>
</html>

