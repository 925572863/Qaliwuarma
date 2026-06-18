<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'login',
            'logout',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Sesión expirada (token CSRF inválido) → redirigir al login en vez de mostrar 419
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            return redirect()->route('login')->withErrors([
                'email' => 'Tu sesión expiró. Por favor inicia sesión de nuevo.',
            ]);
        });
    })->create();
