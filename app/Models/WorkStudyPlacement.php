<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkStudyPlacement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'placement_number',
        'program_id',
        'user_id',
        'employer_id',
        'job_title',
        'job_description',
        'job_responsibilities',
        'start_date',
        'end_date',
        'hours_per_week',
        'work_days',
        'shift_type',
        'hourly_wage',
        'receives_tips',
        'avg_tips_weekly',
        'estimated_weekly_earnings',
        'total_earnings_to_date',
        'meals_provided',
        'uniform_provided',
        'transportation_provided',
        'housing_provided',
        'housing_cost_deduction',
        'total_hours_worked',
        'weeks_completed',
        'attendance_rating',
        'performance_rating',
        'supervisor_feedback',
        'incidents_reported',
        'warnings_issued',
        'issues_notes',
        'status',
        'activation_date',
        'completion_date',
        'termination_date',
        'termination_reason',
        'terminated_by',
        'job_offer_letter_path',
        'work_permit_path',
        'contract_signed_path',
        'performance_review_path',
        'supervisor_name',
        'supervisor_phone',
        'supervisor_email',
        'student_rating',
        'employer_rating',
        'student_review',
        'employer_review',
        'processed_by',
        'internal_notes',
    ];

    protected $casts = [
        'job_responsibilities' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'hours_per_week' => 'integer',
        'work_days' => 'array',
        'hourly_wage' => 'decimal:2',
        'receives_tips' => 'boolean',
        'avg_tips_weekly' => 'decimal:2',
        'estimated_weekly_earnings' => 'decimal:2',
        'total_earnings_to_date' => 'decimal:2',
        'meals_provided' => 'boolean',
        'uniform_provided' => 'boolean',
        'transportation_provided' => 'boolean',
        'housing_provided' => 'boolean',
        'housing_cost_deduction' => 'decimal:2',
        'total_hours_worked' => 'integer',
        'weeks_completed' => 'integer',
        'incidents_reported' => 'integer',
        'warnings_issued' => 'integer',
        'activation_date' => 'date',
        'completion_date' => 'date',
        'termination_date' => 'date',
        'student_rating' => 'integer',
        'employer_rating' => 'integer',
    ];

    /**
     * Relationships
     */
    public function program()
    {
        return $this->belongsTo(WorkStudyProgram::class, 'program_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function employer()
    {
        return $this->belongsTo(WorkStudyEmployer::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function terminator()
    {
        return $this->belongsTo(User::class, 'terminated_by');
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Accessors
     */
    public function getStatusBadgeColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'active' => 'success',
            'on_hold' => 'secondary',
            'completed' => 'primary',
            'terminated_student', 'terminated_employer', 'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getWeeksRemainingAttribute()
    {
        if (!$this->end_date) {
            return null;
        }
        return now()->diffInWeeks($this->end_date, false);
    }

    public function getCompletionPercentageAttribute()
    {
        if (!$this->start_date || !$this->end_date) {
            return 0;
        }
        
        $totalDays = $this->start_date->diffInDays($this->end_date);
        $daysCompleted = $this->start_date->diffInDays(now());
        
        return min(100, round(($daysCompleted / $totalDays) * 100, 1));
    }

    public function getNetEarningsAttribute()
    {
        $net = $this->total_earnings_to_date;
        
        if ($this->housing_cost_deduction) {
            $net -= ($this->housing_cost_deduction * $this->weeks_completed);
        }
        
        return max(0, $net);
    }

    /**
     * Helper Methods
     */
    public function activate()
    {
        $this->update([
            'status' => 'active',
            'activation_date' => now(),
        ]);
        
        // Update employer student count
        $this->employer->incrementStudent();
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completion_date' => now(),
        ]);
        
        // Update employer student count
        $this->employer->decrementStudent();
    }

    public function terminate($reason, $terminatedBy)
    {
        $this->update([
            'status' => 'terminated_student',
            'termination_date' => now(),
            'termination_reason' => $reason,
            'terminated_by' => $terminatedBy,
        ]);
        
        // Update employer student count
        $this->employer->decrementStudent();
    }

    public function addHoursWorked($hours, $earnings = null)
    {
        $this->increment('total_hours_worked', $hours);
        
        if ($earnings) {
            $this->increment('total_earnings_to_date', $earnings);
        }
    }

    public function reportIncident($description)
    {
        $this->increment('incidents_reported');
        
        $notes = $this->issues_notes ?? '';
        $notes .= "\n[" . now()->format('Y-m-d H:i') . "] " . $description;
        
        $this->update(['issues_notes' => $notes]);
    }

    public function issueWarning($reason)
    {
        $this->increment('warnings_issued');
        
        $notes = $this->issues_notes ?? '';
        $notes .= "\n[WARNING - " . now()->format('Y-m-d H:i') . "] " . $reason;
        
        $this->update(['issues_notes' => $notes]);
    }
}
