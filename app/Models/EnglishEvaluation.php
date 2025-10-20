<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnglishEvaluation extends Model
{
    protected $fillable = [
        'user_id',
        'ef_set_id',
        'cefr_level',
        'classification',
        'score',
        'attempt_number',
        'evaluated_at',
        'notes',
    ];

    protected $casts = [
        'evaluated_at' => 'datetime',
        'score' => 'integer',
        'attempt_number' => 'integer',
    ];

    /**
     * Relación con User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Obtener solo el mejor intento de un usuario
     */
    public function scopeBestAttempt($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->orderBy('score', 'desc')
            ->first();
    }

    /**
     * Scope: Filtrar por nivel CEFR
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('cefr_level', $level);
    }

    /**
     * Scope: Filtrar por clasificación
     */
    public function scopeByClassification($query, $classification)
    {
        return $query->where('classification', $classification);
    }

    /**
     * Método: Clasificar automáticamente según el score
     */
    public static function classifyScore(int $score): string
    {
        if ($score >= 71) return 'EXCELLENT';
        if ($score >= 61) return 'GREAT';
        if ($score >= 51) return 'GOOD';
        return 'INSUFFICIENT';
    }

    /**
     * Método: Obtener nivel CEFR según score
     */
    public static function getCefrLevel(int $score): string
    {
        if ($score >= 71) return 'C2';
        if ($score >= 61) return 'C1';
        if ($score >= 51) return 'B2';
        if ($score >= 41) return 'B1';
        if ($score >= 31) return 'A2';
        return 'A1';
    }

    /**
     * Método: Verificar si el usuario puede hacer otro intento
     */
    public static function canAttempt(int $userId): bool
    {
        $attempts = self::where('user_id', $userId)->count();
        return $attempts < 3;
    }

    /**
     * Método: Obtener número de intentos restantes
     */
    public static function remainingAttempts(int $userId): int
    {
        $attempts = self::where('user_id', $userId)->count();
        return max(0, 3 - $attempts);
    }
}
