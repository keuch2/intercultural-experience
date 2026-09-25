<?php

namespace App\Services\ProgramEngine;

use App\Events\ProgramEngine\JobPoolAssignmentReleased;
use App\Events\ProgramEngine\JobPoolOfferExhausted;
use App\Events\ProgramEngine\JobPoolOfferPublished;
use App\Events\ProgramEngine\JobPoolOfferSelected;
use App\Models\ActivityLog;
use App\Models\JobPoolAssignment;
use App\Models\JobPoolEvent;
use App\Models\JobPoolOffer;
use App\Models\Program;
use App\Models\ProgramProcess;
use App\Models\User;
use App\Services\ProgramEngine\Exceptions\JobPoolException;
use Illuminate\Database\QueryException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Pool de Ofertas Laborales. Toda mutación de cupos ocurre en transacción con
 * lockForUpdate (orden fijo: oferta → proceso), decrement condicional y el UNIQUE
 * de active_process_id como tercera barrera contra dobles selecciones.
 */
class JobPoolService
{
    public const DISK = 'public';

    // ── Ofertas (admin) ────────────────────────────────────────────────
    public function publish(Program $program, array $data, ?UploadedFile $pdf, ?User $actor, ?UploadedFile $image = null): JobPoolOffer
    {
        $offer = new JobPoolOffer([
            'program_id' => $program->id,
            'job_title' => $data['job_title'] ?? null,
            'requirements' => $data['requirements'] ?? null,
            'sponsor_id' => $data['sponsor_id'] ?? null,
            'employer_name' => $data['employer_name'],
            'state' => $data['state'],
            'city' => $data['city'],
            'positions_total' => (int) $data['positions_total'],
            'positions_available' => (int) $data['positions_total'],
            'application_deadline' => $data['application_deadline'] ?? null,
            'status' => JobPoolOffer::STATUS_ACTIVE,
            'published_at' => now(),
            'created_by' => $actor?->id,
            'notes' => $data['notes'] ?? null,
        ]);
        if ($pdf) {
            $this->attachPdf($offer, $pdf);
        }
        if ($image) {
            $this->attachImage($offer, $image);
        }
        $offer->save();

        $this->event($offer, null, 'offer_published', $actor, ['positions' => $offer->positions_total]);
        $this->log($program, $actor, 'job_offer_published', "Oferta publicada: {$offer->headline} ({$offer->city}, {$offer->state})", $offer);
        event(new JobPoolOfferPublished($offer, $actor));

        return $offer;
    }

    public function update(JobPoolOffer $offer, array $data, ?UploadedFile $pdf, ?User $actor, ?UploadedFile $image = null): JobPoolOffer
    {
        DB::transaction(function () use ($offer, $data, $pdf, $actor, $image) {
            $offer = JobPoolOffer::whereKey($offer->id)->lockForUpdate()->firstOrFail();
            $taken = $offer->positions_total - $offer->positions_available;
            $newTotal = (int) ($data['positions_total'] ?? $offer->positions_total);
            if ($newTotal < $taken) {
                throw new JobPoolException('positions_below_taken', "No se puede reducir a {$newTotal} posiciones: ya hay {$taken} asignadas.");
            }
            $offer->fill([
                'job_title' => array_key_exists('job_title', $data) ? $data['job_title'] : $offer->job_title,
                'requirements' => array_key_exists('requirements', $data) ? $data['requirements'] : $offer->requirements,
                'sponsor_id' => array_key_exists('sponsor_id', $data) ? ($data['sponsor_id'] ?: null) : $offer->sponsor_id,
                'employer_name' => $data['employer_name'] ?? $offer->employer_name,
                'state' => $data['state'] ?? $offer->state,
                'city' => $data['city'] ?? $offer->city,
                'positions_total' => $newTotal,
                'positions_available' => $newTotal - $taken,
                'application_deadline' => array_key_exists('application_deadline', $data) ? $data['application_deadline'] : $offer->application_deadline,
                'notes' => $data['notes'] ?? $offer->notes,
            ]);
            if ($pdf) {
                $this->attachPdf($offer, $pdf);
            }
            if ($image) {
                $this->attachImage($offer, $image);
            }
            $offer->save();

            $this->event($offer, null, $pdf ? 'offer_pdf_replaced' : 'offer_updated', $actor, ['positions_total' => $newTotal]);
            $this->log($offer->program, $actor, 'job_offer_updated', "Oferta editada: {$offer->headline}", $offer);
        });

        return $offer->refresh();
    }

    public function replacePdf(JobPoolOffer $offer, UploadedFile $pdf, ?User $actor): JobPoolOffer
    {
        $this->attachPdf($offer, $pdf);
        $offer->save();
        $this->event($offer, null, 'offer_pdf_replaced', $actor);

        return $offer;
    }

    public function pause(JobPoolOffer $offer, ?User $actor): JobPoolOffer
    {
        return $this->setStatus($offer, JobPoolOffer::STATUS_PAUSED, 'offer_paused', $actor);
    }

