<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramStage extends Model
{
    protected $fillable = [
        'program_id', 'key', 'label', 'description', 'sort_order', 'is_terminal', 'mobile_screen', 'guards',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_terminal' => 'boolean',
        'guards' => 'array',
    ];

    public const GUARD_KEYS = [
        'require_docs_approved',
        'require_gates',
        'require_checklist',
        'require_english_min_level',
        'require_job_assignment',
        'require_placement_complete',
        'require_visa_approved',
        'manual_only',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function guardValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->guards ?? [], $key, $default);
    }

    /**
     * La etapa tiene condiciones automáticas de avance (no es "solo manual" y al menos
     * un guard está activo). Sirve para no anunciar "requisitos completos" en etapas
     * donde nada se evalúa.
     */
    public function hasAutomaticGuards(): bool
    {
        if ($this->guardValue('manual_only', false)) {
            return false;
        }
        foreach (self::GUARD_KEYS as $key) {
            if ($key === 'manual_only') {
                continue;
            }
            $v = $this->guardValue($key, $key === 'require_docs_approved' ? true : null);
            if (is_array($v) ? $v !== [] : (bool) $v) {
                return true;
            }
        }

        return false;
    }
}
