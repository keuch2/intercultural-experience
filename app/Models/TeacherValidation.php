<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherValidation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'application_id',
        'mec_registration_number',
        'has_mec_validation',
        'mec_validation_date',
        'mec_certificate_path',
        'mec_status',
        'teaching_degree_title',
        'university_name',
        'graduation_year',
        'diploma_path',
        'diploma_apostilled',
        'transcript_path',
        'transcript_apostilled',
        'teaching_years_total',
        'teaching_years_verified',
        'subjects_taught',
        'grade_levels_taught',
        'current_employment_status',
        'current_school_name',
        'current_school_contact',
        'has_tefl_certification',
        'tefl_certificate_path',
        'has_tesol_certification',
        'tesol_certificate_path',
        'other_certifications',
        'has_criminal_record',
        'criminal_record_path',
        'criminal_record_date',
        'has_child_abuse_clearance',
        'child_abuse_clearance_path',
        'registered_for_job_fair',
        'job_fair_date',
        'job_fair_status',
        'interviews_scheduled',
        'offers_received',
        'preferred_states',
        'preferred_grade_levels',
        'preferred_subjects',
        'school_type_preference',
        'willing_to_relocate',
        'validation_status',
        'rejection_reasons',
        'validated_by',
        'validation_completed_at'
    ];

    protected $casts = [
        'mec_validation_date' => 'date',
        'criminal_record_date' => 'date',
        'job_fair_date' => 'date',
        'validation_completed_at' => 'datetime',
        'subjects_taught' => 'array',
        'grade_levels_taught' => 'array',
        'other_certifications' => 'array',
        'preferred_states' => 'array',
        'preferred_grade_levels' => 'array',
        'preferred_subjects' => 'array',
        'has_mec_validation' => 'boolean',
        'diploma_apostilled' => 'boolean',
        'transcript_apostilled' => 'boolean',
        'has_tefl_certification' => 'boolean',
        'has_tesol_certification' => 'boolean',
        'has_criminal_record' => 'boolean',
        'has_child_abuse_clearance' => 'boolean',
        'registered_for_job_fair' => 'boolean',
        'willing_to_relocate' => 'boolean',
    ];

    /**
     * Get the user that owns the validation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the application associated with this validation.
     */
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Get the admin who validated this.
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Get the teacher certifications.
     */
    public function certifications()
    {
        return $this->hasMany(TeacherCertification::class, 'user_id', 'user_id');
    }

    /**
     * Get the job fair registrations.
     */
    public function jobFairRegistrations()
    {
        return $this->hasMany(JobFairRegistration::class, 'user_id', 'user_id');
    }

    /**
     * Check if MEC validation is valid.
     */
    public function isMecValid()
    {
        return $this->has_mec_validation && 
               $this->mec_status === 'approved';
    }

    /**
     * Check if has required certifications.
     */
    public function hasRequiredCertifications()
    {
        return $this->has_tefl_certification || 
               $this->has_tesol_certification;
    }

    /**
     * Check if background checks are complete.
     */
    public function hasCompleteBackgroundChecks()
    {
        return !$this->has_criminal_record && 
               $this->has_child_abuse_clearance;
    }

    /**
     * Check if validation is complete.
     */
    public function isValidationComplete()
    {
        return $this->validation_status === 'approved' &&
               $this->isMecValid() &&
               $this->hasCompleteBackgroundChecks() &&
               $this->diploma_apostilled &&
               $this->transcript_apostilled;
    }

    /**
     * Check if eligible for job fair.
     */
    public function isEligibleForJobFair()
    {
        return $this->isValidationComplete() &&
               $this->teaching_years_verified >= 2;
    }

    /**
     * Calculate match score with school requirements.
     */
    public function calculateSchoolMatchScore(School $school)
    {
        $score = 0;
        
        // Experience match (30 points)
        if ($this->teaching_years_verified >= $school->minimum_experience_years) {
            $score += 30;
        }
        
        // Subject match (25 points)
        $subjectsMatch = array_intersect(
            $this->subjects_taught ?? [],
            $school->subjects_needed ?? []
        );
        if (!empty($subjectsMatch)) {
            $score += 25;
        }
        
        // Grade level match (20 points)
        $gradeLevelsMatch = array_intersect(
            $this->grade_levels_taught ?? [],
            $school->grade_levels ?? []
        );
        if (!empty($gradeLevelsMatch)) {
            $score += 20;
        }
        
        // Certification bonus (15 points)
        if ($this->hasRequiredCertifications()) {
            $score += 15;
        }
        
        // MEC validation bonus (10 points)
        if ($this->isMecValid()) {
            $score += 10;
        }
        
        return $score;
    }

    /**
     * Scope for MEC approved teachers.
     */
    public function scopeMecApproved($query)
    {
        return $query->where('has_mec_validation', true)
                    ->where('mec_status', 'approved');
    }

    /**
     * Scope for job fair registered teachers.
     */
    public function scopeJobFairRegistered($query)
    {
        return $query->where('registered_for_job_fair', true);
    }

    /**
     * Scope for validated teachers.
     */
    public function scopeValidated($query)
    {
        return $query->where('validation_status', 'approved');
    }

    /**
     * Scope for experienced teachers.
     */
    public function scopeExperienced($query, $minYears = 2)
    {
        return $query->where('teaching_years_verified', '>=', $minYears);
    }
}
