<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramPaymentGate extends Model
{
    protected $fillable = [
        'program_id', 'key', 'label', 'sort_order', 'amount', 'currency_id', 'concept_match',
        'auto_verify_from_payments', 'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'amount' => 'decimal:2',
        'auto_verify_from_payments' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
