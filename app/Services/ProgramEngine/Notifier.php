<?php

namespace App\Services\ProgramEngine;

use App\Models\Notification;
use App\Models\Program;
use App\Models\ProgramProcess;
use App\Models\User;
use App\Services\Notifications\PushPolicy;
use Throwable;

/**
 * Avisos in-app (tabla `notifications`). Con `$type` (ver NotificationCatalog) el aviso además queda
 * marcado para push según PushPolicy; la push la envía `notifications:dispatch`.
 */
class Notifier
{
    /** @param  array  $data  a dónde lleva el aviso en la app (target, program_id, url) */
    public function toUser(User|int $user, string $title, string $message, string $category = 'program', ?string $type = null, array $data = []): Notification
    {
        $userId = $user instanceof User ? $user->id : $user;
        $pushStatus = null;
        if ($type) {
            try {
                $pushStatus = app(PushPolicy::class)->statusFor($userId, $type);
            } catch (Throwable $e) {
                report($e); // la push nunca debe impedir el aviso in-app
            }
        }

        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'category' => $category,
            'notification_type' => $type,
            'data' => $data ?: null,
            'push_status' => $pushStatus,
            'is_read' => false,
            'created_at' => now(),
        ]);
    }

    public function toAdmins(string $title, string $message, string $category = 'program'): int
    {
        $count = 0;
        User::query()->where('role', 'admin')->pluck('id')->each(function ($id) use (&$count, $title, $message, $category) {
            $this->toUser($id, $title, $message, $category);
            $count++;
        });

        return $count;
    }

    /**
     * Notifica a los participantes de un programa; $filter recibe cada proceso y
     * decide si notificarlo (p.ej. acceso al pool habilitado).
     */
    public function toProgramParticipants(Program $program, string $title, string $message, string $category = 'program', ?callable $filter = null, ?string $type = null): int
    {
        $count = 0;
        ProgramProcess::query()
            ->forProgram($program)
            ->active()
            ->chunkById(200, function ($processes) use (&$count, $filter, $title, $message, $category, $type) {
                foreach ($processes as $process) {
                    if ($filter && ! $filter($process)) {
                        continue;
                    }
                    $this->toUser($process->user_id, $title, $message, $category, $type);
                    $count++;
                }
            });

        return $count;
    }
}
