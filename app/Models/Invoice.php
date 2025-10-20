<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
        'application_id',
        'program_id',
        'user_program_requisite_id',
        'billing_name',
        'billing_email',
        'billing_address',
        'billing_city',
        'billing_country',
        'billing_tax_id',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total',
        'currency_id',
        'concept',
        'notes',
        'status',
        'issue_date',
        'due_date',
        'paid_date',
        'pdf_path',
        'created_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
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

    public function userProgramRequisite(): BelongsTo
    {
        return $this->belongsTo(UserProgramRequisite::class);
    }

    // Scopes
    public function scopeIssued($query)
    {
        return $query->where('status', 'issued');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'issued']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'issued')
                    ->where('due_date', '<', now());
    }

    // Métodos auxiliares
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'draft' => 'Borrador',
            'issued' => 'Emitido',
            'paid' => 'Pagado',
            'cancelled' => 'Cancelado',
            'refunded' => 'Reembolsado',
            default => 'Desconocido'
        };
    }

    public function getFormattedTotalAttribute()
    {
        if ($this->currency) {
            return $this->currency->symbol . ' ' . number_format($this->total, 2, ',', '.');
        }
        return '₲ ' . number_format($this->total, 0, ',', '.');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'issued' && 
               $this->due_date && 
               $this->due_date->isPast();
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => now(),
        ]);
    }

    /**
     * Genera el número de factura automáticamente
     */
    public static function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        
        // Formato: INV-YYYY-MM-0001
        $lastInvoice = self::where('invoice_number', 'like', "INV-{$year}-{$month}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return sprintf('INV-%s-%s-%04d', $year, $month, $newNumber);
    }
}
