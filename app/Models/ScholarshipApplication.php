<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScholarshipApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'scholarship_id',
        'university_application_id',
        'application_number',
        'application_date',
        'essay_path',
        'motivation_letter_path',
        'financial_need_statement_path',
        'portfolio_path',
        'additional_documents_paths',
        'essay_text',
        'why_deserve_scholarship',
        'financial_need_explanation',
        'status',
        'submission_date',
        'decision_date',
        'decision_notes',
        'is_awarded',
        'awarded_amount',
        'award_start_date',
        'award_end_date',
        'award_accepted',
        'award_acceptance_date',
        'interview_date',
        'interview_time',
        'interview_location',
        'interview_notes',
        'reviewed_by',
        'last_status_update',
        'internal_notes',
    ];

    protected $casts = [
        'application_date' => 'date',
        'additional_documents_paths' => 'array',
        'submission_date' => 'date',
        'decision_date' => 'date',
        'is_awarded' => 'boolean',
        'awarded_amount' => 'decimal:2',
        'award_start_date' => 'date',
        'award_end_date' => 'date',
        'award_accepted' => 'boolean',
        'award_acceptance_date' => 'date',
        'interview_date' => 'date',
        'interview_time' => 'time',
        'last_status_update' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function universityApplication()
    {
        return $this->belongsTo(HigherEducationApplication::class, 'university_application_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scopes
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeAwarded($query)
    {
        return $query->where('is_awarded', true);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted', 'under_review']);
    }

    public function scopeNeedsInterview($query)
    {
        return $query->where('status', 'interview_scheduled')
                    ->whereNotNull('interview_date');
    }

    /**
     * Accessors
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'draft' => 'secondary',
            'submitted', 'under_review' => 'info',
            'shortlisted' => 'primary',
            'interview_scheduled' => 'warning',
            'awarded' => 'success',
            'rejected' => 'danger',
            'withdrawn' => 'secondary',
            default => 'secondary',
        };
    }

    public function getDaysUntilInterviewAttribute()
    {
        if (!$this->interview_date) {
            return null;
        }
        return now()->diffInDays($this->interview_date, false);
    }

    /**
     * Helper Methods
     */
    public function isComplete()
    {
        $scholarship = $this->scholarship;
        
        $hasRequiredDocs = true;
        
        if ($scholarship->requires_essay && !$this->essay_path && !$this->essay_text) {
            $hasRequiredDocs = false;
        }
        
        if ($scholarship->requires_portfolio && !$this->portfolio_path) {
            $hasRequiredDocs = false;
        }
        
        return $hasRequiredDocs && !empty($this->motivation_letter_path);
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
            'decision_notes' => $notes ?? $this->decision_notes,
        ]);
        
        if ($status === 'submitted') {
            $this->update(['submission_date' => now()]);
        }
        
        if (in_array($status, ['awarded', 'rejected'])) {
            $this->update(['decision_date' => now()]);
        }
    }

    public function awardScholarship($amount, $startDate, $endDate)
    {
        $this->update([
            'is_awarded' => true,
            'status' => 'awarded',
            'awarded_amount' => $amount,
            'award_start_date' => $startDate,
            'award_end_date' => $endDate,
            'decision_date' => now(),
        ]);
        
        // Decrease available awards
        $this->scholarship->decrementAward();
    }

    public function acceptAward()
    {
        if (!$this->is_awarded) {
            return false;
        }
        
        $this->update([
            'award_accepted' => true,
            'award_acceptance_date' => now(),
        ]);
        
        return true;
    }

    public function scheduleInterview($date, $time, $location)
    {
        $this->update([
            'status' => 'interview_scheduled',
            'interview_date' => $date,
            'interview_time' => $time,
            'interview_location' => $location,
        ]);
    }
}
