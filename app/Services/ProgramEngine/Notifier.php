<?php

namespace App\Services\ProgramEngine;

use App\Models\Notification;
use App\Models\Program;
use App\Models\ProgramProcess;
use App\Models\User;

/**
 * Notificaciones in-app (tabla `notifications`: user_id, title, message, category, is_read).
 */
class Notifier
{
    public function toUser(User|int $user, string $title, string $message, string $category = 'program'): Notification
    {
        return Notification::create([
            'user_id' => $user instanceof User ? $user->id : $user,
            'title' => $title,
            'message' => $message,
            'category' => $category,
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
    public function toProgramParticipants(Program $program, string $title, string $message, string $category = 'program', ?callable $filter = null): int
    {
        $count = 0;
        ProgramProcess::query()
            ->forProgram($program)
            ->active()
            ->chunkById(200, function ($processes) use (&$count, $filter, $title, $message, $category) {
                foreach ($processes as $process) {
                    if ($filter && ! $filter($process)) {
                        continue;
                    }
                    $this->toUser($process->user_id, $title, $message, $category);
                    $count++;
                }
            });

        return $count;
    }
}
