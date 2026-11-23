<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Un envío push (ticket de Expo) de un aviso a un dispositivo, con su recibo. */
class PushDelivery extends Model
{
    protected $fillable = [
        'notification_id', 'device_token_id', 'ticket_id', 'status', 'error', 'error_message',
        'receipt_status', 'receipt_error', 'receipt_checked_at',
    ];

    protected $casts = ['receipt_checked_at' => 'datetime'];

    public function notification()
    {
        return $this->belongsTo(Notification::class);
    }

    public function deviceToken()
    {
        return $this->belongsTo(DeviceToken::class);
    }
}
