<?php

namespace App\Services\Notifications;

use App\Models\NotificationType;
use App\Models\User;
use App\Models\UserNotificationPreference;
use App\Support\NotificationCatalog;

/**
 * Decide si un aviso genera push (el aviso in-app se crea siempre):
 * push si el tipo tiene la push activa (admin) Y (tipo obligatorio, aviso "Importante" o categoría no silenciada)
 * Y el destinatario es participante con cuenta de la app vigente. Los dispositivos se resuelven al enviar.
 */
class PushPolicy
{
    /**
     * @param  list<int>  $userIds
     * @return array<int, ?string> user_id => push_status (null = sin push · pending · muted · disabled)
     */
    public function statuses(array $userIds, ?string $type, bool $important = false): array
    {
        $out = array_fill_keys($userIds, null);
        $category = NotificationCatalog::categoryOf($type);
        if (! $category || ! $userIds) {
            return $out;
        }

        $config = NotificationType::settings()[$type];
        $mandatory = $config['mandatory'] || $important;

        foreach (array_chunk($userIds, 1000) as $chunk) {
            $participants = User::query()->whereIn('id', $chunk)->where('role', 'user')
                ->whereNull('app_account_deleted_at')->pluck('id')->all();
            $muted = $mandatory ? [] : UserNotificationPreference::query()->whereIn('user_id', $participants)
                ->where('category', $category)->where('push_enabled', false)->pluck('user_id')->flip()->all();

            foreach ($participants as $id) {
                $out[$id] = match (true) {
                    ! $config['push_enabled'] => 'disabled',
                    isset($muted[$id]) => 'muted',
                    default => 'pending',
                };
            }
        }

        return $out;
    }

    public function statusFor(int $userId, ?string $type, bool $important = false): ?string
    {
        return $this->statuses([$userId], $type, $important)[$userId] ?? null;
    }
}
