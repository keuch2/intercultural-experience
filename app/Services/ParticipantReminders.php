<?php

namespace App\Services;

use App\Models\Application;
use App\Models\AuPairDocument;
use App\Models\AuPairProcess;
use App\Models\AuPairVisaProcess;
use App\Models\InstallmentDetail;
use App\Models\ParticipantReminder;
use App\Models\ProgramProcess;
use App\Models\ProgramVisaProcess;
use App\Services\ProgramEngine\DocumentService;
use App\Services\ProgramEngine\Notifier;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Recordatorios diarios al participante (Avisos en la app), motor y Au Pair:
 *  - cuotas a 7/3/1 días y vencidas; fecha límite de pago a 7/1 días con saldo;
 *  - documentos requeridos faltantes o rechazados (semanal);
 *  - cita de visa a 3/1 días.
 * Idempotente por clave en participant_reminders. Inicio/fin del programa los cubre ProgramDatesAutomation.
 */
class ParticipantReminders
{
    public const INSTALLMENT_DAYS = [7, 3, 1];

    public const DEADLINE_DAYS = [7, 1];

    public const VISA_DAYS = [3, 1];

    public const DOCS_EVERY_DAYS = 7;

    private bool $dryRun = false;

    private CarbonImmutable $today;

    private array $counts = [];

    private array $actions = [];

    public function __construct(
        private readonly Notifier $notifier,
        private readonly DocumentService $documents,
        private readonly PaymentPlanService $plans,
    ) {}

    /** @return array{counts: array<string,int>, actions: list<array>} */
    public function run(CarbonInterface $today, bool $dryRun = false): array
    {
        $this->dryRun = $dryRun;
        $this->today = CarbonImmutable::instance($today)->startOfDay();
        $this->counts = [];
        $this->actions = [];

        if (! $dryRun) {
            // Como PaymentInstallment::markOverdueInstallments() pero relativo a la fecha de referencia
            InstallmentDetail::where('status', 'pending')->where('due_date', '<', $this->today->toDateString())->update(['status' => 'overdue']);
        }
        $this->installments();
        $this->paymentDeadlines();
        $this->engineDocuments();
        $this->auPairDocuments();
        $this->visaAppointments();

        ksort($this->counts);

        return ['counts' => $this->counts, 'actions' => $this->actions];
    }

    // ── Pagos ──────────────────────────────────────────────────────────

    private function installments(): void
    {
        $from = $this->today->toDateString();
        $to = $this->today->addDays(max(self::INSTALLMENT_DAYS))->toDateString();
        $details = InstallmentDetail::query()->with('paymentInstallment.currency')
            ->whereHas('paymentInstallment', fn ($q) => $q->where('status', 'active'))
            ->where(fn ($q) => $q->where('status', 'overdue')->orWhere(fn ($w) => $w->where('status', 'pending')->whereBetween('due_date', [$from, $to])))
            ->get();

        foreach ($details as $d) {
            $plan = $d->paymentInstallment;
            if (! $plan?->user_id) {
                continue;
            }
            $amount = ($plan->currency->code ?? '').' '.number_format((float) $d->amount, 2);
            $due = $d->due_date->format('d/m/Y');
            if ($d->status === 'overdue') {
                $this->sendOnce($plan->user_id, "installment:{$d->id}:overdue", 'Cuota vencida', "Tu cuota #{$d->installment_number} de {$amount} venció el {$due}. Registrá el pago desde Pagos o comunicate con IE si ya la pagaste.", 'payment', 'installment_overdue');

                continue;
            }
            $days = $this->daysUntil($d->due_date);
            if (in_array($days, self::INSTALLMENT_DAYS, true)) {
                $this->sendOnce($plan->user_id, "installment:{$d->id}:{$days}d", "Cuota vence en {$days} día(s)", "Tu cuota #{$d->installment_number} de {$amount} vence el {$due}. Registrá el pago desde Pagos.", 'payment', 'installment_due');
            }
        }
    }

    private function paymentDeadlines(): void
    {
        $from = $this->today->toDateString();
        $to = $this->today->addDays(max(self::DEADLINE_DAYS))->toDateString();
        $apps = Application::query()->whereNotNull('payment_deadline')->whereBetween('payment_deadline', [$from, $to])
            ->whereIn('status', Application::ACTIVE_STATUSES)->get();
        foreach ($apps as $app) {
            $days = $this->daysUntil($app->payment_deadline);
            if (! in_array($days, self::DEADLINE_DAYS, true)) {
                continue;
            }
            $summary = $this->plans->summary($app);
            if (($summary['balance'] ?? 0) <= 0) {
                continue;
            }
            $balance = ($summary['currency'] ?? 'USD').' '.number_format((float) $summary['balance'], 2);
            $this->sendOnce($app->user_id, "payment_deadline:{$app->id}:{$days}d", "Fecha límite de pago en {$days} día(s)", "Tu saldo pendiente es {$balance}. La fecha límite de pago es el {$app->payment_deadline->format('d/m/Y')}.", 'payment', 'payment_deadline');
        }
    }

    // ── Documentos ─────────────────────────────────────────────────────

