<?php

namespace App\Services\ProgramEngine;

use App\Models\ActivityLog;
use App\Models\ProgramDocument;
use App\Models\ProgramProcess;
use App\Models\User;
use App\Services\ProgramEngine\Exceptions\DocumentException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/**
 * Pipeline de documentos del motor. Misma semántica que AuPairDocumentController
 * (aggregate status, multi-file, bloqueo de duplicados) pero configurable.
 */
class DocumentService
{
    public const DISK = 'public';

    public const MAX_FILE_MB = 15;

    public const MAX_VIDEO_MB = 200;

    public const ALLOWED_EXTENSIONS = ['jpeg', 'jpg', 'png', 'pdf', 'mp4', 'mov', 'doc', 'docx'];

    public function __construct(private readonly EnvelopeBuilder $envelope = new EnvelopeBuilder) {}

    /**
     * Describe los requisitos (config + uploads) de un grupo o de todos.
     * Shape compatible con AuPairDocumentEntry (document_type, label, stage, …).
     */
    public function describe(ProgramProcess $process, ?string $groupKey = null): array
    {
        $definition = ProgramDefinition::for($process->program);
        $docs = $process->documents()->orderBy('created_at')->get();
        $items = [];

        foreach ($definition->groups() as $group) {
            if ($groupKey !== null && $group['key'] !== $groupKey) {
                continue;
            }
            [$unlocked, $lockReason] = $this->envelope->groupUnlockState($process, $definition, $group);

            foreach ($definition->requirements($group['stage_key'], $group['key']) as $req) {
                $forReq = $docs->where('requirement_key', $req->key)->values();
                $items[] = [
                    'document_type' => $req->key,
                    'label' => $req->label,
                    'description' => $req->description,
                    'stage' => $group['key'],           // compat mobile: "stage" = tab
                    'stage_key' => $req->stage_key,
                    'group' => $group['key'],
                    'required' => (bool) $req->is_required,
                    'min_count' => $req->min_count > 1 ? (int) $req->min_count : null,
                    'allow_multiple' => $req->min_count > 1 || (bool) $req->allow_multiple,
                    'uploaded_by' => $req->uploaded_by,
                    'section' => $req->section,
                    'unlocked' => $unlocked,
                    'lock_reason' => $lockReason,
                    'count' => $forReq->count(),
                    'approved_count' => $forReq->where('status', ProgramDocument::STATUS_APPROVED)->count(),
                    'status' => $this->aggregateStatus($forReq, (int) $req->min_count),
                    'files' => $forReq->map(fn ($d) => $this->serialize($d))->all(),
                ];
            }
        }

        return $items;
    }

