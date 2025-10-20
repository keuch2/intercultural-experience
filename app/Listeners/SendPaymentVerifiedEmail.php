<?php

namespace App\Listeners;

use App\Events\PaymentVerified;
use App\Mail\PaymentVerified as PaymentVerifiedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Envía email cuando un pago es verificado
 */
class SendPaymentVerifiedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentVerified $event): void
    {
        $payment = $event->payment;
        $user = $payment->application->user;
        
        Mail::to($user->email)->send(
            new PaymentVerifiedMail($payment, $user)
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(PaymentVerified $event, \Throwable $exception): void
    {
        \Log::error('Error al enviar email de pago verificado', [
            'payment_id' => $event->payment->id,
            'error' => $exception->getMessage()
        ]);
    }
}
