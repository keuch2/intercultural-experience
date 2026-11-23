<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Token de Expo Push de un dispositivo donde el participante inició sesión. */
class DeviceToken extends Model
{
    protected $fillable = ['user_id', 'token', 'platform', 'app_version', 'device_name', 'last_seen_at'];

    protected $casts = ['last_seen_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
