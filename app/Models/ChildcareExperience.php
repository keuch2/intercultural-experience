<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildcareExperience extends Model
{
    protected $fillable = [
        'user_id',
        'experience_type',
        'ages_cared',
        'duration_months',
        'responsibilities',
        'cared_for_infants',
        'special_needs_experience',
        'special_needs_detail',
        'reference_name',
        'reference_phone',
        'reference_email',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'cared_for_infants' => 'boolean',
        'special_needs_experience' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the user that owns the childcare experience.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get total experience in months.
     */
    public function getTotalExperienceMonthsAttribute()
    {
        return $this->duration_months;
    }

    /**
     * Check if experience includes infant care.
     */
    public function hasInfantExperience()
    {
        return $this->cared_for_infants;
    }

    /**
     * Check if experience includes special needs.
     */
    public function hasSpecialNeedsExperience()
    {
        return $this->special_needs_experience;
    }
}
