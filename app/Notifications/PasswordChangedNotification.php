<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Contraseña Actualizada - Intercultural Experience')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Te informamos que tu contraseña ha sido actualizada exitosamente.')
            ->line('**Fecha y hora:** ' . now()->format('d/m/Y H:i'))
            ->line('Por seguridad, se cerraron todas tus sesiones activas en otros dispositivos.')
            ->line('Si no realizaste este cambio, por favor contacta a soporte inmediatamente.')
            ->action('Iniciar Sesión', url('/login'))
            ->salutation('Saludos, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'password_changed',
            'message' => 'Tu contraseña ha sido actualizada',
            'changed_at' => now()
        ];
    }
}
