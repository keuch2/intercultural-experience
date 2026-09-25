<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\AuPairProcess;
use App\Models\ProgramProcess;
use App\Services\ProgramEngine\Exceptions\StageTransitionException;
use App\Services\ProgramEngine\ModuleCatalog;
use App\Services\ProgramEngine\Notifier;
use App\Services\ProgramEngine\ProgramDefinition;
use App\Services\ProgramEngine\StageAdvancer;
use App\Services\ProgramEngine\StageEvaluator;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Automatización por fechas del programa (motor y Au Pair), pensada para correr a diario:
 *  - start_soon:  7 días antes del inicio → alerta a IE.
 *  - start_today: el día del inicio → pasa a Support si está en la etapa previa y cumple los
 *                 requisitos; si no, NO lo mueve y avisa a IE qué falta.
 *  - end_soon:    7 días antes del fin → alerta a IE.
 *  - end_today:   el día del fin → alerta a IE para registrar la finalización desde Support.
 * Cada evento se marca en automation_flags (idempotente); las ventanas toleran días sin cron.
 */
class ProgramDatesAutomation
{
    public const CATEGORY = 'program_dates';

    public const DAYS_BEFORE = 7;

    public const FLAG_START_SOON = 'start_alert_sent_at';

    public const FLAG_STARTED = 'started_handled_at';

    public const FLAG_AUTO_ADVANCED = 'auto_advanced_at';

    public const FLAG_END_SOON = 'end_alert_sent_at';

    public const FLAG_ENDED = 'ended_alert_sent_at';

    /** @var list<array{scope:string,event:string,process:int,user:string,detail:string}> */
    private array $actions = [];

    private array $counts = [];

    private bool $dryRun = false;

    public function __construct(
        private readonly StageAdvancer $advancer,
        private readonly StageEvaluator $evaluator,
        private readonly Notifier $notifier,
    ) {}

    /** @return array{counts: array<string,int>, actions: list<array>} */
    public function run(CarbonInterface $today, bool $dryRun = false): array
    {
        $this->dryRun = $dryRun;
        $this->actions = [];
        $this->counts = [];
        $today = CarbonImmutable::instance($today)->startOfDay();

        $this->engineStartSoon($today);
        $this->engineStartToday($today);
        $this->engineEndSoon($today);
        $this->engineEndToday($today);

        $this->auPairStartSoon($today);
        $this->auPairStartToday($today);
        $this->auPairEndSoon($today);
        $this->auPairEndToday($today);

        ksort($this->counts);

        return ['counts' => $this->counts, 'actions' => $this->actions];
    }

    // ── Motor de programas ─────────────────────────────────────────────

    private function engineStartSoon(CarbonImmutable $today): void
    {
        $q = ProgramProcess::query()->active()->with(['user', 'program'])
            ->whereBetween('program_start_date', [$today->addDay()->toDateString(), $today->addDays(self::DAYS_BEFORE)->toDateString()])
            ->whereNull('automation_flags->'.self::FLAG_START_SOON);
        foreach ($q->get() as $p) {
            $days = $this->daysUntil($today, $p->program_start_date);
            $this->notify('engine', 'start_soon', $p,
                "Inicio de programa en {$days} días: {$p->user?->name}",
                "{$p->user?->name} ({$p->program?->name}) inicia el programa el {$this->d($p->program_start_date)}. Verificá visa, documentos y viaje para que el paso automático a Support se realice ese día. ".$this->engineUrl($p, 'visa'),
                self::FLAG_START_SOON, $this->participantStartSoon($days, $p->program?->name ?? 'programa', $this->d($p->program_start_date)));
        }
    }

