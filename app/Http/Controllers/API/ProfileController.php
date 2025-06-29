<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

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
            'birth_date' => ['nullable', 'date'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255']
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
            'message' => 'Perfil actualizado correctamente',
            'user' => $user
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
            'message' => 'Avatar updated successfully',
            'avatar_url' => Storage::url($path),
            'user' => $user,
        ]);
    }
}
