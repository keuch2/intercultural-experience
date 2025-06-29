<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{
    // Deshabilitar timestamps automáticos ya que la tabla solo tiene created_at
    public $timestamps = false;
    
    protected $fillable = [
        'user_id', 'change', 'reason', 'related_id', 'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
    
    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}