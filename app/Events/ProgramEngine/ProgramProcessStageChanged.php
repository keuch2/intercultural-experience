<?php

namespace App\Events\ProgramEngine;

use App\Models\ProgramProcess;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProgramProcessStageChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ProgramProcess $process,
        public readonly string $fromStage,
        public readonly string $toStage,
        public readonly ?User $actor = null,
    ) {}
}
