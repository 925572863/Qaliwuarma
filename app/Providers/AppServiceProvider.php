<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Usado en Blade con @can('gestionar-investigacion') para mostrar/ocultar
        // botones de importar, exportar comparativos y eliminar en las fichas de tesis.
        Gate::define('gestionar-investigacion', fn (User $user) => $user->puedeGestionarInvestigacion());
    }
}
