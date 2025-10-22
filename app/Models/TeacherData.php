<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherData extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id', 'university_degree', 'degree_title', 'educational_institution',
        'graduation_year', 'degree_certificate_path', 'academic_transcript_path',
        'mec_registration_number', 'mec_certificate_path', 'mec_registration_date',
        'mec_validated', 'mec_validation_date', 'specializations',
        'teaching_experience_detailed', 'institutions_worked', 'age_segments',
        'subjects_taught', 'weekly_hours', 'years_experience', 'institution_type',
        'educational_levels', 'methodologies', 'professional_references',
        'english_level_required', 'cefr_level', 'efset_id', 'english_attempts',
        'last_english_evaluation', 'english_deadline', 'english_requirement_met',
        'program_expectations', 'tolerant_to_demands', 'flexible_cultural_activities',
        'share_paraguayan_culture', 'intention_to_stay', 'willing_share_accommodation',
        'aware_maturity_required', 'school_district_id', 'specific_school',
        'school_city', 'school_state', 'education_level', 'subjects_to_teach',
        'teaching_start_date', 'teaching_end_date', 'annual_salary', 'housing_notes',
        'participated_job_fair', 'job_fair_interviews', 'job_offer_received_at', 'current_stage',
    ];

    protected $casts = [
        'graduation_year' => 'integer',
        'mec_registration_date' => 'date',
        'mec_validated' => 'boolean',
        'mec_validation_date' => 'date',
        'specializations' => 'array',
        'institutions_worked' => 'array',
        'professional_references' => 'array',
        'english_attempts' => 'integer',
        'last_english_evaluation' => 'date',
        'english_deadline' => 'date',
        'english_requirement_met' => 'boolean',
        'tolerant_to_demands' => 'boolean',
        'flexible_cultural_activities' => 'boolean',
        'share_paraguayan_culture' => 'boolean',
        'intention_to_stay' => 'boolean',
        'willing_share_accommodation' => 'boolean',
        'aware_maturity_required' => 'boolean',
        'teaching_start_date' => 'date',
        'teaching_end_date' => 'date',
        'annual_salary' => 'decimal:2',
        'participated_job_fair' => 'boolean',
        'job_fair_interviews' => 'array',
        'job_offer_received_at' => 'date',
        'weekly_hours' => 'integer',
        'years_experience' => 'integer',
    ];

    public function application(): BelongsTo { return $this->belongsTo(Application::class); }

    public function getCurrentStageLabel(): string {
        $labels = [
            'registration' => 'Inscripción', 'english_evaluation' => 'Evaluación de Inglés',
            'documentation' => 'Documentación', 'mec_validation' => 'Validación MEC',
            'application_review' => 'Revisión de Aplicación', 'job_fair' => 'Job Fair',
            'district_interviews' => 'Entrevistas con Distritos', 'job_offer' => 'Oferta de Trabajo',
            'position_confirmed' => 'Posición Confirmada', 'visa_process' => 'Proceso de Visa',
            'pre_departure' => 'Pre-Viaje', 'in_program' => 'En Programa', 'completed' => 'Completado',
        ];
        return $labels[$this->current_stage] ?? ucfirst($this->current_stage);
    }

    public function isMecValidated(): bool { return $this->mec_validated === true; }

    public function meetsEnglishRequirement(): bool {
        return in_array($this->cefr_level, ['C1', 'C2']);
    }

    public function hasRequiredExperience(): bool { return $this->years_experience >= 2; }

    public function isReadyForJobFair(): bool {
        return $this->isMecValidated() 
            && $this->meetsEnglishRequirement()
            && $this->hasRequiredExperience()
            && !empty($this->degree_certificate_path);
    }

    public function scopeMecValidated($query) { return $query->where('mec_validated', true); }
    public function scopeEnglishReady($query) { return $query->whereIn('cefr_level', ['C1', 'C2']); }
    public function scopeInStage($query, $stage) { return $query->where('current_stage', $stage); }
}
