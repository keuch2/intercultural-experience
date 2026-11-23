<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Categoría silenciada (o no) por el participante. Sin fila = push activa. */
class UserNotificationPreference extends Model
{
    protected $fillable = ['user_id', 'category', 'push_enabled'];

    protected $casts = ['push_enabled' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
