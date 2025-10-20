<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyContact extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'relationship',
        'phone',
        'alternative_phone',
        'email',
        'address',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the user that owns the emergency contact
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
