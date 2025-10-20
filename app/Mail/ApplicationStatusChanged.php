<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email notificando cambio de estado de aplicación
 */
class ApplicationStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $application;
    public $oldStatus;
    public $newStatus;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Application $application, string $oldStatus, string $newStatus)
    {
        $this->user = $user;
        $this->application = $application;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match($this->newStatus) {
            'approved' => '¡Tu aplicación ha sido aprobada!',
            'rejected' => 'Actualización de tu aplicación',
            'pending' => 'Tu aplicación está en revisión',
            default => 'Actualización de tu aplicación'
        };

        return new Envelope(
            subject: $subject . ' - Intercultural Experience',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.application-status-changed',
            with: [
                'user' => $this->user,
                'application' => $this->application,
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
