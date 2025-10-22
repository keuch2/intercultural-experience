<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostCompany extends Model
{
    use SoftDeletes;

    protected $fillable = [
        // Basic Info
        'company_name',
        'legal_name',
        'company_code',
        'company_type',
        'industry_sector',
        'company_description',
        'website',
        'ein_number',
        'tax_id',
        'established_year',
        'number_of_employees',
        // Contact
        'address',
        'city',
        'state',
        'zip_code',
        'country',
        'phone',
        'email',
        'contact_name',
        'contact_title',
        'contact_phone',
        'contact_email',
        'hr_contact_name',
        'hr_contact_title',
        'hr_contact_phone',
        'hr_contact_email',
        // Program
        'years_in_program',
        'interns_hosted_total',
        'trainees_hosted_total',
        'current_participants',
        'positions_available',
        'sectors_offered',
        'positions_available_list',
        'has_training_program',
        'provides_mentorship',
        'offers_certification',
        // Compensation
        'offers_stipend',
        'stipend_range_min',
        'stipend_range_max',
        'stipend_frequency',
        'provides_housing',
        'housing_details',
        'provides_transportation',
        'benefits_offered',
        // Requirements
        'minimum_education_level',
        'minimum_experience_years',
        'required_skills',
        'preferred_skills',
        'english_level_required',
        'min_duration_months',
        'max_duration_months',
        'flexible_start_dates',
        // Verification
        'is_verified',
        'verification_date',
        'verified_by',
        'e_verify_enrolled',
        'has_liability_insurance',
        'last_audit_date',
        // Status
        'is_active',
        'is_blacklisted',
        'blacklist_reason',
        'rating',
        'total_reviews',
        // Documents
        'business_license_path',
        'insurance_certificate_path',
        'training_plan_template_path',
        // Legacy
        'name',
        'industry',
        'contact_person',
        'total_participants',
        'notes',
    ];

    protected $casts = [
        'established_year' => 'integer',
        'number_of_employees' => 'integer',
        'years_in_program' => 'integer',
        'interns_hosted_total' => 'integer',
        'trainees_hosted_total' => 'integer',
        'current_participants' => 'integer',
        'positions_available' => 'integer',
        'sectors_offered' => 'array',
        'positions_available_list' => 'array',
        'has_training_program' => 'boolean',
        'provides_mentorship' => 'boolean',
        'offers_certification' => 'boolean',
        'offers_stipend' => 'boolean',
        'stipend_range_min' => 'decimal:2',
        'stipend_range_max' => 'decimal:2',
        'provides_housing' => 'boolean',
        'provides_transportation' => 'boolean',
        'benefits_offered' => 'array',
        'minimum_education_level' => 'integer',
        'minimum_experience_years' => 'integer',
        'required_skills' => 'array',
        'preferred_skills' => 'array',
        'min_duration_months' => 'integer',
        'max_duration_months' => 'integer',
        'flexible_start_dates' => 'boolean',
        'is_verified' => 'boolean',
        'verification_date' => 'datetime',
        'e_verify_enrolled' => 'boolean',
        'has_liability_insurance' => 'boolean',
        'last_audit_date' => 'date',
        'is_active' => 'boolean',
        'is_blacklisted' => 'boolean',
        'rating' => 'decimal:2',
        'total_reviews' => 'integer',
        'total_participants' => 'integer',
    ];

    /**
     * Relación con JobOffers
     */
    public function jobOffers(): HasMany
    {
        return $this->hasMany(JobOffer::class);
    }

    /**
     * Relación con Intern/Trainee Validations
     */
    public function internTraineeValidations(): HasMany
    {
        return $this->hasMany(InternTraineeValidation::class);
    }

    /**
     * Relación con Training Plans
     */
    public function trainingPlans(): HasMany
    {
        return $this->hasMany(TrainingPlan::class);
    }

    /**
     * Scope: Solo empresas activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filtrar por ciudad
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Scope: Filtrar por estado
     */
    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    /**
     * Scope: Filtrar por industria
     */
    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    /**
     * Scope: Empresas con buen rating (>= 4.0)
     */
    public function scopeHighRated($query)
    {
        return $query->where('rating', '>=', 4.0);
    }

    /**
     * Método: Incrementar contador de participantes
     */
    public function incrementParticipants(): void
    {
        $this->increment('total_participants');
    }

    /**
     * Método: Actualizar rating promedio
     */
    public function updateRating(float $newRating): void
    {
        $this->update(['rating' => $newRating]);
    }
}
