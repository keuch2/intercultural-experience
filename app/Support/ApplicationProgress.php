<?php

namespace App\Support;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\Program;
use App\Models\ProgramProcess;
use App\Services\ProgramEngine\ProgramDefinition;
use App\Services\ProgramEngine\ProgressCalculator;

/**
 * Etapa y progreso reales de una postulación, tomados de su proceso:
 * motor (program_processes) o Au Pair (au_pair_processes). Las columnas
 * applications.current_stage / progress_percentage quedaron como legado y
 * solo se usan para programas sin proceso.
 */
class ApplicationProgress
{
    /** @return array{key: string, label: string, percent: int, finished: bool} */
    public static function for(Application $application): array
    {
        return self::resolve($application);
    }

    /** Postulación que representa al participante en el perfil: la actual, si no la activa más reciente. */
    public static function currentApplication($applications): ?Application
    {
        $applications = collect($applications);

        return $applications->firstWhere('is_current_program', true)
            ?? $applications->sortByDesc('id')->first(fn ($a) => in_array($a->status, Application::ACTIVE_STATUSES, true))
            ?? $applications->sortByDesc('id')->first();
    }

    /** Pisa (en memoria) las columnas legacy para que la API y las vistas viejas muestren el valor real. */
    public static function apply(Application $application): Application
    {
        $p = self::for($application);
        $application->setAttribute('current_stage', $p['key']);
        $application->setAttribute('progress_percentage', $p['percent']);
        $application->setAttribute('current_stage_label', $p['label']);
        // Finalizado por IE: habilita volver a postular al mismo programa (nueva convocatoria).
        $application->setAttribute('is_finished', $p['finished']);

        return $application;
    }

    /** @return array{key: string, label: string, percent: int, finished: bool} */
    private static function resolve(Application $application): array
    {
        $program = $application->program;

        if ($program?->engine_enabled) {
            $process = $application->relationLoaded('programProcess')
                ? $application->programProcess
                : ProgramProcess::where('application_id', $application->id)->first();
            if ($process) {
                $stage = ProgramDefinition::for($program)->stage($process->current_stage_key);

                return [
                    'key' => (string) $process->current_stage_key,
                    'label' => $stage?->label ?? (string) $process->current_stage_key,
                    'percent' => app(ProgressCalculator::class)->percent($process),
                    'finished' => in_array($process->status, [ProgramProcess::STATUS_COMPLETED, ProgramProcess::STATUS_CANCELLED], true),
                ];
            }
        }

        if ($program?->subcategory === Program::SUBCATEGORY_AU_PAIR) {
            $process = AuPairProcess::where('application_id', $application->id)->first();
            if ($process) {
                return [
                    'key' => (string) $process->current_stage,
                    'label' => AuPairProcess::stageLabel((string) $process->current_stage),
                    'percent' => $process->progressPercent(),
                    'finished' => in_array($process->current_stage, ['completed', 'cancelled'], true),
                ];
            }
        }

        $key = $application->current_stage ?: 'registration';

        return [
            'key' => $key,
            'label' => ucfirst(str_replace('_', ' ', $key)),
            'percent' => (int) ($application->progress_percentage ?? 0),
            'finished' => in_array($application->status, ['completed', 'cancelled', 'withdrawn'], true),
        ];
    }
}
