<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_name',
        'school_type',
        'district_name',
        'school_code',
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'phone',
        'email',
        'website',
        'principal_name',
        'principal_email',
        'hr_contact_name',
        'hr_contact_email',
        'hr_contact_phone',
        'grade_levels',
        'total_students',
        'total_teachers',
        'student_teacher_ratio',
        'subjects_needed',
        'years_in_program',
        'teachers_hired_total',
        'teachers_hired_current_year',
        'positions_available',
        'required_certifications',
        'minimum_experience_years',
        'preferred_nationalities',
        'sponsors_visa',
        'provides_housing_assistance',
        'housing_details',
        'salary_range_min',
        'salary_range_max',
        'benefits_offered',
        'is_active',
        'is_verified',
        'verification_date',
        'rating',
        'total_reviews',
        'accreditation_certificate_path',
        'agreement_path',
        'notes',
        'special_requirements'
    ];

    protected $casts = [
        'verification_date' => 'date',
        'grade_levels' => 'array',
        'subjects_needed' => 'array',
        'required_certifications' => 'array',
        'preferred_nationalities' => 'array',
        'benefits_offered' => 'array',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'sponsors_visa' => 'boolean',
        'provides_housing_assistance' => 'boolean',
        'student_teacher_ratio' => 'decimal:2',
        'salary_range_min' => 'decimal:2',
        'salary_range_max' => 'decimal:2',
        'rating' => 'decimal:2',
    ];

    /**
     * Get job fair registrations for this school.
     */
    public function jobFairRegistrations()
    {
        return $this->hasMany(JobFairRegistration::class);
    }

    /**
     * Get teachers placed at this school.
     */
    public function placedTeachers()
    {
        return $this->hasMany(JobFairRegistration::class, 'placed_at_school_id')
                    ->where('placement_successful', true);
    }

    /**
     * Check if school can hire.
     */
    public function canHire()
    {
        return $this->is_active && 
               $this->is_verified && 
               $this->positions_available > 0;
    }

    /**
     * Check if school is elementary.
     */
    public function isElementary()
    {
        $elementary = ['K-5', 'K-6', 'Elementary'];
        return !empty(array_intersect($this->grade_levels ?? [], $elementary));
    }

    /**
     * Check if school is middle school.
     */
    public function isMiddleSchool()
    {
        $middle = ['6-8', '7-8', 'Middle School'];
        return !empty(array_intersect($this->grade_levels ?? [], $middle));
    }

    /**
     * Check if school is high school.
     */
    public function isHighSchool()
    {
        $high = ['9-12', '10-12', 'High School'];
        return !empty(array_intersect($this->grade_levels ?? [], $high));
    }

    /**
     * Get average salary.
     */
    public function getAverageSalaryAttribute()
    {
        if ($this->salary_range_min && $this->salary_range_max) {
            return ($this->salary_range_min + $this->salary_range_max) / 2;
        }
        return null;
    }

    /**
     * Calculate match score with teacher.
     */
    public function calculateTeacherMatchScore(TeacherValidation $teacher)
    {
        $score = 0;
        
        // Experience match (25 points)
        if ($teacher->teaching_years_verified >= $this->minimum_experience_years) {
            $score += 25;
        }
        
        // Subject match (30 points)
        $subjectsMatch = array_intersect(
            $teacher->subjects_taught ?? [],
            $this->subjects_needed ?? []
        );
        if (!empty($subjectsMatch)) {
            $score += 30;
        }
        
        // Grade level match (25 points)
        $gradeLevelsMatch = array_intersect(
            $teacher->grade_levels_taught ?? [],
            $this->grade_levels ?? []
        );
        if (!empty($gradeLevelsMatch)) {
            $score += 25;
        }
        
        // Certification match (20 points)
        $certMatch = array_intersect(
            $teacher->other_certifications ?? [],
            $this->required_certifications ?? []
        );
        if (!empty($certMatch) || empty($this->required_certifications)) {
            $score += 20;
        }
        
        return $score;
    }

    /**
     * Update rating based on review.
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
     * Verify the school.
     */
    public function verify()
    {
        $this->update([
            'is_verified' => true,
            'verification_date' => now()
        ]);
    }

    /**
     * Scope for active schools.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for verified schools.
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for schools with positions.
     */
    public function scopeWithPositions($query)
    {
        return $query->where('positions_available', '>', 0);
    }

    /**
     * Scope for schools by state.
     */
    public function scopeInState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Scope for schools by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('school_type', $type);
    }

    /**
     * Scope for top rated schools.
     */
    public function scopeTopRated($query, $minRating = 4.0)
    {
        return $query->where('rating', '>=', $minRating)
                    ->where('total_reviews', '>=', 3);
    }
}
