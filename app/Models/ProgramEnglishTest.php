<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramEnglishTest extends Model
{
    public const CEFR_ORDER = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

    protected $fillable = [
        'program_process_id', 'english_evaluation_id', 'evaluator_name', 'exam_name', 'oral_score',
        'listening_score', 'reading_score', 'final_score', 'cefr_level', 'observations', 'test_pdf_path',
        'results_sent_to_applicant', 'results_sent_at', 'attempt_number', 'recorded_by',
    ];

    protected $casts = [
        'oral_score' => 'string',
        'listening_score' => 'integer',
        'reading_score' => 'integer',
        'final_score' => 'integer',
        'results_sent_to_applicant' => 'boolean',
        'results_sent_at' => 'datetime',
        'attempt_number' => 'integer',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(ProgramProcess::class, 'program_process_id');
    }

    public function englishEvaluation(): BelongsTo
    {
        return $this->belongsTo(EnglishEvaluation::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /** Misma escala que AuPairEnglishTest::scoreToLevel(). */
    public static function scoreToLevel(?int $score): ?string
    {
        if ($score === null) {
            return null;
        }
        if ($score >= 71) {
            return 'C2';
        }
        if ($score >= 61) {
            return 'C1';
        }
        if ($score >= 51) {
            return 'B2';
        }
        if ($score >= 41) {
            return 'B1';
        }
        if ($score >= 31) {
            return 'A2';
        }

        return 'A1';
    }

    public static function levelMeets(?string $level, string $minimum): bool
    {
        $idx = array_search($level, self::CEFR_ORDER, true);
        $min = array_search($minimum, self::CEFR_ORDER, true);

        return $idx !== false && $min !== false && $idx >= $min;
    }
}
