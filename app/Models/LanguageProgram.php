<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LanguageProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'program_number',
        'school_name',
        'school_city',
        'school_state',
        'school_country',
        'school_address',
        'school_website',
        'school_phone',
        'school_accreditation',
        'language',
        'current_level',
        'target_level',
        'program_type',
        'exam_type',
        'activity_type',
        'weeks_duration',
        'hours_per_week',
        'class_size_max',
        'start_date',
        'end_date',
        'tuition_fee',
        'registration_fee',
        'materials_fee',
        'exam_fee',
        'activity_fee',
        'accommodation_type',
        'accommodation_included',
        'accommodation_cost_weekly',
        'meals_included',
        'meal_plan',
        'airport_transfer',
        'airport_transfer_cost',
        'insurance_included',
        'insurance_cost',
        'study_materials_included',
        'total_program_cost',
        'acceptance_letter_path',
        'visa_support_letter_path',
        'payment_receipt_path',
        'certificate_path',
        'placement_test_score',
        'placement_test_date',
        'assigned_level',
        'attendance_percentage',
        'completed_weeks',
        'teacher_feedback',
        'progress_rating',
        'status',
        'submission_date',
        'acceptance_date',
        'enrollment_date',
        'completion_date',
        'learning_goals',
        'special_requirements',
        'dietary_restrictions',
        'internal_notes',
        'processed_by',
        'last_status_update',
    ];

    protected $casts = [
        'weeks_duration' => 'integer',
        'hours_per_week' => 'integer',
        'class_size_max' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'tuition_fee' => 'decimal:2',
        'registration_fee' => 'decimal:2',
        'materials_fee' => 'decimal:2',
        'exam_fee' => 'decimal:2',
        'activity_fee' => 'decimal:2',
        'accommodation_included' => 'boolean',
        'accommodation_cost_weekly' => 'decimal:2',
        'meals_included' => 'boolean',
        'airport_transfer' => 'boolean',
        'airport_transfer_cost' => 'decimal:2',
        'insurance_included' => 'boolean',
        'insurance_cost' => 'decimal:2',
        'study_materials_included' => 'boolean',
        'total_program_cost' => 'decimal:2',
        'placement_test_score' => 'integer',
        'placement_test_date' => 'date',
        'attendance_percentage' => 'integer',
        'completed_weeks' => 'integer',
        'submission_date' => 'date',
        'acceptance_date' => 'date',
        'enrollment_date' => 'date',
        'completion_date' => 'date',
        'last_status_update' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['draft', 'submitted', 'under_review']);
    }

    public function scopeByLanguage($query, $language)
    {
        return $query->where('language', $language);
    }

    public function scopeByProgramType($query, $type)
    {
        return $query->where('program_type', $type);
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
            'enrolled', 'active' => 'primary',
            'completed' => 'success',
            'rejected', 'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getLanguageNameAttribute()
    {
        return match($this->language) {
            'english' => 'English',
            'spanish' => 'Spanish',
            'french' => 'French',
            'german' => 'German',
            'italian' => 'Italian',
            'portuguese' => 'Portuguese',
            'mandarin' => 'Mandarin Chinese',
            default => ucfirst($this->language),
        };
    }

    public function getProgramTypeNameAttribute()
    {
        return match($this->program_type) {
            'general_language' => 'General Language',
            'intensive' => 'Intensive',
            'super_intensive' => 'Super Intensive',
            'business_language' => 'Business Language',
            'exam_preparation' => 'Exam Preparation',
            'academic_language' => 'Academic Language',
            'conversation_only' => 'Conversation Only',
            'private_lessons' => 'Private Lessons',
            'language_plus_activity' => 'Language + Activity',
            default => ucfirst(str_replace('_', ' ', $this->program_type)),
        };
    }

    public function getTotalHoursAttribute()
    {
        return $this->weeks_duration * $this->hours_per_week;
    }

    public function getWeeksRemainingAttribute()
    {
        if (!$this->end_date) {
            return null;
        }
        return max(0, now()->diffInWeeks($this->end_date, false));
    }

    public function getCompletionPercentageAttribute()
    {
        if ($this->weeks_duration == 0) {
            return 0;
        }
        return min(100, round(($this->completed_weeks / $this->weeks_duration) * 100, 1));
    }

    public function getDaysUntilStartAttribute()
    {
        if (!$this->start_date) {
            return null;
        }
        return now()->diffInDays($this->start_date, false);
    }

    /**
     * Helper Methods
     */
    public function calculateTotalCost()
    {
        $total = $this->tuition_fee + $this->registration_fee + $this->materials_fee;
        
        if ($this->exam_fee) {
            $total += $this->exam_fee;
        }
        
        if ($this->activity_fee) {
            $total += $this->activity_fee;
        }
        
        if ($this->accommodation_included && $this->accommodation_cost_weekly) {
            $total += ($this->accommodation_cost_weekly * $this->weeks_duration);
        }
        
        if ($this->airport_transfer && $this->airport_transfer_cost) {
            $total += $this->airport_transfer_cost;
        }
        
        if ($this->insurance_included && $this->insurance_cost) {
            $total += $this->insurance_cost;
        }
        
        return $total;
    }

    public function isComplete()
    {
        return !empty($this->school_name)
            && !empty($this->start_date)
            && !empty($this->end_date)
            && !empty($this->tuition_fee);
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
        
        if ($status === 'enrolled' && !$this->enrollment_date) {
            $this->update(['enrollment_date' => now()]);
        }
        
        if ($status === 'completed' && !$this->completion_date) {
            $this->update(['completion_date' => now()]);
        }
    }

    public function recordAttendance($percentage)
    {
        $this->update(['attendance_percentage' => $percentage]);
    }

    public function updateProgress($weeksCompleted, $feedback = null, $rating = null)
    {
        $data = ['completed_weeks' => $weeksCompleted];
        
        if ($feedback) {
            $data['teacher_feedback'] = $feedback;
        }
        
        if ($rating) {
            $data['progress_rating'] = $rating;
        }
        
        $this->update($data);
    }

    public function issueCertificate($certificatePath)
    {
        $this->update([
            'certificate_path' => $certificatePath,
            'status' => 'completed',
            'completion_date' => now(),
        ]);
    }
}