    /**
     * Sube uno o varios archivos para un requisito.
     *
     * @param  UploadedFile[]  $files
     * @return ProgramDocument[]
     */
    public function store(ProgramProcess $process, string $requirementKey, array $files, string $uploaderType, ?User $actor = null): array
    {
        $definition = ProgramDefinition::for($process->program);
        $req = $definition->requirement($requirementKey);

        if (! $req || ! $req->is_active) {
            throw new DocumentException('unknown_requirement', 'Tipo de documento inválido.', 422);
        }
        if ($uploaderType === 'participant') {
            if (! $process->applicantApproved()) {
                throw new DocumentException('pending_approval', 'Tu postulación está pendiente de aprobación. El equipo IE debe aprobarla antes de subir documentos.', 403);
            }
            if ($req->uploaded_by === 'staff') {
                throw new DocumentException('staff_only', 'Este documento lo carga el equipo IE, no se puede subir desde la app.', 403);
            }
            $group = $definition->groups()->firstWhere('key', $req->effective_group);
            [$unlocked, $reason] = $this->envelope->groupUnlockState($process, $definition, $group);
            if (! $unlocked) {
                throw new DocumentException('locked', $this->lockMessage($definition, $reason), 403);
            }
        }

        $isMulti = $req->min_count > 1 || $req->allow_multiple;
        if ($isMulti && $uploaderType === 'participant') {
            // Multi-archivo: completo y aprobado → solo IE puede modificarlo.
            $approvedCount = $process->documents()->where('requirement_key', $req->key)->where('status', ProgramDocument::STATUS_APPROVED)->count();
            if ($approvedCount >= max(1, (int) $req->min_count)) {
                throw new DocumentException('already_approved', 'Este documento ya fue aprobado. Para cambiarlo, contactá al equipo IE.', 403);
            }
        }
        if (! $isMulti) {
            $existing = $process->documents()->where('requirement_key', $req->key)->get();
            if ($existing->contains(fn ($d) => $d->status === ProgramDocument::STATUS_APPROVED) && $uploaderType === 'participant') {
                throw new DocumentException('already_approved', 'Este documento ya fue aprobado. Para cambiarlo, contactá al equipo IE.', 403);
            }
            if ($existing->contains(fn ($d) => $d->status === ProgramDocument::STATUS_PENDING) && $uploaderType === 'participant') {
                throw new DocumentException('pending_exists', 'Ya tenés un archivo en revisión para este documento. Eliminalo antes de subir otro.', 409);
            }
        }

        foreach ($files as $file) {
            $this->assertFileAllowed($file);
        }

        $created = [];
        $slug = $process->program?->slug ?? 'program';
        foreach ($files as $file) {
            $path = $file->store("program-docs/{$slug}/{$process->id}/{$req->key}", self::DISK);
            $created[] = ProgramDocument::create([
                'program_process_id' => $process->id,
                'requirement_key' => $req->key,
                'stage_key' => $req->stage_key,
                'uploaded_by_type' => $uploaderType,
                'uploaded_by' => $actor?->id,
                'file_path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                // Lo que sube el staff se considera validado por el staff.
                'status' => $uploaderType === 'staff' ? ProgramDocument::STATUS_APPROVED : ProgramDocument::STATUS_PENDING,
                'reviewed_by' => $uploaderType === 'staff' ? $actor?->id : null,
                'reviewed_at' => $uploaderType === 'staff' ? now() : null,
            ]);
        }

        $this->log($process, $actor, 'document_upload', "Documento(s) '{$req->label}' subido(s)", [
            'requirement_key' => $req->key, 'files_count' => count($created), 'uploaded_by_type' => $uploaderType,
        ]);

        return $created;
    }

