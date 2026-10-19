<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Estado de revisión de un registro de Support (motor y Au Pair). Solo los
 * reportes del participante (source=participant) quedan pendientes de revisión.
 */
trait HasSupportReviewState
{
    public const REVIEW_PENDING = 'pending';

    public const REVIEW_READ = 'read';

    public const REVIEW_RESOLVED = 'resolved';

    public function initializeHasSupportReviewState(): void
    {
        $this->mergeFillable(['read_at', 'read_by', 'resolved_by']);
        $this->mergeCasts(['read_at' => 'datetime']);
    }

    /** Reportes del participante que nadie leyó ni solucionó todavía. */
    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('source', 'participant')->whereNull('read_at')->whereNull('resolved_at');
    }

    public function reviewStatus(): string
    {
        return match (true) {
            $this->resolved_at !== null => self::REVIEW_RESOLVED,
            $this->read_at !== null => self::REVIEW_READ,
            default => self::REVIEW_PENDING,
        };
    }

    public function isPendingReview(): bool
    {
        return $this->source === 'participant' && $this->reviewStatus() === self::REVIEW_PENDING;
    }

    public function markRead(User $by): void
    {
        $this->update(['read_at' => $this->read_at ?? now(), 'read_by' => $this->read_by ?? $by->id]);
    }

    public function markResolved(User $by, string $resolution): void
    {
        $this->update([
            'resolution' => $resolution,
            'resolved_at' => now(),
            'resolved_by' => $by->id,
            'read_at' => $this->read_at ?? now(),
            'read_by' => $this->read_by ?? $by->id,
        ]);
    }

    public function readBy()
    {
        return $this->belongsTo(User::class, 'read_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
