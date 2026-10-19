<?php

namespace App\Services\ProgramEngine;

use App\Models\JobPlacement;
use App\Models\JobPoolAssignment;
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

    /** Nueva oferta asignada (primera vez o cambio): fecha de elección, entrevistas a cero y sponsor de la oferta. */
    public function attachAssignment(ProgramProcess $process, JobPoolAssignment $assignment): JobPlacement
    {
        $placement = JobPlacement::firstOrNew(['program_process_id' => $process->id]);
        $isChange = $placement->exists && $placement->job_pool_assignment_id && $placement->job_pool_assignment_id !== $assignment->id;
        $offerSponsor = $assignment->offer?->sponsor_id;

        $placement->fill([
            'job_pool_assignment_id' => $assignment->id,
            'acceptance_date' => $assignment->selected_at?->toDateString() ?? today()->toDateString(),
            'sponsor_interview_date' => null, 'sponsor_interview_result' => JobPlacement::INTERVIEW_PENDING,
            'employer_interview_date' => null, 'employer_interview_result' => JobPlacement::INTERVIEW_PENDING,
            'job_accepted_at' => null,
            // Cambio de oferta: manda el sponsor de la nueva oferta; primera vez: solo si IE no cargó uno.
            'sponsor_id' => $isChange ? ($offerSponsor ?? $placement->sponsor_id) : ($placement->sponsor_id ?? $offerSponsor),
        ]);
        if (in_array($placement->status, ['completed', 'cancelled'], true) || ! $placement->exists) {
            $placement->status = 'pending';
        }
        $placement->status = $this->deriveStatus($process, $placement, ignoreManual: true);
        $placement->save();

        return $placement;
    }

    /** La asignación se liberó: el placement queda sin oferta hasta la próxima selección. */
    public function detachAssignment(ProgramProcess $process, JobPoolAssignment $assignment): void
    {
        $placement = JobPlacement::where('program_process_id', $process->id)->first();
        if (! $placement || $placement->job_pool_assignment_id !== $assignment->id) {
            return;
        }
        $placement->job_pool_assignment_id = null;
        $placement->job_accepted_at = null;
        $placement->status = $this->deriveStatus($process, $placement, ignoreManual: true);
        $placement->save();
    }

    public function update(ProgramProcess $process, array $data, ?User $actor = null): JobPlacement
    {
        return $this->updateWithOutcome($process, $data, $actor)['placement'];
    }

    /** @return array{placement: JobPlacement, released: bool}  released = una entrevista no aprobada liberó la oferta */
    public function updateWithOutcome(ProgramProcess $process, array $data, ?User $actor = null): array
    {
        $placement = $this->ensure($process);
        $placement->fill($data);
        // Aceptación laboral: con las entrevistas superadas se registra la fecha (IE puede corregirla);
        // si dejan de estar superadas se borra, salvo que IE la cargue a mano en este mismo guardado.
        if ($placement->interviewsPassed()) {
            $placement->job_accepted_at ??= today();
        } elseif (! $placement->isDirty('job_accepted_at')) {
            $placement->job_accepted_at = null;
        }
        if (empty($data['status'])) {
            $placement->status = $this->deriveStatus($process, $placement, ignoreManual: true);
        }
        $placement->save();

        // Entrevista no aprobada: se libera la oferta y el participante vuelve al pool.
        $released = false;
        if ($placement->interviewFailed()) {
            $assignment = $process->activeJobAssignment()->first();
            if ($assignment) {
                $who = $placement->sponsor_interview_result === JobPlacement::INTERVIEW_NOT_APPROVED ? 'sponsor' : 'empleador';
                app(JobPoolService::class)->release($assignment, $actor, "Entrevista con el {$who} no aprobada", JobPoolAssignment::STATUS_RELEASED, reenablePool: true);
                $released = true;
                $placement->refresh();
            }
        }

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

        return ['placement' => $placement, 'released' => $released];
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

    /**
     * Pasos: Oferta elegida (entrevistas) → Aceptación laboral → Documentación del Sponsor
     * completa → DS-2019 emitido (SEVIS + DS-2019 cargados) = Placement completo.
     */
    public function deriveStatus(ProgramProcess $process, JobPlacement $placement, bool $ignoreManual = false): string
    {
        if (! $ignoreManual && in_array($placement->status, ['completed', 'cancelled'], true)) {
            return $placement->status;
        }
        if ($placement->status === 'cancelled') {
            return 'cancelled';
        }
        if (! $placement->job_pool_assignment_id) {
            return 'pending';
        }
        $docs = $this->documentsComplete($process);
        if ($docs && $placement->sevis_number && $placement->ds2019_number) {
            return 'completed';
        }
        if (! $placement->job_accepted_at) {
            return 'in_progress';
        }

        return $docs ? 'documents_complete' : 'job_accepted';
    }

    /** Pasos para la app y el panel (clave, etiqueta, hecho). */
    public function steps(ProgramProcess $process, ?JobPlacement $placement): array
    {
        $hasOffer = (bool) $placement?->job_pool_assignment_id;
        $accepted = $hasOffer && (bool) $placement?->job_accepted_at;
        $docs = $accepted && $this->documentsComplete($process);

        return [
            ['key' => 'offer_selected', 'label' => 'Oferta elegida', 'done' => $hasOffer],
            ['key' => 'job_accepted', 'label' => 'Entrevistas aprobadas · aceptación laboral', 'done' => $accepted],
            ['key' => 'documents_complete', 'label' => 'Documentación del Sponsor completa', 'done' => $docs],
            ['key' => 'completed', 'label' => 'DS-2019 emitido · Placement completo', 'done' => $docs && $placement?->sevis_number && $placement?->ds2019_number],
        ];
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
                // acceptance_date = fecha de elección de la oferta (se mantiene la clave para apps viejas)
                'acceptance_date' => $placement->acceptance_date?->toDateString(),
                'offer_selected_date' => $placement->acceptance_date?->toDateString(),
                'job_accepted_at' => $placement->job_accepted_at?->toDateString(),
                'interviews' => [
                    'sponsor' => ['date' => $placement->sponsor_interview_date?->toDateString(), 'result' => $placement->sponsor_interview_result, 'result_label' => JobPlacement::INTERVIEW_RESULTS[$placement->sponsor_interview_result] ?? null],
                    'employer' => ['date' => $placement->employer_interview_date?->toDateString(), 'result' => $placement->employer_interview_result, 'result_label' => JobPlacement::INTERVIEW_RESULTS[$placement->employer_interview_result] ?? null],
                ],
                'steps' => $this->steps($process, $placement),
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
