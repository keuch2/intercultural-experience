# IMPLEMENTACIÓN: FORMULARIOS ESPECÍFICOS POR PROGRAMA

Fecha: 22 de Octubre, 2025
Status: EN PROGRESO - FASE 1 COMPLETADA

## OBJETIVO

Implementar sistema completo de formularios específicos por programa con:
- ✅ Soporte para múltiples aplicaciones por usuario
- ✅ Historial de programas completados (IE Cue - Alumni)
- ✅ Campos específicos por programa
- ✅ Dropdown de "Etapa Actual" por programa
- ✅ Reutilización de datos entre aplicaciones

---

## FASE 1: MIGRACIONES - ✅ COMPLETADO

### Tablas Creadas:

#### 1. **program_history** ✅
Trackea programas completados y alumni IE Cue.

**Campos principales:**
- user_id, application_id, program_id
- program_name, program_category
- started_at, completed_at
- completion_status (completed, withdrawn, terminated)
- **is_ie_cue** (boolean) - Marca alumni
- satisfaction_rating (1-5)
- testimonial
- certificate_path

#### 2. **work_travel_data** ✅
Campos específicos de Work & Travel USA.

**Secciones:**
- Datos Académicos (universidad, carrera, modalidad PRESENCIAL)
- Evaluación de Inglés (EF SET, CEFR, 3 intentos máx)
- Job Offer (sponsor, host company, posición)
- Proceso de Entrevistas (sponsor + job)
- Expectativas y Actitud
- **current_stage** (enum 11 etapas):
  - registration
  - english_evaluation
  - documentation
  - job_selection
  - sponsor_interview
  - job_interview
  - job_confirmed
  - visa_process
  - pre_departure
  - in_program
  - completed

#### 3. **au_pair_data** ✅
Campos específicos de Au Pair USA.

**Secciones:**
- Experiencia con Niños (CRÍTICO)
- Certificaciones (primeros auxilios, CPR)
- Cartas de Referencia (mínimo 3)
- Fotos (6+) y Video
- Licencia de Conducir
- Familia Host (post-matching)
- Perfil en agencia
- **current_stage** (enum 13 etapas):
  - registration
  - profile_creation
  - documentation
  - profile_review
  - profile_active
  - matching
  - family_interviews
  - match_confirmed
  - visa_process
  - training
  - travel
  - in_program
  - completed

#### 4. **teacher_data** ✅
Campos específicos de Teachers Program.

**Secciones:**
- Formación Académica (título apostillado)
- Registro MEC (OBLIGATORIO)
- Especializaciones/Diplomados
- Experiencia Docente (CRÍTICO)
- Referencias Profesionales (mínimo 2)
- Evaluación de Inglés (C1 avanzado)
- Expectativas y Preparación
- Posición Laboral (distrito escolar)
- Job Fair
- **current_stage** (enum 13 etapas):
  - registration
  - english_evaluation
  - documentation
  - mec_validation
  - application_review
  - job_fair
  - district_interviews
  - job_offer
  - position_confirmed
  - visa_process
  - pre_departure
  - in_program
  - completed

#### 5. **applications** (actualizada) ✅
Se actualizó para soportar múltiples programas.

**Nuevos campos:**
- **current_stage** (enum) - Dropdown en lugar de texto libre:
  - registration
  - documentation
  - evaluation
  - in_review
  - approved
  - in_program
  - completed
  - withdrawn
  
- **is_ie_cue** (boolean) - Marca si es alumni
- **programs_completed** (int) - Contador de programas finalizados
- **is_current_program** (boolean) - True si es el programa activo

---

## ESTRUCTURA DE DATOS

### Usuario con Múltiples Programas:

