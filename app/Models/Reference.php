<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reference extends Model
{
    protected $fillable = [
        'user_id',
        'reference_type',
        'name',
        'relationship',
        'phone',
        'email',
        'organization',
        'position',
        'letter_content',
        'letter_file_path',
        'verified',
        'verification_date'
    ];

    protected $casts = [
        'verified' => 'boolean',
        'verification_date' => 'date',
    ];

    /**
     * Get the user that owns the reference.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include verified references.
     */
    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    /**
     * Scope a query to only include childcare references.
     */
    public function scopeChildcare($query)
    {
        return $query->where('reference_type', 'childcare');
    }

    /**
     * Scope a query to only include character references.
     */
    public function scopeCharacter($query)
    {
        return $query->where('reference_type', 'character');
    }

    /**
     * Scope a query to only include professional references.
     */
    public function scopeProfessional($query)
    {
        return $query->where('reference_type', 'professional');
    }

    /**
     * Verify this reference.
     */
    public function verify()
    {
        $this->update([
            'verified' => true,
            'verification_date' => now()
        ]);
    }
}
