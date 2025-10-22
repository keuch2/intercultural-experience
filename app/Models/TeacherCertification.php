<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherCertification extends Model
{
    protected $fillable = [
        'user_id',
        'certification_name',
        'issuing_institution',
        'issue_date',
        'expiry_date',
        'certification_number',
        'document_path',
        'is_apostilled',
        'verified',
        'verification_date'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_apostilled' => 'boolean',
        'verified' => 'boolean',
        'verification_date' => 'date',
    ];

    /**
     * Get the user that owns the certification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if certification is expired.
     */
    public function getIsExpiredAttribute()
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }

    /**
     * Check if certification is valid.
     */
    public function getIsValidAttribute()
    {
        return $this->verified && !$this->is_expired;
    }

    /**
     * Verify this certification.
     */
    public function verify()
    {
        $this->update([
            'verified' => true,
            'verification_date' => now()
        ]);
    }

    /**
     * Scope for verified certifications.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope for apostilled certifications.
     */
    public function scopeApostilled($query)
    {
        return $query->where('is_apostilled', true);
    }

    /**
     * Scope for valid certifications (verified and not expired).
     */
    public function scopeValid($query)
    {
        return $query->where('verified', true)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', now());
            });
    }
}
