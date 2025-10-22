<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'role_id', 'phone', 'nationality', 
        'birth_date', 'address', 'bank_info', 'email_verified_at',
        'city', 'country', 'academic_level', 'english_level', 'profile_photo',
        'created_by_agent_id', 'bio', 'avatar',
        // Health fields
        'medical_conditions', 'allergies', 'medications', 'health_insurance',
        'health_insurance_number', 'blood_type', 'emergency_medical_contact',
        'emergency_medical_phone',
        // New fields from migration
        'ci_number', 'passport_number', 'passport_expiry', 'marital_status',
        'skype', 'instagram', 'university', 'career', 'academic_year',
        'study_modality', 'current_job', 'job_position', 'work_address',
        'has_been_to_usa', 'usa_times', 'has_relatives_in_usa',
        'relatives_in_usa_location', 'previous_visa_type', 'visa_denied',
        'entry_denied', 'visa_denial_reason', 'smoker', 'has_drivers_license',
        'driving_years', 'can_swim', 'first_aid_certified', 'cpr_certified',
        'mec_registration', 'teaching_degree', 'teaching_years',
        'program_expectations', 'hobbies', 'gender', 'date_of_birth'
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'birth_date' => 'date',
        'passport_expiry' => 'date',
        'date_of_birth' => 'date',
        'hobbies' => 'array',
        'has_been_to_usa' => 'boolean',
        'has_relatives_in_usa' => 'boolean',
        'visa_denied' => 'boolean',
        'entry_denied' => 'boolean',
        'smoker' => 'boolean',
        'has_drivers_license' => 'boolean',
        'can_swim' => 'boolean',
        'first_aid_certified' => 'boolean',
        'cpr_certified' => 'boolean',
        'usa_times' => 'integer',
        'driving_years' => 'integer',
        'teaching_years' => 'integer',
    ];

    protected $hidden = [
        'password', 'remember_token', 'bank_info',
    ];

    /**
     * Accessors que se agregan automáticamente a las respuestas JSON
     */
    protected $appends = [
        'avatar_url',
    ];

    /**
     * Encrypt bank_info when storing
     */
    public function setBankInfoAttribute($value)
    {
        if ($value === null) {
            $this->attributes['bank_info'] = null;
            return;
        }
        
        if (is_array($value)) {
            $value = json_encode($value);
        }
        
        $this->attributes['bank_info'] = encrypt($value);
    }

    /**
     * Decrypt bank_info when retrieving
     */
    public function getBankInfoAttribute($value)
    {
        if ($value === null) {
            return null;
        }
        
        try {
            $decrypted = decrypt($value);
            return json_decode($decrypted, true);
        } catch (\Exception $e) {
            // If decryption fails, log the error and return null
            \Log::warning('Failed to decrypt bank_info for user ' . $this->id . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the full URL for the avatar
     */
    public function getAvatarUrlAttribute()
    {
        if (!$this->avatar) {
            // Retornar avatar por defecto basado en iniciales
            return $this->getDefaultAvatarUrl();
        }
        
        // Si ya es una URL completa, retornarla
        if (filter_var($this->avatar, FILTER_VALIDATE_URL)) {
            return $this->avatar;
        }
        
        // Si es una ruta relativa, construir URL completa
        return asset('storage/' . $this->avatar);
    }

    /**
     * Generate a default avatar URL based on user initials
     */
    protected function getDefaultAvatarUrl()
    {
        $initials = $this->getInitials();
        // Usar servicio de avatares por defecto (ui-avatars.com)
        return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&size=200&background=667eea&color=fff";
    }

    /**
     * Get user initials
     */
    public function getInitials()
    {
        $words = explode(' ', $this->name);
        if (count($words) >= 2) {
            return strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 2));
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function points()
    {
        return $this->hasMany(Point::class);
    }

    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function programAssignments()
    {
        return $this->hasMany(ProgramAssignment::class);
    }

    /**
     * Relación con EnglishEvaluations
     */
    public function englishEvaluations()
    {
        return $this->hasMany(EnglishEvaluation::class);
    }

    /**
     * Relación con JobOfferReservations
     */
    public function jobOfferReservations()
    {
        return $this->hasMany(JobOfferReservation::class);
    }

    /**
     * Relación con el rol
     */
    public function userRole()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin siempre tiene todos los permisos
        if ($this->role === 'admin' && !$this->role_id) {
            return true;
        }

        return $this->userRole && $this->userRole->hasPermission($permission);
    }

    /**
     * Verificar si el usuario tiene alguno de los permisos dados
     */
    public function hasAnyPermission(array $permissions): bool
    {
        // Super admin siempre tiene todos los permisos
        if ($this->role === 'admin' && !$this->role_id) {
            return true;
        }

        return $this->userRole && $this->userRole->hasAnyPermission($permissions);
    }

    /**
     * Verificar si el usuario tiene todos los permisos dados
     */
    public function hasAllPermissions(array $permissions): bool
    {
        // Super admin siempre tiene todos los permisos
        if ($this->role === 'admin' && !$this->role_id) {
            return true;
        }

        return $this->userRole && $this->userRole->hasAllPermissions($permissions);
    }

    /**
     * Verificar si es admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verificar si es super admin (admin sin role_id)
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'admin' && !$this->role_id;
    }

    /**
     * Verificar si es agente
     */
    public function isAgent(): bool
    {
        return $this->role === 'agent';
    }

    /**
     * Relación con el agente que creó este usuario
     */
    public function createdByAgent()
    {
        return $this->belongsTo(User::class, 'created_by_agent_id');
    }

    /**
     * Relación con participantes creados por este agente
     */
    public function createdParticipants()
    {
        return $this->hasMany(User::class, 'created_by_agent_id')->where('role', 'user');
    }

    public function assignedPrograms()
    {
        return $this->belongsToManyThrough(
            Program::class,
            ProgramAssignment::class,
            'user_id',
            'id',
            'id',
            'program_id'
        );
    }

    public function activeAssignments()
    {
        return $this->hasMany(ProgramAssignment::class)->active();
    }

    public function assignmentsAssignedByMe()
    {
        return $this->hasMany(ProgramAssignment::class, 'assigned_by');
    }

    // Métodos auxiliares para asignaciones
    public function hasAssignmentFor($programId)
    {
        return $this->programAssignments()
            ->where('program_id', $programId)
            ->exists();
    }

    public function getAssignmentFor($programId)
    {
        return $this->programAssignments()
            ->where('program_id', $programId)
            ->first();
    }

    public function canApplyTo($programId)
    {
        $assignment = $this->getAssignmentFor($programId);
        return $assignment && $assignment->canBeAppliedTo();
    }

    public function getActiveAssignmentsCount()
    {
        return $this->activeAssignments()->count();
    }

    /**
     * Relación con historial de contraseñas
     */
    public function passwordHistory()
    {
        return $this->hasMany(PasswordHistory::class)->latest('created_at')->take(3);
    }

    /**
     * Verificar si puede usar una contraseña (no está en las últimas 3)
     */
    public function canUsePassword($password)
    {
        foreach ($this->passwordHistory as $history) {
            if (\Hash::check($password, $history->password)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Agregar contraseña al historial
     */
    public function addPasswordToHistory($password)
    {
        $this->passwordHistory()->create([
            'password' => \Hash::make($password),
            'created_at' => now()
        ]);

        // Mantener solo las últimas 3
        $historyCount = $this->passwordHistory()->count();
        if ($historyCount > 3) {
            $this->passwordHistory()
                ->orderBy('created_at', 'asc')
                ->take($historyCount - 3)
                ->delete();
        }
    }

    /**
     * Get emergency contacts for the user
     */
    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class);
    }

    /**
     * Get primary emergency contact
     */
    public function primaryEmergencyContact()
    {
        return $this->hasOne(EmergencyContact::class)->where('is_primary', true);
    }

    /**
     * Get work experiences for the user
     */
    public function workExperiences()
    {
        return $this->hasMany(WorkExperience::class)->orderBy('start_date', 'desc');
    }

    /**
     * Get current work experience
     */
    public function currentWorkExperience()
    {
        return $this->hasOne(WorkExperience::class)->where('is_current', true);
    }
    
    /**
     * Get visa process for the user
     */
    public function visaProcess()
    {
        return $this->hasOne(VisaProcess::class);
    }

    /**
     * Get health declaration for the user
     */
    public function healthDeclaration()
    {
        return $this->hasOne(HealthDeclaration::class)->latest();
    }

    /**
     * Get childcare experiences for the user
     */
    public function childcareExperiences()
    {
        return $this->hasMany(ChildcareExperience::class);
    }

    /**
     * Get references for the user
     */
    public function references()
    {
        return $this->hasMany(Reference::class);
    }

    /**
     * Get Au Pair profile for the user
     */
    public function auPairProfile()
    {
        return $this->hasOne(AuPairProfile::class);
    }

    /**
     * Get teacher certifications for the user
     */
    public function teacherCertifications()
    {
        return $this->hasMany(TeacherCertification::class);
    }

    /**
     * Get detailed work experiences for the user
     */
    public function workExperiencesDetailed()
    {
        return $this->hasMany(WorkExperienceDetailed::class);
    }

    /**
     * Get user's age from birth_date or date_of_birth
     */
    public function getAgeAttribute()
    {
        $birthDate = $this->date_of_birth ?? $this->birth_date;
        
        if (!$birthDate) {
            return null;
        }

        return $birthDate->age;
    }

    /**
     * Check if user is eligible for Au Pair
     */
    public function isEligibleForAuPair()
    {
        $age = $this->age;
        
        if (!$age) {
            return false;
        }

        // Au Pair age requirements: 18-26 years old
        return $age >= 18 && $age <= 26;
    }

    /**
     * Check if user has complete Au Pair requirements
     */
    public function hasCompleteAuPairRequirements()
    {
        return $this->childcareExperiences()->count() > 0
            && $this->references()->count() >= 3
            && $this->healthDeclaration !== null
            && $this->emergencyContacts()->count() >= 2;
    }
}
