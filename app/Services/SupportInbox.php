<?php

namespace App\Services;

use App\Models\AuPairSupportLog;
use App\Models\ProgramSupportLog;
use App\Models\User;
use App\Services\ProgramEngine\Notifier;
use Illuminate\Support\Collection;

/**
 * Bandeja transversal de reportes del participante (todos los programas del
 * motor + Au Pair): alimenta el dashboard y las acciones Leído / Solucionado.
 */
class SupportInbox
{
    public const KIND_PROGRAM = 'program';

    public const KIND_AU_PAIR = 'au-pair';

    public function __construct(private readonly Notifier $notifier) {}

    public function pendingCount(): int
    {
        return ProgramSupportLog::pendingReview()->count() + AuPairSupportLog::pendingReview()->count();
    }

    /**
     * Reportes pendientes, urgentes primero y luego los más recientes.
     *
     * @return Collection<int, array{kind:string,id:int,title:string,type_label:string,urgent:bool,created_at:\Carbon\Carbon,participant:?string,program:?string,url:string}>
     */
    public function pending(int $limit = 20): Collection
    {
        $engine = ProgramSupportLog::pendingReview()->with('process.user:id,name', 'process.program:id,name,slug')->latest()->limit($limit)->get()
            ->map(fn (ProgramSupportLog $log) => $this->row(self::KIND_PROGRAM, $log, $log->process?->user?->name, $log->process?->program?->name,
                $log->process ? route('admin.program.participants.show', ['program' => $log->process->program->slug, 'process' => $log->process->id, 'tab' => 'support']) : '#'));

        $auPair = AuPairSupportLog::pendingReview()->with('process.user:id,name')->latest()->limit($limit)->get()
            ->map(fn (AuPairSupportLog $log) => $this->row(self::KIND_AU_PAIR, $log, $log->process?->user?->name, 'Au Pair',
                $log->process ? route('admin.aupair.profiles.show', ['id' => $log->process->user_id, 'tab' => 'support']) : '#'));

        return $engine->concat($auPair)
            ->sortBy([['urgent', 'desc'], ['created_at', 'desc']])
            ->take($limit)
            ->values();
    }

    public function find(string $kind, int $id): ProgramSupportLog|AuPairSupportLog
    {
        return $kind === self::KIND_AU_PAIR ? AuPairSupportLog::findOrFail($id) : ProgramSupportLog::findOrFail($id);
    }

    public function markRead(ProgramSupportLog|AuPairSupportLog $log, User $by): void
    {
        $log->markRead($by);
    }

    public function resolve(ProgramSupportLog|AuPairSupportLog $log, User $by, string $resolution): void
    {
        $log->markResolved($by, $resolution);
        $userId = $log->process?->user_id;
        if ($userId) {
            $this->notifier->toUser($userId, 'Tu reporte fue solucionado', "IE marcó como solucionado tu reporte \"{$log->title}\": {$resolution}", ParticipantSupportReports::CATEGORY);
        }
    }

    private function row(string $kind, ProgramSupportLog|AuPairSupportLog $log, ?string $participant, ?string $program, string $url): array
    {
        return [
            'kind' => $kind,
            'id' => $log->id,
            'title' => $log->title,
            'type_label' => ParticipantSupportReports::LABELS[$log->log_type] ?? str($log->log_type)->headline()->toString(),
            'urgent' => in_array($log->severity, ['high', 'critical'], true),
            'created_at' => $log->created_at,
            'participant' => $participant,
            'program' => $program,
            'url' => $url,
        ];
    }
}
