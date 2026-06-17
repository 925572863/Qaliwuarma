@extends('layouts.auth')
@section('title', 'Iniciar Sesión')

@push('styles')
<style>
    * { box-sizing: border-box; }

    @keyframes fade-in-up {
        from { opacity: 0; transform: translateY(40px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0)    scale(1); }
    }
    @keyframes zoom-in {
        from { opacity: 0; transform: scale(0.7) rotate(-8deg); }
        to   { opacity: 1; transform: scale(1)   rotate(0deg); }
    }
    @keyframes slide-left {
        from { opacity: 0; transform: translateX(-60px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes shimmer {
        0%   { background-position: -300% center; }
        100% { background-position: 300% center; }
    }
    @keyframes spin-slow {
        to { transform: rotate(360deg); }
    }
    @keyframes spin-reverse {
        to { transform: rotate(-360deg); }
    }
    @keyframes pulse-ring {
        0%   { transform: scale(1);    opacity: .6; }
        100% { transform: scale(1.5);  opacity: 0; }
    }
    @keyframes float-text {
        0%, 100% { transform: translateY(0); }
        50%       { transform: translateY(-6px); }
    }
    @keyframes line-grow {
        from { width: 0; opacity: 0; }
        to   { width: 4rem; opacity: 1; }
    }
    @keyframes dot-bounce {
        0%, 100% { transform: scale(1); }
        50%       { transform: scale(1.6); }
    }

    /* Panel izquierdo */
    .left-panel { animation: slide-left .9s cubic-bezier(.22,1,.36,1) both; }

    /* Escudo */
    .escudo-wrap { animation: zoom-in .8s .2s cubic-bezier(.34,1.56,.64,1) both; }

    /* Anillos giratorios */
    .ring-1 {
        position: absolute; inset: -10px; border-radius: 50%;
        border: 2px solid transparent;
        border-top-color: rgba(99,102,241,.8);
        border-right-color: rgba(99,102,241,.2);
        animation: spin-slow 3s linear infinite;
    }
    .ring-2 {
        position: absolute; inset: -20px; border-radius: 50%;
        border: 1.5px solid transparent;
        border-bottom-color: rgba(59,130,246,.5);
        border-left-color: rgba(59,130,246,.15);
        animation: spin-reverse 5s linear infinite;
    }
    /* Pulso expansivo detrás del escudo */
    .pulse-ring {
        position: absolute; inset: -4px; border-radius: 50%;
        border: 2px solid rgba(99,102,241,.5);
        animation: pulse-ring 2.5s ease-out infinite;
    }
    .pulse-ring-2 {
        position: absolute; inset: -4px; border-radius: 50%;
        border: 2px solid rgba(59,130,246,.4);
        animation: pulse-ring 2.5s 1.25s ease-out infinite;
    }

    /* Texto flotante */
    .float-text { animation: float-text 4s ease-in-out infinite; }

    /* Fondo panel derecho */
    .aurora-bg {
        background: #f1f5f9;
    }
    .dark .aurora-bg {
        background: #0f172a;
    }

    /* Formulario */
    .form-wrap { animation: fade-in-up .75s .1s cubic-bezier(.22,1,.36,1) both; }
    .field-1   { animation: fade-in-up .6s .25s both; }
    .field-2   { animation: fade-in-up .6s .35s both; }
    .field-3   { animation: fade-in-up .6s .45s both; }
    .field-4   { animation: fade-in-up .6s .55s both; }

    /* Inputs */
    .input-field {
        transition: border-color .25s, box-shadow .25s, background .25s;
        caret-color: #60a5fa;
    }
    .input-field:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59,130,246,.2), 0 0 12px rgba(59,130,246,.1);
        background: #1e293b !important;
    }
    .input-wrap:focus-within svg { color: #60a5fa; transition: color .25s; }

    /* Botón */
    .btn-login {
        position: relative; overflow: hidden;
        transition: transform .18s, box-shadow .18s;
        background: linear-gradient(135deg, #2563eb, #4f46e5);
    }
    .btn-login::before {
        content: '';
        position: absolute; top: 0; left: -100%; width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.2), transparent);
        animation: shimmer 3s infinite;
    }
    .btn-login:hover  { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(79,70,229,.5); }
    .btn-login:active { transform: translateY(-1px); }

    /* Líneas decorativas */
    .line-left  { animation: line-grow .8s .7s both; }
    .line-right { animation: line-grow .8s .9s both; }
    .dot-mid    { animation: dot-bounce 2s 1.2s ease-in-out infinite; }

    /* Canvas partículas */
    #particles-canvas { position: absolute; inset: 0; pointer-events: none; }

    /* Scroll suave */
    html { scroll-behavior: smooth; }
</style>
@endpush

@section('content')
<div class="flex min-h-screen overflow-hidden">

    {{-- Botón modo oscuro/claro --}}
    <div class="absolute top-4 right-4 z-50">
        <button id="theme-toggle" type="button"
                class="text-white/70 hover:text-white hover:bg-white/10 focus:outline-none rounded-lg p-2.5 transition-colors">
            <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
            </svg>
            <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
            </svg>
        </button>
    </div>

    {{-- ══════════════════════ PANEL IZQUIERDO ══════════════════════ --}}
    <div class="hidden lg:flex lg:w-1/2 relative overflow-hidden">

        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat"
             style="background-image: url('{{ asset('images/fondo.png') }}')"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-black/50 via-indigo-950/40 to-black/70"></div>

        {{-- Partículas --}}
        <canvas id="particles-canvas"></canvas>

        {{-- Contenido --}}
        <div class="relative z-10 flex flex-col items-center justify-center w-full px-14 text-center text-white left-panel">

            {{-- Escudo --}}
            <div class="escudo-wrap relative inline-flex items-center justify-center mb-10">
                <div class="pulse-ring"></div>
                <div class="pulse-ring-2"></div>
                <div class="ring-1"></div>
                <div class="ring-2"></div>
                @if(file_exists(public_path('images/escudo.png')))
                    <img src="{{ asset('images/escudo.png') }}" alt="Insignia"
                         class="w-48 h-48 object-contain relative z-10 drop-shadow-2xl">
                @else
                    <div class="w-44 h-44 rounded-full bg-white/15 backdrop-blur-md border-2 border-white/30
                                flex items-center justify-center text-5xl font-black relative z-10">QW</div>
                @endif
            </div>

            {{-- Texto --}}
            <div class="float-text">
                <p class="text-2xl font-bold tracking-wide mb-2" style="text-shadow:0 2px 20px rgba(0,0,0,.6)">
                    I.E. Leonor Cerna de Valdiviezo
                </p>
                <p class="text-sm text-white/55 tracking-widest uppercase mb-8">
                    Gestión Escolar · Qali Warma
                </p>
            </div>

            {{-- Línea decorativa --}}
            <div class="flex items-center gap-4">
                <div class="line-left h-px bg-white/50" style="width:4rem;"></div>
                <div class="dot-mid w-2 h-2 rounded-full bg-indigo-400"></div>
                <div class="line-right h-px bg-white/50" style="width:4rem;"></div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════ PANEL DERECHO ══════════════════════ --}}
    <div class="w-full lg:w-1/2 aurora-bg flex items-center justify-center px-8 py-12 relative overflow-hidden">

        {{-- Luces de fondo --}}
        <div class="absolute top-0 right-0 w-80 h-80 rounded-full blur-3xl"
             style="background:radial-gradient(circle,rgba(99,102,241,.15),transparent 70%); transform:translate(30%,-30%)"></div>
        <div class="absolute bottom-0 left-0 w-72 h-72 rounded-full blur-3xl"
             style="background:radial-gradient(circle,rgba(59,130,246,.12),transparent 70%); transform:translate(-30%,30%)"></div>
        <div class="absolute inset-0 opacity-[0.03]"
             style="background-image:radial-gradient(circle at 1px 1px,white 1px,transparent 0);background-size:28px 28px;"></div>

        <div class="w-full max-w-sm relative z-10 form-wrap">

            {{-- Logo pequeño --}}
            <div class="text-center mb-8">
                @if(file_exists(public_path('images/escudo.png')))
                    <img src="{{ asset('images/escudo.png') }}" alt="Escudo"
                         class="w-16 h-16 object-contain mx-auto mb-4 drop-shadow-xl">
                @else
                    <div class="w-14 h-14 rounded-xl mx-auto mb-4 flex items-center justify-center text-lg font-black text-white"
                         style="background:linear-gradient(135deg,#2563eb,#4f46e5)">QW</div>
                @endif
                <h2 class="text-3xl font-bold text-slate-800 dark:text-white mb-1">Bienvenido</h2>
                <p class="text-slate-500 dark:text-slate-400 text-sm">Ingresa tus credenciales para continuar</p>
            </div>

            <form method="POST" action="{{ route('login') }}" autocomplete="off" class="space-y-5" id="login-form">
                @csrf

                {{-- Email --}}
                <div class="field-1">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Usuario</label>
                    <div class="input-wrap relative">
                        <span class="absolute inset-y-0 left-3.5 flex items-center text-slate-400 dark:text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}" autofocus autocomplete="off"
                               placeholder="correo@ejemplo.com"
                               class="input-field w-full pl-10 pr-4 py-3.5 bg-white dark:bg-slate-800/80 border text-slate-800 dark:text-white rounded-xl text-sm
                                      placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none
                                      {{ $errors->has('email') ? 'border-red-500' : 'border-slate-300 dark:border-slate-700' }}">
                    </div>
                    @error('email')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>

                {{-- Contraseña --}}
                <div class="field-2">
                    <label class="block text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-2">Contraseña</label>
                    <div class="input-wrap relative">
                        <span class="absolute inset-y-0 left-3.5 flex items-center text-slate-400 dark:text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input id="pass-input" type="password" name="password" autocomplete="new-password"
                               placeholder="••••••••"
                               class="input-field w-full pl-10 pr-12 py-3.5 bg-white dark:bg-slate-800/80 border text-slate-800 dark:text-white rounded-xl text-sm
                                      placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none
                                      {{ $errors->has('password') ? 'border-red-500' : 'border-slate-300 dark:border-slate-700' }}">
                        <button type="button" id="eye-btn"
                                class="absolute inset-y-0 right-0 px-4 flex items-center text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                            <svg id="eye-open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eye-closed" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')<p class="text-red-400 text-xs mt-1.5">{{ $message }}</p>@enderror
                </div>

                {{-- Recordarme --}}
                <div class="field-3 flex items-center">
                    <input type="checkbox" name="remember" id="remember"
                           class="w-4 h-4 rounded bg-white dark:bg-slate-800 border-slate-300 dark:border-slate-600 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-white dark:focus:ring-offset-slate-900">
                    <label for="remember" class="ml-3 text-sm text-slate-500 dark:text-slate-400 cursor-pointer select-none">Recordarme</label>
                </div>

                {{-- Botón --}}
                <div class="field-4">
                    <button type="submit" id="btn-submit"
                            class="btn-login w-full text-white font-bold py-3.5 rounded-xl text-sm tracking-widest uppercase shadow-xl mt-1">
                        <span id="btn-text">Ingresar</span>
                        <span id="btn-loading" class="hidden items-center justify-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            Verificando...
                        </span>
                    </button>
                </div>
            </form>

            <p class="text-center text-slate-400 dark:text-slate-700 text-xs mt-8">&copy; {{ date('Y') }} Qualiwuarma</p>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// ── Modo oscuro/claro ─────────────────────────────────────────
