<?php

namespace App\Services\ProgramEngine;

use App\Models\JobPlacement;
use App\Models\ProgramDocument;
use App\Models\ProgramProcess;
use App\Models\User;

/**
 * Job Placement: oferta asignada + docs por Sponsor (requisitos de la etapa
 * `placement`, uploaded_by=staff) + datos SEVIS / DS-2019.
 */
class PlacementService
{
    public const STAGE_KEY = 'placement';

    /** Garantiza el placement del proceso, vinculado a la asignación activa si existe. */
    public function ensure(ProgramProcess $process): JobPlacement
    {
        $assignment = $process->activeJobAssignment()->first();
        $placement = JobPlacement::firstOrCreate(['program_process_id' => $process->id], [
            'job_pool_assignment_id' => $assignment?->id,
            'status' => 'pending',
            'acceptance_date' => $assignment?->selected_at?->toDateString(),
        ]);
        if ($assignment && $placement->job_pool_assignment_id !== $assignment->id) {
            $placement->update(['job_pool_assignment_id' => $assignment->id, 'acceptance_date' => $placement->acceptance_date ?? $assignment->selected_at?->toDateString()]);
        }
        // Sin sponsor cargado por el staff: hereda el de la oferta seleccionada.
        if ($assignment && ! $placement->sponsor_id && $assignment->offer?->sponsor_id) {
            $placement->update(['sponsor_id' => $assignment->offer->sponsor_id]);
        }

        return $placement;
    }

    public function update(ProgramProcess $process, array $data, ?User $actor = null): JobPlacement
    {
        $placement = $this->ensure($process);
        $placement->fill($data);
        if (($data['terms_accepted'] ?? null) !== null) {
            $placement->terms_accepted_at = $data['terms_accepted'] ? ($placement->terms_accepted_at ?? now()) : null;
        }
        if (empty($data['status'])) {
            $placement->status = $this->deriveStatus($process, $placement);
        }
        $placement->save();

        // La fecha de inicio/fin del programa vive en el proceso (fuente para API y app);
        // la pestaña Job Placement es solo otro lugar desde donde cargarla.
        $sync = [];
        foreach (['program_start_date', 'program_end_date'] as $k) {
            if (array_key_exists($k, $data)) {
                $sync[$k] = $data[$k] ?: null;
            }
        }
        if ($sync) {
            $process->update($sync);
        }

        return $placement;
    }

    /** Documentos del Sponsor aprobados + SEVIS + DS-2019 cargados. */
    public function isComplete(ProgramProcess $process): bool
    {
        $placement = $process->placement ?? JobPlacement::where('program_process_id', $process->id)->first();
        if (! $placement || ! $placement->sevis_number || ! $placement->ds2019_number) {
            return false;
        }

        return $this->documentsComplete($process);
    }

    public function documentsComplete(ProgramProcess $process): bool
    {
        $definition = ProgramDefinition::for($process->program);
        $required = $definition->requirements(self::STAGE_KEY)->where('is_required', true);
        if ($required->isEmpty()) {
            return true;
        }
        $docs = $process->documents()->where('stage_key', self::STAGE_KEY)->where('status', ProgramDocument::STATUS_APPROVED)->get();

        return $required->every(fn ($req) => $docs->where('requirement_key', $req->key)->count() >= max(1, (int) $req->min_count));
    }

    public function deriveStatus(ProgramProcess $process, JobPlacement $placement): string
    {
        if (in_array($placement->status, ['completed', 'cancelled'], true)) {
            return $placement->status;
        }
        if ($placement->ds_received_at) {
            return 'completed';
        }
        if ($placement->ds_tracking_number) {
            return 'ds_shipped';
        }
        if ($placement->sponsor_id && $this->documentsComplete($process)) {
            return 'documents_complete';
        }
        if ($placement->sponsor_id || $placement->job_pool_assignment_id) {
            return 'in_progress';
        }

        return 'pending';
    }

    /** Payload para la app (solo lectura). */
    public function toArray(ProgramProcess $process): array
    {
        $placement = $process->placement;
        $assignment = $process->activeJobAssignment()->with('offer')->first();
        $offer = $assignment?->offer;

        return [
            'has_assignment' => $assignment !== null,
            'offer' => $offer ? [
                'id' => $offer->id, 'job_title' => $offer->job_title, 'requirements' => $offer->requirements, 'image_url' => $offer->image_url, 'sponsor_name' => $offer->sponsor?->name, 'employer_name' => $offer->employer_name, 'state' => $offer->state, 'city' => $offer->city,
                'pdf_url' => $offer->hasPdf() ? route('api.programs.job-pool.pdf', ['engineProgram' => $process->program->slug, 'id' => $offer->id]) : null,
                'selected_at' => $assignment->selected_at?->toIso8601String(),
            ] : null,
            'placement' => $placement ? [
                'status' => $placement->status,
                'status_label' => $placement->status_label,
                'sponsor' => $placement->sponsor?->name,
                'acceptance_date' => $placement->acceptance_date?->toDateString(),
                'program_start_date' => $placement->program_start_date?->toDateString(),
                'program_end_date' => $placement->program_end_date?->toDateString(),
                'terms_accepted_at' => $placement->terms_accepted_at?->toIso8601String(),
                'sevis_number' => $placement->sevis_number,
                'ds2019_number' => $placement->ds2019_number,
                'ds_tracking_carrier' => $placement->ds_tracking_carrier,
                'ds_tracking_number' => $placement->ds_tracking_number,
                'ds_received_at' => $placement->ds_received_at?->toDateString(),
                'documents_complete' => $this->documentsComplete($process),
                'is_complete' => $this->isComplete($process),
            ] : null,
        ];
    }
}
