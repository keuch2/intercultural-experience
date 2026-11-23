<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\Request;

/**
 * Dispositivos para notificaciones push (token de Expo).
 *  POST   /api/me/device-tokens   alta/actualización (al iniciar sesión y al abrir la app)
 *  DELETE /api/me/device-tokens   baja (al cerrar sesión)
 */
class DeviceTokenController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:255', 'regex:/^Expo(nent)?PushToken\[[^\]]+\]$/'],
            'platform' => 'nullable|in:ios,android',
            'app_version' => 'nullable|string|max:20',
            'device_name' => 'nullable|string|max:100',
        ]);

        // Un dispositivo pertenece a quien tiene la sesión: si otra persona inició sesión antes en él, se reasigna.
        $device = DeviceToken::updateOrCreate(['token' => $data['token']], [
            'user_id' => $request->user()->id,
            'platform' => $data['platform'] ?? null,
            'app_version' => $data['app_version'] ?? null,
            'device_name' => $data['device_name'] ?? null,
            'last_seen_at' => now(),
        ]);

        return response()->json(['status' => 'success', 'data' => ['id' => $device->id]]);
    }

    public function destroy(Request $request)
    {
        $data = $request->validate(['token' => 'required|string|max:255']);
        DeviceToken::where('user_id', $request->user()->id)->where('token', $data['token'])->delete();

        return response()->json(['status' => 'success']);
    }
}
