<?php

namespace App\Services\Notifications;

use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\Program;
use App\Models\ProgramProcess;
use App\Models\User;
use App\Services\ProgramEngine\ProgramDefinition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Destinatarios de un aviso especial. Siempre participantes (role=user) con la cuenta de la app vigente.
 *  - all: todos;
 *  - segment: con una postulación visible que cumpla los filtros (programas, estado, etapa y temporada);
 *  - users: personas elegidas.
 */
class CampaignAudience
{
    public const APPLICATION_STATUSES = ['pending' => 'Por revisar', 'approved' => 'Aprobada'];

    public function query(array $audience): Builder
    {
        $query = User::query()->where('role', 'user')->whereNull('app_account_deleted_at');
        $mode = $audience['mode'] ?? 'all';

        if ($mode === 'users') {
            return $query->whereIn('id', array_map('intval', $audience['user_ids'] ?? []) ?: [0]);
        }
        if ($mode !== 'segment') {
            return $query;
        }

        $programIds = array_map('intval', $audience['program_ids'] ?? []);
        $statuses = array_values(array_intersect($audience['application_statuses'] ?? [], array_keys(self::APPLICATION_STATUSES)));
        $stage = count($programIds) === 1 ? ($audience['stage'] ?? null) : null;
        $season = $audience['season'] ?? null;

        return $query->whereHas('applications', function (Builder $app) use ($programIds, $statuses, $stage, $season) {
            $app->visibleToParticipant();
            if ($programIds) {
                $app->whereIn('program_id', $programIds);
            }
            if ($statuses) {
                $app->where(function (Builder $s) use ($statuses) {
                    if (in_array('pending', $statuses, true)) {
                        $s->orWhereIn('status', Application::AWAITING_REVIEW_STATUSES);
                    }
                    if (in_array('approved', $statuses, true)) {
                        $s->orWhere('status', 'approved');
                    }
                });
            }
            if ($stage) {
                $app->where(fn (Builder $p) => $p
                    ->whereHas('programProcess', fn (Builder $q) => $q->where('current_stage_key', $stage))
                    ->orWhereHas('auPairProcess', fn (Builder $q) => $q->where('current_stage', $stage)));
            }
            if ($season) {
                $app->whereHas('programProcess', fn (Builder $q) => $q->where('season', $season));
            }
        });
    }

    public function count(array $audience): int
    {
        return $this->query($audience)->count();
    }

    /** @return Collection<int, int> */
    public function userIds(array $audience): Collection
    {
        return $this->query($audience)->orderBy('id')->pluck('id');
    }

    /** Etapas por programa para el filtro (incluye la terminal: "Finalizado"). @return array<int, array<string,string>> */
    public function stagesByProgram(Collection $programs): array
    {
        $out = [];
        foreach ($programs as $program) {
            if ($program->engine_enabled) {
                $out[$program->id] = ProgramDefinition::for($program)->stages()->mapWithKeys(fn ($s) => [$s->key => $s->label])->all();
            } elseif ($program->subcategory === Program::SUBCATEGORY_AU_PAIR) {
                $out[$program->id] = collect(['admission', 'application', 'match_visa', 'support', 'completed'])
                    ->mapWithKeys(fn ($k) => [$k => AuPairProcess::stageLabel($k)])->all();
            }
        }

        return $out;
    }

    /** @return list<string> temporadas cargadas en los procesos del motor */
    public function seasons(): array
    {
        return ProgramProcess::query()->whereNotNull('season')->where('season', '!=', '')
            ->distinct()->orderByDesc('season')->pluck('season')->all();
    }

    /** Descripción legible del público para el historial. */
    public function describe(array $audience): string
    {
        $mode = $audience['mode'] ?? 'all';
        if ($mode === 'users') {
            $n = count($audience['user_ids'] ?? []);

            return $n === 1 ? '1 participante elegido' : "{$n} participantes elegidos";
        }
        if ($mode !== 'segment') {
            return 'Todos los participantes';
        }
        $parts = [];
        $programIds = $audience['program_ids'] ?? [];
        $parts[] = $programIds ? Program::whereIn('id', $programIds)->pluck('name')->implode(', ') : 'Todos los programas';
        if ($statuses = $audience['application_statuses'] ?? []) {
            $parts[] = 'Postulación '.collect($statuses)->map(fn ($s) => mb_strtolower(self::APPLICATION_STATUSES[$s] ?? $s))->implode(' o ');
        }
        if (($stage = $audience['stage'] ?? null) && count($programIds) === 1) {
            $program = Program::find($programIds[0]);
            $parts[] = 'Etapa: '.($program ? ($this->stagesByProgram(collect([$program]))[$program->id][$stage] ?? $stage) : $stage);
        }
        if ($season = $audience['season'] ?? null) {
            $parts[] = "Temporada {$season}";
        }

        return implode(' · ', $parts);
    }
}