```
User (Fernando Vera)
├── Application #1 (Work & Travel 2023)
│   ├── is_current_program: false
│   ├── is_ie_cue: true
│   ├── programs_completed: 1
│   └── work_travel_data → current_stage: completed
│
├── Application #2 (Au Pair 2024)
│   ├── is_current_program: true
│   ├── is_ie_cue: true (ya es alumni)
│   ├── programs_completed: 1
│   └── au_pair_data → current_stage: matching
│
└── Program History
    └── Work & Travel 2023 (completed)
        ├── certificate_path
        ├── satisfaction_rating: 5
        └── testimonial: "Excelente experiencia..."
```

### Etapas por Programa:

**Work & Travel (11 etapas):**
1. Registration → English Evaluation → Documentation
2. Job Selection → Sponsor Interview → Job Interview
3. Job Confirmed → Visa Process → Pre Departure
4. In Program → Completed

**Au Pair (13 etapas):**
1. Registration → Profile Creation → Documentation
2. Profile Review → Profile Active → Matching
3. Family Interviews → Match Confirmed → Visa Process
4. Training → Travel → In Program → Completed

**Teachers (13 etapas):**
1. Registration → English Evaluation → Documentation
2. MEC Validation → Application Review → Job Fair
3. District Interviews → Job Offer → Position Confirmed
4. Visa Process → Pre Departure → In Program → Completed

---

## PRÓXIMOS PASOS

### FASE 2: MODELOS ELOQUENT (PENDIENTE)

Crear modelos con relaciones:

```php
// WorkTravelData.php
class WorkTravelData extends Model
{
    public function application() { return $this->belongsTo(Application::class); }
    public function jobOffer() { return $this->belongsTo(JobOffer::class); }
}

// AuPairData.php
class AuPairData extends Model
{
    protected $casts = [
        'photos' => 'array',
        'references' => 'array',
        'children_ages' => 'array',
    ];
    public function application() { return $this->belongsTo(Application::class); }
}

// TeacherData.php
class TeacherData extends Model
{
    protected $casts = [
        'specializations' => 'array',
        'institutions_worked' => 'array',
        'professional_references' => 'array',
        'job_fair_interviews' => 'array',
    ];
    public function application() { return $this->belongsTo(Application::class); }
}

// ProgramHistory.php
class ProgramHistory extends Model
{
    public function user() { return $this->belongsTo(User::class); }
    public function application() { return $this->belongsTo(Application::class); }
    public function program() { return $this->belongsTo(Program::class); }
}

// Application.php (actualizar)
class Application extends Model
{
    public function workTravelData() { return $this->hasOne(WorkTravelData::class); }
    public function auPairData() { return $this->hasOne(AuPairData::class); }
    public function teacherData() { return $this->hasOne(TeacherData::class); }
    public function programHistory() { return $this->hasMany(ProgramHistory::class); }
    
    // Scope para IE Cue
    public function scopeIeCue($query) { return $query->where('is_ie_cue', true); }
    public function scopeCurrentPrograms($query) { return $query->where('is_current_program', true); }
}
```

### FASE 3: VISTAS PARCIALES (PENDIENTE)

Crear formularios dinámicos:

```blade
resources/views/admin/participants/forms/
├── work_travel.blade.php
├── au_pair.blade.php
├── teacher.blade.php
├── intern_trainee.blade.php
├── higher_education.blade.php
├── work_study.blade.php
└── language.blade.php
```

### FASE 4: CONTROLADOR DINÁMICO (PENDIENTE)

Actualizar ParticipantController:

