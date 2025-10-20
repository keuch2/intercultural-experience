<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Program;
use App\Models\ProgramAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email notificando asignación a programa
 */
class ProgramAssigned extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $program;
    public $assignment;
    public $assignedBy;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Program $program, ProgramAssignment $assignment, User $assignedBy)
    {
        $this->user = $user;
        $this->program = $program;
        $this->assignment = $assignment;
        $this->assignedBy = $assignedBy;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Has sido asignado a un programa! - Intercultural Experience',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.program-assigned',
            with: [
                'user' => $this->user,
                'program' => $this->program,
                'assignment' => $this->assignment,
                'assignedBy' => $this->assignedBy,
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