    public function reactivate(JobPoolOffer $offer, ?User $actor): JobPoolOffer
    {
        $offer->closed_at = null;
        $offer->closed_by = null;

        return $this->setStatus($offer, JobPoolOffer::STATUS_ACTIVE, 'offer_reactivated', $actor);
    }

    public function close(JobPoolOffer $offer, ?User $actor): JobPoolOffer
    {
        $offer->closed_at = now();
        $offer->closed_by = $actor?->id;

        return $this->setStatus($offer, JobPoolOffer::STATUS_CLOSED, 'offer_closed', $actor);
    }

    public function delete(JobPoolOffer $offer, ?User $actor): void
    {
        if ($offer->activeAssignments()->exists()) {
            throw new JobPoolException('has_active_assignments', 'No se puede eliminar una oferta con participantes asignados. Liberá las asignaciones primero.');
        }
        $this->event($offer, null, 'offer_deleted', $actor);
        $this->log($offer->program, $actor, 'job_offer_deleted', "Oferta eliminada: {$offer->employer_name}", $offer);
        $offer->delete();
    }

    // ── Selección / asignaciones ───────────────────────────────────────
    /** Ofertas visibles para un participante habilitado. */
    public function availableFor(ProgramProcess $process)
    {
        return JobPoolOffer::forProgram($process->program_id)->selectable()->orderBy('state')->orderBy('city')->orderBy('employer_name')->get();
    }

    public function canAccess(ProgramProcess $process): bool
    {
        return $process->applicantApproved() && $process->isActive() && $process->hasModuleAccess(ModuleCatalog::JOB_POOL);
    }

    /**
     * Selección atómica de una oferta. $staff = null cuando la elige el participante.
     */
    public function select(JobPoolOffer|int $offer, ProgramProcess $process, ?User $staff = null, bool $force = false): JobPoolAssignment
    {
        $offerId = $offer instanceof JobPoolOffer ? $offer->id : $offer;

        try {
            $assignment = DB::transaction(function () use ($offerId, $process, $staff, $force) {
                $offer = JobPoolOffer::whereKey($offerId)->lockForUpdate()->firstOrFail();
                $proc = ProgramProcess::whereKey($process->id)->lockForUpdate()->firstOrFail();

                if ($offer->program_id !== $proc->program_id) {
                    throw new JobPoolException('wrong_program', 'La oferta no pertenece a este programa.', 422);
                }
                if (! $staff && ! $force && ! $this->canAccess($proc)) {
                    throw new JobPoolException('not_enabled', 'Tu acceso al Pool de Ofertas aún no fue habilitado por IE.', 403);
                }
                if ($offer->status !== JobPoolOffer::STATUS_ACTIVE) {
                    throw new JobPoolException('offer_unavailable', 'Esta oferta ya no está disponible.', 409);
                }
                if (! $staff && $offer->isDeadlinePassed()) {
                    throw new JobPoolException('deadline_passed', 'La fecha límite para postular a esta oferta ya pasó.', 409);
                }
                if (JobPoolAssignment::where('active_process_id', $proc->id)->exists()) {
                    throw new JobPoolException('already_assigned', 'Ya tenés una oferta asignada. Para cambiarla, contactá al equipo IE.', 409);
                }

                $affected = JobPoolOffer::whereKey($offer->id)->where('positions_available', '>', 0)->decrement('positions_available');
                if ($affected === 0) {
                    throw new JobPoolException('no_positions', 'Esta oferta ya no tiene posiciones disponibles.', 409);
                }

                $assignment = JobPoolAssignment::create([
                    'job_pool_offer_id' => $offer->id,
                    'program_process_id' => $proc->id,
                    'active_process_id' => $proc->id,
                    'status' => JobPoolAssignment::STATUS_ACTIVE,
                    'selected_at' => now(),
                    'assigned_by' => $staff?->id,
                ]);

                $offer->refresh();
                $this->event($offer, $proc, 'selected', $staff ?? $proc->user, ['assignment_id' => $assignment->id, 'positions_available' => $offer->positions_available], $staff ? 'staff' : 'participant');
                $this->log($offer->program, $staff, 'job_offer_selected', "Oferta seleccionada: {$offer->headline} ({$offer->city}, {$offer->state})", $proc->user);

                return $assignment->setRelation('offer', $offer);
            });
        } catch (QueryException $e) {
            // Violación del UNIQUE active_process_id: dos selecciones concurrentes del mismo participante.
            if (str_contains($e->getMessage(), 'active_process_id')) {
                throw new JobPoolException('already_assigned', 'Ya tenés una oferta asignada.', 409, $e);
            }
            throw $e;
        }

        $offer = $assignment->offer;
        event(new JobPoolOfferSelected($assignment, $staff));
        if ($offer->positions_available === 0) {
            $this->event($offer, null, 'positions_exhausted', null, [], 'system');
            event(new JobPoolOfferExhausted($offer));
        }

        return $assignment;
    }

