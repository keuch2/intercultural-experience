<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuPairData extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'childcare_experience_detailed',
        'ages_cared_for',
        'experience_durations',
        'care_types',
        'cared_for_babies',
        'special_needs_experience',
        'special_needs_details',
        'has_first_aid_cert',
        'first_aid_cert_path',
        'first_aid_cert_expiry',
        'has_cpr_cert',
        'cpr_cert_path',
        'cpr_cert_expiry',
        'other_certifications',
        'references',
        'photos',
        'presentation_video_path',
        'dear_host_family_letter_path',
        'has_drivers_license',
        'drivers_license_number',
        'drivers_license_country',
        'drivers_license_expiry',
        'drivers_license_path',
        'host_family_id',
        'host_family_city',
        'host_family_state',
        'host_family_country',
        'number_of_children',
        'children_ages',
        'children_special_needs',
        'children_special_needs_details',
        'has_pets',
        'pets_details',
        'work_schedule',
        'start_date_with_family',
        'profile_active',
        'profile_activated_at',
        'family_interviews',
        'matched_at',
        'english_level',
        'cefr_level',
        'current_stage',
    ];

    protected $casts = [
        'cared_for_babies' => 'boolean',
        'special_needs_experience' => 'boolean',
        'has_first_aid_cert' => 'boolean',
        'first_aid_cert_expiry' => 'date',
        'has_cpr_cert' => 'boolean',
        'cpr_cert_expiry' => 'date',
        'references' => 'array',
        'photos' => 'array',
        'has_drivers_license' => 'boolean',
        'drivers_license_expiry' => 'date',
        'children_ages' => 'array',
        'children_special_needs' => 'boolean',
        'has_pets' => 'boolean',
        'start_date_with_family' => 'date',
        'profile_active' => 'boolean',
        'profile_activated_at' => 'date',
        'family_interviews' => 'integer',
        'matched_at' => 'date',
    ];

    /**
     * Relación con Application
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    /**
     * Obtener etiqueta legible de la etapa actual
     */
    public function getCurrentStageLabel(): string
    {
        $labels = [
            'registration' => 'Inscripción',
            'profile_creation' => 'Creación de Perfil',
            'documentation' => 'Documentación',
            'profile_review' => 'Revisión de Perfil',
            'profile_active' => 'Perfil Activo',
            'matching' => 'Búsqueda de Familia',
            'family_interviews' => 'Entrevistas con Familias',
            'match_confirmed' => 'Match Confirmado',
            'visa_process' => 'Proceso de Visa',
            'training' => 'Entrenamiento',
            'travel' => 'Viaje',
            'in_program' => 'En Programa',
            'completed' => 'Completado',
        ];

        return $labels[$this->current_stage] ?? ucfirst($this->current_stage);
    }

    /**
     * Verificar si el perfil está completo
     */
    public function isProfileComplete(): bool
    {
        return !empty($this->childcare_experience_detailed)
            && !empty($this->references)
            && count($this->references ?? []) >= 3
            && !empty($this->photos)
            && count($this->photos ?? []) >= 6
            && !empty($this->presentation_video_path)
            && !empty($this->dear_host_family_letter_path);
    }

    /**
     * Verificar si cumple experiencia mínima (200 horas)
     */
    public function meetsMinimumExperience(): bool
    {
        // Lógica para calcular horas totales basado en experience_durations
        return !empty($this->childcare_experience_detailed);
    }

    /**
     * Verificar si tiene certificaciones requeridas
     */
    public function hasRequiredCertifications(): bool
    {
        return $this->has_first_aid_cert && $this->has_cpr_cert;
    }

    /**
     * Calcular puntaje de perfil
     */
    public function calculateProfileScore(): int
    {
        $score = 0;

        // Experiencia (40 puntos)
        if ($this->cared_for_babies) $score += 20;
        if ($this->special_needs_experience) $score += 20;

        // Certificaciones (20 puntos)
        if ($this->has_first_aid_cert) $score += 10;
        if ($this->has_cpr_cert) $score += 10;

        // Licencia de conducir (15 puntos)
        if ($this->has_drivers_license) $score += 15;

        // Referencias (15 puntos)
        $refCount = count($this->references ?? []);
        $score += min($refCount * 5, 15);

        // Completitud del perfil (10 puntos)
        if ($this->isProfileComplete()) $score += 10;

        return $score;
    }

    /**
     * Scope: Perfiles activos
     */
    public function scopeActive($query)
    {
        return $query->where('profile_active', true);
    }

    /**
     * Scope: Por etapa
     */
    public function scopeInStage($query, $stage)
    {
        return $query->where('current_stage', $stage);
    }
}
