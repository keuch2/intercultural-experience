<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\NotificationType;
use App\Models\UserNotificationPreference;
use App\Support\NotificationCatalog;
use Illuminate\Http\Request;

/**
 * Preferencias de notificaciones push por categoría (Perfil > Notificaciones).
 * Silenciar una categoría solo corta la push: el aviso sigue apareciendo en Avisos.
 *  GET /api/me/notification-preferences
 *  PUT /api/me/notification-preferences   {preferences: {category: bool}}
 */
class NotificationPreferenceController extends Controller
{
    public function show(Request $request)
    {
        return response()->json(['status' => 'success', 'data' => $this->payload($request->user()->id)]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'preferences' => 'required|array',
            'preferences.*' => 'boolean',
        ]);
        $categories = array_keys(NotificationCatalog::CATEGORIES);
        foreach ($data['preferences'] as $category => $enabled) {
            if (! in_array($category, $categories, true)) {
                continue;
            }
            UserNotificationPreference::updateOrCreate(
                ['user_id' => $request->user()->id, 'category' => $category],
                ['push_enabled' => (bool) $enabled],
            );
        }

        return response()->json(['status' => 'success', 'message' => 'Preferencias guardadas.', 'data' => $this->payload($request->user()->id)]);
    }

    private function payload(int $userId): array
    {
        $prefs = UserNotificationPreference::where('user_id', $userId)->pluck('push_enabled', 'category');
        $settings = NotificationType::settings();
        $categories = [];
        foreach (NotificationCatalog::typesByCategory() as $category => $types) {
            $active = array_values(array_filter($types, fn ($t) => $settings[$t]['push_enabled']));
            $categories[] = [
                'key' => $category,
                'label' => NotificationCatalog::CATEGORIES[$category]['label'],
                'description' => NotificationCatalog::CATEGORIES[$category]['description'],
                'icon' => NotificationCatalog::CATEGORIES[$category]['icon'],
                'push_enabled' => (bool) ($prefs[$category] ?? true),
                // Avisos que llegan igual aunque la categoría esté silenciada.
                'mandatory' => array_values(array_map(fn ($t) => NotificationCatalog::label($t), array_filter($active, fn ($t) => $settings[$t]['mandatory']))),
                'available' => $active !== [],
            ];
        }

        return [
            'categories' => $categories,
            'note' => 'Los avisos siempre quedan en la sección Avisos de la app. Estas opciones solo deciden qué notificaciones push recibís en el celular.',
        ];
    }
}
