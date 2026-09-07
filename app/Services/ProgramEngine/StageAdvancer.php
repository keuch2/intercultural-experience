<?php

namespace App\Services\ProgramEngine;

use App\Events\ProgramEngine\ProgramProcessStageChanged;
use App\Models\ActivityLog;
use App\Models\ProgramProcess;
use App\Models\User;
use App\Services\ProgramEngine\Exceptions\StageTransitionException;
use Illuminate\Support\Facades\DB;

class StageAdvancer
{
    public function __construct(
        private readonly StageEvaluator $evaluator = new StageEvaluator,
        private readonly ProgressCalculator $progress = new ProgressCalculator,
    ) {}

    /**
     * Avanza a la siguiente etapa. Con $force = true ignora los guards (acción
     * explícita del staff), pero nunca la estructura (etapa terminal, etc.).
     */
    public function advance(ProgramProcess $process, ?User $actor = null, bool $force = false): ProgramProcess
    {
        $definition = ProgramDefinition::for($process->program);
        $reasons = $this->evaluator->blockingReasons($process);
        $current = $definition->stage($process->current_stage_key);
        $next = $current ? $definition->nextStage($current->key) : null;

        if (! $current || ! $next || ($current->is_terminal)) {
            throw new StageTransitionException($reasons ?: ['Transición inválida.']);
        }
        if ($reasons !== [] && ! $force) {
            throw new StageTransitionException($reasons);
        }

        DB::transaction(function () use ($process, $current, $next, $actor, $force) {
            $now = now()->toIso8601String();
            $process->setStageState($current->key, ['status' => ProgramProcess::STAGE_APPROVED, 'completed_at' => $now]);
            $process->setStageState($next->key, ['status' => ProgramProcess::STAGE_IN_PROGRESS, 'entered_at' => $now]);
            $process->current_stage_key = $next->key;
            if ($next->is_terminal) {
                $process->status = ProgramProcess::STATUS_COMPLETED;
            }
            $process->save();

            $this->syncApplication($process);
            $this->log($process, $actor, 'stage_advanced', "Etapa avanzada de '{$current->label}' a '{$next->label}'".($force ? ' (forzado)' : ''), [
                'from' => $current->key, 'to' => $next->key, 'forced' => $force,
            ]);
        });

        event(new ProgramProcessStageChanged($process->fresh(), $current->key, $next->key, $actor));

        return $process;
    }

    /** Vuelve a una etapa anterior; las posteriores quedan bloqueadas. */
    public function revert(ProgramProcess $process, string $toStageKey, ?User $actor = null, ?string $reason = null): ProgramProcess
    {
        $definition = ProgramDefinition::for($process->program);
        $target = $definition->stage($toStageKey);
        $from = $process->current_stage_key;

        if (! $target || $definition->stageIndex($toStageKey) >= $definition->stageIndex($from)) {
            throw new StageTransitionException(['Solo se puede retroceder a una etapa anterior.']);
        }

        DB::transaction(function () use ($process, $definition, $target, $from, $actor, $reason) {
            $targetIdx = $definition->stageIndex($target->key);
            foreach ($definition->stages() as $i => $stage) {
                if ($i === $targetIdx) {
                    $process->setStageState($stage->key, ['status' => ProgramProcess::STAGE_IN_PROGRESS, 'entered_at' => now()->toIso8601String(), 'completed_at' => null]);
                } elseif ($i > $targetIdx) {
                    $process->setStageState($stage->key, ['status' => ProgramProcess::STAGE_LOCKED, 'entered_at' => null, 'completed_at' => null]);
                }
            }
            $process->current_stage_key = $target->key;
            $process->status = ProgramProcess::STATUS_ACTIVE;
            $process->save();

            $this->syncApplication($process);
            $this->log($process, $actor, 'stage_reverted', "Etapa retrocedida a '{$target->label}'".($reason ? ": {$reason}" : ''), [
                'from' => $from, 'to' => $target->key, 'reason' => $reason,
            ]);
        });

        event(new ProgramProcessStageChanged($process->fresh(), $from, $target->key, $actor));

        return $process;
    }

    public function cancel(ProgramProcess $process, ?User $actor = null, ?string $reason = null): ProgramProcess
    {
        $process->status = ProgramProcess::STATUS_CANCELLED;
        $process->finalization_result = 'cancelled';
        $process->finalization_reason = $reason;
        $process->finalization_date = now()->toDateString();
        $process->finalized_by = $actor?->id;
        $process->save();

        $this->syncApplication($process, 'cancelled');
        $this->log($process, $actor, 'process_cancelled', 'Proceso cancelado'.($reason ? ": {$reason}" : ''), ['reason' => $reason]);

        return $process;
    }

    public function finalize(ProgramProcess $process, ?User $actor, string $result, ?string $reason = null, ?string $date = null): ProgramProcess
    {
        $process->finalization_result = $result;
        $process->finalization_reason = $reason;
        $process->finalization_date = $date ?: now()->toDateString();
        $process->finalized_by = $actor?->id;
        $process->save();

        $this->log($process, $actor, 'process_finalized', "Finalización registrada: {$result}", ['result' => $result, 'reason' => $reason]);

        return $process;
    }

    private function syncApplication(ProgramProcess $process, ?string $applicationStage = null): void
    {
        $application = $process->application;
        if (! $application) {
            return;
        }
        $application->current_stage = $applicationStage ?? $process->current_stage_key;
        $application->progress_percentage = $this->progress->percent($process);
        if ($process->status === ProgramProcess::STATUS_COMPLETED && ! $application->completed_at) {
            $application->completed_at = now();
        }
        $application->save();
    }

    private function log(ProgramProcess $process, ?User $actor, string $action, string $description, array $props = []): void
    {
        $builder = ActivityLog::log($process->program?->slug ?? 'program_engine')
            ->performedOn($process->user ?? $process)
            ->withAction($action)
            ->withProperties(array_merge(['program_process_id' => $process->id], $props));
        if ($actor) {
            $builder->causedBy($actor);
        }
        $builder->log($description);
    }
}
