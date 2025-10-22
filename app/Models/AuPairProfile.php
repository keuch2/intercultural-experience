<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuPairProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'application_id',
        'photos',
        'main_photo',
        'video_presentation',
        'video_duration',
        'dear_family_letter',
        'preferred_ages',
        'max_children',
        'ideal_family_description',
        'profile_status',
        'profile_complete',
        'profile_views',
        'available_from',
        'commitment_months'
    ];

    protected $casts = [
        'photos' => 'array',
        'profile_complete' => 'boolean',
        'available_from' => 'date',
        'profile_views' => 'integer',
        'commitment_months' => 'integer',
        'max_children' => 'integer',
    ];

    /**
     * Get the user that owns the Au Pair profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the application associated with the Au Pair profile.
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the matches for this Au Pair profile.
     */
    public function matches()
    {
        return $this->hasMany(AuPairMatch::class);
    }

    /**
     * Get the accepted matches.
     */
    public function acceptedMatches()
    {
        return $this->matches()->where('is_matched', true);
    }

    /**
     * Get the pending matches.
     */
    public function pendingMatches()
    {
        return $this->matches()->where('au_pair_status', 'pending');
    }

    /**
     * Check if profile is complete based on requirements.
     */
    public function getIsCompleteAttribute()
    {
        return !empty($this->photos) && count($this->photos) >= 6
            && !empty($this->video_presentation)
            && !empty($this->dear_family_letter)
            && $this->user->references()->count() >= 3
            && $this->user->childcareExperiences()->count() > 0
            && $this->user->healthDeclaration !== null
            && $this->user->emergencyContacts()->count() >= 2;
    }

    /**
     * Increment profile views.
     */
    public function incrementViews()
    {
        $this->increment('profile_views');
    }

    /**
     * Scope for active profiles.
     */
    public function scopeActive($query)
    {
        return $query->where('profile_status', 'active');
    }

    /**
     * Scope for pending profiles.
     */
    public function scopePending($query)
    {
        return $query->where('profile_status', 'pending');
    }

    /**
     * Scope for matched profiles.
     */
    public function scopeMatched($query)
    {
        return $query->where('profile_status', 'matched');
    }

    /**
     * Scope for complete profiles.
     */
    public function scopeComplete($query)
    {
        return $query->where('profile_complete', true);
    }
}