    private function engineStartToday(CarbonImmutable $today): void
    {
        $q = ProgramProcess::query()->active()->with(['user', 'program'])
            ->whereBetween('program_start_date', [$today->subDays(self::DAYS_BEFORE)->toDateString(), $today->toDateString()])
            ->whereNull('automation_flags->'.self::FLAG_STARTED);
        foreach ($q->get() as $p) {
            $name = $p->user?->name;
            $prog = $p->program?->name;
            $date = $this->d($p->program_start_date);
            $definition = ProgramDefinition::for($p->program);
            $support = $definition->stages()->first(fn ($s) => $s->key === 'support' || $s->mobile_screen === ModuleCatalog::get(ModuleCatalog::SUPPORT)['mobile_screen']);

            if (! $support) {
                $this->notify('engine', 'start_no_support', $p, "Inicio de programa: {$name}",
                    "{$name} ({$prog}) inició el programa el {$date}. El programa no tiene etapa Support configurada; revisá el proceso. ".$this->engineUrl($p), self::FLAG_STARTED);

                continue;
            }
            if ($p->current_stage_key === $support->key) {
                $this->mark($p, self::FLAG_STARTED, 'engine', 'already_in_support', 'ya estaba en Support');

                continue;
            }

            $prev = $definition->stages()->get($definition->stageIndex($support->key) - 1);
            $reasons = $prev && $p->current_stage_key === $prev->key
                ? $this->evaluator->blockingReasons($p)
                : ['Está en la etapa "'.($definition->stage($p->current_stage_key)?->label ?? $p->current_stage_key).'", no en "'.($prev?->label ?? '—').'" (la previa a Support).'];

            if (empty($reasons)) {
                if (! $this->dryRun) {
                    try {
                        $this->advancer->advance($p, null, false);
                    } catch (StageTransitionException $e) {
                        $reasons = $e->reasons ?? [$e->getMessage()];
                    }
                }
                if (empty($reasons)) {
                    $this->record('engine', 'auto_advanced', $p, "pasa a {$support->label}");
                    if (! $this->dryRun) {
                        $this->log($p->program?->slug ?? 'program_engine', $p->user, ['program_process_id' => $p->id, 'from' => $prev->key, 'to' => $support->key, 'program_start_date' => $p->program_start_date->toDateString()],
                            "Avance automático a '{$support->label}' por inicio del programa ({$date})");
                        $p->markAutomation(self::FLAG_AUTO_ADVANCED);
                        $p->markAutomation(self::FLAG_STARTED);
                        $this->notifier->toAdmins("Avance automático a Support: {$name}",
                            "{$name} ({$prog}) inició el programa el {$date} y pasó automáticamente a la etapa {$support->label}. ".$this->engineUrl($p, 'support'), self::CATEGORY);
                    }

                    continue;
                }
            }

            $this->notify('engine', 'start_blocked', $p, "Inicio de programa sin avance: {$name}",
                "{$name} ({$prog}) inició el programa el {$date} pero no pudo pasar a Support. Pendiente: ".implode(' ', $reasons).' Resolvé lo pendiente y avanzá la etapa manualmente. '.$this->engineUrl($p, $p->current_stage_key),
                self::FLAG_STARTED);
        }
    }

    private function engineEndSoon(CarbonImmutable $today): void
    {
        $q = ProgramProcess::query()->active()->with(['user', 'program'])
            ->whereBetween('program_end_date', [$today->addDay()->toDateString(), $today->addDays(self::DAYS_BEFORE)->toDateString()])
            ->whereNull('automation_flags->'.self::FLAG_END_SOON);
        foreach ($q->get() as $p) {
            $days = $this->daysUntil($today, $p->program_end_date);
            $this->notify('engine', 'end_soon', $p, "Fin de programa en {$days} días: {$p->user?->name}",
                "El programa de {$p->user?->name} ({$p->program?->name}) termina el {$this->d($p->program_end_date)}. Coordiná el cierre y la evaluación final desde Support. ".$this->engineUrl($p, 'support'),
                self::FLAG_END_SOON, $this->participantEndSoon($days, $p->program?->name ?? 'programa', $this->d($p->program_end_date)));
        }
    }

    private function engineEndToday(CarbonImmutable $today): void
    {
        $q = ProgramProcess::query()->active()->with(['user', 'program'])
            ->whereBetween('program_end_date', [$today->subDays(self::DAYS_BEFORE)->toDateString(), $today->toDateString()])
            ->whereNull('automation_flags->'.self::FLAG_ENDED);
        foreach ($q->get() as $p) {
            $this->notify('engine', 'end_today', $p, "Fin de programa: {$p->user?->name}",
                "El programa de {$p->user?->name} ({$p->program?->name}) terminó el {$this->d($p->program_end_date)}. Registrá la finalización desde el tab Support. ".$this->engineUrl($p, 'support'),
                self::FLAG_ENDED, $this->participantEnded($p->program?->name ?? 'programa', $this->d($p->program_end_date)));
        }
    }