    /** Libera una asignación y devuelve el cupo (capado al total). */
    public function release(JobPoolAssignment $assignment, ?User $actor, ?string $reason = null, string $newStatus = JobPoolAssignment::STATUS_RELEASED): JobPoolAssignment
    {
        DB::transaction(function () use ($assignment, $actor, $reason, $newStatus) {
            $offer = JobPoolOffer::whereKey($assignment->job_pool_offer_id)->lockForUpdate()->firstOrFail();
            $assignment = JobPoolAssignment::whereKey($assignment->id)->lockForUpdate()->firstOrFail();
            if (! $assignment->isActive()) {
                throw new JobPoolException('not_active', 'La asignación ya no está activa.', 409);
            }

            $assignment->update([
                'status' => $newStatus,
                'active_process_id' => null,
                'released_at' => now(),
                'released_by' => $actor?->id,
                'release_reason' => $reason,
            ]);
            if ($offer->positions_available < $offer->positions_total) {
                $offer->increment('positions_available');
            }

            $this->event($offer, $assignment->process, $newStatus === JobPoolAssignment::STATUS_REASSIGNED ? 'reassigned' : 'released', $actor, ['assignment_id' => $assignment->id, 'reason' => $reason]);
            $this->log($offer->program, $actor, 'job_assignment_released', "Asignación liberada: {$offer->employer_name}".($reason ? " — {$reason}" : ''), $assignment->process?->user);
        });

        $assignment->refresh();
        event(new JobPoolAssignmentReleased($assignment, $actor, $reason));

        return $assignment;
    }

    /** Reasigna la oferta de un participante a otro (libera + selecciona por staff). */
    public function reassign(JobPoolAssignment $assignment, ProgramProcess $to, ?User $actor, ?string $reason = null): JobPoolAssignment
    {
        return DB::transaction(function () use ($assignment, $to, $actor, $reason) {
            $this->release($assignment, $actor, $reason ?? 'Reasignación', JobPoolAssignment::STATUS_REASSIGNED);

            return $this->select($assignment->job_pool_offer_id, $to, $actor ?? $to->user, force: true);
        });
    }

    /** Procesos del programa habilitados para el pool y sin asignación activa (para el picker de reasignación). */
    public function eligibleProcesses(Program $program)
    {
        return ProgramProcess::forProgram($program)->active()
            ->whereDoesntHave('activeJobAssignment')
            ->with('user')
            ->get()
            ->filter(fn ($p) => $p->hasModuleAccess(ModuleCatalog::JOB_POOL))
            ->values();
    }

    // ── Helpers ────────────────────────────────────────────────────────
    private function setStatus(JobPoolOffer $offer, string $status, string $eventType, ?User $actor): JobPoolOffer
    {
        $offer->status = $status;
        $offer->save();
        $this->event($offer, null, $eventType, $actor);
        $this->log($offer->program, $actor, 'job_offer_'.$status, "Oferta {$offer->status_label}: {$offer->employer_name}", $offer);

        return $offer;
    }

    private function attachImage(JobPoolOffer $offer, UploadedFile $image): void
    {
        if ($offer->image_path && Storage::disk(self::DISK)->exists($offer->image_path)) {
            Storage::disk(self::DISK)->delete($offer->image_path);
        }
        $offer->image_path = $image->store('job-pool/'.($offer->program?->slug ?? $offer->program_id).'/flyers', self::DISK);
        $offer->image_original_filename = $image->getClientOriginalName();
    }

    private function attachPdf(JobPoolOffer $offer, UploadedFile $pdf): void
    {
        if ($offer->pdf_path && Storage::disk(self::DISK)->exists($offer->pdf_path)) {
            Storage::disk(self::DISK)->delete($offer->pdf_path);
        }
        $offer->pdf_path = $pdf->store('job-pool/'.($offer->program?->slug ?? $offer->program_id), self::DISK);
        $offer->pdf_original_filename = $pdf->getClientOriginalName();
    }

    private function event(JobPoolOffer $offer, ?ProgramProcess $process, string $type, ?User $actor, array $payload = [], ?string $actorType = null): void
    {
        JobPoolEvent::create([
            'job_pool_offer_id' => $offer->id,
            'program_process_id' => $process?->id,
            'event_type' => $type,
            'actor_id' => $actor?->id,
            'actor_type' => $actorType ?? ($actor ? ($actor->role === 'user' ? 'participant' : 'staff') : 'system'),
            'payload' => $payload ?: null,
            'created_at' => now(),
        ]);
    }

    private function log(?Program $program, ?User $actor, string $action, string $description, $subject = null): void
    {
        $builder = ActivityLog::log($program?->slug ?? 'program_engine')->withAction($action);
        if ($subject) {
            $builder->performedOn($subject);
        }
        if ($actor) {
            $builder->causedBy($actor);
        }
        $builder->log($description);
    }
}
