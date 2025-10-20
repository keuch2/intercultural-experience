<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class FinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'category',
        'description',
        'amount',
        'currency_id',
        'amount_pyg',
        'exchange_rate_snapshot',
        'transaction_date',
        'reference',
        'payment_method',
        'application_id',
        'program_id',
        'user_id',
        'created_by',
        'notes',
        'receipt_file',
        'status'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'amount_pyg' => 'decimal:2',
        'exchange_rate_snapshot' => 'decimal:4',
    ];

    protected $hidden = [
        'reference', 'notes', 'receipt_file',
    ];

    // Relaciones
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('transaction_date', Carbon::now()->year);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereYear('transaction_date', Carbon::now()->year)
                    ->whereMonth('transaction_date', Carbon::now()->month);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    // Métodos auxiliares
    public function getFormattedAmountAttribute()
    {
        if ($this->currency) {
            return $this->currency->symbol . ' ' . number_format($this->amount, 2, ',', '.');
        }
        return '₲ ' . number_format($this->amount, 0, ',', '.');
    }

    public function getFormattedAmountPygAttribute()
    {
        return '₲ ' . number_format($this->amount_pyg ?? $this->amount, 0, ',', '.');
    }

    public function getTypeLabel()
    {
        return $this->type === 'income' ? 'Ingreso' : 'Egreso';
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmado',
            'cancelled' => 'Cancelado',
            default => 'Desconocido'
        };
    }

    // Convertir a guaraníes si es necesario
    public function convertToPyg()
    {
        if ($this->currency && $this->currency->code !== 'PYG') {
            // Guardar el exchange rate del momento para histórico
            $this->exchange_rate_snapshot = $this->currency->exchange_rate_to_pyg;
            // Convertir el monto
            $this->amount_pyg = $this->currency->convertToPyg($this->amount);
            $this->save();
        } else {
            // Si es PYG, el monto es el mismo y el exchange rate es 1
            $this->amount_pyg = $this->amount;
            $this->exchange_rate_snapshot = 1;
            $this->save();
        }
    }

    // Categorías predefinidas
    public static function getIncomeCategories()
    {
        return [
            'program_payment' => 'Pago de Programa',
            'registration_fee' => 'Tasa de Inscripción',
            'additional_service' => 'Servicio Adicional',
            'deposit' => 'Depósito/Seña',
            'late_payment' => 'Pago con Recargo',
            'refund_recovery' => 'Recuperación de Reembolso',
            'other_income' => 'Otros Ingresos'
        ];
    }

    public static function getExpenseCategories()
    {
        return [
            'program_cost' => 'Costo de Programa',
            'accommodation' => 'Alojamiento',
            'transportation' => 'Transporte',
            'meals' => 'Alimentación',
            'insurance' => 'Seguros',
            'visa_fees' => 'Tasas de Visa',
            'administrative' => 'Gastos Administrativos',
            'marketing' => 'Marketing y Publicidad',
            'staff_salary' => 'Salarios',
            'office_expenses' => 'Gastos de Oficina',
            'utilities' => 'Servicios Públicos',
            'legal_fees' => 'Gastos Legales',
            'technology' => 'Tecnología',
            'refunds' => 'Reembolsos',
            'bank_fees' => 'Comisiones Bancarias',
            'other_expense' => 'Otros Gastos'
        ];
    }

    public static function getPaymentMethods()
    {
        return [
            'cash' => 'Efectivo',
            'bank_transfer' => 'Transferencia Bancaria',
            'credit_card' => 'Tarjeta de Crédito',
            'debit_card' => 'Tarjeta de Débito',
            'check' => 'Cheque',
            'paypal' => 'PayPal',
            'western_union' => 'Western Union',
            'crypto' => 'Criptomoneda',
            'other' => 'Otro'
        ];
    }
} 