    private function engineDocuments(): void
    {
        ProgramProcess::query()->active()->with(['program', 'application'])->chunkById(100, function ($processes) {
            foreach ($processes as $process) {
                if (optional($process->application)->status !== 'approved' || $this->docsRemindedRecently($process->user_id, "docs:engine:{$process->id}")) {
                    continue;
                }
                $labels = collect($this->documents->describe($process))
                    ->filter(fn ($i) => $i['required'] && $i['unlocked'] && ($i['uploaded_by'] ?? 'participant') === 'participant' && in_array($i['status'], ['missing', 'rejected'], true))
                    ->pluck('label')->values();
                $this->docsReminder($process->user_id, "docs:engine:{$process->id}", $labels);
            }
        });
    }

    private function auPairDocuments(): void
    {
        $stagesFor = ['admission' => ['admission'], 'application' => ['admission', 'application'], 'match_visa' => ['admission', 'application', 'visa'], 'support' => ['admission', 'application', 'visa']];
        AuPairProcess::query()->active()->with(['application', 'documents'])->chunkById(100, function ($processes) use ($stagesFor) {
            foreach ($processes as $process) {
                if (optional($process->application)->status !== 'approved' || $this->docsRemindedRecently($process->user_id, "docs:aupair:{$process->id}")) {
                    continue;
                }
                $labels = collect();
                foreach ($stagesFor[$process->current_stage] ?? [] as $stage) {
                    foreach (AuPairDocument::documentTypesForStage($stage) as $type => $cfg) {
                        if (empty($cfg['required']) || ($cfg['uploaded_by'] ?? 'participant') !== 'participant') {
                            continue;
                        }
                        $docs = $process->documents->where('document_type', $type);
                        $approved = $docs->where('status', 'approved')->count();
                        $needed = max(1, (int) ($cfg['min_count'] ?? 1));
                        $hasPending = $docs->where('status', 'pending')->isNotEmpty();
                        if ($approved < $needed && ! $hasPending) {
                            $labels->push($cfg['label']);
                        }
                    }
                }
                $this->docsReminder($process->user_id, "docs:aupair:{$process->id}", $labels);
            }
        });
    }

    private function docsRemindedRecently(int $userId, string $prefix): bool
    {
        return ParticipantReminder::where('user_id', $userId)->where('key', 'like', $prefix.':%')
            ->where('sent_on', '>', $this->today->subDays(self::DOCS_EVERY_DAYS)->toDateString())->exists();
    }

    private function docsReminder(int $userId, string $prefix, $labels): void
    {
        if ($labels->isEmpty()) {
            return;
        }
        $n = $labels->count();
        $list = $labels->take(3)->implode(', ').($n > 3 ? '…' : '');
        $this->sendOnce($userId, $prefix.':'.$this->today->toDateString(), 'Documentos pendientes', "Tenés {$n} documento(s) requerido(s) pendiente(s) o rechazado(s): {$list}. Subilos desde la app para no demorar tu proceso.", 'documents', 'documents_pending');
    }

    // ── Visa ───────────────────────────────────────────────────────────

    private function visaAppointments(): void
    {
        $from = $this->today->toDateString();
        $to = $this->today->addDays(max(self::VISA_DAYS))->toDateString();
        foreach (ProgramVisaProcess::query()->whereBetween('appointment_date', [$from, $to])->with('process')->get() as $visa) {
            $this->visaReminder($visa->process?->user_id, 'engine', $visa);
        }
        foreach (AuPairVisaProcess::query()->whereBetween('appointment_date', [$from, $to])->with('process')->get() as $visa) {
            $this->visaReminder($visa->process?->user_id, 'aupair', $visa);
        }
    }

    private function visaReminder(?int $userId, string $flow, $visa): void
    {
        if (! $userId) {
            return;
        }
        $days = $this->daysUntil($visa->appointment_date);
        if (! in_array($days, self::VISA_DAYS, true)) {
            return;
        }
        $when = $visa->appointment_date->format('d/m/Y').($visa->appointment_time ? ' a las '.substr((string) $visa->appointment_time, 0, 5) : '');
        $this->sendOnce($userId, "visa:{$flow}:{$visa->id}:{$days}d", "Tu cita de visa es en {$days} día(s)", "Recordá tu cita en la embajada el {$when}. Llevá todos tus documentos.", 'visa', 'visa_appointment');
    }

    // ── Helpers ────────────────────────────────────────────────────────

    private function sendOnce(int $userId, string $key, string $title, string $message, string $category, string $event): bool
    {
        if (ParticipantReminder::where('user_id', $userId)->where('key', $key)->exists()) {
            return false;
        }
        $this->counts[$event] = ($this->counts[$event] ?? 0) + 1;
        $this->actions[] = ['event' => $event, 'user' => $userId, 'key' => $key, 'title' => $title];
        if ($this->dryRun) {
            return true;
        }
        $this->notifier->toUser($userId, $title, $message, $category);
        ParticipantReminder::create(['user_id' => $userId, 'key' => $key, 'sent_on' => $this->today->toDateString()]);

        return true;
    }

    private function daysUntil(CarbonInterface $date): int
    {
        $target = CarbonImmutable::create($date->year, $date->month, $date->day, 0, 0, 0, $this->today->getTimezone());

        return (int) round($this->today->diffInDays($target, false));
    }
}
