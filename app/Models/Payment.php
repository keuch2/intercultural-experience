<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'user_id',
        'program_id',
        'currency_id',
        'amount',
        'payment_method',
        'concept',
        'reference_number',
        'payment_date',
        'status',
        'notes',
        'receipt_path',
        'verified_by',
        'verified_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
    ];

    // Relaciones
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    // Métodos auxiliares
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'verified' => 'Verificado',
            'rejected' => 'Rechazado',
            default => 'Desconocido'
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'verified' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getFormattedAmountAttribute()
    {
        $currencyCode = $this->currency->code ?? 'USD';
        return $currencyCode . ' ' . number_format($this->amount, 2);
    }

    /**
     * Marcar pago como verificado
     */
    public function verify($verifiedBy = null): bool
    {
        return $this->update([
            'status' => 'verified',
            'verified_by' => $verifiedBy ?? auth()->id(),
            'verified_at' => now(),
        ]);
    }

    /**
     * Rechazar pago
     */
    public function reject($verifiedBy = null, $notes = null): bool
    {
        return $this->update([
            'status' => 'rejected',
            'verified_by' => $verifiedBy ?? auth()->id(),
            'verified_at' => now(),
            'notes' => $notes ?? $this->notes,
        ]);
    }
}
