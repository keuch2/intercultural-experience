<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para proteger rutas de agentes
 * Solo permite acceso a usuarios con rol 'agent'
 */
class AgentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder.');
        }
        
        $user = Auth::user();
        
        // Verificar que el usuario sea agente
        if ($user->role !== 'agent') {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Solo los agentes pueden acceder a esta área.');
        }
        
        return $next($request);
    }
}
