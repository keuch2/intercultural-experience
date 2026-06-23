<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;

/**
 * Throttle condicional. Si el flag de entorno API_RATE_LIMIT_DISABLED está
 * activo, deja pasar la request sin aplicar rate limiting (útil para pruebas).
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
        if (filter_var(env('API_RATE_LIMIT_DISABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            return $next($request);
        }

        return parent::handle($request, $next, ...$args);
    }
}
