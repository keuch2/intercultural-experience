<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Orquestador genérico del proceso de un participante en un programa del motor.
 * Equivalente configurable de AuPairProcess.
 */
class ProgramProcess extends Model
{
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STAGE_LOCKED = 'locked';

    public const STAGE_PENDING = 'pending';

    public const STAGE_IN_PROGRESS = 'in_progress';

    public const STAGE_APPROVED = 'approved';

    public const STAGE_REJECTED = 'rejected';

    protected $fillable = [
        'application_id', 'program_id', 'user_id', 'current_stage_key', 'status', 'stage_states',
        'module_access', 'season', 'enrollment_date', 'notes', 'finalization_result',
        'finalization_reason', 'finalization_date', 'finalized_by',
    ];

    protected $casts = [
        'stage_states' => 'array',
        'module_access' => 'array',
        'enrollment_date' => 'date',
        'finalization_date' => 'date',
    ];

    // Relaciones
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

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProgramDocument::class);
    }

    public function checklist(): HasMany
    {
        return $this->hasMany(ProgramProcessChecklist::class);
    }

    public function gates(): HasMany
    {
        return $this->hasMany(ProgramProcessGate::class);
    }

    public function englishTests(): HasMany
    {
        return $this->hasMany(ProgramEnglishTest::class)->orderBy('attempt_number');
    }

    public function visaProcess(): HasOne
    {
        return $this->hasOne(ProgramVisaProcess::class);
    }

    public function supportLogs(): HasMany
    {
        return $this->hasMany(ProgramSupportLog::class)->orderByDesc('log_date');
    }

    // Helpers de estado
    public function stageState(string $stageKey): array
    {
        return ($this->stage_states ?? [])[$stageKey] ?? ['status' => self::STAGE_LOCKED];
    }

    public function stageStatus(string $stageKey): string
    {
        return $this->stageState($stageKey)['status'] ?? self::STAGE_LOCKED;
    }

    public function setStageState(string $stageKey, array $state): void
    {
        $states = $this->stage_states ?? [];
        $states[$stageKey] = array_merge($states[$stageKey] ?? [], $state);
        $this->stage_states = $states;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function applicantApproved(): bool
    {
        return optional($this->application)->status === 'approved';
    }

    public function moduleAccess(string $module): array
    {
        return ($this->module_access ?? [])[$module] ?? [];
    }

    public function hasModuleAccess(string $module): bool
    {
        return (bool) ($this->moduleAccess($module)['enabled'] ?? false);
    }

    public function isChecklistDone(string $itemKey): bool
    {
        return (bool) $this->checklist->firstWhere('item_key', $itemKey)?->is_done;
    }

    public function isGateVerified(string $gateKey): bool
    {
        return (bool) $this->gates->firstWhere('gate_key', $gateKey)?->is_verified;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeAtStage($query, string $stageKey)
    {
        return $query->where('current_stage_key', $stageKey);
    }

    public function scopeForProgram($query, Program|int $program)
    {
        return $query->where('program_id', $program instanceof Program ? $program->id : $program);
    }
}
