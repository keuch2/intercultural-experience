<?php

namespace App\Mail;

use App\Models\User;
use App\Models\FinancialTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email notificando verificación de pago
 */
class PaymentVerified extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $transaction;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, FinancialTransaction $transaction)
    {
        $this->user = $user;
        $this->transaction = $transaction;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu pago ha sido verificado! - Intercultural Experience',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-verified',
            with: [
                'user' => $this->user,
                'transaction' => $this->transaction,
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
