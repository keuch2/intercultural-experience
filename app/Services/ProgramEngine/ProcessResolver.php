<?php

namespace App\Services\ProgramEngine;

use App\Models\Application;
use App\Models\Program;
use App\Models\ProgramProcess;
use App\Models\User;
use RuntimeException;

/**
 * Resuelve (o crea) el ProgramProcess de una postulación. Espejo genérico de
 * ResolvesAuPairProcess.
 */
class ProcessResolver
{
    public function forApplication(Application $application): ProgramProcess
    {
        $program = $application->program ?? Program::findOrFail($application->program_id);
        $definition = ProgramDefinition::for($program);
        $first = $definition->firstStage();

        if (! $first) {
            throw new RuntimeException("El programa '{$program->name}' no tiene etapas configuradas.");
        }

        $process = ProgramProcess::firstOrCreate(
            ['application_id' => $application->id],
            [
                'program_id' => $program->id,
                'user_id' => $application->user_id,
                'current_stage_key' => $first->key,
                'status' => ProgramProcess::STATUS_ACTIVE,
                'stage_states' => $this->initialStageStates($definition),
                'season' => $this->defaultSeason($program),
                'enrollment_date' => ($application->applied_at ?? $application->created_at)?->toDateString(),
            ]
        );

        $this->syncRows($process, $definition);

        return $process;
    }

    /**
     * Crea el proceso del motor si la postulación pertenece a un programa con motor
     * habilitado (p. ej. cuando el admin asigna Work & Travel desde Participantes).
     * Devuelve null para programas legacy o sin etapas configuradas.
     */
    public function ensureForApplication(Application $application): ?ProgramProcess
    {
        $program = $application->program ?? Program::find($application->program_id);
        if (! $program || ! $program->engine_enabled) {
            return null;
        }

        try {
            return $this->forApplication($application->setRelation('program', $program));
        } catch (RuntimeException $e) {
            report($e);

            return null;
        }
    }

    /**
     * Última postulación del usuario en un programa del motor (opcionalmente uno específico).
     */
    public function latestForUser(User $user, ?Program $program = null): ?ProgramProcess
    {
        $application = Application::query()
            ->where('user_id', $user->id)
            ->when($program, fn ($q) => $q->where('program_id', $program->id))
            ->whereHas('program', fn ($q) => $q->where('engine_enabled', true))
            ->latest('id')
            ->first();

        return $application ? $this->forApplication($application) : null;
    }

    /**
     * Garantiza que existan filas de gates y checklist para cada ítem configurado
     * (idempotente). Facilita el admin; la evaluación tolera filas ausentes.
     */
    public function syncRows(ProgramProcess $process, ?ProgramDefinition $definition = null): void
    {
        $definition ??= ProgramDefinition::for($process->program);

        foreach ($definition->gates() as $gate) {
            $process->gates()->firstOrCreate(['gate_key' => $gate->key]);
        }
        foreach ($definition->checklist() as $item) {
            $process->checklist()->firstOrCreate(['item_key' => $item->key]);
        }
        $process->unsetRelation('gates')->unsetRelation('checklist');
    }

    private function initialStageStates(ProgramDefinition $definition): array
    {
        $states = [];
        foreach ($definition->stages() as $i => $stage) {
            $states[$stage->key] = $i === 0
                ? ['status' => ProgramProcess::STAGE_IN_PROGRESS, 'entered_at' => now()->toIso8601String()]
                : ['status' => ProgramProcess::STAGE_LOCKED];
        }

        return $states;
    }

    private function defaultSeason(Program $program): string
    {
        $year = $program->start_date?->year ?? now()->year;

        return (string) $year;
    }
}
