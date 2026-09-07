<?php

namespace App\Events\ProgramEngine;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class JobPoolOfferSelected
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly \App\Models\JobPoolAssignment $assignment, public readonly ?\App\Models\User $actor = null, public readonly ?string $reason = null) {}
}
