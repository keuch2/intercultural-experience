<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProgramAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'program_id',
        'assigned_by',
        'status',
        'assignment_notes',
        'admin_notes',
        'assigned_at',
        'applied_at',
        'reviewed_at',
        'accepted_at',
        'completed_at',
        'program_data',
        'can_apply',
        'is_priority',
        'application_deadline',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'applied_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
        'application_deadline' => 'date',
        'program_data' => 'array',
        'can_apply' => 'boolean',
        'is_priority' => 'boolean',
    ];

    // Estados disponibles
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_APPLIED = 'applied';
    public const STATUS_UNDER_REVIEW = 'under_review';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_ASSIGNED => 'Asignado',
        self::STATUS_APPLIED => 'Aplicado',
        self::STATUS_UNDER_REVIEW => 'En Revisión',
        self::STATUS_ACCEPTED => 'Aceptado',
        self::STATUS_REJECTED => 'Rechazado',
        self::STATUS_COMPLETED => 'Completado',
        self::STATUS_CANCELLED => 'Cancelado',
    ];

    /**
     * Relaciones
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'user_id', 'user_id')
            ->where('program_id', $this->program_id);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATUS_ASSIGNED,
            self::STATUS_APPLIED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_ACCEPTED
        ]);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByProgram($query, $programId)
    {
        return $query->where('program_id', $programId);
    }

    public function scopePriority($query)
    {
        return $query->where('is_priority', true);
    }

    public function scopeCanApply($query)
    {
        return $query->where('can_apply', true)
            ->where('status', self::STATUS_ASSIGNED);
    }

    public function scopeDeadlineApproaching($query, $days = 7)
    {
        return $query->where('application_deadline', '<=', Carbon::now()->addDays($days))
            ->where('status', self::STATUS_ASSIGNED);
    }

    /**
     * Accessors
     */
    public function getStatusNameAttribute()
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getIsOverdueAttribute()
    {
        return $this->application_deadline && 
               $this->application_deadline->isPast() && 
               $this->status === self::STATUS_ASSIGNED;
    }

    public function getDaysUntilDeadlineAttribute()
    {
        if (!$this->application_deadline) {
            return null;
        }
        return Carbon::now()->diffInDays($this->application_deadline, false);
    }

    public function getCanUserApplyAttribute()
    {
        return $this->can_apply && 
               $this->status === self::STATUS_ASSIGNED && 
               (!$this->application_deadline || !$this->application_deadline->isPast());
    }

    public function getProgressPercentageAttribute()
    {
        $statusProgress = [
            self::STATUS_ASSIGNED => 10,
            self::STATUS_APPLIED => 40,
            self::STATUS_UNDER_REVIEW => 60,
            self::STATUS_ACCEPTED => 80,
            self::STATUS_COMPLETED => 100,
            self::STATUS_REJECTED => 0,
            self::STATUS_CANCELLED => 0,
        ];

        return $statusProgress[$this->status] ?? 0;
    }

    /**
     * Métodos de acción
     */
    public function markAsApplied()
    {
        $this->update([
            'status' => self::STATUS_APPLIED,
            'applied_at' => Carbon::now(),
        ]);
    }

    public function markUnderReview($reviewerId = null)
    {
        $this->update([
            'status' => self::STATUS_UNDER_REVIEW,
            'reviewed_at' => Carbon::now(),
        ]);
    }

    public function markAsAccepted($adminNotes = null)
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => Carbon::now(),
            'admin_notes' => $adminNotes,
        ]);
    }

    public function markAsRejected($adminNotes = null)
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'admin_notes' => $adminNotes,
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => Carbon::now(),
        ]);
    }

    public function cancel($adminNotes = null)
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'admin_notes' => $adminNotes,
        ]);
    }

    /**
     * Verificaciones de estado
     */
    public function canBeAppliedTo()
    {
        return $this->status === self::STATUS_ASSIGNED && 
               $this->can_apply && 
               (!$this->application_deadline || !$this->application_deadline->isPast());
    }

    public function canBeCancelled()
    {
        return in_array($this->status, [
            self::STATUS_ASSIGNED,
            self::STATUS_APPLIED,
            self::STATUS_UNDER_REVIEW
        ]);
    }

    public function canBeReviewed()
    {
        return $this->status === self::STATUS_APPLIED;
    }

    public function isActive()
    {
        return in_array($this->status, [
            self::STATUS_ASSIGNED,
            self::STATUS_APPLIED,
            self::STATUS_UNDER_REVIEW,
            self::STATUS_ACCEPTED
        ]);
    }

    /**
     * Métodos estáticos
     */
    public static function createAssignment($userId, $programId, $assignedBy, $options = [])
    {
        return self::create([
            'user_id' => $userId,
            'program_id' => $programId,
            'assigned_by' => $assignedBy,
            'status' => self::STATUS_ASSIGNED,
            'assigned_at' => Carbon::now(),
            'assignment_notes' => $options['assignment_notes'] ?? null,
            'can_apply' => $options['can_apply'] ?? true,
            'is_priority' => $options['is_priority'] ?? false,
            'application_deadline' => $options['application_deadline'] ?? null,
            'program_data' => $options['program_data'] ?? null,
        ]);
    }

    public static function getAssignmentStats($programId = null)
    {
        $query = self::query();
        
        if ($programId) {
            $query->where('program_id', $programId);
        }

        return [
            'total' => $query->count(),
            'assigned' => $query->where('status', self::STATUS_ASSIGNED)->count(),
            'applied' => $query->where('status', self::STATUS_APPLIED)->count(),
            'under_review' => $query->where('status', self::STATUS_UNDER_REVIEW)->count(),
            'accepted' => $query->where('status', self::STATUS_ACCEPTED)->count(),
            'rejected' => $query->where('status', self::STATUS_REJECTED)->count(),
            'completed' => $query->where('status', self::STATUS_COMPLETED)->count(),
            'cancelled' => $query->where('status', self::STATUS_CANCELLED)->count(),
            'overdue' => $query->where('application_deadline', '<', Carbon::now())
                              ->where('status', self::STATUS_ASSIGNED)->count(),
        ];
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($assignment) {
            if (!$assignment->assigned_at) {
                $assignment->assigned_at = Carbon::now();
            }
        });
    }
}
