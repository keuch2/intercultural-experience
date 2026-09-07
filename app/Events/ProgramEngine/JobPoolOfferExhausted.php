<?php

namespace App\Events\ProgramEngine;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobPoolOfferExhausted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly \App\Models\JobPoolOffer $offer, public readonly ?\App\Models\User $actor = null) {}
}
