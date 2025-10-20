<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Enviar link de recuperación de contraseña
     * 
     * POST /api/password/forgot
     * Rate limited: 3 intentos por hora
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Verificar que el usuario existe
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Por seguridad, no revelar si el email existe o no
            return response()->json([
                'status' => 'success',
                'message' => 'Si el email existe en nuestro sistema, recibirás un link de recuperación.'
            ]);
        }

        // Generar token único
        $token = Str::random(64);

        // Eliminar tokens anteriores del usuario
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Guardar nuevo token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]);

        // Enviar email con el token
        $user->notify(new ResetPasswordNotification($token));

        return response()->json([
            'status' => 'success',
            'message' => 'Si el email existe en nuestro sistema, recibirás un link de recuperación.'
        ]);
    }

    /**
     * Validar token de recuperación
     * 
     * POST /api/password/validate-token
     */
    public function validateToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        $tokenData = DB::table('password_reset_tokens')
            ->where('created_at', '>=', Carbon::now()->subMinutes(60))
            ->get();

        foreach ($tokenData as $record) {
            if (Hash::check($request->token, $record->token)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Token válido',
                    'email' => $record->email
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Token inválido o expirado'
        ], 422);
    }

    /**
     * Resetear contraseña con token
     * 
     * POST /api/password/reset
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'password' => [
                'required',
                'min:8',
                'regex:/[a-z]/',      // al menos una minúscula
                'regex:/[A-Z]/',      // al menos una mayúscula
                'regex:/[0-9]/',      // al menos un número
                'confirmed'
            ],
        ], [
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden'
        ]);

        // Buscar token válido (no expirado)
        $tokenData = DB::table('password_reset_tokens')
            ->where('created_at', '>=', Carbon::now()->subMinutes(60))
            ->get();

        $validRecord = null;
        foreach ($tokenData as $record) {
            if (Hash::check($request->token, $record->token)) {
                $validRecord = $record;
                break;
            }
        }

        if (!$validRecord) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token inválido o expirado. Por favor, solicita un nuevo link de recuperación.'
            ], 422);
        }

        // Buscar usuario
        $user = User::where('email', $validRecord->email)->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Actualizar contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        // Eliminar token usado
        DB::table('password_reset_tokens')
            ->where('email', $validRecord->email)
            ->delete();

        // Invalidar todos los tokens de Sanctum (cerrar sesión en todos los dispositivos)
        $user->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Contraseña actualizada exitosamente. Por favor, inicia sesión con tu nueva contraseña.'
        ]);
    }
}