    // ── Au Pair ────────────────────────────────────────────────────────

    private function auPairStartSoon(CarbonImmutable $today): void
    {
        $q = AuPairProcess::query()->active()->with('user')
            ->whereBetween('program_start_date', [$today->addDay()->toDateString(), $today->addDays(self::DAYS_BEFORE)->toDateString()])
            ->whereNull('automation_flags->'.self::FLAG_START_SOON);
        foreach ($q->get() as $p) {
            $days = $this->daysUntil($today, $p->program_start_date);
            $this->notify('au_pair', 'start_soon', $p, "Inicio de programa en {$days} días: {$p->user?->name}",
                "{$p->user?->name} (Au Pair) inicia el programa el {$this->d($p->program_start_date)}. Verificá visa, documentos y viaje para que el paso automático a Support se realice ese día. ".$this->auPairUrl($p, 'match_visa'),
                self::FLAG_START_SOON, $this->participantStartSoon($days, 'Au Pair', $this->d($p->program_start_date)));
        }
    }

    private function auPairStartToday(CarbonImmutable $today): void
    {
        $q = AuPairProcess::query()->active()->with(['user', 'visaProcess'])
            ->whereBetween('program_start_date', [$today->subDays(self::DAYS_BEFORE)->toDateString(), $today->toDateString()])
            ->whereNull('automation_flags->'.self::FLAG_STARTED);
        foreach ($q->get() as $p) {
            $name = $p->user?->name;
            $date = $this->d($p->program_start_date);

            if (in_array($p->current_stage, ['support', 'completed'], true)) {
                $this->mark($p, self::FLAG_STARTED, 'au_pair', 'already_in_support', 'ya estaba en Support');

                continue;
            }

            $reasons = $p->current_stage === 'match_visa'
                ? $p->supportBlockingReasons()
                : ['Está en la etapa "'.$p->current_stage.'", no en "match_visa" (la previa a Support).'];

            if (empty($reasons)) {
                $this->record('au_pair', 'auto_advanced', $p, 'pasa a Support');
                if (! $this->dryRun && $p->advanceStage()) {
                    $this->log('au_pair', $p->user, ['from' => 'match_visa', 'to' => 'support', 'program_start_date' => $p->program_start_date->toDateString()],
                        "Avance automático a 'Support' por inicio del programa ({$date})");
                    $p->markAutomation(self::FLAG_AUTO_ADVANCED);
                    $p->markAutomation(self::FLAG_STARTED);
                    $this->notifier->toUser($p->user_id, 'Tu proceso avanzó: Support',
                        'Tu programa Au Pair comenzó y tu proceso pasó a la etapa "Support". Tu coordinador te acompaña desde ahora; revisá la app.', 'program_stage');
                    $this->notifier->toAdmins("Avance automático a Support: {$name}",
                        "{$name} (Au Pair) inició el programa el {$date} y pasó automáticamente a la etapa Support. ".$this->auPairUrl($p, 'support'), self::CATEGORY);
                }

                continue;
            }

            $this->notify('au_pair', 'start_blocked', $p, "Inicio de programa sin avance: {$name}",
                "{$name} (Au Pair) inició el programa el {$date} pero no pudo pasar a Support. Pendiente: ".implode(' ', $reasons).' Resolvé lo pendiente y avanzá la etapa manualmente. '.$this->auPairUrl($p, 'match_visa'),
                self::FLAG_STARTED);
        }
    }

    private function auPairEndSoon(CarbonImmutable $today): void
    {
        $q = AuPairProcess::query()->active()->with('user')
            ->whereBetween('program_end_date', [$today->addDay()->toDateString(), $today->addDays(self::DAYS_BEFORE)->toDateString()])
            ->whereNull('automation_flags->'.self::FLAG_END_SOON);
        foreach ($q->get() as $p) {
            $days = $this->daysUntil($today, $p->program_end_date);
            $this->notify('au_pair', 'end_soon', $p, "Fin de programa en {$days} días: {$p->user?->name}",
                "El programa de {$p->user?->name} (Au Pair) termina el {$this->d($p->program_end_date)}. Coordiná el cierre y la evaluación final desde Support. ".$this->auPairUrl($p, 'support'),
                self::FLAG_END_SOON, $this->participantEndSoon($days, 'Au Pair', $this->d($p->program_end_date)));
        }
    }

