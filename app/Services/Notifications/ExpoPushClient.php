<?php

namespace App\Services\Notifications;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/** Cliente mínimo de la API de Expo Push (https://docs.expo.dev/push-notifications/sending-notifications/). */
class ExpoPushClient
{
    public const SEND_URL = 'https://exp.host/--/api/v2/push/send';

    public const RECEIPTS_URL = 'https://exp.host/--/api/v2/push/getReceipts';

    /** Máximo de mensajes por pedido que acepta Expo. */
    public const BATCH = 100;

    /**
     * @param  list<array>  $messages
     * @return list<array> tickets en el mismo orden que los mensajes
     */
    public function send(array $messages): array
    {
        return $this->http()->post(self::SEND_URL, $messages)->throw()->json('data') ?? [];
    }

    /**
     * @param  list<string>  $ticketIds
     * @return array<string, array> ticket_id => recibo
     */
    public function receipts(array $ticketIds): array
    {
        return $this->http()->post(self::RECEIPTS_URL, ['ids' => array_values($ticketIds)])->throw()->json('data') ?? [];
    }

    private function http(): PendingRequest
    {
        $request = Http::acceptJson()->asJson()->timeout(20)->retry(2, 500, throw: false);
        if ($token = config('services.expo.access_token')) {
            $request = $request->withToken($token);
        }

        return $request;
    }
}
