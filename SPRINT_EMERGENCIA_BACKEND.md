# 🚨 SPRINT EMERGENCIA - BACKEND TASKS

## 📋 MIGRACIONES URGENTES A CREAR

### 1. add_missing_fields_to_users_table.php
```php
Schema::table('users', function (Blueprint $table) {
    // Datos personales extendidos
    $table->string('ci_number')->nullable()->after('email');
    $table->string('passport_number')->nullable();
    $table->date('passport_expiry')->nullable();
    $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
    $table->string('skype')->nullable();
    $table->string('instagram')->nullable();
    
    // Datos académicos
    $table->string('university')->nullable();
    $table->string('career')->nullable();
    $table->string('academic_year')->nullable();
    $table->enum('study_modality', ['presencial', 'online', 'hybrid'])->nullable();
    
    // Datos laborales
    $table->string('current_job')->nullable();
    $table->string('job_position')->nullable();
    $table->string('work_address')->nullable();
    
    // Experiencia USA
    $table->boolean('has_been_to_usa')->default(false);
    $table->integer('usa_times')->default(0);
    $table->boolean('has_relatives_in_usa')->default(false);
    $table->string('relatives_in_usa_location')->nullable();
    $table->string('previous_visa_type')->nullable();
    $table->boolean('visa_denied')->default(false);
    $table->boolean('entry_denied')->default(false);
    $table->text('visa_denial_reason')->nullable();
    
    // Au Pair específico
    $table->boolean('smoker')->default(false);
    $table->boolean('has_drivers_license')->default(false);
    $table->integer('driving_years')->default(0);
    $table->boolean('can_swim')->default(false);
    $table->boolean('first_aid_certified')->default(false);
    $table->boolean('cpr_certified')->default(false);
    
    // Teachers específico
    $table->string('mec_registration')->nullable();
    $table->string('teaching_degree')->nullable();
    $table->integer('teaching_years')->default(0);
    
    // Expectativas
    $table->text('program_expectations')->nullable();
    $table->json('hobbies')->nullable();
    
    $table->index('university');
    $table->index('study_modality');
});
```

### 2. create_emergency_contacts_table.php
```php
Schema::create('emergency_contacts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->enum('relationship', ['parent', 'sibling', 'spouse', 'friend', 'other']);
    $table->string('phone');
    $table->string('email')->nullable();
    $table->string('address')->nullable();
    $table->boolean('is_primary')->default(false);
    $table->timestamps();
    
    $table->index('user_id');
});
```

### 3. create_health_declarations_table.php
```php
Schema::create('health_declarations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->boolean('has_diseases')->default(false);
    $table->text('diseases_detail')->nullable();
    $table->boolean('has_allergies')->default(false);
    $table->text('allergies_detail')->nullable();
    $table->boolean('has_dietary_restrictions')->default(false);
    $table->text('dietary_restrictions_detail')->nullable();
    $table->boolean('has_learning_disabilities')->default(false);
    $table->text('learning_disabilities_detail')->nullable();
    $table->boolean('has_physical_limitations')->default(false);
    $table->text('physical_limitations_detail')->nullable();
    $table->boolean('under_medical_treatment')->default(false);
    $table->text('medical_treatment_detail')->nullable();
    $table->boolean('takes_medication')->default(false);
    $table->text('medication_detail')->nullable();
    $table->boolean('can_lift_25_pounds')->default(true); // Au Pair
    $table->boolean('allergic_to_pets')->default(false);
    $table->text('pet_allergies_detail')->nullable();
    $table->date('declaration_date');
    $table->timestamps();
    
    $table->index('user_id');
});
```

