<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InternTraineeValidation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'program_type',
        'university_name',
        'degree_field',
        'current_year',
        'total_years',
        'gpa',
        'expected_graduation',
        'graduation_date',
        'is_currently_enrolled',
        'field_of_expertise',
        'years_of_experience',
        'years_verified',
        'current_position',
        'current_employer',
        'previous_positions',
        'host_company_id',
        'training_plan_title',
        'training_objectives',
        'learning_goals',
        'skills_to_develop',
        'job_description',
        'hours_per_week',
        'industry_sector',
        'specific_sectors',
        'position_title',
        'program_start_date',
        'program_end_date',
        'duration_months',
        'is_flexible_duration',
        'is_paid_position',
        'stipend_amount',
        'stipend_frequency',
        'provides_housing',
        'housing_details',
        'meets_age_requirement',
        'meets_education_requirement',
        'meets_experience_requirement',
        'meets_english_requirement',
        'has_valid_passport',
        'has_clean_record',
        'is_student_or_recent_grad',
        'months_since_graduation',
        'has_minimum_experience',
        'has_professional_references',
        'enrollment_certificate_path',
        'diploma_path',
        'transcript_path',
        'cv_path',
        'training_plan_document_path',
        'reference_letters_paths',
        'portfolio_url',
        'professional_references',
        'academic_references',
        'career_goals',
        'why_this_field',
        'technical_skills',
        'software_proficiency',
        'languages_spoken',
        'preferred_states',
        'preferred_cities',
        'company_size_preference',
        'willing_to_relocate',
        'validation_status',
        'rejection_reason',
        'validated_by',
        'validation_completed_at',
    ];

    protected $casts = [
        'gpa' => 'decimal:2',
        'expected_graduation' => 'date',
        'graduation_date' => 'date',
        'is_currently_enrolled' => 'boolean',
        'previous_positions' => 'array',
        'learning_goals' => 'array',
        'skills_to_develop' => 'array',
        'specific_sectors' => 'array',
        'program_start_date' => 'date',
        'program_end_date' => 'date',
        'is_flexible_duration' => 'boolean',
        'is_paid_position' => 'boolean',
        'stipend_amount' => 'decimal:2',
        'provides_housing' => 'boolean',
        'meets_age_requirement' => 'boolean',
        'meets_education_requirement' => 'boolean',
        'meets_experience_requirement' => 'boolean',
        'meets_english_requirement' => 'boolean',
        'has_valid_passport' => 'boolean',
        'has_clean_record' => 'boolean',
        'is_student_or_recent_grad' => 'boolean',
        'has_minimum_experience' => 'boolean',
        'has_professional_references' => 'boolean',
        'reference_letters_paths' => 'array',
        'professional_references' => 'array',
        'academic_references' => 'array',
        'technical_skills' => 'array',
        'software_proficiency' => 'array',
        'languages_spoken' => 'array',
        'preferred_states' => 'array',
        'preferred_cities' => 'array',
        'willing_to_relocate' => 'boolean',
        'validation_completed_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hostCompany()
    {
        return $this->belongsTo(HostCompany::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function trainingPlan()
    {
        return $this->hasOne(TrainingPlan::class, 'validation_id');
    }

    /**
     * Scopes
     */
    public function scopeInterns($query)
    {
        return $query->where('program_type', 'intern');
    }

    public function scopeTrainees($query)
    {
        return $query->where('program_type', 'trainee');
    }

    public function scopeApproved($query)
    {
        return $query->where('validation_status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('validation_status', 'pending_review');
    }

    /**
     * Accessors & Mutators
     */
    public function getIsInternAttribute()
    {
        return $this->program_type === 'intern';
    }

    public function getIsTraineeAttribute()
    {
        return $this->program_type === 'trainee';
    }

    public function getDurationWeeksAttribute()
    {
        if (!$this->duration_months) {
            return null;
        }
        return $this->duration_months * 4;
    }

    /**
     * Helper Methods
     */
    public function isEligible()
    {
        if ($this->program_type === 'intern') {
            return $this->is_student_or_recent_grad 
                && ($this->is_currently_enrolled || $this->months_since_graduation <= 12);
        }
        
        if ($this->program_type === 'trainee') {
            return $this->has_minimum_experience 
                && $this->years_verified >= 1;
        }
        
        return false;
    }

    public function meetsAllRequirements()
    {
        return $this->meets_age_requirement
            && $this->meets_education_requirement
            && $this->meets_experience_requirement
            && $this->meets_english_requirement
            && $this->has_valid_passport
            && $this->has_clean_record;
    }

    public function calculateMonthsSinceGraduation()
    {
        if (!$this->graduation_date) {
            return null;
        }
        
        return now()->diffInMonths($this->graduation_date);
    }
}
