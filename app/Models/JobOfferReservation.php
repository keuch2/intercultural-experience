<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOfferReservation extends Model
{
    protected $fillable = [
        'user_id',
        'job_offer_id',
        'application_id',
        'reservation_fee',
        'cancellation_fee',
        'status',
        'reserved_at',
        'confirmed_at',
        'cancelled_at',
        'cancelled_by',
        'cancellation_reason',
        'fee_paid',
        'refund_processed',
    ];

    protected $casts = [
        'reservation_fee' => 'decimal:2',
        'cancellation_fee' => 'decimal:2',
        'reserved_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'fee_paid' => 'boolean',
        'refund_processed' => 'boolean',
    ];

    /**
     * Relación con User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con JobOffer
     */
    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }

    /**
     * Relación con Application
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relación con el usuario que canceló
     */
    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Scope: Solo reservas activas
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['reserved', 'confirmed']);
    }

    /**
     * Scope: Reservas canceladas
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope: Reservas confirmadas
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope: Reservas pendientes de pago
     */
    public function scopePendingPayment($query)
    {
        return $query->where('fee_paid', false)
            ->whereIn('status', ['reserved', 'confirmed']);
    }

    /**
     * Método: Confirmar reserva
     */
    public function confirm(): bool
    {
        if ($this->status !== 'reserved') {
            return false;
        }

        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return true;
    }

    /**
     * Método: Cancelar reserva
     */
    public function cancel(int $cancelledBy, string $reason = null): bool
    {
        if (!in_array($this->status, ['reserved', 'confirmed'])) {
            return false;
        }

        // Liberar el cupo en la job offer
        $this->jobOffer->releaseSlot();

        // Actualizar la reserva
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => $cancelledBy,
            'cancellation_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Método: Marcar como pagada
     */
    public function markAsPaid(): void
    {
        $this->update(['fee_paid' => true]);
    }

    /**
     * Método: Procesar reembolso
     */
    public function processRefund(): void
    {
        $this->update(['refund_processed' => true]);
    }

    /**
     * Método: Calcular monto a reembolsar
     */
    public function calculateRefundAmount(): float
    {
        if ($this->status !== 'cancelled' || !$this->fee_paid) {
            return 0.0;
        }

        // Reembolso = Tarifa de reserva - Penalidad por cancelación
        return (float) ($this->reservation_fee - $this->cancellation_fee);
    }

    /**
     * Método: Verificar si el usuario tiene una reserva activa
     */
    public static function hasActiveReservation(int $userId): bool
    {
        return self::where('user_id', $userId)
            ->whereIn('status', ['reserved', 'confirmed'])
            ->exists();
    }
}
