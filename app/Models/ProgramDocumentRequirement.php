<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramDocumentRequirement extends Model
{
    protected $fillable = [
        'program_id', 'stage_key', 'group_key', 'key', 'label', 'description', 'is_required', 'min_count',
        'allow_multiple', 'uploaded_by', 'unlock_gate_key', 'section', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'min_count' => 'integer',
        'allow_multiple' => 'boolean',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /** Grupo visual efectivo (tab): group_key o, por defecto, stage_key. */
    public function getEffectiveGroupAttribute(): string
    {
        return $this->group_key ?: $this->stage_key;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
