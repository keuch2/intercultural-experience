<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisaProcess extends Model
{
    protected $fillable = [
        'application_id',
        'current_status',
        'ds160_number',
        'ds2019_number',
        'sevis_id',
        'sevis_amount',
        'sevis_paid_at',
        'consular_fee_amount',
        'consular_fee_paid_at',
        'appointment_date',
        'appointment_location',
        'interview_result',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'sevis_amount' => 'decimal:2',
        'consular_fee_amount' => 'decimal:2',
        'sevis_paid_at' => 'datetime',
        'consular_fee_paid_at' => 'datetime',
        'appointment_date' => 'datetime',
    ];

    /**
     * Estados del proceso de visa en orden
     */
    const STATUS_ORDER = [
        'documentation_pending',
        'sponsor_interview_pending',
        'sponsor_interview_approved',
        'job_interview_pending',
        'job_interview_approved',
        'ds160_pending',
        'ds160_completed',
        'ds2019_pending',
        'ds2019_received',
        'sevis_paid',
        'consular_fee_paid',
        'appointment_scheduled',
        'in_correspondence',
        'visa_approved',
        'visa_rejected',
    ];

    /**
     * Relación con Application
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relación con StatusHistory
     */
    public function statusHistory(): HasMany
    {
        return $this->hasMany(VisaStatusHistory::class);
    }

    /**
     * Scope: Filtrar por estado actual
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('current_status', $status);
    }

    /**
     * Scope: Visas aprobadas
     */
    public function scopeApproved($query)
    {
        return $query->where('current_status', 'visa_approved');
    }

    /**
     * Scope: Visas rechazadas
     */
    public function scopeRejected($query)
    {
        return $query->where('current_status', 'visa_rejected');
    }

    /**
     * Scope: En proceso
     */
    public function scopeInProgress($query)
    {
        return $query->whereNotIn('current_status', ['visa_approved', 'visa_rejected']);
    }

    /**
     * Scope: Pendientes de pago SEVIS
     */
    public function scopePendingSevisPayment($query)
    {
        return $query->where('current_status', 'ds2019_received')
            ->whereNull('sevis_paid_at');
    }

    /**
     * Scope: Con cita programada
     */
    public function scopeWithAppointment($query)
    {
        return $query->where('current_status', 'appointment_scheduled')
            ->whereNotNull('appointment_date');
    }

    /**
     * Método: Cambiar estado del proceso
     */
    public function changeStatus(string $newStatus, int $changedBy, string $notes = null): bool
    {
        if (!in_array($newStatus, self::STATUS_ORDER)) {
            return false;
        }

        $oldStatus = $this->current_status;

        // Crear registro en el historial
        $this->statusHistory()->create([
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_by' => $changedBy,
            'notes' => $notes,
            'changed_at' => now(),
        ]);

        // Actualizar el estado actual
        $this->update(['current_status' => $newStatus]);

        return true;
    }

    /**
     * Método: Obtener progreso del proceso (0-100%)
     */
    public function getProgressPercentage(): int
    {
        $currentIndex = array_search($this->current_status, self::STATUS_ORDER);
        
        if ($currentIndex === false) {
            return 0;
        }

        // Visa aprobada = 100%
        if ($this->current_status === 'visa_approved') {
            return 100;
        }

        // Visa rechazada = 0%
        if ($this->current_status === 'visa_rejected') {
            return 0;
        }

        // Calcular porcentaje basado en la posición
        $totalSteps = count(self::STATUS_ORDER) - 2; // Excluir approved y rejected
        return (int) (($currentIndex / $totalSteps) * 100);
    }

    /**
     * Método: Obtener siguiente paso requerido
     */
    public function getNextStep(): ?string
    {
        $currentIndex = array_search($this->current_status, self::STATUS_ORDER);
        
        if ($currentIndex === false || $currentIndex >= count(self::STATUS_ORDER) - 1) {
            return null;
        }

        return self::STATUS_ORDER[$currentIndex + 1];
    }

    /**
     * Método: Verificar si puede avanzar al siguiente estado
     */
    public function canAdvanceToNextStatus(): bool
    {
        // No puede avanzar si ya está aprobada o rechazada
        if (in_array($this->current_status, ['visa_approved', 'visa_rejected'])) {
            return false;
        }

        // Validaciones específicas por estado
        switch ($this->current_status) {
            case 'ds2019_received':
                return $this->sevis_paid_at !== null;
            
            case 'sevis_paid':
                return $this->consular_fee_paid_at !== null;
            
            case 'consular_fee_paid':
                return $this->appointment_date !== null;
            
            default:
                return true;
        }
    }

    /**
     * Método: Marcar pago de SEVIS
     */
    public function markSevisPaid(float $amount): void
    {
        $this->update([
            'sevis_amount' => $amount,
            'sevis_paid_at' => now(),
        ]);

        if ($this->current_status === 'ds2019_received') {
            $this->changeStatus('sevis_paid', auth()->id() ?? 1, 'Pago SEVIS procesado');
        }
    }

    /**
     * Método: Marcar pago de tasa consular
     */
    public function markConsularFeePaid(float $amount): void
    {
        $this->update([
            'consular_fee_amount' => $amount,
            'consular_fee_paid_at' => now(),
        ]);

        if ($this->current_status === 'sevis_paid') {
            $this->changeStatus('consular_fee_paid', auth()->id() ?? 1, 'Tasa consular pagada');
        }
    }

    /**
     * Método: Programar cita
     */
    public function scheduleAppointment(\DateTime $date, string $location): void
    {
        $this->update([
            'appointment_date' => $date,
            'appointment_location' => $location,
        ]);

        if ($this->current_status === 'consular_fee_paid') {
            $this->changeStatus('appointment_scheduled', auth()->id() ?? 1, "Cita programada para {$date->format('Y-m-d')}");
        }
    }

    /**
     * Método: Obtener días restantes hasta la cita
     */
    public function getDaysUntilAppointment(): ?int
    {
        if (!$this->appointment_date) {
            return null;
        }

        $now = now();
        $appointment = $this->appointment_date;

        return $now->diffInDays($appointment, false);
    }

    /**
     * Método: Obtener timeline visual del proceso
     */
    public function getTimeline(): array
    {
        $timeline = [];
        
        foreach (self::STATUS_ORDER as $status) {
            $isPast = array_search($status, self::STATUS_ORDER) < array_search($this->current_status, self::STATUS_ORDER);
            $isCurrent = $status === $this->current_status;
            
            $timeline[] = [
                'status' => $status,
                'label' => $this->getStatusLabel($status),
                'is_past' => $isPast,
                'is_current' => $isCurrent,
                'is_future' => !$isPast && !$isCurrent,
            ];
        }

        return $timeline;
    }

    /**
     * Método: Obtener etiqueta legible del estado
     */
    private function getStatusLabel(string $status): string
    {
        $labels = [
            'documentation_pending' => 'Documentación Pendiente',
            'sponsor_interview_pending' => 'Entrevista Sponsor Pendiente',
            'sponsor_interview_approved' => 'Entrevista Sponsor Aprobada',
            'job_interview_pending' => 'Entrevista Trabajo Pendiente',
            'job_interview_approved' => 'Entrevista Trabajo Aprobada',
            'ds160_pending' => 'DS-160 Pendiente',
            'ds160_completed' => 'DS-160 Completado',
            'ds2019_pending' => 'DS-2019 Pendiente',
            'ds2019_received' => 'DS-2019 Recibido',
            'sevis_paid' => 'SEVIS Pagado',
            'consular_fee_paid' => 'Tasa Consular Pagada',
            'appointment_scheduled' => 'Cita Programada',
            'in_correspondence' => 'En Correspondencia',
            'visa_approved' => 'Visa Aprobada',
            'visa_rejected' => 'Visa Rechazada',
        ];

        return $labels[$status] ?? $status;
    }
}
