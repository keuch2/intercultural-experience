<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'plan_number',
        'user_id',
        'host_company_id',
        'validation_id',
        'plan_title',
        'plan_description',
        'program_type',
        'position_title',
        'department',
        'primary_objectives',
        'learning_outcomes',
        'skills_to_acquire',
        'competencies_to_develop',
        'training_phases',
        'milestones',
        'evaluation_criteria',
        'participant_responsibilities',
        'company_responsibilities',
        'supervisor_responsibilities',
        'supervisor_name',
        'supervisor_title',
        'supervisor_email',
        'supervisor_phone',
        'supervision_hours_per_week',
        'start_date',
        'end_date',
        'total_duration_months',
        'hours_per_week',
        'work_schedule',
        'training_location_address',
        'training_location_city',
        'training_location_state',
        'training_location_zip',
        'allows_remote_work',
        'remote_days_per_week',
        'is_paid',
        'stipend_amount',
        'stipend_frequency',
        'benefits_included',
        'provides_housing',
        'housing_details',
        'housing_cost',
        'requires_progress_reports',
        'report_frequency',
        'requires_final_presentation',
        'offers_certificate',
        'certificate_type',
        'company_approved',
        'company_approved_at',
        'company_approved_by',
        'participant_approved',
        'participant_approved_at',
        'sponsor_approved',
        'sponsor_approved_at',
        'sponsor_approved_by',
        'status',
        'completion_percentage',
        'completed_milestones',
        'actual_start_date',
        'actual_end_date',
        'completion_notes',
        'plan_document_path',
        'signed_plan_path',
        'progress_reports_paths',
        'final_report_path',
        'certificate_path',
        'termination_reason',
        'termination_date',
        'terminated_by',
    ];

    protected $casts = [
        'primary_objectives' => 'array',
        'learning_outcomes' => 'array',
        'skills_to_acquire' => 'array',
        'competencies_to_develop' => 'array',
        'training_phases' => 'array',
        'milestones' => 'array',
        'evaluation_criteria' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'work_schedule' => 'array',
        'allows_remote_work' => 'boolean',
        'is_paid' => 'boolean',
        'stipend_amount' => 'decimal:2',
        'benefits_included' => 'array',
        'provides_housing' => 'boolean',
        'housing_cost' => 'decimal:2',
        'requires_progress_reports' => 'boolean',
        'requires_final_presentation' => 'boolean',
        'offers_certificate' => 'boolean',
        'company_approved' => 'boolean',
        'company_approved_at' => 'datetime',
        'participant_approved' => 'boolean',
        'participant_approved_at' => 'datetime',
        'sponsor_approved' => 'boolean',
        'sponsor_approved_at' => 'datetime',
        'completion_percentage' => 'integer',
        'completed_milestones' => 'array',
        'actual_start_date' => 'date',
        'actual_end_date' => 'date',
        'progress_reports_paths' => 'array',
        'termination_date' => 'date',
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

    public function validation()
    {
        return $this->belongsTo(InternTraineeValidation::class, 'validation_id');
    }

    public function companyApprover()
    {
        return $this->belongsTo(User::class, 'company_approved_by');
    }

    public function sponsorApprover()
    {
        return $this->belongsTo(User::class, 'sponsor_approved_by');
    }

    public function terminator()
    {
        return $this->belongsTo(User::class, 'terminated_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending_company_approval', 'pending_participant_approval', 'pending_sponsor_approval']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Accessors
     */
    public function getIsFullyApprovedAttribute()
    {
        return $this->company_approved 
            && $this->participant_approved 
            && $this->sponsor_approved;
    }

    public function getDurationWeeksAttribute()
    {
        return $this->total_duration_months * 4;
    }

    public function getTotalEarningsAttribute()
    {
        if (!$this->is_paid || !$this->stipend_amount) {
            return 0;
        }

        $multiplier = match($this->stipend_frequency) {
            'weekly' => $this->duration_weeks,
            'monthly' => $this->total_duration_months,
            default => 0,
        };

        return $this->stipend_amount * $multiplier;
    }

    /**
     * Helper Methods
     */
    public function canBeActivated()
    {
        return $this->is_fully_approved && $this->status === 'approved';
    }

    public function updateCompletionPercentage()
    {
        if (!$this->milestones) {
            return;
        }

        $totalMilestones = count($this->milestones);
        $completedMilestones = count($this->completed_milestones ?? []);
        
        $percentage = ($completedMilestones / $totalMilestones) * 100;
        
        $this->update(['completion_percentage' => round($percentage)]);
    }

    public function markMilestoneComplete($milestoneId)
    {
        $completed = $this->completed_milestones ?? [];
        
        if (!in_array($milestoneId, $completed)) {
            $completed[] = $milestoneId;
            $this->update(['completed_milestones' => $completed]);
            $this->updateCompletionPercentage();
        }
    }

    public function approveByCompany($userId)
    {
        $this->update([
            'company_approved' => true,
            'company_approved_at' => now(),
            'company_approved_by' => $userId,
        ]);

        $this->checkAndUpdateStatus();
    }

    public function approveByParticipant()
    {
        $this->update([
            'participant_approved' => true,
            'participant_approved_at' => now(),
        ]);

        $this->checkAndUpdateStatus();
    }

    public function approveBySponsor($userId)
    {
        $this->update([
            'sponsor_approved' => true,
            'sponsor_approved_at' => now(),
            'sponsor_approved_by' => $userId,
        ]);

        $this->checkAndUpdateStatus();
    }

    protected function checkAndUpdateStatus()
    {
        if ($this->is_fully_approved) {
            $this->update(['status' => 'approved']);
        }
    }

    public function terminate($reason, $userId)
    {
        $this->update([
            'status' => 'terminated',
            'termination_reason' => $reason,
            'termination_date' => now(),
            'terminated_by' => $userId,
        ]);
    }
}
