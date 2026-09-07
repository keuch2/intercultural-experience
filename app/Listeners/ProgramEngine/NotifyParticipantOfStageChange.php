<?php

namespace App\Listeners\ProgramEngine;

use App\Events\ProgramEngine\ProgramProcessStageChanged;
use App\Services\ProgramEngine\Notifier;
use App\Services\ProgramEngine\ProgramDefinition;

class NotifyParticipantOfStageChange
{
    public function __construct(private readonly Notifier $notifier) {}

    public function handle(ProgramProcessStageChanged $event): void
    {
        $process = $event->process;
        $program = $process->program;
        if (! $program) {
            return;
        }
        $stage = ProgramDefinition::for($program)->stage($event->toStage);
        $label = $stage?->label ?? $event->toStage;

        $this->notifier->toUser(
            $process->user_id,
            "Tu proceso avanzó: {$label}",
            "Tu postulación a {$program->name} pasó a la etapa \"{$label}\". Revisá la app para ver los próximos pasos.",
            'program_stage',
        );
    }
}
