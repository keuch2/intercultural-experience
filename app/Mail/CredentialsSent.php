<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email con credenciales de acceso
 */
class CredentialsSent extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $temporaryPassword;
    public $createdBy;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $temporaryPassword, User $createdBy = null)
    {
        $this->user = $user;
        $this->temporaryPassword = $temporaryPassword;
        $this->createdBy = $createdBy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tus Credenciales de Acceso - Intercultural Experience',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.credentials',
            with: [
                'user' => $this->user,
                'temporaryPassword' => $this->temporaryPassword,
                'createdBy' => $this->createdBy,
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
