<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Application;
use App\Models\AuPairProcess;
use App\Models\Program;
use App\Models\User;
use App\Services\ProgramEngine\Notifier;
use App\Services\ProgramEngine\ProcessResolver;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Decisión de IE sobre una solicitud de acceso a un programa (pedido de IE, nov 2026):
 *  - el proceso del programa (motor o Au Pair) se crea recién al APROBAR;
 *  - al RECHAZAR la postulación queda como historial (no se muestra en la app), se borra el
 *    proceso que hubiera quedado de antes y la persona puede volver a postular;
 *  - en ambos casos el participante recibe un aviso.
 */
class ApplicationReviewService
{
    public const CATEGORY = 'application';

    public function __construct(
        private readonly ProcessResolver $resolver,
        private readonly ApplicationDeletionService $deletion,
        private readonly Notifier $notifier,
    ) {}

    public function approve(Application $application, ?User $actor, bool $notify = true): Application
    {
        if ($application->status === 'rejected') {
            throw new InvalidArgumentException('La solicitud fue rechazada. La persona debe volver a postular.');
        }

        DB::transaction(function () use ($application, $actor) {
            $application->forceFill([
                'status' => 'approved',
                'reviewed_at' => now(),
                'reviewed_by' => $actor?->id,
                'rejection_reason' => null,
            ])->save();

            $this->createProcess($application->loadMissing(['program', 'user']));
            $this->log($application, $actor, 'application_approved', 'Solicitud aprobada');
        });

        if ($notify) {
            $program = $application->program?->name ?? 'el programa';
            $this->notifier->toUser(
                $application->user_id,
                'Tu postulación fue aprobada',
                "¡Felicitaciones! IE aprobó tu acceso a {$program}. Ya podés continuar con tu proceso desde la app.",
                self::CATEGORY,
            );
        }

        return $application;
    }

    public function reject(Application $application, ?User $actor, ?string $reason = null): Application
    {
        if ($application->status === 'approved') {
            throw new InvalidArgumentException('La solicitud ya está aprobada. Para darla de baja, eliminá la postulación.');
        }
        $reason = trim((string) $reason) ?: null;

        DB::transaction(function () use ($application, $actor, $reason) {
            // Nada del proceso debe quedar: puede existir de antes de este cambio (se creaba al postular).
            $this->deletion->deleteProcesses($application, $actor);

            $application->forceFill([
                'status' => 'rejected',
                'reviewed_at' => now(),
                'reviewed_by' => $actor?->id,
                'rejection_reason' => $reason,
                'is_current_program' => false,
            ])->save();

            $this->log($application, $actor, 'application_rejected', 'Solicitud rechazada', ['reason' => $reason]);
        });

        $program = $application->program?->name ?? 'el programa';
        $this->notifier->toUser(
            $application->user_id,
            'Tu postulación no fue aprobada',
            "Tu solicitud de acceso a {$program} no fue aprobada.".($reason ? " Motivo: {$reason}." : '')
                .' Si tenés dudas, escribinos por WhatsApp.',
            self::CATEGORY,
        );

        return $application;
    }

    /** Aviso a IE cuando llega una solicitud nueva desde la app. */
    public function notifyStaffOfNewApplication(Application $application): void
    {
        $application->loadMissing(['user', 'program']);
        $this->notifier->toAdmins(
            'Nueva solicitud de acceso',
            "{$application->user?->name} solicitó acceso a {$application->program?->name}. Revisala en Solicitudes.",
            self::CATEGORY,
        );
    }

    private function createProcess(Application $application): void
    {
        $program = $application->program ?? Program::find($application->program_id);

        if ($program?->engine_enabled) {
            $this->resolver->forApplication($application->setRelation('program', $program));

            return;
        }

        if ($program?->subcategory === Program::SUBCATEGORY_AU_PAIR) {
            AuPairProcess::firstOrCreate(['application_id' => $application->id], AuPairProcess::initialAttributes($application));
        }
    }

    private function log(Application $application, ?User $actor, string $action, string $description, array $extra = []): void
    {
        $log = ActivityLog::log('participants')->withAction($action)
            ->withProperties(['application_id' => $application->id, 'program' => $application->program?->name] + $extra);
        if ($application->user) {
            $log->performedOn($application->user);
        }
        if ($actor) {
            $log->causedBy($actor);
        }
        $log->log("{$description}: {$application->user?->name} — {$application->program?->name}");
    }
}
