<?php

namespace App\Http\Controllers\Auth;

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
     * Mostrar formulario para solicitar reset
     */
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Enviar email con link de reset
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Verificar que el usuario existe
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Por seguridad, no revelar si el email existe
            return back()->with('status', 'Si el email existe en nuestro sistema, recibirás un link de recuperación.');
        }

        // Generar token único
        $token = Str::random(64);

        // Eliminar tokens anteriores
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Guardar nuevo token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]);

        // Enviar email
        $user->notify(new ResetPasswordNotification($token));

        return back()->with('status', 'Si el email existe en nuestro sistema, recibirás un link de recuperación.');
    }

    /**
     * Mostrar formulario de reset con token
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.passwords.reset')->with([
            'token' => $token,
            'email' => $request->email
        ]);
    }

    /**
     * Resetear contraseña
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'confirmed'
            ],
        ], [
            'password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'password.confirmed' => 'Las contraseñas no coinciden'
        ]);

        // Buscar token válido
        $tokenData = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('created_at', '>=', Carbon::now()->subMinutes(60))
            ->first();

        if (!$tokenData || !Hash::check($request->token, $tokenData->token)) {
            return back()->withErrors(['email' => 'El link de recuperación es inválido o ha expirado.']);
        }

        // Buscar usuario
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No se encontró un usuario con ese email.']);
        }

        // Actualizar contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        // Eliminar token usado
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Invalidar tokens de Sanctum
        $user->tokens()->delete();

        return redirect()->route('login')->with('status', 'Tu contraseña ha sido actualizada exitosamente. Por favor, inicia sesión.');
    }
}