const darkIcon  = document.getElementById('theme-toggle-dark-icon');
const lightIcon = document.getElementById('theme-toggle-light-icon');

function updateThemeIcons() {
    if (document.documentElement.classList.contains('dark')) {
        lightIcon.classList.remove('hidden');
        darkIcon.classList.add('hidden');
    } else {
        lightIcon.classList.add('hidden');
        darkIcon.classList.remove('hidden');
    }
}
updateThemeIcons();

document.getElementById('theme-toggle').addEventListener('click', () => {
    if (document.documentElement.classList.contains('dark')) {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('color-theme', 'light');
    } else {
        document.documentElement.classList.add('dark');
        localStorage.setItem('color-theme', 'dark');
    }
    updateThemeIcons();
});

// ── Mostrar/ocultar contraseña ────────────────────────────────
const eyeBtn  = document.getElementById('eye-btn');
const passInput = document.getElementById('pass-input');
eyeBtn.addEventListener('click', () => {
    const show = passInput.type === 'password';
    passInput.type = show ? 'text' : 'password';
    document.getElementById('eye-open').classList.toggle('hidden', show);
    document.getElementById('eye-closed').classList.toggle('hidden', !show);
});

// ── Spinner al enviar ─────────────────────────────────────────
document.getElementById('login-form').addEventListener('submit', () => {
    document.getElementById('btn-text').classList.add('hidden');
    const loading = document.getElementById('btn-loading');
    loading.classList.remove('hidden');
    loading.classList.add('flex');
    document.getElementById('btn-submit').disabled = true;
});

