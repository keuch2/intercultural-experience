<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected $token;

    /**
     * Create a new notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
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
        // URL para la app móvil (deep link)
        $mobileUrl = 'interculturalexp://reset-password?token=' . $this->token;
        
        // URL para web admin
        $webUrl = url('/password/reset/' . $this->token);

        return (new MailMessage)
            ->subject('Recuperar Contraseña - Intercultural Experience')
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Recibiste este email porque solicitaste recuperar tu contraseña.')
            ->line('Para restablecer tu contraseña, haz click en el botón de abajo:')
            ->action('Recuperar Contraseña', $webUrl)
            ->line('**Para la aplicación móvil:** Abre este link en tu dispositivo móvil.')
            ->line('Este link expirará en **60 minutos**.')
            ->line('Si no solicitaste recuperar tu contraseña, puedes ignorar este email de forma segura.')
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
            'token' => $this->token,
            'expires_at' => now()->addMinutes(60)
        ];
    }
}
