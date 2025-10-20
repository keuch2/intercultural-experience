<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Modelo para gestionar asignaciones de programas a participantes
 * 
 * Una asignación es cuando un agente asigna un programa específico a un participante,
 * permitiendo que el participante vea el programa en su app y pueda aplicar a él.
 */
class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'program_id',
        'assigned_by',
        'application_id',
        'status',
        'assigned_at',
        'applied_at',
        'application_deadline',
        'assignment_notes',
        'admin_notes',
        'is_priority',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'applied_at' => 'datetime',
        'application_deadline' => 'date',
        'is_priority' => 'boolean',
    ];

    protected $appends = [
        'status_name',
        'can_apply',
        'is_overdue',
        'days_until_deadline',
        'progress_percentage',
    ];

    /**
     * Relación con el participante (usuario)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relación con el programa asignado
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Relación con el agente que asignó
     */
    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Relación con la aplicación (si ya aplicó)
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Scope: Asignaciones activas (assigned o applied)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['assigned', 'applied', 'under_review']);
    }

    /**
     * Scope: Asignaciones por estado
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Asignaciones de un usuario específico
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Asignaciones de un agente específico
     */
    public function scopeByAgent($query, $agentId)
    {
        return $query->where('assigned_by', $agentId);
    }

    /**
     * Scope: Asignaciones prioritarias
     */
    public function scopePriority($query)
    {
        return $query->where('is_priority', true);
    }

    /**
     * Scope: Asignaciones vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'assigned')
            ->whereNotNull('application_deadline')
            ->where('application_deadline', '<', now());
    }

    /**
     * Accessor: Nombre del estado en español
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            'assigned' => 'Asignado',
            'applied' => 'Aplicado',
            'under_review' => 'En Revisión',
            'accepted' => 'Aceptado',
            'rejected' => 'Rechazado',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    /**
     * Accessor: Puede aplicar al programa
     */
    public function getCanApplyAttribute()
    {
        // Solo puede aplicar si está en estado 'assigned' y no ha vencido
        if ($this->status !== 'assigned') {
            return false;
        }

        // Si tiene deadline, verificar que no haya vencido
        if ($this->application_deadline) {
            return $this->application_deadline >= now()->toDateString();
        }

        return true;
    }

    /**
     * Accessor: Está vencida
     */
    public function getIsOverdueAttribute()
    {
        if ($this->status !== 'assigned') {
            return false;
        }

        if (!$this->application_deadline) {
            return false;
        }

        return $this->application_deadline < now()->toDateString();
    }

    /**
     * Accessor: Días hasta el deadline
     */
    public function getDaysUntilDeadlineAttribute()
    {
        if (!$this->application_deadline) {
            return null;
        }

        $deadline = Carbon::parse($this->application_deadline);
        $today = Carbon::today();

        return $today->diffInDays($deadline, false);
    }

    /**
     * Accessor: Porcentaje de progreso
     */
    public function getProgressPercentageAttribute()
    {
        // Si tiene aplicación, calcular progreso basado en requisitos completados
        if ($this->application) {
            $totalRequisites = $this->application->program->requisites()->count();
            
            if ($totalRequisites === 0) {
                return 100;
            }

            $completedRequisites = $this->application->userRequisites()
                ->where('status', 'verified')
                ->count();

            return round(($completedRequisites / $totalRequisites) * 100);
        }

        // Si no ha aplicado, progreso es 0
        return 0;
    }

    /**
     * Marcar como aplicado
     */
    public function markAsApplied($applicationId)
    {
        $this->update([
            'status' => 'applied',
            'applied_at' => now(),
            'application_id' => $applicationId,
        ]);
    }

    /**
     * Marcar como aceptado
     */
    public function markAsAccepted($adminNotes = null)
    {
        $this->update([
            'status' => 'accepted',
            'admin_notes' => $adminNotes,
        ]);
    }

    /**
     * Marcar como rechazado
     */
    public function markAsRejected($adminNotes = null)
    {
        $this->update([
            'status' => 'rejected',
            'admin_notes' => $adminNotes,
        ]);
    }

    /**
     * Cancelar asignación
     */
    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'admin_notes' => $reason,
        ]);
    }

    /**
     * Verificar si el usuario puede ver los detalles
     */
    public function canViewDetails()
    {
        return in_array($this->status, ['assigned', 'applied', 'under_review', 'accepted']);
    }

    /**
     * Verificar si puede aplicar ahora
     */
    public function canApplyNow()
    {
        return $this->can_apply && !$this->is_overdue;
    }
}
