<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentInstallment extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'user_id',
        'program_id',
        'plan_name',
        'total_installments',
        'total_amount',
        'interest_rate',
        'currency_id',
        'status',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
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

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function installmentDetails(): HasMany
    {
        return $this->hasMany(InstallmentDetail::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Métodos auxiliares
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'active' => 'Activo',
            'completed' => 'Completado',
            'defaulted' => 'En Mora',
            'cancelled' => 'Cancelado',
            default => 'Desconocido'
        };
    }

    public function getPaidInstallmentsCountAttribute()
    {
        return $this->installmentDetails()->where('status', 'paid')->count();
    }

    public function getPendingInstallmentsCountAttribute()
    {
        return $this->installmentDetails()->where('status', 'pending')->count();
    }

    public function getOverdueInstallmentsCountAttribute()
    {
        return $this->installmentDetails()->where('status', 'overdue')->count();
    }

    public function getTotalPaidAttribute()
    {
        return $this->installmentDetails()->where('status', 'paid')->sum('amount');
    }

    public function getTotalPendingAttribute()
    {
        return $this->installmentDetails()->whereIn('status', ['pending', 'overdue'])->sum('amount');
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_installments == 0) return 0;
        return ($this->paid_installments_count / $this->total_installments) * 100;
    }

    /**
     * Verifica si el plan está completo
     */
    public function checkCompletion(): void
    {
        $allPaid = $this->installmentDetails()
            ->where('status', '!=', 'paid')
            ->count() === 0;
        
        if ($allPaid && $this->status !== 'completed') {
            $this->update(['status' => 'completed']);
        }
    }

    /**
     * Marca cuotas vencidas como overdue
     */
    public static function markOverdueInstallments(): int
    {
        return InstallmentDetail::where('status', 'pending')
            ->where('due_date', '<', now())
            ->update(['status' => 'overdue']);
    }
}
