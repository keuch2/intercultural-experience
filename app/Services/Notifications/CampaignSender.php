<?php

namespace App\Services\Notifications;

use App\Models\Notification;
use App\Models\NotificationCampaign;
use Illuminate\Support\Facades\DB;

/**
 * Crea los avisos de una campaña (uno por destinatario, con su estado de push). La push la envía PushSender
 * en la siguiente corrida de `notifications:dispatch`.
 */
class CampaignSender
{
    public const TYPE = 'campaign';

    public const CATEGORY = 'news';

    public function __construct(
        private readonly CampaignAudience $audience,
        private readonly PushPolicy $policy,
    ) {}

    /** Envía las campañas programadas que ya vencieron. */
    public function sendDue(): int
    {
        $sent = 0;
        foreach (NotificationCampaign::query()->due()->orderBy('id')->get() as $campaign) {
            if ($this->send($campaign)) {
                $sent++;
            }
        }

        return $sent;
    }

    public function send(NotificationCampaign $campaign): bool
    {
        // Reclamo atómico: evita doble envío si dos procesos toman la misma campaña.
        $claimed = NotificationCampaign::query()->whereKey($campaign->id)->where('status', NotificationCampaign::STATUS_SCHEDULED)
            ->update(['status' => NotificationCampaign::STATUS_SENDING, 'updated_at' => now()]);
        if (! $claimed) {
            return false;
        }

        $data = $campaign->linkData() ?: null;
        $count = 0;
        $now = now();
        foreach ($this->audience->userIds($campaign->audience ?? [])->chunk(500) as $chunk) {
            $ids = $chunk->values()->all();
            $push = $campaign->channel === 'push' ? $this->policy->statuses($ids, self::TYPE, $campaign->is_important) : [];
            $rows = array_map(fn ($userId) => [
                'user_id' => $userId,
                'title' => $campaign->title,
                'message' => $campaign->message,
                'category' => self::CATEGORY,
                'notification_type' => self::TYPE,
                'data' => $data ? json_encode($data) : null,
                'campaign_id' => $campaign->id,
                'push_status' => $push[$userId] ?? null,
                'is_read' => false,
                'created_at' => $now,
            ], $ids);
            DB::transaction(fn () => Notification::insert($rows));
            $count += count($rows);
        }

        $campaign->forceFill(['status' => NotificationCampaign::STATUS_SENT, 'sent_at' => now(), 'recipients_count' => $count])->save();

        return true;
    }

    /** Resumen para el historial: destinatarios, push enviadas/errores, leídos. */
    public function stats(NotificationCampaign $campaign): array
    {
        $byStatus = Notification::query()->where('campaign_id', $campaign->id)
            ->selectRaw('push_status, count(*) as total')->groupBy('push_status')->pluck('total', 'push_status');

        return [
            'recipients' => $campaign->recipients_count,
            'read' => Notification::query()->where('campaign_id', $campaign->id)->where('is_read', true)->count(),
            'push_sent' => (int) ($byStatus['sent'] ?? 0),
            'push_pending' => (int) (($byStatus['pending'] ?? 0) + ($byStatus['sending'] ?? 0)),
            'push_failed' => (int) (($byStatus['failed'] ?? 0) + ($byStatus['expired'] ?? 0)),
            'no_device' => (int) ($byStatus['no_device'] ?? 0),
            'muted' => (int) ($byStatus['muted'] ?? 0),
        ];
    }
}
