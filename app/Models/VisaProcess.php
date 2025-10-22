<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VisaProcess extends Model
{
    protected $fillable = [
        'user_id',
        'application_id',
        'documentation_complete',
        'documentation_complete_date',
        'sponsor_interview_status',
        'sponsor_interview_date',
        'sponsor_interview_notes',
        'job_interview_status',
        'job_interview_date',
        'job_interview_notes',
        'ds160_completed',
        'ds160_completed_date',
        'ds160_confirmation_number',
        'ds160_file_path',
        'ds2019_received',
        'ds2019_received_date',
        'ds2019_file_path',
        'sevis_paid',
        'sevis_paid_date',
        'sevis_amount',
        'sevis_receipt_path',
        'consular_fee_paid',
        'consular_fee_paid_date',
        'consular_fee_amount',
        'consular_fee_receipt_path',
        'consular_appointment_scheduled',
        'consular_appointment_date',
        'consular_appointment_location',
        'visa_result',
        'visa_result_date',
        'visa_result_notes',
        'passport_file_path',
        'visa_photo_path',
        'process_notes',
        'current_step',
        'progress_percentage',
    ];

    protected $casts = [
        'documentation_complete' => 'boolean',
        'documentation_complete_date' => 'date',
        'sponsor_interview_date' => 'datetime',
        'job_interview_date' => 'datetime',
        'ds160_completed' => 'boolean',
        'ds160_completed_date' => 'date',
        'ds2019_received' => 'boolean',
        'ds2019_received_date' => 'date',
        'sevis_paid' => 'boolean',
        'sevis_paid_date' => 'date',
        'sevis_amount' => 'decimal:2',
        'consular_fee_paid' => 'boolean',
        'consular_fee_paid_date' => 'date',
        'consular_fee_amount' => 'decimal:2',
        'consular_appointment_scheduled' => 'boolean',
        'consular_appointment_date' => 'datetime',
        'visa_result_date' => 'date',
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
     * Relación con User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
        return $query->where('current_step', $status);
    }

    /**
     * Scope: Visas aprobadas
     */
    public function scopeApproved($query)
    {
        return $query->where('visa_result', 'approved');
    }

    /**
     * Scope: Visas rechazadas
     */
    public function scopeRejected($query)
    {
        return $query->where('visa_result', 'rejected');
    }

    /**
     * Scope: En proceso
     */
    public function scopeInProgress($query)
    {
        return $query->whereNotIn('visa_result', ['approved', 'rejected']);
    }

    /**
     * Scope: Pendientes de pago SEVIS
     */
    public function scopePendingSevisPayment($query)
    {
        return $query->where('current_step', 'ds2019_received')
            ->where('sevis_paid', false);
    }

    /**
     * Scope: Con cita programada
     */
    public function scopeWithAppointment($query)
    {
        return $query->where('consular_appointment_scheduled', true)
            ->whereNotNull('consular_appointment_date');
    }

    /**
     * Método: Cambiar estado del proceso
     */
    public function changeStatus(string $newStatus, int $changedBy, string $notes = null): bool
    {
        if (!in_array($newStatus, self::STATUS_ORDER)) {
            return false;
        }

        $oldStatus = $this->current_step;

        // Crear registro en el historial
        $this->statusHistory()->create([
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'changed_by' => $changedBy,
            'notes' => $notes,
            'changed_at' => now(),
        ]);

        // Actualizar el estado actual
        $this->update(['current_step' => $newStatus]);

        return true;
    }

    /**
     * Método: Obtener progreso del proceso (0-100%)
     */
    public function getProgressPercentage(): int
    {
        $currentIndex = array_search($this->current_step, self::STATUS_ORDER);
        
        if ($currentIndex === false) {
            return 0;
        }

        // Visa aprobada = 100%
        if ($this->visa_result === 'approved') {
            return 100;
        }

        // Visa rechazada = 0%
        if ($this->visa_result === 'rejected') {
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
        $currentIndex = array_search($this->current_step, self::STATUS_ORDER);
        
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
        if (in_array($this->visa_result, ['approved', 'rejected'])) {
            return false;
        }

        // Validaciones específicas por estado
        switch ($this->current_step) {
            case 'ds2019_received':
                return $this->sevis_paid === true;
            
            case 'sevis_paid':
                return $this->consular_fee_paid === true;
            
            case 'consular_fee_paid':
                return $this->consular_appointment_scheduled === true;
            
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
            'sevis_paid' => true,
            'sevis_paid_date' => now(),
        ]);

        if ($this->current_step === 'ds2019_received') {
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
            'consular_fee_paid' => true,
            'consular_fee_paid_date' => now(),
        ]);

        if ($this->current_step === 'sevis_paid') {
            $this->changeStatus('consular_fee_paid', auth()->id() ?? 1, 'Tasa consular pagada');
        }
    }

    /**
     * Método: Programar cita
     */
    public function scheduleAppointment(\DateTime $date, string $location): void
    {
        $this->update([
            'consular_appointment_date' => $date,
            'consular_appointment_location' => $location,
            'consular_appointment_scheduled' => true,
        ]);

        if ($this->current_step === 'consular_fee_paid') {
            $this->changeStatus('appointment_scheduled', auth()->id() ?? 1, "Cita programada para {$date->format('Y-m-d')}");
        }
    }

    /**
     * Método: Obtener días restantes hasta la cita
     */
    public function getDaysUntilAppointment(): ?int
    {
        if (!$this->consular_appointment_date) {
            return null;
        }

        $now = now();
        $appointment = $this->consular_appointment_date;

        return $now->diffInDays($appointment, false);
    }

    /**
     * Método: Obtener timeline visual del proceso
     */
    public function getTimeline(): array
    {
        $timeline = [];
        
        foreach (self::STATUS_ORDER as $status) {
            $isPast = array_search($status, self::STATUS_ORDER) < array_search($this->current_step, self::STATUS_ORDER);
            $isCurrent = $status === $this->current_step;
            
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
