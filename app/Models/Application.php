<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'user_id', 'program_id', 'status', 'applied_at', 'completed_at',
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class);
    }
    
    public function requisites()
    {
        return $this->hasMany(UserProgramRequisite::class);
    }
    
    /**
     * Calcula el progreso de la solicitud en porcentaje
     * 
     * @return int
     */
    public function getProgressPercentage()
    {
        $totalRequisites = $this->requisites()->count();
        
        if ($totalRequisites === 0) {
            return 0;
        }
        
        $completedRequisites = $this->requisites()
            ->whereIn('status', ['completed', 'verified'])
            ->count();
            
        return round(($completedRequisites / $totalRequisites) * 100);
    }
}