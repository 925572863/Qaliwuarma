<?php

namespace App\Http\Controllers\Concerns;

use App\Models\RegistroAsistencia;

/**
 * Agrega los registros de asistencia por día (fecha + nivel), sumando las
 * raciones de todas las secciones y determinando el clima/evento del día:
 * si cualquier sección reportó lluvia/evento especial, el día completo se
 * marca así (misma regla usada en PrediccionIAService para el modelo IA,
 * de modo que los reportes sean consistentes con las variables del modelo).
 */
trait CalculaContextoDiario
{
    private function contextoDiario(string $nivel): array
    {
        return RegistroAsistencia::where('nivel', $nivel)
            ->get()
            ->groupBy(fn ($r) => $r->fecha->toDateString())
            ->map(function ($grupo, $fecha) {
                $climas = $grupo->pluck('condicion_climatica')->filter()->values();
                $clima = null;
                if ($climas->contains('lluvioso')) $clima = 'lluvioso';
                elseif ($climas->contains('nublado')) $clima = 'nublado';
                elseif ($climas->contains('soleado')) $clima = 'soleado';

                return [
                    'fecha' => $fecha,
                    'raciones' => (float) $grupo->sum('raciones'),
                    'clima' => $clima,
                    'evento_especial' => $grupo->contains(fn ($r) => (bool) $r->evento_especial),
                ];
            })
            ->values()
            ->toArray();
    }
}
