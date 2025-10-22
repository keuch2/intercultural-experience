<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FinanceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado
        if (!auth()->check()) {
            return redirect()->route('login')->with('error', 'Debe iniciar sesión para acceder a esta sección.');
        }
        
        // Verificar que el usuario tenga rol 'finance' o 'admin'
        if (!in_array(auth()->user()->role, ['finance', 'admin'])) {
            abort(403, 'No tiene permisos para acceder a esta sección.');
        }
        
        return $next($request);
    }
}
