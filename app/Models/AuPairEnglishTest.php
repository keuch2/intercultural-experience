<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuPairEnglishTest extends Model
{
    protected $fillable = [
        'au_pair_process_id',
        'english_evaluation_id',
        'evaluator_name',
        'exam_name',
        'oral_score',
        'listening_score',
        'reading_score',
        'final_score',
        'cefr_level',
        'observations',
        'test_pdf_path',
        'results_sent_to_applicant',
        'results_sent_at',
        'attempt_number',
    ];

    protected $casts = [
        'oral_score' => 'integer',
        'listening_score' => 'integer',
        'reading_score' => 'integer',
        'final_score' => 'integer',
        'results_sent_to_applicant' => 'boolean',
        'results_sent_at' => 'datetime',
        'attempt_number' => 'integer',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(AuPairProcess::class, 'au_pair_process_id');
    }

    public function englishEvaluation(): BelongsTo
    {
        return $this->belongsTo(EnglishEvaluation::class);
    }

    public function meetsMinimumLevel(): bool
    {
        return in_array($this->cefr_level, ['B1', 'B2', 'C1', 'C2']);
    }

    public function getCefrColorAttribute(): string
    {
        return $this->meetsMinimumLevel() ? 'success' : 'warning';
    }

    /**
     * Calculate CEFR level from final score
     */
    public static function scoreToLevel(?int $score): ?string
    {
        if ($score === null) return null;
        if ($score >= 71) return 'C2';
        if ($score >= 61) return 'C1';
        if ($score >= 51) return 'B2';
        if ($score >= 41) return 'B1';
        if ($score >= 31) return 'A2';
        return 'A1';
    }

    /**
     * Max attempts allowed
     */
    public static function maxAttempts(): int
    {
        return 3;
    }

    /**
     * Get remaining attempts for a process
     */
    public static function remainingAttempts(int $processId): int
    {
        $used = static::where('au_pair_process_id', $processId)->count();
        return max(0, static::maxAttempts() - $used);
    }
}
