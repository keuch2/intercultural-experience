<?php

namespace App\Support;

use App\Models\Application;
use App\Models\User;

/**
 * Resuelve datos de Au Pair (nivel de inglés, progreso) prefiriendo el
 * AuPairProcess (englishTests, current_stage) sobre las fuentes legacy
 * (user englishEvaluations, application progress). Así el listado, el perfil
 * y la pestaña de Aplicaciones muestran siempre el mismo valor.
 */
class AuPairResolver
{
    /**
     * Mejor nivel CEFR. Prefiere AuPairProcess->englishTests (por final_score),
     * fallback a user->englishEvaluations (por score).
     */
    public static function bestEnglishLevel(Application $application, ?User $user = null): ?string
    {
        $user = $user ?? $application->user;
        if (! $user) {
            return null;
        }

        $process = $user->auPairProcess;
        if ($process && $process->englishTests->isNotEmpty()) {
            return $process->englishTests->sortByDesc('final_score')->first()?->cefr_level;
        }

        return $user->englishEvaluations->sortByDesc('score')->first()?->cefr_level;
    }

    /**
     * Progreso %. Si hay AuPairProcess, deriva del current_stage real del proceso;
     * si no, usa el progreso de la Application.
     */
    public static function progressPercentage(Application $application, ?User $user = null): int
    {
        $user = $user ?? $application->user;
        $process = $user?->auPairProcess;

        if ($process) {
            return Application::stageToProgress($process->current_stage ?? 'admission');
        }

        return (int) $application->progress_percentage;
    }
}
