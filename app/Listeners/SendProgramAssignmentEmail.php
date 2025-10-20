<?php

namespace App\Listeners;

use App\Events\ParticipantAssignedToProgram;
use App\Mail\ProgramAssigned;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

/**
 * Envía email cuando un participante es asignado a un programa
 */
class SendProgramAssignmentEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(ParticipantAssignedToProgram $event): void
    {
        Mail::to($event->user->email)->send(
            new ProgramAssigned(
                $event->user,
                $event->program,
                $event->assignment,
                $event->assignedBy
            )
        );
    }

    /**
     * Handle a job failure.
     */
    public function failed(ParticipantAssignedToProgram $event, \Throwable $exception): void
    {
        \Log::error('Error al enviar email de asignación de programa', [
            'user_id' => $event->user->id,
            'program_id' => $event->program->id,
            'error' => $exception->getMessage()
        ]);
    }
}
