<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuPairMatch extends Model
{
    protected $fillable = [
        'au_pair_profile_id',
        'family_profile_id',
        'au_pair_status',
        'family_status',
        'is_matched',
        'matched_at',
        'messages_count',
        'last_interaction',
        'video_calls_count'
    ];

    protected $casts = [
        'is_matched' => 'boolean',
        'matched_at' => 'datetime',
        'last_interaction' => 'datetime',
        'messages_count' => 'integer',
        'video_calls_count' => 'integer',
    ];

    /**
     * Get the Au Pair profile.
     */
    public function auPairProfile()
    {
        return $this->belongsTo(AuPairProfile::class);
    }

    /**
     * Get the family profile.
     */
    public function familyProfile()
    {
        return $this->belongsTo(FamilyProfile::class);
    }

    /**
     * Check if both parties are interested.
     */
    public function getBothInterestedAttribute()
    {
        return $this->au_pair_status === 'interested' 
            && $this->family_status === 'interested';
    }

    /**
     * Check if match is pending.
     */
    public function getIsPendingAttribute()
    {
        return !$this->is_matched && (
            $this->au_pair_status === 'pending' || 
            $this->family_status === 'pending'
        );
    }

    /**
     * Check if match was rejected.
     */
    public function getIsRejectedAttribute()
    {
        return $this->au_pair_status === 'not_interested' 
            || $this->family_status === 'not_interested';
    }

    /**
     * Confirm the match.
     */
    public function confirmMatch()
    {
        if ($this->both_interested) {
            $this->update([
                'is_matched' => true,
                'matched_at' => now()
            ]);

            // Update Au Pair profile status
            $this->auPairProfile->update([
                'profile_status' => 'matched'
            ]);

            return true;
        }

        return false;
    }

    /**
     * Update interaction timestamp.
     */
    public function updateInteraction()
    {
        $this->update([
            'last_interaction' => now()
        ]);
    }

    /**
     * Increment message count.
     */
    public function incrementMessages()
    {
        $this->increment('messages_count');
        $this->updateInteraction();
    }

    /**
     * Increment video call count.
     */
    public function incrementVideoCalls()
    {
        $this->increment('video_calls_count');
        $this->updateInteraction();
    }

    /**
     * Scope for confirmed matches.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('is_matched', true);
    }

    /**
     * Scope for pending matches.
     */
    public function scopePending($query)
    {
        return $query->where('is_matched', false)
            ->where(function ($q) {
                $q->where('au_pair_status', 'pending')
                    ->orWhere('family_status', 'pending');
            });
    }

    /**
     * Scope for interested matches.
     */
    public function scopeInterested($query)
    {
        return $query->where('au_pair_status', 'interested')
            ->where('family_status', 'interested')
            ->where('is_matched', false);
    }
}
