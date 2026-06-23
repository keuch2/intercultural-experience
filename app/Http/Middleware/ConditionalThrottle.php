<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Throttle condicional. Si config('app.api_rate_limit_disabled') está activo
 * (env API_RATE_LIMIT_DISABLED=true), deja pasar la request sin rate limiting.
 * De lo contrario, delega en el ThrottleRequests estándar de Laravel,
 * respetando los mismos parámetros (ej. throttle:5,1).
 *
 * Para reactivar el rate limiting: poner API_RATE_LIMIT_DISABLED=false (o
 * quitar la variable) en el .env y limpiar la config.
 */
class ConditionalThrottle extends ThrottleRequests
{
    public function handle($request, Closure $next, ...$args): mixed
    {
        // Leído vía config (no env() directo) para que funcione con config:cache.
        if (config('app.api_rate_limit_disabled', false)) {
            return $next($request);
        }

        return parent::handle($request, $next, ...$args);
    }
}
