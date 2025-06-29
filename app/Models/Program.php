<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'name', 'description', 'country', 'category', 'is_active',
        'location', 'start_date', 'end_date', 'application_deadline',
        'duration', 'credits', 'capacity', 'available_spots', 
        'image_url', 'cost', 'currency_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date', 
        'application_deadline' => 'date',
        'cost' => 'decimal:4',
    ];

    // Relationships
    public function applications()
    {
        return $this->hasMany(Application::class);
    }
    
    public function requisites()
    {
        return $this->hasMany(ProgramRequisite::class)->orderBy('order');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function forms()
    {
        return $this->hasMany(ProgramForm::class);
    }

    public function activeForm()
    {
        return $this->hasOne(ProgramForm::class)->where('is_active', true)->where('is_published', true);
    }

    public function assignments()
    {
        return $this->hasMany(ProgramAssignment::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToManyThrough(
            User::class,
            ProgramAssignment::class,
            'program_id',
            'id',
            'id',
            'user_id'
        );
    }

    public function activeAssignments()
    {
        return $this->hasMany(ProgramAssignment::class)->active();
    }

    // Accessors
    public function getFormattedCostAttribute()
    {
        if (!$this->currency) {
            return number_format($this->cost, 2);
        }
        
        return $this->currency->symbol . ' ' . number_format($this->cost, 2);
    }

    public function getCostInPygAttribute()
    {
        if (!$this->currency) {
            return $this->cost;
        }
        
        return $this->currency->convertToPyg($this->cost);
    }

    public function getFormattedCostInPygAttribute()
    {
        return '₲ ' . number_format($this->cost_in_pyg, 0);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Métodos auxiliares para asignaciones
    public function getAssignmentStats()
    {
        return ProgramAssignment::getAssignmentStats($this->id);
    }

    public function hasAssignmentFor($userId)
    {
        return $this->assignments()
            ->where('user_id', $userId)
            ->exists();
    }

    public function getAssignmentFor($userId)
    {
        return $this->assignments()
            ->where('user_id', $userId)
            ->first();
    }

    public function getAvailableSpots()
    {
        $acceptedAssignments = $this->assignments()
            ->where('status', ProgramAssignment::STATUS_ACCEPTED)
            ->count();
        
        return max(0, $this->capacity - $acceptedAssignments);
    }

    public function canAcceptMoreParticipants()
    {
        return $this->getAvailableSpots() > 0;
    }
}