### 4. create_childcare_experiences_table.php
```php
Schema::create('childcare_experiences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('experience_type', ['babysitter', 'teacher', 'family', 'daycare', 'camp', 'other']);
    $table->string('ages_cared')->nullable(); // "0-2", "3-5", "6-10", etc
    $table->integer('duration_months');
    $table->text('responsibilities');
    $table->boolean('cared_for_infants')->default(false);
    $table->boolean('special_needs_experience')->default(false);
    $table->text('special_needs_detail')->nullable();
    $table->string('reference_name')->nullable();
    $table->string('reference_phone')->nullable();
    $table->string('reference_email')->nullable();
    $table->date('start_date')->nullable();
    $table->date('end_date')->nullable();
    $table->timestamps();
    
    $table->index('user_id');
});
```

### 5. create_references_table.php
```php
Schema::create('references', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('reference_type', ['childcare', 'character', 'professional', 'academic']);
    $table->string('name');
    $table->string('relationship');
    $table->string('phone');
    $table->string('email')->nullable();
    $table->string('organization')->nullable();
    $table->string('position')->nullable();
    $table->text('letter_content')->nullable();
    $table->string('letter_file_path')->nullable();
    $table->boolean('verified')->default(false);
    $table->date('verification_date')->nullable();
    $table->timestamps();
    
    $table->index(['user_id', 'reference_type']);
});
```

### 6. create_au_pair_profiles_table.php
```php
Schema::create('au_pair_profiles', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('application_id')->constrained()->onDelete('cascade');
    
    // Fotos y video
    $table->json('photos')->nullable(); // Array de paths
    $table->string('main_photo')->nullable();
    $table->string('video_presentation')->nullable();
    $table->integer('video_duration')->nullable(); // segundos
    
    // Carta a familia
    $table->text('dear_family_letter');
    
    // Preferencias de familia
    $table->string('preferred_ages')->nullable(); // "0-2,3-5"
    $table->integer('max_children')->default(3);
    $table->text('ideal_family_description')->nullable();
    
    // Estado del perfil
    $table->enum('profile_status', ['draft', 'pending', 'active', 'matched', 'inactive']);
    $table->boolean('profile_complete')->default(false);
    $table->integer('profile_views')->default(0);
    
    // Disponibilidad
    $table->date('available_from');
    $table->integer('commitment_months')->default(12);
    
    $table->timestamps();
    
    $table->index('profile_status');
    $table->index('user_id');
});
```

### 7. create_family_profiles_table.php
```php
Schema::create('family_profiles', function (Blueprint $table) {
    $table->id();
    $table->string('family_name');
    $table->string('parent1_name');
    $table->string('parent2_name')->nullable();
    $table->string('email')->unique();
    $table->string('phone');
    $table->string('city');
    $table->string('state');
    $table->string('country')->default('USA');
    
    // Niños
    $table->integer('number_of_children');
    $table->json('children_ages'); // [2, 5, 8]
    $table->boolean('has_infants')->default(false);
    $table->boolean('has_special_needs')->default(false);
    $table->text('special_needs_detail')->nullable();
    
    // Casa
    $table->boolean('has_pets')->default(false);
    $table->string('pet_types')->nullable();
    $table->boolean('smoking_household')->default(false);
    
    // Requisitos
    $table->enum('required_gender', ['female', 'male', 'any'])->default('any');
    $table->boolean('drivers_license_required')->default(false);
    $table->boolean('swimming_required')->default(false);
    
    // Oferta
    $table->decimal('weekly_stipend', 8, 2)->default(195.75);
    $table->decimal('education_fund', 8, 2)->default(500);
    $table->text('additional_benefits')->nullable();
    
    $table->timestamps();
    
    $table->index('state');
    $table->index('has_infants');
});
```