    public function review(ProgramDocument $doc, string $status, ?string $reason, User $reviewer): ProgramDocument
    {
        if (! in_array($status, [ProgramDocument::STATUS_APPROVED, ProgramDocument::STATUS_REJECTED], true)) {
            throw new DocumentException('invalid_status', 'Estado de revisión inválido.');
        }
        if ($status === ProgramDocument::STATUS_REJECTED && ! $reason) {
            throw new DocumentException('reason_required', 'Indicá el motivo del rechazo.');
        }

        $doc->update([
            'status' => $status,
            'rejection_reason' => $status === ProgramDocument::STATUS_REJECTED ? $reason : null,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        $this->log($doc->process, $reviewer, 'document_'.$status, "Documento '{$doc->requirement_key}' {$doc->status_label}", [
            'document_id' => $doc->id, 'reason' => $reason,
        ]);

        return $doc;
    }

    /** Aprueba todos los pendientes de un requisito (o de todo el proceso). */
    public function bulkApprove(ProgramProcess $process, ?string $requirementKey, User $reviewer): int
    {
        $count = $process->documents()
            ->where('status', ProgramDocument::STATUS_PENDING)
            ->when($requirementKey, fn ($q) => $q->where('requirement_key', $requirementKey))
            ->update(['status' => ProgramDocument::STATUS_APPROVED, 'reviewed_by' => $reviewer->id, 'reviewed_at' => now(), 'rejection_reason' => null]);

        if ($count) {
            $this->log($process, $reviewer, 'documents_bulk_approved', "{$count} documento(s) aprobados en bloque", ['requirement_key' => $requirementKey]);
        }

        return $count;
    }

    public function delete(ProgramDocument $doc, ?User $actor, ?string $reason = null, bool $byParticipant = false): void
    {
        if ($byParticipant && ($doc->status !== ProgramDocument::STATUS_PENDING || $doc->uploaded_by_type !== 'participant')) {
            throw new DocumentException('not_deletable', 'Solo se pueden eliminar documentos pendientes que subiste vos.', 403);
        }

        $doc->deletion_reason = $reason;
        $doc->deleted_by = $actor?->id;
        $doc->save();
        $doc->delete();

        $this->log($doc->process, $actor, 'document_deleted', "Documento '{$doc->requirement_key}' eliminado", ['document_id' => $doc->id, 'reason' => $reason]);
    }

    public function aggregateStatus(Collection $docs, int $minCount = 1): string
    {
        if ($docs->isEmpty()) {
            return 'missing';
        }
        // Multi-archivo: 'approved' solo cuando hay al menos min_count aprobados.
        $approved = $docs->where('status', ProgramDocument::STATUS_APPROVED)->count();
        if ($approved >= max(1, $minCount)) {
            return 'approved';
        }
        if ($docs->contains(fn ($d) => $d->status === ProgramDocument::STATUS_PENDING) || $approved > 0) {
            return 'pending';
        }

        return 'rejected';
    }

    public function serialize(ProgramDocument $d): array
    {
        $slug = $d->process?->program?->slug;
        $downloadUrl = ($slug && Route::has('api.programs.documents.download'))
            ? route('api.programs.documents.download', ['engineProgram' => $slug, 'id' => $d->id])
            : null;

        return [
            'id' => $d->id,
            'document_type' => $d->requirement_key,
            'stage' => $d->stage_key,
            'status' => $d->status,
            'status_label' => $d->status_label,
            'original_filename' => $d->original_filename,
            'file_size' => $d->file_size,
            'file_size_formatted' => $d->file_size_formatted,
            'rejection_reason' => $d->rejection_reason,
            'reviewed_at' => optional($d->reviewed_at)->toIso8601String(),
            'uploaded_at' => optional($d->created_at)->toIso8601String(),
            'uploaded_by_type' => $d->uploaded_by_type,
            'download_url' => $downloadUrl,
        ];
    }

    public function assertFileAllowed(UploadedFile $file): void
    {
        $ext = strtolower($file->getClientOriginalExtension());
        if (! in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
            throw new DocumentException('invalid_type', 'Formato no permitido. Usá PDF, imagen, video MP4/MOV o Word.', 422);
        }
        $isVideo = in_array($ext, ['mp4', 'mov'], true);
        $maxMb = $isVideo ? self::MAX_VIDEO_MB : self::MAX_FILE_MB;
        if ($file->getSize() > $maxMb * 1024 * 1024) {
            throw new DocumentException('too_large', "Archivo demasiado grande. Máximo {$maxMb}MB.", 422);
        }
    }

    public function disk()
    {
        return Storage::disk(self::DISK);
    }

    private function lockMessage(ProgramDefinition $definition, ?string $reason): string
    {
        if ($reason === 'pending_approval') {
            return 'Tu postulación está pendiente de aprobación.';
        }
        if ($reason === 'stage_locked') {
            return 'Todavía no llegaste a esta etapa del proceso.';
        }
        if ($reason && str_starts_with($reason, 'gate:')) {
            $gate = $definition->gate(substr($reason, 5));

            return 'Estos documentos se habilitan al verificar el pago: '.($gate?->label ?? 'requerido').'.';
        }

        return 'Documentos bloqueados.';
    }

    private function log(?ProgramProcess $process, ?User $actor, string $action, string $description, array $props = []): void
    {
        if (! $process) {
            return;
        }
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
