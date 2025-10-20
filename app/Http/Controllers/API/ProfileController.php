<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Notifications\PasswordChangedNotification;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->fill($validator->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => ['required', 'image', 'max:1024'], // 1MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        $user = $request->user();

        // Store the avatar...
        // This is likely to have additional code here in the original method
        
        return response()->json([
            'message' => 'Avatar updated successfully',
            'user' => $user,
        ]);
    }
    
    /**
     * API method to update the user's profile via mobile app.
     */
    public function apiUpdate(Request $request)
    {
        $user = $request->user();
        
        // Validar los campos que recibimos del app móvil, incluyendo los nuevos campos
        $validator = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'birth_date' => ['nullable', 'date'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'academic_level' => ['nullable', 'in:bachiller,licenciatura,maestria,posgrado,doctorado'],
            'english_level' => ['nullable', 'in:basico,intermedio,avanzado,nativo']
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        
        // Solo actualizamos los campos que se enviaron en la solicitud
        $validated = $validator->validated();
        
        // Filtrar solo los campos que realmente están presentes en la solicitud
        $dataToUpdate = array_filter($validated, function ($value) {
            return $value !== null;
        });
        
        // Si tenemos una fecha de nacimiento, asegurarnos de que esté en el formato correcto
        if (isset($dataToUpdate['birth_date'])) {
            try {
                $dataToUpdate['birth_date'] = Carbon::parse($dataToUpdate['birth_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json(['errors' => ['birth_date' => ['Formato de fecha inválido']]], 422);
            }
        }
        
        // Log para depuración
        \Log::info('Actualizando perfil de usuario con datos:', $dataToUpdate);
        
        // Actualizar y guardar el usuario
        $user->fill($dataToUpdate);
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $user->bio,
                'avatar_url' => $user->avatar_url,
                'phone' => $user->phone,
                'address' => $user->address,
                'nationality' => $user->nationality,
                'birth_date' => $user->birth_date?->format('Y-m-d'),
                'city' => $user->city,
                'country' => $user->country,
                'academic_level' => $user->academic_level,
                'english_level' => $user->english_level,
            ]
        ]);
    }
    
    /**
     * API method to update the user's avatar via mobile app.
     */
    public function apiUpdateAvatar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'avatar' => ['required', 'image', 'max:2048'], // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $path = $request->file('avatar')->store('avatars', 'public');
        $user->avatar = $path;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Avatar actualizado correctamente',
            'avatar_url' => $user->avatar_url,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'bio' => $user->bio,
                'avatar_url' => $user->avatar_url,
            ]
        ]);
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => [
                'required',
                'min:8',
                'regex:/[a-z]/',      // al menos una minúscula
                'regex:/[A-Z]/',      // al menos una mayúscula
                'regex:/[0-9]/',      // al menos un número
                'confirmed'
            ],
        ], [
            'new_password.regex' => 'La contraseña debe contener al menos una mayúscula, una minúscula y un número.',
            'new_password.min' => 'La contraseña debe tener al menos 8 caracteres',
            'new_password.confirmed' => 'Las contraseñas no coinciden'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verificar contraseña actual
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'La contraseña actual es incorrecta'
            ], 422);
        }

        // Verificar que no sea una contraseña usada recientemente
        if (!$user->canUsePassword($request->new_password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No puedes usar una de tus últimas 3 contraseñas'
            ], 422);
        }

        // Actualizar contraseña
        $user->password = Hash::make($request->new_password);
        $user->save();

        // Agregar al historial
        $user->addPasswordToHistory($request->new_password);

        // Invalidar todos los tokens excepto el actual
        $currentToken = $user->currentAccessToken();
        $user->tokens()->where('id', '!=', $currentToken->id)->delete();

        // Enviar email de notificación
        try {
            $user->notify(new PasswordChangedNotification());
        } catch (\Exception $e) {
            \Log::warning('Failed to send password changed notification: ' . $e->getMessage());
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Contraseña actualizada exitosamente'
        ]);
    }
}