    private function auPairEndToday(CarbonImmutable $today): void
    {
        $q = AuPairProcess::query()->active()->with('user')
            ->whereBetween('program_end_date', [$today->subDays(self::DAYS_BEFORE)->toDateString(), $today->toDateString()])
            ->whereNull('automation_flags->'.self::FLAG_ENDED);
        foreach ($q->get() as $p) {
            $this->notify('au_pair', 'end_today', $p, "Fin de programa: {$p->user?->name}",
                "El programa de {$p->user?->name} (Au Pair) terminó el {$this->d($p->program_end_date)}. Registrá la finalización desde el tab Support. ".$this->auPairUrl($p, 'support'),
                self::FLAG_ENDED, $this->participantEnded('Au Pair', $this->d($p->program_end_date)));
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────

    /** Alerta a IE + marca del flag (no escribe nada en dry-run). */
    private function notify(string $scope, string $event, ProgramProcess|AuPairProcess $p, string $title, string $message, string $flag, ?array $participant = null): void
    {
        $this->record($scope, $event, $p, $title);
        if ($this->dryRun) {
            return;
        }
        $this->notifier->toAdmins($title, $message, self::CATEGORY);
        if ($participant && $p->user_id) {
            $this->notifier->toUser($p->user_id, $participant[0], $participant[1], self::CATEGORY);
        }
        $p->markAutomation($flag);
    }

    /** Textos para el participante (mismo flag de idempotencia que la alerta a IE). */
    private function participantStartSoon(int $days, string $program, string $date): array
    {
        return ["Tu programa empieza en {$days} día(s)", "Tu programa {$program} inicia el {$date}. Revisá que tengas todo listo: documentos, visa y viaje."];
    }

    private function participantEndSoon(int $days, string $program, string $date): array
    {
        return ["Tu programa termina en {$days} día(s)", "Tu programa {$program} termina el {$date}. Tu coordinador te contactará para el cierre."];
    }

    private function participantEnded(string $program, string $date): array
    {
        return ['Tu programa finalizó', "Tu programa {$program} terminó el {$date}. ¡Gracias por participar! IE te contactará para la evaluación final."];
    }

    private function mark(ProgramProcess|AuPairProcess $p, string $flag, string $scope, string $event, string $detail): void
    {
        $this->record($scope, $event, $p, $detail);
        if (! $this->dryRun) {
            $p->markAutomation($flag);
        }
    }

    private function record(string $scope, string $event, ProgramProcess|AuPairProcess $p, string $detail): void
    {
        $this->counts["{$scope}.{$event}"] = ($this->counts["{$scope}.{$event}"] ?? 0) + 1;
        $this->actions[] = ['scope' => $scope, 'event' => $event, 'process' => $p->id, 'user' => $p->user?->name ?? "user #{$p->user_id}", 'detail' => $detail];
    }

    private function log(string $logName, $user, array $properties, string $description): void
    {
        $builder = ActivityLog::log($logName)->withAction('stage_advanced_auto')->withProperties($properties);
        if ($user) {
            $builder->performedOn($user);
        }
        $builder->log($description);
    }

    /** Días calendario entre hoy y la fecha (solo fecha, sin horas ni zonas). */
    private function daysUntil(CarbonImmutable $today, CarbonInterface $date): int
    {
        $target = CarbonImmutable::create($date->year, $date->month, $date->day, 0, 0, 0, $today->getTimezone());

        return (int) round($today->diffInDays($target, false));
    }

    private function d(?CarbonInterface $date): string
    {
        return $date?->format('d/m/Y') ?? '—';
    }

    private function engineUrl(ProgramProcess $p, ?string $tab = null): string
    {
        $slug = $p->program?->slug;

        return $slug ? route('admin.program.participants.show', array_filter(['program' => $slug, 'process' => $p->id, 'tab' => $tab])) : '';
    }

    private function auPairUrl(AuPairProcess $p, string $tab): string
    {
        return route('admin.aupair.profiles.show', ['id' => $p->user_id, 'tab' => $tab]);
    }
}
