<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name', 
        'symbol',
        'exchange_rate_to_pyg',
        'is_active'
    ];

    protected $casts = [
        'exchange_rate_to_pyg' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function getFormattedSymbolAttribute()
    {
        return $this->symbol ?: $this->code;
    }

    public function convertToPyg($amount)
    {
        return $amount * $this->exchange_rate_to_pyg;
    }

    public function convertFromPyg($amountInPyg)
    {
        return $this->exchange_rate_to_pyg > 0 ? $amountInPyg / $this->exchange_rate_to_pyg : 0;
    }
}
