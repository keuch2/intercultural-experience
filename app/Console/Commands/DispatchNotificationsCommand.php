<?php

namespace App\Console\Commands;

use App\Services\Notifications\CampaignSender;
use App\Services\Notifications\PushSender;
use Illuminate\Console\Command;

/**
 * Corre cada minuto (schedule): envía los avisos especiales programados que vencieron, las push pendientes
 * y procesa los recibos de Expo (borra tokens de dispositivos que ya no tienen la app).
 */
class DispatchNotificationsCommand extends Command
{
    protected $signature = 'notifications:dispatch';

    protected $description = 'Envía avisos especiales programados y notificaciones push pendientes (Expo)';

    public function handle(CampaignSender $campaigns, PushSender $push): int
    {
        $sentCampaigns = $campaigns->sendDue();
        $sent = $push->sendPending();
        $receipts = $push->checkReceipts();

        $summary = array_filter([
            'campañas' => $sentCampaigns,
            'push enviadas' => $sent['sent'],
            'push con error' => $sent['failed'],
            'sin dispositivo' => $sent['no_device'],
            'a reintentar' => $sent['retry'],
            'vencidas' => $sent['expired'],
            'recibos' => $receipts['checked'],
            'recibos con error' => $receipts['errors'],
            'tokens borrados' => $receipts['removed_tokens'],
        ]);
        if ($summary) {
            $this->line(now()->format('Y-m-d H:i').' '.collect($summary)->map(fn ($v, $k) => "{$k}: {$v}")->implode(' · '));
        }

        return self::SUCCESS;
    }
}
