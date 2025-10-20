<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogImageRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Log solo si es una solicitud de imagen
        if (str_contains($request->path(), 'storage/programs')) {
            Log::info('Image Request', [
                'url' => $request->fullUrl(),
                'path' => $request->path(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
            ]);
        }

        $response = $next($request);

        // Log la respuesta si es una imagen
        if (str_contains($request->path(), 'storage/programs')) {
            Log::info('Image Response', [
                'url' => $request->fullUrl(),
                'status' => $response->status(),
                'headers' => $response->headers->all(),
            ]);
        }

        return $response;
    }
}
