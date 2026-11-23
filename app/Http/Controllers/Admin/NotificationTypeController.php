<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\NotificationType;
use App\Support\NotificationCatalog;
use Illuminate\Http\Request;

/**
 * Configuración > Notificaciones: por cada tipo de aviso, si genera push y si es obligatorio
 * (llega aunque el participante haya silenciado la categoría). El aviso in-app se crea siempre.
 */
class NotificationTypeController extends Controller
{
    public function index()
    {
        return view('admin.notification-types.index', [
            'settings' => NotificationType::settings(),
            'groups' => NotificationCatalog::typesByCategory(),
        ]);
    }

    public function update(Request $request)
    {
        $push = (array) $request->input('push', []);
        $mandatory = (array) $request->input('mandatory', []);

        foreach (array_keys(NotificationCatalog::TYPES) as $key) {
            NotificationType::updateOrCreate(['key' => $key], [
                'push_enabled' => (bool) ($push[$key] ?? false),
                'mandatory' => (bool) ($mandatory[$key] ?? false),
            ]);
        }
        NotificationType::forgetCache();

        ActivityLog::log('settings')->withAction('notification_types_updated')
            ->causedBy($request->user())->log('Configuración de notificaciones push actualizada');

        return redirect()->route('admin.notification-types.index')->with('success', 'Configuración de notificaciones guardada.');
    }
}