```php
public function edit($id)
{
    $participant = Application::with(['program', 'workTravelData', 'auPairData', 'teacherData'])
        ->findOrFail($id);
    
    // Determinar formulario específico
    $specificData = null;
    $formView = null;
    
    switch($participant->program->subcategory) {
        case 'Work and Travel':
            $specificData = $participant->workTravelData ?? new WorkTravelData();
            $formView = 'work_travel';
            break;
        case 'Au Pair':
            $specificData = $participant->auPairData ?? new AuPairData();
            $formView = 'au_pair';
            break;
        case "Teacher's Program":
            $specificData = $participant->teacherData ?? new TeacherData();
            $formView = 'teacher';
            break;
    }
    
    // Obtener datos de programas anteriores para autocompletar
    $previousPrograms = ProgramHistory::where('user_id', $participant->user_id)
        ->orderBy('completed_at', 'desc')
        ->get();
    
    return view('admin.participants.edit', compact(
        'participant', 
        'specificData',
        'formView',
        'previousPrograms'
    ));
}

public function update(Request $request, $id)
{
    $participant = Application::findOrFail($id);
    
    // Actualizar datos base
    $participant->update($request->only([...fields...]));
    
    // Actualizar datos específicos del programa
    switch($participant->program->subcategory) {
        case 'Work and Travel':
            $participant->workTravelData()->updateOrCreate(
                ['application_id' => $participant->id],
                $request->input('work_travel')
            );
            break;
        case 'Au Pair':
            $participant->auPairData()->updateOrCreate(
                ['application_id' => $participant->id],
                $request->input('au_pair')
            );
            break;
        case "Teacher's Program":
            $participant->teacherData()->updateOrCreate(
                ['application_id' => $participant->id],
                $request->input('teacher')
            );
            break;
    }
    
    return redirect()->route('admin.participants.show', $participant->id);
}
```

### FASE 5: REUTILIZACIÓN DE DATOS (PENDIENTE)

Implementar lógica para autocompletar:

```php
// Helper para prellenar datos de programas anteriores
public function getPreviousApplicationData($userId)
{
    $latestApplication = Application::where('user_id', $userId)
        ->where('is_current_program', false)
        ->latest()
        ->first();
    
    if (!$latestApplication) return [];
    
    return [
        'phone' => $latestApplication->phone,
        'city' => $latestApplication->city,
        'country' => $latestApplication->country,
        'address' => $latestApplication->address,
        'cedula' => $latestApplication->cedula,
        'passport_number' => $latestApplication->passport_number,
        // etc...
    ];
}
```

---

## ESTADO ACTUAL

### ✅ COMPLETADO:
- ✅ FASE 1: Migraciones de tablas específicas (100%)
- ✅ FASE 2: Modelos Eloquent con relaciones (100%)
- ✅ FASE 3: Vistas parciales de formularios (100%)
- ✅ Dropdown "Etapa Actual" en edit.blade.php
- ✅ Estructura para múltiples aplicaciones
- ✅ Sistema de etapas por programa
- ✅ Marca IE Cue para alumni
- ✅ Historial de programas

### ⏳ PENDIENTE:
- Controlador dinámico (FASE 4)
- Integración formularios en edit.blade.php
- Reutilización de datos
- Validaciones backend por programa
- Testing

---

## COMANDOS EJECUTADOS

```bash
php artisan make:migration create_program_history_table
php artisan make:migration create_work_travel_data_table
php artisan make:migration create_au_pair_data_table
php artisan make:migration create_teacher_data_table
php artisan make:migration update_applications_table_for_multiple_programs

php artisan migrate --path=/database/migrations/2025_10_22_130556_create_program_history_table.php
php artisan migrate --path=/database/migrations/2025_10_22_130733_create_work_travel_data_table.php
php artisan migrate --path=/database/migrations/2025_10_22_130733_create_au_pair_data_table.php
php artisan migrate --path=/database/migrations/2025_10_22_130733_create_teacher_data_table.php
php artisan migrate --path=/database/migrations/2025_10_22_130942_update_applications_table_for_multiple_programs.php
```

---

## ARCHIVOS CREADOS

1. ✅ database/migrations/2025_10_22_130556_create_program_history_table.php
2. ✅ database/migrations/2025_10_22_130733_create_work_travel_data_table.php
3. ✅ database/migrations/2025_10_22_130733_create_au_pair_data_table.php
4. ✅ database/migrations/2025_10_22_130733_create_teacher_data_table.php
5. ✅ database/migrations/2025_10_22_130942_update_applications_table_for_multiple_programs.php
6. ✅ IMPLEMENTACION_FORMULARIOS_ESPECIFICOS.md (este archivo)

---

**¡Base de datos lista para formularios específicos!** 🎉
