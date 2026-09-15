<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobPoolOffer extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'program_id', 'job_title', 'employer_name', 'state', 'city', 'positions_total', 'positions_available', 'application_deadline',
        'pdf_path', 'pdf_original_filename', 'status', 'published_at', 'closed_at', 'closed_by', 'created_by', 'notes',
    ];

    protected $casts = [
        'positions_total' => 'integer',
        'positions_available' => 'integer',
        'application_deadline' => 'date',
        'published_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(JobPoolAssignment::class);
    }

    public function activeAssignments(): HasMany
    {
        return $this->assignments()->where('status', JobPoolAssignment::STATUS_ACTIVE);
    }

    public function events(): HasMany
    {
        return $this->hasMany(JobPoolEvent::class)->orderByDesc('created_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasPdf(): bool
    {
        return ! empty($this->pdf_path);
    }

    /** Venció la fecha límite para postular (si la oferta tiene una). */
    public function isDeadlinePassed(): bool
    {
        return $this->application_deadline !== null && $this->application_deadline->lt(today());
    }

    public function isSelectable(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->positions_available > 0 && ! $this->isDeadlinePassed();
    }

    /** Nombre para mostrar: puesto laboral, o el empleador si la oferta es anterior a este campo. */
    public function getDisplayNameAttribute(): string
    {
        return $this->job_title ?: $this->employer_name;
    }

    /** "Puesto · Empleador" para notificaciones y logs. */
    public function getHeadlineAttribute(): string
    {
        return $this->job_title ? "{$this->job_title} · {$this->employer_name}" : $this->employer_name;
    }

    public function getPositionsTakenAttribute(): int
    {
        return max(0, $this->positions_total - $this->positions_available);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => $this->positions_available <= 0 ? 'Sin cupo' : ($this->isDeadlinePassed() ? 'Vencida' : 'Activa'),
            self::STATUS_PAUSED => 'Pausada',
            self::STATUS_CLOSED => 'Cerrada',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => $this->positions_available <= 0 ? 'secondary' : ($this->isDeadlinePassed() ? 'danger' : 'success'),
            self::STATUS_PAUSED => 'warning',
            self::STATUS_CLOSED => 'dark',
            default => 'secondary',
        };
    }

    /** Ofertas visibles para los participantes: activas, con cupo y dentro de la fecha límite. */
    public function scopeSelectable($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where('positions_available', '>', 0)
            ->where(fn ($q) => $q->whereNull('application_deadline')->orWhereDate('application_deadline', '>=', today()));
    }

    public function scopeForProgram($query, Program|int $program)
    {
        return $query->where('program_id', $program instanceof Program ? $program->id : $program);
    }
}
