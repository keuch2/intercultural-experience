<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }
        
        if ($user->role !== $role) {
            return response()->json([
                'status' => 'error',
                'message' => 'No tienes permisos para acceder a este recurso.',
                'required_role' => $role,
                'user_role' => $user->role
            ], 403);
        }

        return $next($request);
    }
}