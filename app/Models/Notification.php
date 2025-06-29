<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    // Deshabilitar timestamps automáticos ya que la tabla solo tiene created_at
    public $timestamps = false;
    
    protected $fillable = [
        'user_id', 'title', 'message', 'category', 'is_read', 'created_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}