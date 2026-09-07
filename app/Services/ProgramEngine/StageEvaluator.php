<?php

namespace App\Services\ProgramEngine;

use App\Models\ProgramDocument;
use App\Models\ProgramDocumentRequirement;
use App\Models\ProgramProcess;
use App\Models\ProgramStage;

/**
 * Evalúa guards declarativos de las etapas. Sin efectos secundarios.
 */
class StageEvaluator
{
    public function __construct(private readonly EnglishTestService $english = new EnglishTestService) {}

    public function canAdvance(ProgramProcess $process): bool
    {
        return $this->blockingReasons($process) === [];
    }

    /** @return string[] Motivos (en español) que impiden avanzar. Vacío = puede avanzar. */
    public function blockingReasons(ProgramProcess $process): array
    {
        $definition = ProgramDefinition::for($process->program);

        if (! $process->isActive()) {
            return ['El proceso no está activo.'];
        }

        $stage = $definition->stage($process->current_stage_key);
        if (! $stage) {
            return ['La etapa actual no existe en la configuración del programa.'];
        }
        if ($stage->is_terminal) {
            return ['El participante ya está en la etapa final.'];
        }
        if (! $definition->nextStage($stage->key)) {
            return ['No hay una etapa siguiente configurada.'];
        }

        return $this->guardReasons($process, $stage, $definition);
    }

    /** @return string[] */
    public function guardReasons(ProgramProcess $process, ProgramStage $stage, ?ProgramDefinition $definition = null): array
    {
        $definition ??= ProgramDefinition::for($process->program);
        $reasons = [];

        if ($stage->guardValue('manual_only', false)) {
            return [];
        }

        if ($stage->guardValue('require_docs_approved', true) && ! $this->docsApproved($process, $stage->key)) {
            $reasons[] = 'Faltan documentos requeridos aprobados en esta etapa.';
        }

        foreach ((array) $stage->guardValue('require_gates', []) as $gateKey) {
            if (! $process->isGateVerified($gateKey)) {
                $label = $definition->gate($gateKey)?->label ?? $gateKey;
                $reasons[] = "Falta verificar el pago: {$label}.";
            }
        }

        $checklistKeys = array_unique(array_merge(
            (array) $stage->guardValue('require_checklist', []),
            $definition->checklist($stage->key)->where('required_for_advance', true)->pluck('key')->all(),
        ));
        foreach ($checklistKeys as $itemKey) {
            if (! $process->isChecklistDone($itemKey)) {
                $label = $definition->checklistItem($itemKey)?->label ?? $itemKey;
                $reasons[] = "Falta completar: {$label}.";
            }
        }

        if ($stage->guardValue('require_english_min_level', false) && ! $this->englishMeetsMin($process)) {
            $reasons[] = "Nivel de inglés mínimo {$definition->minEnglishLevel()} no alcanzado.";
        }

        if ($stage->guardValue('require_job_assignment', false) && ! $this->hasActiveJobAssignment($process)) {
            $reasons[] = 'El participante aún no tiene una oferta laboral asignada.';
        }

        if ($stage->guardValue('require_placement_complete', false) && ! $this->placementComplete($process)) {
            $reasons[] = 'El Job Placement no está completo.';
        }

        return $reasons;
    }

    /**
     * Todos los requisitos requeridos (activos) de la etapa tienen al menos
     * min_count documentos aprobados. Misma semántica que
     * AuPairProcess::admissionDocsApproved(); sin requisitos → true.
     */
    public function docsApproved(ProgramProcess $process, string $stageKey): bool
    {
        $definition = ProgramDefinition::for($process->program);
        $required = $definition->requirements($stageKey)->where('is_required', true);
        if ($required->isEmpty()) {
            return true;
        }

        $docs = $process->documents()->where('stage_key', $stageKey)->get();
        foreach ($required as $req) {
            if ($this->approvedCount($docs, $req) < max(1, (int) $req->min_count)) {
                return false;
            }
        }

        return true;
    }

    public function approvedCount(iterable $docs, ProgramDocumentRequirement $req): int
    {
        return collect($docs)
            ->where('requirement_key', $req->key)
            ->where('status', ProgramDocument::STATUS_APPROVED)
            ->count();
    }

    public function englishMeetsMin(ProgramProcess $process): bool
    {
        return $this->english->meetsMinimum($process);
    }

    /** Hook completado en la fase Pool de Ofertas (P3). */
    public function hasActiveJobAssignment(ProgramProcess $process): bool
    {
        if (! class_exists(\App\Models\JobPoolAssignment::class)) {
            return false;
        }

        return \App\Models\JobPoolAssignment::query()
            ->where('active_process_id', $process->id)
            ->exists();
    }

    /** Hook completado en la fase Placement (P3). */
    public function placementComplete(ProgramProcess $process): bool
    {
        if (! class_exists(\App\Services\ProgramEngine\PlacementService::class)) {
            return false;
        }

        return app(\App\Services\ProgramEngine\PlacementService::class)->isComplete($process);
    }
}
