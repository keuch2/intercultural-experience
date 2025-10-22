<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkStudyProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'program_number',
        'language_school_name',
        'school_city',
        'school_state',
        'school_address',
        'school_website',
        'school_accreditation',
        'program_type',
        'weeks_of_study',
        'hours_per_week',
        'program_start_date',
        'program_end_date',
        'tuition_cost',
        'program_level',
        'current_english_level',
        'english_test_score',
        'english_test_type',
        'test_date',
        'includes_work_component',
        'work_hours_per_week',
        'work_category',
        'work_preferences',
        'expected_hourly_wage',
        'accommodation_type',
        'accommodation_included',
        'accommodation_cost_weekly',
        'accommodation_preferences',
        'insurance_included',
        'insurance_cost',
        'total_program_cost',
        'registration_fee',
        'materials_fee',
        'acceptance_letter_path',
        'i20_document_path',
        'sevis_id',
        'sevis_fee_paid_date',
        'payment_receipt_path',
        'status',
        'submission_date',
        'acceptance_date',
        'i20_issue_date',
        'visa_interview_date',
        'visa_approval_date',
        'employer_id',
        'work_start_date',
        'work_end_date',
        'special_requirements',
        'dietary_restrictions',
        'internal_notes',
        'processed_by',
        'last_status_update',
    ];

    protected $casts = [
        'program_start_date' => 'date',
        'program_end_date' => 'date',
        'tuition_cost' => 'decimal:2',
        'english_test_score' => 'integer',
        'test_date' => 'date',
        'includes_work_component' => 'boolean',
        'work_hours_per_week' => 'integer',
        'expected_hourly_wage' => 'decimal:2',
        'accommodation_included' => 'boolean',
        'accommodation_cost_weekly' => 'decimal:2',
        'insurance_included' => 'boolean',
        'insurance_cost' => 'decimal:2',
        'total_program_cost' => 'decimal:2',
        'registration_fee' => 'decimal:2',
        'materials_fee' => 'decimal:2',
        'submission_date' => 'date',
        'acceptance_date' => 'date',
        'i20_issue_date' => 'date',
        'visa_interview_date' => 'date',
        'visa_approval_date' => 'date',
        'sevis_fee_paid_date' => 'date',
        'work_start_date' => 'date',
        'work_end_date' => 'date',
        'last_status_update' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employer()
    {
        return $this->belongsTo(WorkStudyEmployer::class, 'employer_id');
    }

    public function placement()
    {
        return $this->hasOne(WorkStudyPlacement::class, 'program_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted', 'under_review']);
    }

    public function scopeBySchoolCity($query, $city)
    {
        return $query->where('school_city', $city);
    }

    /**
     * Accessors
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'submitted', 'under_review' => 'info',
            'accepted' => 'success',
            'i20_issued' => 'primary',
            'visa_approved' => 'success',
            'active' => 'success',
            'completed' => 'primary',
            'rejected', 'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getProgramDurationWeeksAttribute()
    {
        return $this->weeks_of_study;
    }

    public function getTotalCostAttribute()
    {
        $total = $this->tuition_cost + $this->registration_fee + $this->materials_fee;
        
        if ($this->accommodation_included && $this->accommodation_cost_weekly) {
            $total += ($this->accommodation_cost_weekly * $this->weeks_of_study);
        }
        
        if ($this->insurance_included && $this->insurance_cost) {
            $total += $this->insurance_cost;
        }
        
        return $total;
    }

    public function getDaysUntilStartAttribute()
    {
        if (!$this->program_start_date) {
            return null;
        }
        return now()->diffInDays($this->program_start_date, false);
    }

    /**
     * Helper Methods
     */
    public function isComplete()
    {
        return !empty($this->language_school_name)
            && !empty($this->program_start_date)
            && !empty($this->program_end_date)
            && !empty($this->tuition_cost);
    }

    public function canSubmit()
    {
        return $this->status === 'draft' && $this->isComplete();
    }

    public function updateStatus($status, $notes = null)
    {
        $this->update([
            'status' => $status,
            'last_status_update' => now(),
            'internal_notes' => $notes ?? $this->internal_notes,
        ]);
        
        if ($status === 'accepted' && !$this->acceptance_date) {
            $this->update(['acceptance_date' => now()]);
        }
    }

    public function issueI20($sevisId, $documentPath)
    {
        $this->update([
            'status' => 'i20_issued',
            'i20_issue_date' => now(),
            'sevis_id' => $sevisId,
            'i20_document_path' => $documentPath,
        ]);
    }

    public function hasPlacement()
    {
        return $this->placement()->exists();
    }
}
