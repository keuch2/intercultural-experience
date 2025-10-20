<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    public $timestamps = false;
    
    protected $table = 'password_history';
    
    protected $fillable = [
        'user_id',
        'password',
        'created_at'
    ];
    
    protected $casts = [
        'created_at' => 'datetime'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
