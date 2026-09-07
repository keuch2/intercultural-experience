<?php

namespace App\Services\ProgramEngine;

use App\Models\ProgramDocument;
use App\Models\ProgramProcess;

/**
 * Construye el envelope del proceso, superset del que expone
 * AuPairProcessController::transform. Compartido por admin y API para que ambos
 * muestren exactamente el mismo estado.
 */
class EnvelopeBuilder
{
    public function __construct(
        private readonly StageEvaluator $evaluator = new StageEvaluator,
        private readonly ProgressCalculator $progress = new ProgressCalculator,
        private readonly NextActionResolver $nextAction = new NextActionResolver,
        private readonly EnglishTestService $english = new EnglishTestService,
    ) {}

    public function build(ProgramProcess $process): array
    {
        $process->loadMissing(['application', 'program', 'gates', 'checklist']);
        $definition = ProgramDefinition::for($process->program);
        $currentIdx = $definition->stageIndex($process->current_stage_key);

        $stages = $definition->stages()->map(function ($s, $i) use ($process, $currentIdx) {
            $state = 'locked';
            if ($process->status === ProgramProcess::STATUS_COMPLETED) {
                $state = 'complete';
            } elseif ($i < $currentIdx) {
                $state = 'complete';
            } elseif ($i === $currentIdx) {
                $state = 'in_progress';
            }

            return [
                'key' => $s->key,
                'label' => $s->label,
                'state' => $state,
                'status' => $process->stageStatus($s->key),
                'is_terminal' => (bool) $s->is_terminal,
                'mobile_screen' => $s->mobile_screen,
            ];
        })->values()->all();

        $checklist = $definition->checklist()->map(function ($item) use ($process) {
            $row = $process->checklist->firstWhere('item_key', $item->key);

            return [
                'key' => $item->key,
                'label' => $item->label,
                'stage_key' => $item->stage_key,
                'item_type' => $item->item_type,
                'done' => (bool) $row?->is_done,
                'done_at' => $row?->done_at?->toIso8601String(),
                'has_file' => ! empty($row?->file_path),
            ];
        })->values()->all();

        $gates = $definition->gates()->map(function ($gate) use ($process) {
            $row = $process->gates->firstWhere('gate_key', $gate->key);

            return [
                'key' => $gate->key,
                'label' => $gate->label,
                'verified' => (bool) $row?->is_verified,
                'verified_at' => $row?->verified_at?->toIso8601String(),
                'amount' => $gate->amount !== null ? (float) $gate->amount : null,
                'currency' => $gate->currency?->code ?? null,
            ];
        })->values()->all();

        $flags = [];
        foreach ($checklist as $c) {
            $flags[$c['key']] = $c['done'];
        }
        foreach ($gates as $g) {
            $flags[$g['key']] = $g['verified'];
        }

        $statuses = [];
        foreach ($definition->stages() as $s) {
            $statuses[$s->key] = $process->stageStatus($s->key);
        }

        return [
            'id' => $process->id,
            'application_id' => $process->application_id,
            'program' => $definition->toArray(),
            'current_stage' => $process->current_stage_key,
            'status' => $process->status,
            'application_approved' => $process->applicantApproved(),
            'application_review_status' => optional($process->application)->status,
            'enrollment_date' => optional($process->enrollment_date)->toDateString(),
            'season' => $process->season,
            'stages' => $stages,
            'statuses' => $statuses,
            'flags' => $flags,
            'checklist' => $checklist,
            'gates' => $gates,
            'document_groups' => $this->documentGroups($process, $definition),
            'modules' => $this->modules($process, $definition),
            'progress_pct' => $this->progress->percent($process),
            'next_action' => $this->nextAction->resolve($process),
            'blocking_reasons' => $this->evaluator->blockingReasons($process),
            'finalization' => $process->finalization_result ? [
                'result' => $process->finalization_result,
                'reason' => $process->finalization_reason,
                'date' => optional($process->finalization_date)->toDateString(),
            ] : null,
        ];
    }

    public function documentGroups(ProgramProcess $process, ?ProgramDefinition $definition = null): array
    {
        $definition ??= ProgramDefinition::for($process->program);
        $docs = $process->documents()->get();
        $currentIdx = $definition->stageIndex($process->current_stage_key);

        return $definition->groups()->map(function ($group) use ($process, $definition, $docs, $currentIdx) {
            $reqs = $definition->requirements($group['stage_key'], $group['key']);
            [$unlocked, $reason] = $this->groupUnlockState($process, $definition, $group, $currentIdx);

            $required = $approved = $pending = $missing = 0;
            foreach ($reqs as $req) {
                if (! $req->is_required) {
                    continue;
                }
                $required++;
                $forReq = $docs->where('requirement_key', $req->key);
                if ($forReq->where('status', ProgramDocument::STATUS_APPROVED)->count() >= max(1, (int) $req->min_count)) {
                    $approved++;
                } elseif ($forReq->where('status', ProgramDocument::STATUS_PENDING)->isNotEmpty()) {
                    $pending++;
                } else {
                    $missing++;
                }
            }

            return [
                'key' => $group['key'],
                'label' => $group['label'],
                'stage_key' => $group['stage_key'],
                'unlock_gate_key' => $group['unlock_gate_key'],
                'unlocked' => $unlocked,
                'lock_reason' => $reason,
                'counts' => compact('required', 'approved', 'pending', 'missing'),
            ];
        })->values()->all();
    }

    /**
     * Un grupo está desbloqueado cuando la postulación fue aprobada, su etapa ya
     * fue alcanzada y (si tiene) su gate de pago está verificado.
     *
     * @return array{0: bool, 1: ?string}
     */
    public function groupUnlockState(ProgramProcess $process, ProgramDefinition $definition, array $group, ?int $currentIdx = null): array
    {
        if (! $process->applicantApproved()) {
            return [false, 'pending_approval'];
        }
        $currentIdx ??= $definition->stageIndex($process->current_stage_key);
        if ($definition->stageIndex($group['stage_key']) > $currentIdx) {
            return [false, 'stage_locked'];
        }
        $gate = $definition->gate($group['unlock_gate_key']);
        if ($gate && ! $process->isGateVerified($gate->key)) {
            return [false, 'gate:'.$gate->key];
        }

        return [true, null];
    }

    private function modules(ProgramProcess $process, ProgramDefinition $definition): array
    {
        $out = [];
        foreach ($definition->modules() as $module) {
            $meta = ModuleCatalog::get($module);
            $entry = ['enabled' => true, 'label' => $meta['label'], 'icon' => $meta['icon'], 'screen' => $meta['mobile_screen'], 'implemented' => $meta['implemented']];

            switch ($module) {
                case ModuleCatalog::ENGLISH_TEST:
                    $entry += [
                        'best_level' => $this->english->bestLevel($process),
                        'min_level' => $definition->minEnglishLevel(),
                        'remaining_attempts' => $this->english->remainingAttempts($process),
                        'meets_minimum' => $this->english->meetsMinimum($process),
                    ];
                    break;
                case ModuleCatalog::VISA:
                    $entry += ['progress' => $process->visaProcess?->progress ?? 0];
                    break;
                case ModuleCatalog::JOB_POOL:
                    $entry += [
                        'access' => $process->hasModuleAccess(ModuleCatalog::JOB_POOL),
                        'has_active_assignment' => $this->evaluator->hasActiveJobAssignment($process),
                    ];
                    break;
                case ModuleCatalog::PLACEMENT:
                    $entry += ['complete' => $this->evaluator->placementComplete($process)];
                    break;
            }
            $out[$module] = $entry;
        }

        return $out;
    }
}
