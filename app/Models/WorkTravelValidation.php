<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkTravelValidation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'application_id',
        'university_name',
        'study_type',
        'is_presencial_validated',
        'validation_date',
        'student_id_number',
        'enrollment_certificate_path',
        'expected_graduation',
        'current_semester',
        'total_semesters',
        'gpa',
        'is_full_time_student',
        'weekly_class_hours',
        'current_courses',
        'program_start_date',
        'program_end_date',
        'season',
        'meets_age_requirement',
        'meets_academic_requirement',
        'meets_english_requirement',
        'has_valid_passport',
        'has_clean_record',
        'validation_status',
        'rejection_reason',
        'validated_by'
    ];

    protected $casts = [
        'validation_date' => 'date',
        'expected_graduation' => 'date',
        'program_start_date' => 'date',
        'program_end_date' => 'date',
        'current_courses' => 'array',
        'is_presencial_validated' => 'boolean',
        'is_full_time_student' => 'boolean',
        'meets_age_requirement' => 'boolean',
        'meets_academic_requirement' => 'boolean',
        'meets_english_requirement' => 'boolean',
        'has_valid_passport' => 'boolean',
        'has_clean_record' => 'boolean',
        'gpa' => 'decimal:2',
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
     * Check if validation is complete.
     */
    public function isComplete()
    {
        return $this->validation_status === 'approved' &&
               $this->is_presencial_validated &&
               $this->meets_all_requirements();
    }

    /**
     * Check if meets all requirements.
     */
    public function meetsAllRequirements()
    {
        return $this->meets_age_requirement &&
               $this->meets_academic_requirement &&
               $this->meets_english_requirement &&
               $this->has_valid_passport &&
               $this->has_clean_record;
    }

    /**
     * Scope for presencial students only.
     */
    public function scopePresencial($query)
    {
        return $query->where('study_type', 'presencial');
    }

    /**
     * Scope for validated records.
     */
    public function scopeValidated($query)
    {
        return $query->where('is_presencial_validated', true);
    }

    /**
     * Scope for approved validations.
     */
    public function scopeApproved($query)
    {
        return $query->where('validation_status', 'approved');
    }

    /**
     * Scope for summer season.
     */
    public function scopeSummer($query)
    {
        return $query->where('season', 'summer');
    }

    /**
     * Scope for winter season.
     */
    public function scopeWinter($query)
    {
        return $query->where('season', 'winter');
    }
}
