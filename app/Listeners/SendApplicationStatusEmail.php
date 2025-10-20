<?php

namespace App\Listeners;

use App\Events\ApplicationStatusChanged;
use App\Mail\ApplicationStatusChanged as ApplicationStatusChangedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Envía email cuando cambia el estado de una aplicación
 */
class SendApplicationStatusEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ApplicationStatusChanged $event): void
    {
        // Solo enviar email si el cambio es significativo
        $significantChanges = ['approved', 'rejected', 'cancelled'];
        
        if (in_array($event->newStatus, $significantChanges)) {
            Mail::to($event->application->user->email)->send(
                new ApplicationStatusChangedMail(
                    $event->application,
                    $event->oldStatus,
                    $event->newStatus
                )
            );
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(ApplicationStatusChanged $event, \Throwable $exception): void
    {
        \Log::error('Error al enviar email de cambio de estado de aplicación', [
            'application_id' => $event->application->id,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
            'error' => $exception->getMessage()
        ]);
    }
}
