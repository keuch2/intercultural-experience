<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantNote extends Model
{
    protected $fillable = [
        'user_id',
        'admin_id',
        'application_id',
        'content',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scopeForApplication($query, ?int $applicationId)
    {
        if ($applicationId === null) {
            return $query->whereNull('application_id');
        }

        return $query->where('application_id', $applicationId);
    }
}
