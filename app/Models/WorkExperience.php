<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkExperience extends Model
{
    protected $fillable = [
        'user_id',
        'company',
        'position',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'reference_name',
        'reference_phone',
        'reference_email',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];

    /**
     * Get the user that owns the work experience
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get current work experiences
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Get duration in months
     */
    public function getDurationAttribute()
    {
        $end = $this->is_current ? now() : $this->end_date;
        return $this->start_date->diffInMonths($end);
    }
}
