<?php

namespace App\Listeners\ProgramEngine;

use App\Events\ProgramEngine\JobPoolAssignmentReleased;
use App\Events\ProgramEngine\JobPoolOfferExhausted;
use App\Events\ProgramEngine\JobPoolOfferPublished;
use App\Events\ProgramEngine\JobPoolOfferSelected;
use App\Models\ProgramProcess;
use App\Services\ProgramEngine\ModuleCatalog;
use App\Services\ProgramEngine\Notifier;
use Illuminate\Events\Dispatcher;

/**
 * Notificaciones in-app del Pool de Ofertas (spec W&T):
 *  - IE publica una oferta → participantes habilitados
 *  - participante selecciona → participante + admins
 *  - IE libera una asignación → participante
 *  - una oferta se queda sin cupo → admins
 */
class SendJobPoolNotifications
{
    public const CATEGORY = 'job_pool';

    public function __construct(private readonly Notifier $notifier) {}

    public function subscribe(Dispatcher $events): array
    {
        return [
            JobPoolOfferPublished::class => 'onPublished',
            JobPoolOfferSelected::class => 'onSelected',
            JobPoolAssignmentReleased::class => 'onReleased',
            JobPoolOfferExhausted::class => 'onExhausted',
        ];
    }

    public function onPublished(JobPoolOfferPublished $event): void
    {
        $offer = $event->offer;
        $this->notifier->toProgramParticipants(
            $offer->program,
            'Nueva oferta laboral disponible',
            "{$offer->headline} — {$offer->city}, {$offer->state} ({$offer->positions_available} posiciones). Revisá el Pool de Ofertas en la app.",
            self::CATEGORY,
            fn (ProgramProcess $p) => $p->hasModuleAccess(ModuleCatalog::JOB_POOL) && ! $p->activeJobAssignment()->exists(),
        );
    }

    public function onSelected(JobPoolOfferSelected $event): void
    {
        $a = $event->assignment->loadMissing(['offer', 'process.user']);
        $offer = $a->offer;
        $this->notifier->toUser($a->process->user_id, 'Oferta laboral seleccionada', "Confirmamos tu selección: {$offer->headline} — {$offer->city}, {$offer->state}. El equipo IE continuará con tu Job Placement.", self::CATEGORY);
        $this->notifier->toAdmins('Selección de oferta laboral', "{$a->process->user?->name} seleccionó la oferta {$offer->headline} ({$offer->city}, {$offer->state}). Cupos restantes: {$offer->positions_available}.", self::CATEGORY);
    }

    public function onReleased(JobPoolAssignmentReleased $event): void
    {
        $a = $event->assignment->loadMissing(['offer', 'process']);
        $offer = $a->offer;
        $this->notifier->toUser($a->process->user_id, 'Asignación de oferta liberada', "El equipo IE liberó tu asignación a {$offer->headline} ({$offer->city}, {$offer->state}).".($event->reason ? " Motivo: {$event->reason}." : '').' Podés volver a elegir en el Pool de Ofertas.', self::CATEGORY);
    }

    public function onExhausted(JobPoolOfferExhausted $event): void
    {
        $offer = $event->offer;
        $this->notifier->toAdmins('Oferta sin posiciones disponibles', "La oferta {$offer->headline} ({$offer->city}, {$offer->state}) completó sus {$offer->positions_total} posiciones y dejó de mostrarse en la app.", self::CATEGORY);
    }
}
