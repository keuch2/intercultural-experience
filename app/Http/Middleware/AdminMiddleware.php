<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
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
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para acceder.');
        }
        
        $user = Auth::user();
        
        if ($user->role !== 'admin') {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Solo los administradores pueden acceder al panel administrativo.');
        }
        
        return $next($request);
    }
}