// ── Partículas en canvas (panel izquierdo) ────────────────────
const canvas = document.getElementById('particles-canvas');
if (canvas) {
    const ctx = canvas.getContext('2d');

    function resize() {
        canvas.width  = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
    }
    resize();
    window.addEventListener('resize', resize);

    // Colores de partículas: azul, índigo, blanco suave
    const colors = [
        [147, 197, 253],  // blue-300
        [165, 180, 252],  // indigo-300
        [224, 231, 255],  // indigo-100
        [255, 255, 255],  // blanco
    ];

    function mkParticle() {
        const c = colors[Math.floor(Math.random() * colors.length)];
        return {
            x:       Math.random() * canvas.width,
            y:       canvas.height + Math.random() * 20,
            r:       Math.random() * 2.5 + 0.5,
            speed:   Math.random() * 0.8 + 0.3,
            opacity: Math.random() * 0.6 + 0.2,
            drift:   (Math.random() - 0.5) * 0.5,
            color:   c,
            twinkle: Math.random() * Math.PI * 2,
        };
    }

    const particles = [];
    for (let i = 0; i < 60; i++) {
        const p = mkParticle();
        p.y = Math.random() * canvas.height;
        particles.push(p);
    }

    // Líneas de conexión entre partículas cercanas
    function drawConnections() {
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const dx = particles[i].x - particles[j].x;
                const dy = particles[i].y - particles[j].y;
                const dist = Math.sqrt(dx*dx + dy*dy);
                if (dist < 80) {
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.strokeStyle = `rgba(165,180,252,${0.12 * (1 - dist / 80)})`;
                    ctx.lineWidth = 0.5;
                    ctx.stroke();
                }
            }
        }
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        drawConnections();

        particles.forEach((p, i) => {
            p.twinkle += 0.04;
            const alpha = p.opacity * (0.7 + 0.3 * Math.sin(p.twinkle));

            // Halo suave detrás de partícula grande
            if (p.r > 1.8) {
                const grad = ctx.createRadialGradient(p.x, p.y, 0, p.x, p.y, p.r * 3);
                grad.addColorStop(0, `rgba(${p.color.join(',')},${alpha * 0.4})`);
                grad.addColorStop(1, `rgba(${p.color.join(',')},0)`);
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r * 3, 0, Math.PI * 2);
                ctx.fillStyle = grad;
                ctx.fill();
            }

            ctx.beginPath();
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(${p.color.join(',')},${alpha})`;
            ctx.fill();

            p.y -= p.speed;
            p.x += p.drift;

            if (p.y < -10) particles[i] = mkParticle();
        });

        requestAnimationFrame(draw);
    }
    draw();
}
</script>
@endpush
