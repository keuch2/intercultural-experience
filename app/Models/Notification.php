<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // Deshabilitar timestamps automáticos ya que la tabla solo tiene created_at
    public $timestamps = false;

    protected $fillable = [
        'user_id', 'title', 'message', 'category', 'notification_type', 'data', 'campaign_id',
        'push_status', 'pushed_at', 'is_read', 'created_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'data' => 'array',
        'created_at' => 'datetime',
        'pushed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function campaign()
    {
        return $this->belongsTo(NotificationCampaign::class, 'campaign_id');
    }

    public function pushDeliveries()
    {
        return $this->hasMany(PushDelivery::class);
    }
}