### 8. create_au_pair_matches_table.php
```php
Schema::create('au_pair_matches', function (Blueprint $table) {
    $table->id();
    $table->foreignId('au_pair_profile_id')->constrained()->onDelete('cascade');
    $table->foreignId('family_profile_id')->constrained()->onDelete('cascade');
    
    $table->enum('au_pair_status', ['pending', 'interested', 'not_interested']);
    $table->enum('family_status', ['pending', 'interested', 'not_interested']);
    $table->boolean('is_matched')->default(false);
    $table->datetime('matched_at')->nullable();
    
    // Interacciones
    $table->integer('messages_count')->default(0);
    $table->datetime('last_interaction')->nullable();
    $table->integer('video_calls_count')->default(0);
    
    $table->timestamps();
    
    $table->unique(['au_pair_profile_id', 'family_profile_id']);
    $table->index('is_matched');
});
```

### 9. create_teacher_certifications_table.php
```php
Schema::create('teacher_certifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('certification_name');
    $table->string('issuing_institution');
    $table->date('issue_date');
    $table->date('expiry_date')->nullable();
    $table->string('certification_number')->nullable();
    $table->string('document_path')->nullable();
    $table->boolean('is_apostilled')->default(false);
    $table->boolean('verified')->default(false);
    $table->timestamps();
    
    $table->index('user_id');
});
```

### 10. create_work_experiences_detailed_table.php
```php
Schema::create('work_experiences_detailed', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('company_name');
    $table->string('position');
    $table->string('department')->nullable();
    $table->text('responsibilities');
    $table->date('start_date');
    $table->date('end_date')->nullable();
    $table->boolean('is_current')->default(false);
    
    // Para Teachers
    $table->enum('institution_type', ['public', 'private', 'charter', 'other'])->nullable();
    $table->string('grade_levels')->nullable(); // "K-5", "6-8", "9-12"
    $table->json('subjects_taught')->nullable();
    $table->integer('weekly_hours')->nullable();
    
    // Referencias
    $table->string('supervisor_name')->nullable();
    $table->string('supervisor_phone')->nullable();
    $table->string('supervisor_email')->nullable();
    
    $table->timestamps();
    
    $table->index(['user_id', 'is_current']);
});
```

---

## 📦 MODELOS A CREAR

### EmergencyContact.php
```php
class EmergencyContact extends Model
{
    protected $fillable = [
        'user_id', 'name', 'relationship', 'phone', 
        'email', 'address', 'is_primary'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### HealthDeclaration.php
```php
class HealthDeclaration extends Model
{
    protected $fillable = [
        'user_id', 'has_diseases', 'diseases_detail',
        'has_allergies', 'allergies_detail',
        'has_dietary_restrictions', 'dietary_restrictions_detail',
        'has_learning_disabilities', 'learning_disabilities_detail',
        'has_physical_limitations', 'physical_limitations_detail',
        'under_medical_treatment', 'medical_treatment_detail',
        'takes_medication', 'medication_detail',
        'can_lift_25_pounds', 'allergic_to_pets', 
        'pet_allergies_detail', 'declaration_date'
    ];
    
