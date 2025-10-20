<?php

namespace App\Events;

use App\Models\UserProgramRequisite;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Evento disparado cuando un pago es verificado
 */
class PaymentVerified
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public UserProgramRequisite $payment;

    /**
     * Create a new event instance.
     */
    public function __construct(UserProgramRequisite $payment)
    {
        $this->payment = $payment;
    }
}
