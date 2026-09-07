<?php

namespace App\Services\ProgramEngine;

use App\Models\ProgramDocument;
use App\Models\ProgramProcess;

class ProgressCalculator
{
    /**
     * 0..100 = avance por índice de etapa + fracción intra-etapa (docs requeridos
     * aprobados + gates + checklist de la etapa actual).
     */
    public function percent(ProgramProcess $process): int
    {
        if ($process->status === ProgramProcess::STATUS_COMPLETED) {
            return 100;
        }
        if ($process->status === ProgramProcess::STATUS_CANCELLED) {
            return 0;
        }

        $definition = ProgramDefinition::for($process->program);
        $workflow = $definition->workflowStages();
        $n = max(1, $workflow->count());
        $stage = $definition->stage($process->current_stage_key);

        if (! $stage || $stage->is_terminal) {
            return $stage?->is_terminal ? 100 : 0;
        }

        $idx = $workflow->search(fn ($s) => $s->key === $stage->key);
        $idx = $idx === false ? 0 : $idx;
        $base = ($idx / $n) * 100;
        $slice = 100 / $n;

        return (int) min(100, round($base + $this->intraStageFraction($process, $stage->key, $definition) * $slice));
    }

    /** Fracción 0..1 de completitud de la etapa actual. */
    public function intraStageFraction(ProgramProcess $process, string $stageKey, ?ProgramDefinition $definition = null): float
    {
        $definition ??= ProgramDefinition::for($process->program);
        $stage = $definition->stage($stageKey);
        $total = 0;
        $done = 0;

        $required = $definition->requirements($stageKey)->where('is_required', true);
        if ($required->isNotEmpty()) {
            $docs = $process->documents()->where('stage_key', $stageKey)->get();
            foreach ($required as $req) {
                $total++;
                $approved = $docs->where('requirement_key', $req->key)->where('status', ProgramDocument::STATUS_APPROVED)->count();
                if ($approved >= max(1, (int) $req->min_count)) {
                    $done++;
                }
            }
        }

        foreach ((array) $stage?->guardValue('require_gates', []) as $gateKey) {
            $total++;
            if ($process->isGateVerified($gateKey)) {
                $done++;
            }
        }

        foreach ($definition->checklist($stageKey) as $item) {
            $total++;
            if ($process->isChecklistDone($item->key)) {
                $done++;
            }
        }

        return $total === 0 ? 0.0 : $done / $total;
    }
}
