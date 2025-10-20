<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstallmentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_installment_id',
        'installment_number',
        'amount',
        'due_date',
        'paid_date',
        'status',
        'user_program_requisite_id',
        'invoice_id',
        'late_fee',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    // Relaciones
    public function paymentInstallment(): BelongsTo
    {
        return $this->belongsTo(PaymentInstallment::class);
    }

    public function userProgramRequisite(): BelongsTo
    {
        return $this->belongsTo(UserProgramRequisite::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeDueThisMonth($query)
    {
        return $query->whereMonth('due_date', now()->month)
                    ->whereYear('due_date', now()->year);
    }

    // Métodos auxiliares
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'paid' => 'Pagado',
            'overdue' => 'Vencido',
            'cancelled' => 'Cancelado',
            default => 'Desconocido'
        };
    }

    public function getTotalAmountAttribute()
    {
        return $this->amount + $this->late_fee;
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && 
               $this->due_date && 
               $this->due_date->isPast();
    }

    public function getDaysOverdueAttribute()
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        return now()->diffInDays($this->due_date);
    }

    public function markAsPaid(?Invoice $invoice = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => now(),
            'invoice_id' => $invoice?->id,
        ]);
        
        // Verificar si el plan completo está pagado
        $this->paymentInstallment->checkCompletion();
    }

    /**
     * Calcula el recargo por mora según días vencidos
     */
    public function calculateLateFee(float $dailyRate = 0.1): float
    {
        if (!$this->isOverdue()) {
            return 0;
        }
        
        $daysOverdue = $this->days_overdue;
        return round($this->amount * ($dailyRate / 100) * $daysOverdue, 2);
    }

    /**
     * Aplica recargo por mora
     */
    public function applyLateFee(float $dailyRate = 0.1): void
    {
        $lateFee = $this->calculateLateFee($dailyRate);
        $this->update(['late_fee' => $lateFee]);
    }
}
