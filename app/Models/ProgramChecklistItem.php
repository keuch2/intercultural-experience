<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramChecklistItem extends Model
{
    protected $fillable = [
        'program_id', 'stage_key', 'key', 'label', 'item_type', 'required_for_advance', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'required_for_advance' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
