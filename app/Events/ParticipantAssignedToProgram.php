<?php

namespace App\Events;

use App\Models\User;
use App\Models\Program;
use App\Models\ProgramAssignment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento disparado cuando un participante es asignado a un programa
 */
class ParticipantAssignedToProgram
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public Program $program;
    public ProgramAssignment $assignment;
    public User $assignedBy;

    /**
     * Create a new event instance.
     */
    public function __construct(
        User $user, 
        Program $program, 
        ProgramAssignment $assignment,
        User $assignedBy
    ) {
        $this->user = $user;
        $this->program = $program;
        $this->assignment = $assignment;
        $this->assignedBy = $assignedBy;
    }
}