    protected $casts = [
        'declaration_date' => 'date',
        'has_diseases' => 'boolean',
        'has_allergies' => 'boolean',
        'can_lift_25_pounds' => 'boolean',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### ChildcareExperience.php
```php
class ChildcareExperience extends Model
{
    protected $fillable = [
        'user_id', 'experience_type', 'ages_cared',
        'duration_months', 'responsibilities',
        'cared_for_infants', 'special_needs_experience',
        'special_needs_detail', 'reference_name',
        'reference_phone', 'reference_email',
        'start_date', 'end_date'
    ];
    
    protected $casts = [
        'cared_for_infants' => 'boolean',
        'special_needs_experience' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function getTotalExperienceMonthsAttribute()
    {
        return $this->duration_months;
    }
}
```

### AuPairProfile.php
```php
class AuPairProfile extends Model
{
    protected $fillable = [
        'user_id', 'application_id', 'photos', 'main_photo',
        'video_presentation', 'video_duration',
        'dear_family_letter', 'preferred_ages',
        'max_children', 'ideal_family_description',
        'profile_status', 'profile_complete',
        'available_from', 'commitment_months'
    ];
    
    protected $casts = [
        'photos' => 'array',
        'profile_complete' => 'boolean',
        'available_from' => 'date',
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function application()
    {
        return $this->belongsTo(Application::class);
    }
    
    public function matches()
    {
        return $this->hasMany(AuPairMatch::class);
    }
    
    public function getIsCompleteAttribute()
    {
        return !empty($this->photos) 
            && !empty($this->video_presentation)
            && !empty($this->dear_family_letter)
            && $this->user->references()->count() >= 3
            && $this->user->childcareExperiences()->count() > 0;
    }
}
```

---

## 🔄 ACTUALIZAR User.php

```php
// Agregar relaciones al modelo User
public function emergencyContacts()
{
    return $this->hasMany(EmergencyContact::class);
}

public function healthDeclaration()
{
    return $this->hasOne(HealthDeclaration::class)->latest();
}

public function childcareExperiences()
{
    return $this->hasMany(ChildcareExperience::class);
}

public function references()
{
    return $this->hasMany(Reference::class);
}

public function auPairProfile()
{
    return $this->hasOne(AuPairProfile::class);
}

public function teacherCertifications()
{
    return $this->hasMany(TeacherCertification::class);
}

public function workExperiences()
{
    return $this->hasMany(WorkExperienceDetailed::class);
}

// Agregar fillables nuevos
protected $fillable = [
    // ... existing fields ...
    'ci_number', 'passport_number', 'passport_expiry',
    'marital_status', 'skype', 'instagram',
    'university', 'career', 'academic_year', 'study_modality',
    'current_job', 'job_position', 'work_address',
    'has_been_to_usa', 'usa_times', 'has_relatives_in_usa',
    'relatives_in_usa_location', 'previous_visa_type',
    'visa_denied', 'entry_denied', 'visa_denial_reason',
    'smoker', 'has_drivers_license', 'driving_years',
    'can_swim', 'first_aid_certified', 'cpr_certified',
    'mec_registration', 'teaching_degree', 'teaching_years',
    'program_expectations', 'hobbies'
];

protected $casts = [
    // ... existing casts ...
    'passport_expiry' => 'date',
    'hobbies' => 'array',
    'has_been_to_usa' => 'boolean',
    'visa_denied' => 'boolean',
    'smoker' => 'boolean',
];
```

---

## 🎯 COMANDOS A EJECUTAR

```bash
# Crear todas las migraciones
php artisan make:migration add_missing_fields_to_users_table
php artisan make:migration create_emergency_contacts_table
php artisan make:migration create_health_declarations_table
php artisan make:migration create_childcare_experiences_table
php artisan make:migration create_references_table
php artisan make:migration create_au_pair_profiles_table
php artisan make:migration create_family_profiles_table
php artisan make:migration create_au_pair_matches_table
php artisan make:migration create_teacher_certifications_table
php artisan make:migration create_work_experiences_detailed_table

# Crear todos los modelos
php artisan make:model EmergencyContact
php artisan make:model HealthDeclaration
php artisan make:model ChildcareExperience
php artisan make:model Reference
php artisan make:model AuPairProfile
php artisan make:model FamilyProfile
php artisan make:model AuPairMatch
php artisan make:model TeacherCertification
php artisan make:model WorkExperienceDetailed

# Ejecutar migraciones
php artisan migrate

# Crear seeders de prueba
php artisan make:seeder EmergencyDataSeeder
php artisan db:seed --class=EmergencyDataSeeder
```

---

## ✅ CHECKLIST BACKEND

```
☐ Todas las migraciones creadas
☐ Todos los modelos creados
☐ Relaciones configuradas
☐ Fillables y casts definidos
☐ Migraciones ejecutadas sin errores
☐ Seeders con datos de prueba
☐ APIs básicas funcionando
☐ Tests unitarios de modelos
```

**¡PRIORIDAD MÁXIMA! Ejecutar AHORA** 🚀
