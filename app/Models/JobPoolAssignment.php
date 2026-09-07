<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobPoolAssignment extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_RELEASED = 'released';

    public const STATUS_REASSIGNED = 'reassigned';

    protected $fillable = [
        'job_pool_offer_id', 'program_process_id', 'active_process_id', 'status', 'selected_at',
        'assigned_by', 'released_at', 'released_by', 'release_reason',
    ];

    protected $casts = [
        'selected_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function offer(): BelongsTo
    {
        return $this->belongsTo(JobPoolOffer::class, 'job_pool_offer_id');
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(ProgramProcess::class, 'program_process_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'Asignada',
            self::STATUS_RELEASED => 'Liberada',
            self::STATUS_REASSIGNED => 'Reasignada',
            default => $this->status,
        };
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
