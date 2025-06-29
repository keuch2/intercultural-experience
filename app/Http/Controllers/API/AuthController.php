<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:50',
                'nationality' => 'nullable|string|max:100',
                'birth_date' => 'nullable|date',
                'address' => 'nullable|string',
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'nationality' => $request->nationality,
                'birth_date' => $request->birth_date,
                'address' => $request->address,
            ]);

            $token = $user->createToken('mobile-app-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario registrado exitosamente',
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar usuario',
                'errors' => $e instanceof ValidationException ? $e->errors() : ['general' => [$e->getMessage()]]
            ], $e instanceof ValidationException ? 422 : 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Las credenciales proporcionadas son incorrectas.',
                    'errors' => ['email' => ['Las credenciales proporcionadas son incorrectas.']]
                ], 401);
            }
            
            // Check if user is admin, React Native app is only for regular users
            if ($user->role === 'admin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Los administradores deben iniciar sesión a través del panel web.',
                    'errors' => ['role' => ['Los administradores deben usar el panel web.']]
                ], 403);
            }

            // Delete previous tokens if they exist
            $user->tokens()->delete();
            
            // Create a new token
            $token = $user->createToken('mobile-app-token')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Inicio de sesión exitoso',
                'user' => $user,
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al iniciar sesión',
                'errors' => $e instanceof ValidationException ? $e->errors() : ['general' => [$e->getMessage()]]
            ], $e instanceof ValidationException ? 422 : 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Cierre de sesión exitoso'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cerrar sesión',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            $user = $request->user();
            
            // Load related data that might be useful for the app
            $user->load([
                'applications' => function($query) {
                    $query->latest();
                },
                'points' => function($query) {
                    $query->latest()->take(5);
                },
                'notifications' => function($query) {
                    $query->latest()->take(10)->where('is_read', false);
                }
            ]);
            
            // Calculate total points
            $totalPoints = $user->points()->sum('change');
            
            return response()->json([
                'status' => 'success',
                'user' => $user,
                'total_points' => $totalPoints,
                'unread_notifications' => $user->notifications->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al obtener datos del usuario',
                'errors' => ['general' => [$e->getMessage()]]
            ], 500);
        }
    }
}