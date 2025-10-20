<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Mail\WelcomeUser;
use App\Mail\CredentialsSent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Envía email de bienvenida cuando se crea un usuario
 */
class SendWelcomeEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(UserCreated $event): void
    {
        // Si se proporcionó contraseña temporal, enviar email con credenciales
        if ($event->temporaryPassword) {
            Mail::to($event->user->email)->send(
                new CredentialsSent($event->user, $event->temporaryPassword, $event->createdBy)
            );
        } else {
            // Enviar email de bienvenida simple
            Mail::to($event->user->email)->send(
                new WelcomeUser($event->user)
            );
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserCreated $event, \Throwable $exception): void
    {
        \Log::error('Error al enviar email de bienvenida', [
            'user_id' => $event->user->id,
            'email' => $event->user->email,
            'error' => $exception->getMessage()
        ]);
    }
}
