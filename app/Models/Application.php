<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'user_id',
        'program_id',
        'status',
        'full_name',
        'birth_date',
        'cedula',
        'passport_number',
        'passport_expiry',
        'phone',
        'address',
        'city',
        'country',
        'current_stage',
        'progress_percentage',
        'total_cost',
        'amount_paid',
        'applied_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'passport_expiry' => 'date',
        'applied_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_cost' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'progress_percentage' => 'integer',
    ];

    /**
     * Accessors que se agregan automáticamente a las respuestas JSON
     */
    protected $appends = [
        'progress_percentage',
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
     * Relación con VisaProcess
     */
    public function visaProcess()
    {
        return $this->hasOne(VisaProcess::class);
    }

    /**
     * Relación con JobOfferReservation
     */
    public function jobOfferReservation()
    {
        return $this->hasOne(JobOfferReservation::class);
    }

    /**
     * Relación con Work Travel Data
     */
    public function workTravelData()
    {
        return $this->hasOne(WorkTravelData::class);
    }

    /**
     * Relación con Au Pair Data
     */
    public function auPairData()
    {
        return $this->hasOne(AuPairData::class);
    }

    /**
     * Relación con Teacher Data
     */
    public function teacherData()
    {
        return $this->hasOne(TeacherData::class);
    }

    /**
     * Relación con Program History
     */
    public function programHistory()
    {
        return $this->hasMany(ProgramHistory::class);
    }

    /**
     * Relación con Payments
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Obtener datos específicos según el programa
     */
    public function getSpecificData()
    {
        if (!$this->program) return null;

        switch($this->program->subcategory) {
            case 'Work and Travel':
                return $this->workTravelData;
            case 'Au Pair':
                return $this->auPairData;
            case "Teacher's Program":
                return $this->teacherData;
            default:
                return null;
        }
    }

    /**
     * Scope: Participantes IE Cue (Alumni)
     */
    public function scopeIeCue($query)
    {
        return $query->where('is_ie_cue', true);
    }

    /**
     * Scope: Programas actuales (activos)
     */
    public function scopeCurrentPrograms($query)
    {
        return $query->where('is_current_program', true);
    }

    /**
     * Scope: Programas completados
     */
    public function scopeCompletedPrograms($query)
    {
        return $query->where('current_stage', 'completed');
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

    /**
     * Accessor para progress_percentage
     * Para sincronización con React Native
     */
    public function getProgressPercentageAttribute()
    {
        return $this->getProgressPercentage();
    }
}