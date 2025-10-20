<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;

/**
 * @OA\Post(
 *     path="/register",
 *     tags={"Authentication"},
 *     summary="Registrar nuevo usuario",
 *     description="Registra un nuevo usuario en la plataforma. El rol se fuerza siempre a 'user' por seguridad.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name", "email", "password", "password_confirmation"},
 *             @OA\Property(property="name", type="string", example="Juan Pérez", description="Nombre completo del usuario"),
 *             @OA\Property(property="email", type="string", format="email", example="juan@example.com", description="Email único del usuario"),
 *             @OA\Property(property="password", type="string", format="password", example="StrongP@ss123", description="Contraseña fuerte (min 8 chars, mayús, minús, número, especial)"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="StrongP@ss123", description="Confirmación de contraseña"),
 *             @OA\Property(property="phone", type="string", example="+595981234567", description="Teléfono opcional"),
 *             @OA\Property(property="nationality", type="string", example="Paraguaya", description="Nacionalidad opcional"),
 *             @OA\Property(property="birth_date", type="string", example="1990-01-01", description="Fecha de nacimiento opcional"),
 *             @OA\Property(property="address", type="string", example="Av. Mcal. López 123", description="Dirección opcional")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Usuario registrado exitosamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Usuario registrado exitosamente"),
 *             @OA\Property(property="user", ref="#/components/schemas/User"),
 *             @OA\Property(property="token", type="string", example="1|abc123...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Errores de validación",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="The given data was invalid."),
 *             @OA\Property(property="errors", type="object")
 *         )
 *     )
 * )
 * 
 * Register a new user via mobile API.
 */
class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            // Validation is handled by RegisterRequest

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'nationality' => $request->nationality,
                'birth_date' => $request->birth_date,
                'address' => $request->address,
                'city' => $request->city,
                'country' => $request->country,
                'academic_level' => $request->academic_level,
                'english_level' => $request->english_level,
                'role' => 'user', // Force user role for mobile registration
            ]);

            $token = $user->createToken('mobile-app-token', ['*'], now()->addDays(30))->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Usuario registrado exitosamente',
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            \Log::error('Registration error', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'email' => $request->email ?? 'unknown'
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor. Por favor, intenta más tarde.',
                'errors' => ['general' => ['Error interno del servidor']]
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     tags={"Authentication"},
     *     summary="Iniciar sesión",
     *     description="Autentica un usuario y devuelve un token de acceso. Los administradores no pueden usar la API móvil.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="juan@example.com", description="Email del usuario"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="Contraseña del usuario")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Inicio de sesión exitoso"),
     *             @OA\Property(property="user", ref="#/components/schemas/User"),
     *             @OA\Property(property="token", type="string", example="1|abc123...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciales incorrectas",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Administrador intentando acceder via móvil",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Los administradores deben iniciar sesión a través del panel web.")
     *         )
     *     )
     * )
     */
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

            // Delete previous tokens if they exist (security: prevent token accumulation)
            $user->tokens()->delete();
            
            // Create a new token with expiration
            $token = $user->createToken('mobile-app-token', ['*'], now()->addDays(30))->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Inicio de sesión exitoso',
                'user' => $user,
                'token' => $token,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            \Log::error('Login error', [
                'error' => $e->getMessage(),
                'email' => $request->email ?? 'unknown',
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error interno del servidor. Por favor, intenta más tarde.',
                'errors' => ['general' => ['Error interno del servidor']]
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     tags={"Authentication"},
     *     summary="Cerrar sesión",
     *     description="Revoca el token de acceso actual del usuario autenticado",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout exitoso",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Sesión cerrada exitosamente")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="No autenticado",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationError")
     *     )
     * )
     */
    public function logout(Request $request)
    {
        try {
            // Option 1: Delete only current token (recommended for mobile apps)
            $request->user()->currentAccessToken()->delete();
            
            // Option 2: Delete all tokens (uncomment if you want to logout from all devices)
            // $request->user()->tokens()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Cierre de sesión exitoso'
            ]);
        } catch (\Exception $e) {
            \Log::error('Logout error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error al cerrar sesión',
                'errors' => ['general' => ['Error interno del servidor']]
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