<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Scholarship extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'scholarship_name',
        'code',
        'university_id',
        'scholarship_type',
        'description',
        'eligible_degree_levels',
        'eligible_majors',
        'eligible_countries',
        'min_gpa_required',
        'min_test_score',
        'award_type',
        'award_amount',
        'award_percentage',
        'award_frequency',
        'renewable_years',
        'is_renewable',
        'covers_tuition',
        'covers_housing',
        'covers_books',
        'covers_meals',
        'covers_travel',
        'application_deadline',
        'award_notification_date',
        'requires_separate_application',
        'application_url',
        'required_documents',
        'application_instructions',
        'total_awards_available',
        'awards_remaining',
        'is_active',
        'application_year',
        'special_requirements',
        'requires_essay',
        'requires_interview',
        'requires_portfolio',
    ];

    protected $casts = [
        'eligible_degree_levels' => 'array',
        'eligible_majors' => 'array',
        'eligible_countries' => 'array',
        'min_gpa_required' => 'decimal:2',
        'min_test_score' => 'integer',
        'award_amount' => 'decimal:2',
        'award_percentage' => 'integer',
        'renewable_years' => 'integer',
        'is_renewable' => 'boolean',
        'covers_tuition' => 'boolean',
        'covers_housing' => 'boolean',
        'covers_books' => 'boolean',
        'covers_meals' => 'boolean',
        'covers_travel' => 'boolean',
        'application_deadline' => 'date',
        'award_notification_date' => 'date',
        'requires_separate_application' => 'boolean',
        'required_documents' => 'array',
        'total_awards_available' => 'integer',
        'awards_remaining' => 'integer',
        'is_active' => 'boolean',
        'application_year' => 'integer',
        'requires_essay' => 'boolean',
        'requires_interview' => 'boolean',
        'requires_portfolio' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function applications()
    {
        return $this->hasMany(ScholarshipApplication::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('awards_remaining', '>', 0);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('scholarship_type', $type);
    }

    public function scopeAvailableFor($query, $degreeLevel, $country = null)
    {
        $query->where('is_active', true)
              ->where('awards_remaining', '>', 0)
              ->where(function($q) use ($degreeLevel) {
                  $q->whereJsonContains('eligible_degree_levels', $degreeLevel)
                    ->orWhereNull('eligible_degree_levels');
              });
              
        if ($country) {
            $query->where(function($q) use ($country) {
                $q->whereJsonContains('eligible_countries', $country)
                  ->orWhereNull('eligible_countries');
            });
        }
        
        return $query;
    }

    /**
     * Accessors
     */
    public function getAwardDisplayAttribute()
    {
        return match($this->award_type) {
            'full_tuition' => 'Full Tuition',
            'partial_tuition' => 'Partial Tuition',
            'fixed_amount' => '$' . number_format($this->award_amount, 0),
            'percentage' => $this->award_percentage . '% of tuition',
            default => 'Variable',
        };
    }

    public function getCoverageDetailsAttribute()
    {
        $coverage = [];
        if ($this->covers_tuition) $coverage[] = 'Tuition';
        if ($this->covers_housing) $coverage[] = 'Housing';
        if ($this->covers_books) $coverage[] = 'Books';
        if ($this->covers_meals) $coverage[] = 'Meals';
        if ($this->covers_travel) $coverage[] = 'Travel';
        
        return $coverage;
    }

    public function getIsDeadlineApproachingAttribute()
    {
        if (!$this->application_deadline) {
            return false;
        }
        
        return $this->application_deadline->isFuture() 
            && $this->application_deadline->diffInDays(now()) <= 30;
    }

    /**
     * Helper Methods
     */
    public function isEligible($gpa, $degreeLevel, $country = null)
    {
        // GPA check
        if ($this->min_gpa_required && $gpa < $this->min_gpa_required) {
            return false;
        }
        
        // Degree level check
        if ($this->eligible_degree_levels && !in_array($degreeLevel, $this->eligible_degree_levels)) {
            return false;
        }
        
        // Country check
        if ($country && $this->eligible_countries && !in_array($country, $this->eligible_countries)) {
            return false;
        }
        
        return true;
    }

    public function decrementAward()
    {
        if ($this->awards_remaining > 0) {
            $this->decrement('awards_remaining');
        }
    }

    public function incrementAward()
    {
        if ($this->awards_remaining < $this->total_awards_available) {
            $this->increment('awards_remaining');
        }
    }
}
