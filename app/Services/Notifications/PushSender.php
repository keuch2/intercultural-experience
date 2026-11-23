<?php

namespace App\Services\Notifications;

use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\PushDelivery;
use App\Support\NotificationCatalog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Envía las push pendientes (notifications.push_status = pending) a todos los dispositivos del participante
 * y procesa los recibos de Expo. Lo corre `notifications:dispatch` cada minuto (sin cola).
 */
class PushSender
{
    /** Una push que no salió en este tiempo ya no se envía (el aviso sigue en la app). */
    public const EXPIRE_AFTER_HOURS = 24;

    /** Expo recomienda consultar los recibos ~15 minutos después del envío. */
    public const RECEIPT_DELAY_MINUTES = 15;

    public function __construct(private readonly ExpoPushClient $client) {}

    /** @return array{sent: int, failed: int, no_device: int, retry: int, expired: int} */
    public function sendPending(int $limit = 1000): array
    {
        $stats = ['sent' => 0, 'failed' => 0, 'no_device' => 0, 'retry' => 0];
        $stats['expired'] = Notification::query()->whereIn('push_status', ['pending', 'sending'])
            ->where('created_at', '<', now()->subHours(self::EXPIRE_AFTER_HOURS))->update(['push_status' => 'expired']);

        $ids = Notification::query()->where('push_status', 'pending')->orderBy('id')->limit($limit)->pluck('id');
        if ($ids->isEmpty()) {
            return $stats;
        }
        Notification::query()->whereIn('id', $ids)->where('push_status', 'pending')->update(['push_status' => 'sending']);
        $notifications = Notification::query()->whereIn('id', $ids)->where('push_status', 'sending')->get()->keyBy('id');
        $tokens = DeviceToken::query()->whereIn('user_id', $notifications->pluck('user_id')->unique())->get()->groupBy('user_id');

        $queue = [];
        foreach ($notifications as $n) {
            foreach ($tokens[$n->user_id] ?? [] as $token) {
                $queue[] = ['notification' => $n, 'token' => $token, 'message' => $this->message($n, $token)];
            }
        }

        $outcome = []; // notification_id => list<ok|error|request_failed>
        foreach (array_chunk($queue, ExpoPushClient::BATCH) as $chunk) {
            try {
                $tickets = $this->client->send(array_column($chunk, 'message'));
            } catch (Throwable $e) {
                Log::warning('Expo push: falló el envío del lote', ['error' => $e->getMessage()]);
                foreach ($chunk as $item) {
                    $outcome[$item['notification']->id][] = 'request_failed';
                }

                continue;
            }
            foreach ($chunk as $i => $item) {
                $outcome[$item['notification']->id][] = $this->recordTicket($item['notification'], $item['token'], $tickets[$i] ?? null);
            }
        }

        foreach ($notifications as $n) {
            $results = $outcome[$n->id] ?? [];
            $status = match (true) {
                $results === [] => 'no_device',
                in_array('ok', $results, true) => 'sent',
                ! in_array('error', $results, true) => 'pending', // solo fallas de red: se reintenta en la próxima corrida
                default => 'failed',
            };
            $n->forceFill(['push_status' => $status, 'pushed_at' => $status === 'pending' ? null : now()])->save();
            $stats[$status === 'pending' ? 'retry' : $status]++;
        }

        return $stats;
    }

    /** @return array{checked: int, errors: int, removed_tokens: int} */
    public function checkReceipts(int $limit = 3000): array
    {
        $stats = ['checked' => 0, 'errors' => 0, 'removed_tokens' => 0];
        $deliveries = PushDelivery::query()->where('status', 'ok')->whereNull('receipt_status')->whereNotNull('ticket_id')
            ->where('created_at', '<=', now()->subMinutes(self::RECEIPT_DELAY_MINUTES))->orderBy('id')->limit($limit)->get();

        foreach ($deliveries->chunk(1000) as $chunk) {
            try {
                $receipts = $this->client->receipts($chunk->pluck('ticket_id')->all());
            } catch (Throwable $e) {
                Log::warning('Expo push: falló la consulta de recibos', ['error' => $e->getMessage()]);

                continue;
            }
            foreach ($chunk as $delivery) {
                $receipt = $receipts[$delivery->ticket_id] ?? null;
                if (! $receipt) {
                    // Expo guarda los recibos 24 h: si no aparece después, se da por desconocido.
                    if ($delivery->created_at->lt(now()->subDay())) {
                        $delivery->update(['receipt_status' => 'unknown', 'receipt_checked_at' => now()]);
                    }

                    continue;
                }
                $stats['checked']++;
                $ok = ($receipt['status'] ?? null) === 'ok';
                $error = $ok ? null : ($receipt['details']['error'] ?? 'error');
                $delivery->update(['receipt_status' => $ok ? 'ok' : 'error', 'receipt_error' => $error, 'receipt_checked_at' => now()]);
                if (! $ok) {
                    $stats['errors']++;
                    if ($error === 'DeviceNotRegistered' && $delivery->device_token_id) {
                        $stats['removed_tokens'] += DeviceToken::whereKey($delivery->device_token_id)->delete();
                    }
                }
            }
        }

        return $stats;
    }

    private function message(Notification $n, DeviceToken $token): array
    {
        $category = NotificationCatalog::categoryOf($n->notification_type) ?? 'news';

        return [
            'to' => $token->token,
            'title' => Str::limit($n->title, 100),
            'body' => Str::limit($n->message, 240),
            'sound' => 'default',
            'priority' => 'high',
            'channelId' => $category,
            'data' => array_merge($n->data ?? [], [
                'notification_id' => $n->id,
                'category' => $n->category,
                'notification_type' => $n->notification_type,
            ]),
        ];
    }

    private function recordTicket(Notification $n, DeviceToken $token, ?array $ticket): string
    {
        $ok = ($ticket['status'] ?? null) === 'ok';
        $error = $ok ? null : ($ticket['details']['error'] ?? 'error');
        PushDelivery::create([
            'notification_id' => $n->id,
            'device_token_id' => $token->id,
            'ticket_id' => $ticket['id'] ?? null,
            'status' => $ok ? 'ok' : 'error',
            'error' => $error,
            'error_message' => $ok ? null : Str::limit((string) ($ticket['message'] ?? 'Sin respuesta de Expo'), 250),
        ]);
        if ($error === 'DeviceNotRegistered') {
            $token->delete();
        }

        return $ok ? 'ok' : 'error';
    }
}
