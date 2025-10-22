<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkExperienceDetailed extends Model
{
    protected $table = 'work_experiences_detailed';

    protected $fillable = [
        'user_id',
        'company_name',
        'position',
        'department',
        'responsibilities',
        'start_date',
        'end_date',
        'is_current',
        'institution_type',
        'grade_levels',
        'subjects_taught',
        'weekly_hours',
        'supervisor_name',
        'supervisor_phone',
        'supervisor_email'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
        'subjects_taught' => 'array',
        'weekly_hours' => 'integer',
    ];

    /**
     * Get the user that owns the work experience.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the duration in months.
     */
    public function getDurationMonthsAttribute()
    {
        if ($this->is_current) {
            return $this->start_date->diffInMonths(now());
        }

        if ($this->end_date) {
            return $this->start_date->diffInMonths($this->end_date);
        }

        return 0;
    }

    /**
     * Get the duration in years.
     */
    public function getDurationYearsAttribute()
    {
        return round($this->duration_months / 12, 1);
    }

    /**
     * Check if this is teaching experience.
     */
    public function getIsTeachingExperienceAttribute()
    {
        return !empty($this->institution_type) 
            || !empty($this->grade_levels) 
            || !empty($this->subjects_taught);
    }

    /**
     * Scope for current positions.
     */
    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    /**
     * Scope for teaching experiences.
     */
    public function scopeTeaching($query)
    {
        return $query->whereNotNull('institution_type');
    }

    /**
     * Scope for experiences with supervisor references.
     */
    public function scopeWithReferences($query)
    {
        return $query->whereNotNull('supervisor_name')
            ->whereNotNull('supervisor_phone');
    }

    /**
     * Get formatted grade levels.
     */
    public function getFormattedGradeLevelsAttribute()
    {
        if (!$this->grade_levels) {
            return null;
        }

        $levels = [
            'K-5' => 'Elementary',
            '6-8' => 'Middle School',
            '9-12' => 'High School'
        ];

        return $levels[$this->grade_levels] ?? $this->grade_levels;
    }
}
