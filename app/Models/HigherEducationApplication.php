<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HigherEducationApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'application_number',
        'user_id',
        'university_id',
        'degree_level',
        'major_field',
        'specific_program',
        'admission_term',
        'admission_year',
        'desired_start_date',
        'highest_degree_completed',
        'institution_name',
        'country_of_study',
        'graduation_year',
        'gpa',
        'gpa_scale',
        'education_history',
        'toefl_score',
        'toefl_test_date',
        'ielts_score',
        'ielts_test_date',
        'sat_score',
        'sat_math',
        'sat_verbal',
        'gre_score',
        'gre_verbal',
        'gre_quantitative',
        'gmat_score',
        'english_level',
        'needs_esl',
        'interested_pathway',
        'funding_source',
        'available_funds_annual',
        'needs_financial_aid',
        'applying_for_scholarship',
        'scholarship_interests',
        'years_work_experience',
        'work_history',
        'transcript_path',
        'diploma_path',
        'cv_resume_path',
        'personal_statement_path',
        'recommendation_letter_1_path',
        'recommendation_letter_2_path',
        'recommendation_letter_3_path',
        'financial_statement_path',
        'passport_copy_path',
        'additional_documents_paths',
        'references',
        'personal_statement',
        'why_this_program',
        'career_goals',
        'research_interests',
        'extracurricular_activities',
        'awards_honors',
        'needs_housing',
        'housing_preference',
        'needs_airport_pickup',
        'needs_health_insurance',
        'application_status',
        'submission_date',
        'decision_date',
        'decision_notes',
        'is_conditional',
        'conditions',
        'conditions_deadline',
        'i20_requested',
        'i20_issued_date',
        'i20_document_path',
        'sevis_id',
        'enrollment_confirmed',
        'enrollment_date',
        'deposit_paid',
        'deposit_paid_date',
        'application_fee_paid',
        'application_fee_paid_date',
        'processed_by',
        'last_status_update',
        'internal_notes',
    ];

    protected $casts = [
        'desired_start_date' => 'date',
        'graduation_year' => 'integer',
        'gpa' => 'decimal:2',
        'education_history' => 'array',
        'toefl_score' => 'integer',
        'toefl_test_date' => 'date',
        'ielts_score' => 'decimal:1',
        'ielts_test_date' => 'date',
        'sat_score' => 'integer',
        'sat_math' => 'integer',
        'sat_verbal' => 'integer',
        'gre_score' => 'integer',
        'gre_verbal' => 'integer',
        'gre_quantitative' => 'integer',
        'gmat_score' => 'integer',
        'needs_esl' => 'boolean',
        'interested_pathway' => 'boolean',
        'available_funds_annual' => 'decimal:2',
        'needs_financial_aid' => 'boolean',
        'applying_for_scholarship' => 'boolean',
        'scholarship_interests' => 'array',
        'years_work_experience' => 'integer',
        'work_history' => 'array',
        'additional_documents_paths' => 'array',
        'references' => 'array',
        'extracurricular_activities' => 'array',
        'awards_honors' => 'array',
        'needs_housing' => 'boolean',
        'needs_airport_pickup' => 'boolean',
        'needs_health_insurance' => 'boolean',
        'submission_date' => 'date',
        'decision_date' => 'date',
        'is_conditional' => 'boolean',
        'conditions' => 'array',
        'conditions_deadline' => 'date',
        'i20_requested' => 'boolean',
        'i20_issued_date' => 'date',
        'enrollment_confirmed' => 'boolean',
        'enrollment_date' => 'date',
        'deposit_paid' => 'decimal:2',
        'deposit_paid_date' => 'date',
        'application_fee_paid' => 'decimal:2',
        'application_fee_paid_date' => 'date',
        'last_status_update' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scholarshipApplications()
    {
        return $this->hasMany(ScholarshipApplication::class, 'university_application_id');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('application_status', $status);
    }

    public function scopeSubmitted($query)
    {
        return $query->whereIn('application_status', ['submitted', 'under_review', 'additional_docs_required']);
    }

    public function scopeAccepted($query)
    {
        return $query->whereIn('application_status', ['accepted', 'conditionally_accepted']);
    }

    public function scopePending($query)
    {
        return $query->whereIn('application_status', ['draft', 'submitted', 'under_review']);
    }

    public function scopeByDegreeLevel($query, $level)
    {
        return $query->where('degree_level', $level);
    }

    public function scopeByTerm($query, $term, $year)
    {
        return $query->where('admission_term', $term)
                    ->where('admission_year', $year);
    }

    /**
     * Accessors
     */
    public function getIsGraduateProgramAttribute()
    {
        return in_array($this->degree_level, ['master', 'phd']);
    }

    public function getIsUndergraduateAttribute()
    {
        return in_array($this->degree_level, ['associate', 'bachelor']);
    }

    public function getStatusBadgeColorAttribute()
    {
        return match($this->application_status) {
            'draft' => 'secondary',
            'submitted', 'under_review' => 'info',
            'additional_docs_required' => 'warning',
            'accepted' => 'success',
            'conditionally_accepted' => 'primary',
            'waitlisted' => 'warning',
            'rejected' => 'danger',
            'deferred' => 'secondary',
            default => 'secondary',
        };
    }

    public function getDaysUntilStartAttribute()
    {
        if (!$this->desired_start_date) {
            return null;
        }
        return now()->diffInDays($this->desired_start_date, false);
    }

    /**
     * Helper Methods
     */
    public function isComplete()
    {
        return !empty($this->transcript_path)
            && !empty($this->diploma_path)
            && !empty($this->personal_statement_path)
            && !empty($this->passport_copy_path)
            && ($this->toefl_score || $this->ielts_score);
    }

    public function canSubmit()
    {
        return $this->application_status === 'draft' && $this->isComplete();
    }

    public function meetsUniversityRequirements()
    {
        $university = $this->university;
        
        // GPA check
        if (!$university->meetsGpaRequirement($this->gpa, $this->is_graduate_program ? 'graduate' : 'undergraduate')) {
            return false;
        }
        
        // English proficiency check
        if (!$university->meetsEnglishRequirement($this->toefl_score, $this->ielts_score)) {
            return false;
        }
        
        return true;
    }

    public function updateStatus($status, $notes = null)
    {
        $this->update([
            'application_status' => $status,
            'last_status_update' => now(),
            'decision_notes' => $notes ?? $this->decision_notes,
        ]);
        
        if (in_array($status, ['accepted', 'conditionally_accepted', 'rejected', 'deferred'])) {
            $this->update(['decision_date' => now()]);
        }
    }

    public function requestI20()
    {
        $this->update([
            'i20_requested' => true,
        ]);
    }

    public function issueI20($sevisId, $documentPath)
    {
        $this->update([
            'i20_issued_date' => now(),
            'sevis_id' => $sevisId,
            'i20_document_path' => $documentPath,
        ]);
    }
}
