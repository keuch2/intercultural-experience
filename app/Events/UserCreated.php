<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento disparado cuando se crea un nuevo usuario
 */
class UserCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public User $user;
    public ?string $temporaryPassword;
    public ?User $createdBy;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, ?string $temporaryPassword = null, ?User $createdBy = null)
    {
        $this->user = $user;
        $this->temporaryPassword = $temporaryPassword;
        $this->createdBy = $createdBy;
    }
}
