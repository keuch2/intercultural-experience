<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkTravelData extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'university',
        'career',
        'year_semester',
        'modality',
        'university_certificate_path',
        'english_level_self',
        'english_level_certified',
        'cefr_level',
        'efset_id',
        'english_attempts',
        'last_english_evaluation',
        'job_offer_id',
        'sponsor',
        'host_company_name',
        'job_position',
        'job_city',
        'job_state',
        'hourly_rate',
        'housing',
        'program_start_date',
        'program_end_date',
        'job_reserved_at',
        'sponsor_interview_status',
        'sponsor_interview_date',
        'sponsor_interview_notes',
        'job_interview_status',
        'job_interview_date',
        'job_interview_notes',
        'program_expectations',
        'tolerant_to_demands',
        'flexible_to_changes',
        'intention_to_stay',
        'willing_share_accommodation',
        'aware_adult_program',
        'current_stage',
    ];

    protected $casts = [
        'last_english_evaluation' => 'date',
        'program_start_date' => 'date',
        'program_end_date' => 'date',
        'job_reserved_at' => 'date',
        'sponsor_interview_date' => 'datetime',
        'job_interview_date' => 'datetime',
        'hourly_rate' => 'decimal:2',
        'tolerant_to_demands' => 'boolean',
        'flexible_to_changes' => 'boolean',
        'intention_to_stay' => 'boolean',
        'willing_share_accommodation' => 'boolean',
        'aware_adult_program' => 'boolean',
        'english_attempts' => 'integer',
    ];

    /**
     * Relación con Application
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Relación con JobOffer
     */
    public function jobOffer(): BelongsTo
    {
        return $this->belongsTo(JobOffer::class);
    }

    /**
     * Obtener etiqueta legible de la etapa actual
     */
    public function getCurrentStageLabel(): string
    {
        $labels = [
            'registration' => 'Inscripción',
            'english_evaluation' => 'Evaluación de Inglés',
            'documentation' => 'Documentación',
            'job_selection' => 'Selección de Job Offer',
            'sponsor_interview' => 'Entrevista Sponsor',
            'job_interview' => 'Entrevista Trabajo',
            'job_confirmed' => 'Trabajo Confirmado',
            'visa_process' => 'Proceso de Visa',
            'pre_departure' => 'Pre-Viaje',
            'in_program' => 'En Programa',
            'completed' => 'Completado',
        ];

        return $labels[$this->current_stage] ?? ucfirst($this->current_stage);
    }

    /**
     * Verificar si puede realizar evaluación de inglés
     */
    public function canTakeEnglishEvaluation(): bool
    {
        return $this->english_attempts < 3;
    }

    /**
     * Verificar si cumple requisito mínimo de inglés (B1+)
     */
    public function meetsEnglishRequirement(): bool
    {
        $validLevels = ['B1', 'B2', 'C1', 'C2'];
        return in_array($this->cefr_level, $validLevels);
    }

    /**
     * Verificar si está listo para aplicar a job offers
     */
    public function isReadyForJobOffers(): bool
    {
        return $this->meetsEnglishRequirement() 
            && $this->modality === 'presencial'
            && !empty($this->university_certificate_path);
    }

    /**
     * Scope: Participantes con inglés aprobado
     */
    public function scopeEnglishApproved($query)
    {
        return $query->whereIn('cefr_level', ['B1', 'B2', 'C1', 'C2']);
    }

    /**
     * Scope: Por etapa
     */
    public function scopeInStage($query, $stage)
    {
        return $query->where('current_stage', $stage);
    }
}
