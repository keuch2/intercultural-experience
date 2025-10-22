<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_name',
        'business_type',
        'ein_number',
        'established_year',
        'number_of_employees',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'phone',
        'email',
        'website',
        'contact_name',
        'contact_title',
        'contact_phone',
        'contact_email',
        'years_in_program',
        'participants_hired_total',
        'participants_hired_this_year',
        'positions_available',
        'job_categories',
        'seasons_hiring',
        'is_verified',
        'verification_date',
        'is_active',
        'is_blacklisted',
        'blacklist_reason',
        'rating',
        'total_reviews',
        'notes',
        'business_license_path',
        'insurance_certificate_path',
        'agreement_path',
        'e_verify_enrolled',
        'workers_comp_insurance',
        'liability_insurance',
        'last_audit_date'
    ];

    protected $casts = [
        'verification_date' => 'date',
        'last_audit_date' => 'date',
        'job_categories' => 'array',
        'seasons_hiring' => 'array',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'is_blacklisted' => 'boolean',
        'e_verify_enrolled' => 'boolean',
        'workers_comp_insurance' => 'boolean',
        'liability_insurance' => 'boolean',
        'rating' => 'decimal:2',
    ];

    /**
     * Get the contracts for this employer.
     */
    public function contracts()
    {
        return $this->hasMany(WorkContract::class);
    }

    /**
     * Get the job offers from this employer.
     */
    public function jobOffers()
    {
        return $this->hasMany(JobOffer::class);
    }

    /**
     * Get active contracts.
     */
    public function activeContracts()
    {
        return $this->contracts()->where('status', 'active');
    }

    /**
     * Check if employer is eligible to hire.
     */
    public function canHire()
    {
        return $this->is_active && 
               $this->is_verified && 
               !$this->is_blacklisted &&
               $this->positions_available > 0;
    }

    /**
     * Check if employer has all required insurance.
     */
    public function hasRequiredInsurance()
    {
        return $this->workers_comp_insurance && 
               $this->liability_insurance;
    }

    /**
     * Update rating based on reviews.
     */
    public function updateRating($newRating)
    {
        if ($this->total_reviews == 0) {
            $this->rating = $newRating;
        } else {
            // Calculate weighted average
            $totalScore = ($this->rating * $this->total_reviews) + $newRating;
            $this->rating = $totalScore / ($this->total_reviews + 1);
        }
        
        $this->total_reviews++;
        $this->save();
    }

    /**
     * Blacklist the employer.
     */
    public function blacklist($reason)
    {
        $this->update([
            'is_blacklisted' => true,
            'blacklist_reason' => $reason,
            'is_active' => false
        ]);
    }

    /**
     * Verify the employer.
     */
    public function verify()
    {
        $this->update([
            'is_verified' => true,
            'verification_date' => now()
        ]);
    }

    /**
     * Scope for active employers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('is_blacklisted', false);
    }

    /**
     * Scope for verified employers.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for employers hiring in summer.
     */
    public function scopeHiringSummer($query)
    {
        return $query->whereJsonContains('seasons_hiring', 'summer');
    }

    /**
     * Scope for employers hiring in winter.
     */
    public function scopeHiringWinter($query)
    {
        return $query->whereJsonContains('seasons_hiring', 'winter');
    }

    /**
     * Scope for employers with available positions.
     */
    public function scopeWithAvailablePositions($query)
    {
        return $query->where('positions_available', '>', 0);
    }

    /**
     * Scope for top rated employers.
     */
    public function scopeTopRated($query, $minRating = 4.0)
    {
        return $query->where('rating', '>=', $minRating)
                    ->where('total_reviews', '>=', 5);
    }
}
