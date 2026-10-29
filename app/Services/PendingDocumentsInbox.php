<?php

namespace App\Services;

use App\Models\AuPairDocument;
use App\Models\ProgramDocument;
use App\Models\ProgramDocumentRequirement;
use Illuminate\Support\Collection;

/**
 * Bandeja transversal de documentos pendientes de aprobación (todos los
 * programas del motor + Au Pair): alimenta el bloque del dashboard.
 */
class PendingDocumentsInbox
{
    public function pendingCount(): int
    {
        return $this->engineQuery()->count() + $this->auPairQuery()->count();
    }

    /**
     * Documentos pendientes, los más antiguos primero (los que llevan más tiempo esperando).
     *
     * @return Collection<int, array{label:string,participant:?string,program:?string,from_participant:bool,created_at:\Carbon\Carbon,url:string}>
     */
    public function pending(int $limit = 10): Collection
    {
        $engineDocs = $this->engineQuery()
            ->with('process.user:id,name', 'process.program:id,name,slug')
            ->oldest()->limit($limit)->get();

        $labels = ProgramDocumentRequirement::query()
            ->whereIn('program_id', $engineDocs->pluck('process.program_id')->filter()->unique())
            ->get(['program_id', 'key', 'label'])
            ->mapWithKeys(fn ($r) => ["{$r->program_id}.{$r->key}" => $r->label]);

        $engine = $engineDocs->map(fn (ProgramDocument $doc) => $this->row(
            $labels["{$doc->process->program_id}.{$doc->requirement_key}"] ?? str($doc->requirement_key)->headline()->toString(),
            $doc,
            $doc->process->user?->name,
            $doc->process->program?->name,
            route('admin.program.participants.show', ['program' => $doc->process->program->slug, 'process' => $doc->process->id, 'tab' => $doc->stage_key]),
        ));

        $auPairTypes = AuPairDocument::documentTypes();
        $auPair = $this->auPairQuery()
            ->with('process.user:id,name')
            ->oldest()->limit($limit)->get()
            ->map(fn (AuPairDocument $doc) => $this->row(
                $auPairTypes[$doc->document_type]['label'] ?? str($doc->document_type)->headline()->toString(),
                $doc,
                $doc->process->user?->name,
                'Au Pair',
                route('admin.aupair.profiles.show', ['id' => $doc->process->user_id, 'tab' => $this->auPairTab((string) $doc->stage)]),
            ));

        return $engine->concat($auPair)->sortBy('created_at')->take($limit)->values();
    }

    private function engineQuery()
    {
        return ProgramDocument::query()
            ->where('status', ProgramDocument::STATUS_PENDING)
            ->whereHas('process.program');
    }

    private function auPairQuery()
    {
        return AuPairDocument::query()
            ->where('status', 'pending')
            ->whereNotNull('file_path')
            ->whereHas('process');
    }

    /** Misma correspondencia etapa → tab que AuPairProfileController::stageToTab(). */
    private function auPairTab(string $stage): string
    {
        return match ($stage) {
            'application_payment1', 'application_payment2' => 'application',
            'visa' => 'match_visa',
            default => 'admission',
        };
    }

    private function row(string $label, ProgramDocument|AuPairDocument $doc, ?string $participant, ?string $program, string $url): array
    {
        return [
            'label' => $label,
            'participant' => $participant,
            'program' => $program,
            'from_participant' => $doc->uploaded_by_type === 'participant',
            'created_at' => $doc->created_at,
            'url' => $url,
        ];
    }
}
