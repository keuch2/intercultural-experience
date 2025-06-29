<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redemption extends Model
{
    // Enable timestamps since we added created_at and updated_at
    public $timestamps = true;
    
    protected $fillable = [
        'user_id', 'reward_id', 'points_cost', 'status', 'requested_at', 'resolved_at',
        'admin_notes', 'tracking_number', 'carrier', 'delivery_notes', 'delivered_at'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'resolved_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class);
    }
}