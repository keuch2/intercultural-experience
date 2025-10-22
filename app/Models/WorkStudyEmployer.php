<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkStudyEmployer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employer_name',
        'employer_code',
        'business_type',
        'description',
        'website',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'contact_person_name',
        'contact_person_title',
        'contact_email',
        'contact_phone',
        'hr_email',
        'hr_phone',
        'available_positions',
        'total_positions_available',
        'positions_currently_filled',
        'hourly_wage_min',
        'hourly_wage_max',
        'hours_per_week_min',
        'hours_per_week_max',
        'work_schedule',
        'provides_tips',
        'avg_tips_per_week',
        'provides_meals',
        'provides_uniform',
        'provides_transportation',
        'provides_housing',
        'housing_cost_weekly',
        'other_benefits',
        'min_english_level',
        'min_age',
        'requires_experience',
        'special_requirements',
        'students_hosted_total',
        'students_current',
        'rating',
        'total_reviews',
        'positive_reviews',
        'negative_reviews',
        'is_verified',
        'verification_date',
        'verified_by',
        'is_active',
        'years_partnership',
        'business_license_number',
        'tax_id',
        'complies_labor_laws',
        'last_inspection_date',
    ];

    protected $casts = [
        'available_positions' => 'array',
        'total_positions_available' => 'integer',
        'positions_currently_filled' => 'integer',
        'hourly_wage_min' => 'decimal:2',
        'hourly_wage_max' => 'decimal:2',
        'hours_per_week_min' => 'integer',
        'hours_per_week_max' => 'integer',
        'work_schedule' => 'array',
        'provides_tips' => 'boolean',
        'avg_tips_per_week' => 'decimal:2',
        'provides_meals' => 'boolean',
        'provides_uniform' => 'boolean',
        'provides_transportation' => 'boolean',
        'provides_housing' => 'boolean',
        'housing_cost_weekly' => 'decimal:2',
        'min_age' => 'integer',
        'requires_experience' => 'boolean',
        'students_hosted_total' => 'integer',
        'students_current' => 'integer',
        'rating' => 'decimal:1',
        'total_reviews' => 'integer',
        'positive_reviews' => 'integer',
        'negative_reviews' => 'integer',
        'is_verified' => 'boolean',
        'verification_date' => 'date',
        'is_active' => 'boolean',
        'years_partnership' => 'integer',
        'complies_labor_laws' => 'boolean',
        'last_inspection_date' => 'date',
    ];

    /**
     * Relationships
     */
    public function programs()
    {
        return $this->hasMany(WorkStudyProgram::class, 'employer_id');
    }

    public function placements()
    {
        return $this->hasMany(WorkStudyPlacement::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByBusinessType($query, $type)
    {
        return $query->where('business_type', $type);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeHasAvailablePositions($query)
    {
        return $query->whereRaw('total_positions_available > positions_currently_filled');
    }

    /**
     * Accessors
     */
    public function getAvailablePositionsCountAttribute()
    {
        return max(0, $this->total_positions_available - $this->positions_currently_filled);
    }

    public function getAverageWageAttribute()
    {
        if (!$this->hourly_wage_min || !$this->hourly_wage_max) {
            return 0;
        }
        return ($this->hourly_wage_min + $this->hourly_wage_max) / 2;
    }

    public function getEstimatedWeeklyEarningsAttribute()
    {
        $avgHours = ($this->hours_per_week_min + $this->hours_per_week_max) / 2;
        $earnings = $this->average_wage * $avgHours;
        
        if ($this->provides_tips && $this->avg_tips_per_week) {
            $earnings += $this->avg_tips_per_week;
        }
        
        return $earnings;
    }

    public function getRatingPercentageAttribute()
    {
        if ($this->total_reviews == 0) {
            return 0;
        }
        return round(($this->rating / 5) * 100, 1);
    }

    /**
     * Helper Methods
     */
    public function hasAvailablePositions()
    {
        return $this->available_positions_count > 0;
    }

    public function incrementStudent()
    {
        $this->increment('students_current');
        $this->increment('students_hosted_total');
        $this->increment('positions_currently_filled');
    }

    public function decrementStudent()
    {
        if ($this->students_current > 0) {
            $this->decrement('students_current');
        }
        if ($this->positions_currently_filled > 0) {
            $this->decrement('positions_currently_filled');
        }
    }

    public function addReview($rating, $isPositive = null)
    {
        $totalRating = ($this->rating * $this->total_reviews) + $rating;
        $this->increment('total_reviews');
        $this->update(['rating' => $totalRating / $this->total_reviews]);
        
        if ($isPositive === true) {
            $this->increment('positive_reviews');
        } elseif ($isPositive === false) {
            $this->increment('negative_reviews');
        }
    }
}
