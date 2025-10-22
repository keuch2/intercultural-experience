<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'family_name',
        'parent1_name',
        'parent2_name',
        'email',
        'phone',
        'city',
        'state',
        'country',
        'number_of_children',
        'children_ages',
        'has_infants',
        'has_special_needs',
        'special_needs_detail',
        'has_pets',
        'pet_types',
        'smoking_household',
        'required_gender',
        'drivers_license_required',
        'swimming_required',
        'weekly_stipend',
        'education_fund',
        'additional_benefits'
    ];

    protected $casts = [
        'children_ages' => 'array',
        'has_infants' => 'boolean',
        'has_special_needs' => 'boolean',
        'has_pets' => 'boolean',
        'smoking_household' => 'boolean',
        'drivers_license_required' => 'boolean',
        'swimming_required' => 'boolean',
        'weekly_stipend' => 'decimal:2',
        'education_fund' => 'decimal:2',
        'number_of_children' => 'integer',
    ];

    /**
     * Get the matches for this family.
     */
    public function matches()
    {
        return $this->hasMany(AuPairMatch::class);
    }

    /**
     * Get the confirmed match relationship.
     */
    public function confirmedMatch()
    {
        return $this->hasOne(AuPairMatch::class)->where('is_matched', true);
    }

    /**
     * Get potential Au Pair matches.
     */
    public function potentialMatches()
    {
        return $this->matches()->where('family_status', 'pending');
    }

    /**
     * Check if family has infants.
     */
    public function getHasInfantsAttribute()
    {
        if (!$this->children_ages) {
            return false;
        }
        
        return collect($this->children_ages)->contains(function ($age) {
            return $age < 2;
        });
    }

    /**
     * Get the youngest child's age.
     */
    public function getYoungestChildAgeAttribute()
    {
        if (!$this->children_ages || empty($this->children_ages)) {
            return null;
        }
        
        return min($this->children_ages);
    }

    /**
     * Get the oldest child's age.
     */
    public function getOldestChildAgeAttribute()
    {
        if (!$this->children_ages || empty($this->children_ages)) {
            return null;
        }
        
        return max($this->children_ages);
    }

    /**
     * Scope for families in a specific state.
     */
    public function scopeInState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Scope for families with infants.
     */
    public function scopeWithInfants($query)
    {
        return $query->where('has_infants', true);
    }

    /**
     * Scope for families without matched Au Pair.
     */
    public function scopeAvailable($query)
    {
        return $query->whereDoesntHave('matches', function ($q) {
            $q->where('is_matched', true);
        });
    }
}
