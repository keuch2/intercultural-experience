# Au Pair Prototype - Plan Completo

> Generado: 2025-02-10 | Autor: Claude Code (Arquitecto + Lead Frontend)

---

## A) AS-IS MAP (Estado Actual del Sistema)

### A.1 Resumen Ejecutivo

El sistema actual es una plataforma Laravel 12 multi-programa con:
- **55 modelos Eloquent**, **41 controllers**, **~580 rutas web**, **~270 rutas API**
- **35+ directorios de vistas** admin (muchos programas con scaffolding incompleto)
- Sidebar con **14 secciones** de navegación — sobrecargado, difícil de navegar
- Stack: Laravel 12 + Bootstrap 5 + Blade templates + Vite + Font Awesome

### A.2 Módulos Existentes Relevantes para Au Pair

| Pantalla | Ruta Web | Controller | Modelos | Estado |
|----------|----------|------------|---------|--------|
| Au Pair Dashboard | `/admin/au-pair/dashboard` | `AuPairController@dashboard` | AuPairProfile, FamilyProfile, AuPairMatch | Funcional (métricas básicas) |
| Perfiles Au Pair (lista) | `/admin/au-pair/profiles` | `AuPairController@profiles` | AuPairProfile → User | Funcional (filtros básicos) |
| Perfil Au Pair (detalle) | `/admin/au-pair/profiles/{id}` | `AuPairController@profileShow` | AuPairProfile + relaciones | Funcional (vista solo lectura) |
| Familias Host | `/admin/au-pair/families` | `AuPairController@families` | FamilyProfile | Funcional |
| Crear Familia | `/admin/au-pair/families/create` | `AuPairController@createFamily` | FamilyProfile | Funcional |
| Matching | `/admin/au-pair/matching` | `AuPairController@matching` | AuPairMatch | Funcional (7 factores) |
| Childcare Exp. | `/admin/au-pair/childcare/{userId}` | `AuPairController@childcareExperiences` | ChildcareExperience | Funcional |
| Referencias | `/admin/au-pair/references/{userId}` | `AuPairController@references` | Reference | Funcional |
| Participantes (general) | `/admin/participants` | `ParticipantController` | User + Application | Funcional (CRUD completo) |
| English Evaluations | `/admin/english-evaluations/*` | `EnglishEvaluationController` | EnglishEvaluation | Funcional (3 intentos, CEFR) |
| Documentos (general) | `/admin/documents/*` | `AdminDocumentController` | ApplicationDocument | Funcional (verify/reject) |
| Pagos | `/admin/finance/payments/*` | `AdminFinanceController` | Payment | Funcional (verify/reject) |
| Visa | `/admin/visa/*` | `AdminVisaController` | VisaProcess | Funcional (15 estados) |

### A.3 Modelos de Datos Existentes (Reutilizables)

| Modelo | Tabla | Campos Clave | Reutilizable |
|--------|-------|-------------|-------------|
| `User` | users | name, email, phone, birth_date, ci_number, nationality, city, country, address, gender, marital_status, academic_level, university, current_job, job_position, has_drivers_license, can_swim, smoker | **SI** — contiene datos personales del participante |
| `Application` | applications | user_id, program_id, status, current_stage, progress_percentage, total_cost, amount_paid | **SI** — contenedor del proceso |
| `AuPairProfile` | au_pair_profiles | user_id, application_id, photos, video_presentation, dear_family_letter, profile_status, profile_complete | **SI** — pero necesita extensión |
| `AuPairData` | au_pair_data | application_id, current_stage (enum 13 valores), childcare fields, host family fields, english_level | **SI** — datos específicos por aplicación |
| `FamilyProfile` | family_profiles | family_name, parent1/2_name, email, phone, city, state, children_ages, requirements | **SI** — para Match |
| `AuPairMatch` | au_pair_matches | au_pair_profile_id, family_profile_id, statuses, is_matched, matched_at | **PARCIAL** — necesita extensión para rematch/extensión |
| `EnglishEvaluation` | english_evaluations | user_id, score, cefr_level, classification, attempt_number, evaluated_by, notes | **PARCIAL** — falta oral/listening/reading scores separados, PDF, toggle "enviado" |
| `ApplicationDocument` | application_documents | application_id, name, document_type, file_path, status, observations | **SI** — pero necesita: stage, payment_gate, min_count, uploaded_by_type |
| `Payment` | payments | application_id, user_id, amount, status, concept, receipt_path, verified_by | **SI** — pero necesita: payment_number (1er/2do pago) |
| `ChildcareExperience` | childcare_experiences | user_id, campos de experiencia | **SI** |
| `Reference` | references | user_id, reference_type, verified, verification_date | **SI** |
| `HealthDeclaration` | health_declarations | user_id, campos de salud | **SI** |
| `EmergencyContact` | emergency_contacts | user_id, is_primary, campos | **SI** |
| `VisaProcess` | visa_processes | application_id, 15 estados | **PARCIAL** — necesita reestructurar para flujo J1 específico |

### A.4 Fricciones UX Detectadas

1. **Sidebar sobrecargado**: 14 secciones con ~50 links — Au Pair es solo 1 sección entre muchas
2. **Navegación dispersa**: Datos del participante en `/participants`, documentos en `/documents`, pagos en `/finance`, inglés en `/english-evaluations`, visa en `/visa` — no hay hub central
3. **Sin flujo por etapas**: El perfil Au Pair actual (`profile-show.blade.php`) muestra datos pero NO guía al staff por un proceso
4. **Sin estados visuales**: No hay candados, semáforos, ni indicadores de "bloqueado por pago"
5. **Sin checklist de proceso**: No existe casillas de "correo enviado", "contrato firmado", etc.
6. **Documentos genéricos**: `ApplicationDocument` no distingue etapa, ni tipo de pago que los habilita, ni quién sube (participant vs IE)
7. **English Evaluation incompleta**: Falta scores por habilidad (oral, listening, reading), PDF obligatorio, campo "evaluador", toggle "resultados enviados"
8. **Match/Visa desconectado**: El módulo de visa existe como módulo general, no integrado al flujo Au Pair
9. **Sin rematch/extensión**: AuPairMatch solo tiene un match por perfil, no soporta rematches ni extensiones
10. **Sin soporte post-arribo**: No existe seguimiento mensual, incidentes, ni evaluación de experiencia

### A.5 Qué se Puede Reutilizar vs. Qué se Debe Crear

**REUTILIZAR (no tocar, solo integrar):**
- `User` model con todos sus campos de datos personales
- `Application` model como contenedor del proceso
- `Payment` model para gestión de pagos (extender con `payment_number`)
- `AuPairProfile` model (extender)
- Layout `admin.blade.php` (modificar sidebar)
- Auth system (login/logout)
- Activity logging

**SIMPLIFICAR/OCULTAR (no borrar, solo quitar del menú):**
- Teachers, Work & Travel, Intern/Trainee, Higher Education, Work & Study, Language Program
- YFU Programs
- Rewards/Points/Redemptions
- Job Offers
- Sponsors/Host Companies
- Bulk Import (temporalmente)

**CREAR NUEVO:**
- `AuPairProcess` — modelo central que orquesta las etapas y sus flags
- `AuPairDocument` — modelo específico con stage, payment_gate, uploaded_by_type, min_count
- `AuPairVisa` — submódulo visa J1 con todas las sub-secciones
- `AuPairRematch` — soporte multi-rematch
- `AuPairExtension` — datos de extensión
- `AuPairSupport` — seguimientos, incidentes, evaluación
- `ProgramResource` — documentos descargables para participante
- Nuevo controller `AuPairProfileController` — hub central con todas las operaciones
- Nuevas vistas con tabs/steps/timeline

---

## B) DISEÑO TO-BE DEL PROTOTIPO

### B.1 Navegación Simplificada

```
SIDEBAR (nuevo):
├── Tablero (dashboard general simplificado)
├── Programas (accordion)
│   └── Au Pair (accordion)
│       └── Perfiles Au Pair (link → listado)
├── Pagos (link → gestión de pagos Au Pair)
├── Informes (link → reportes con filtros)
└── Configuración (link → settings mínimos)
```

### B.2 Pantallas del Prototipo

#### Pantalla 1: Listado Perfiles Au Pair
- **Ruta**: `/admin/au-pair/profiles`
- **Componente**: `au-pair/profiles/index.blade.php`
- **Funcionalidad**:
  - Tabla con columnas: Nombre, Fecha inscripción, Etapa actual, Docs pendientes, Pagos, Nivel inglés, Última actualización
  - Filtros: fecha inscripción, etapa/proceso, pagos (al día/pendiente), nivel inglés, país
  - Búsqueda por nombre/email/CI
  - Badge de estado por fila (colores semáforo)
  - Click → va al Perfil Au Pair

#### Pantalla 2: Perfil Au Pair (Hub Central)
- **Ruta**: `/admin/au-pair/profiles/{id}`
- **Componente**: `au-pair/profiles/show.blade.php`
- **Layout**:
  ```
  ┌─────────────────────────────────────────────────┐
  │ HEADER: Nombre + Estado Global + Alertas        │
  ├──────────┬──────────────────────────────────────│
  │ SIDEBAR  │ CONTENIDO DE LA ETAPA ACTIVA         │
  │          │                                      │
  │ A.Admis. │  [Formularios / Checklists /         │
  │ B.Aplic. │   Uploads / Validaciones]            │
  │ C.Match  │                                      │
  │ D.Supprt │                                      │
  │ E.Recurs │                                      │
  │ F.Inform │                                      │
  │ G.Pagos  │                                      │
  │          │                                      │
  │ Estado:  │                                      │
  │ 🔒🟡🔄✅ │                                      │
  └──────────┴──────────────────────────────────────┘
  ```

#### Etapas/Tabs del Perfil:

**Tab A: Admisión**
- **A1: Datos Personales** — Formulario editable inline con todos los campos solicitados
- **A2: Documentos de Admisión** — Upload de cédula, pasaporte, licencia, foto perfil + verificación por staff
- **Gate**: Staff debe verificar docs obligatorios para habilitar Tab B

**Tab B: Aplicación**
- **B1: Test de Inglés** — Campos extendidos (evaluador, oral, listening, reading, resultado final, observaciones, PDF, toggle "enviado") + aviso B1 mínimo + 3 evaluaciones sin costo
- **B2: Documentos (por pago)** — Sección 1er pago y 2do pago con candado visual
- **B3: Gestión Documentos** — Checklist staff: presentados / aprobados / pendientes + motivo rechazo
- **B4: Checklist de Proceso** — Casillas: correo bienvenida, correo entrevistas, docs+pagos ok, ITEP

**Tab C: Match / Visa J1**
- **C1: Aplicación Visa** — Casilla correo, uploads participante, checks pago consular/cita/envío docs
- **C2: Cita de Visa** — Fecha, hora, embajada
- **C3: Documentos IE** — DS-160, DS-2019, carta participación, instrucciones + check "chequeo realizado"
- **C4: Resultado Entrevista** — Aprobada / Denegada / Proceso administrativo
- **C5: Info Viaje** — Fechas salida/llegada, vuelo, escalas
- **C6: Orientación Pre-partida** — Fecha + check "se realizó"
- **C7: Datos del Match** — Fecha, estado, ciudad, dirección, nombres padres, email, teléfono
- **C8: Rematch** — Lista de rematches (multi), mismos campos que C7
- **C9: Extensión** — Fecha, estado, ciudad, dirección, datos familia
- **C10: Finalización** — Éxito / No éxito (motivo) / Cambio estatus / Otros

**Tab D: Support**
- Seguimiento de llegada
- Seguimientos mensuales (lista cronológica)
- Incidentes
- Evaluación de experiencia

**Tab E: Recursos**
- Lista de documentos del programa descargables (perfil au pair, tips entrevistas, tips visa, carta, video, derechos USA)
- Admin gestiona el repositorio

**Tab F: Informes**
- Vista filtrada para este perfil específico
- Timeline de actividad

**Tab G: Pagos**
- Estado pago 1 (inscripción) y pago 2 (programa)
- Registro de pagos con comprobantes
- Alertas de pagos pendientes

### B.3 Indicadores UX por Etapa

| Estado | Icono | Color | Significado |
|--------|-------|-------|-------------|
| Bloqueado | 🔒 | Gris | Requiere acción previa (pago, verificación) |
| Pendiente | 🟡 | Amarillo | Habilitado, esperando acción |
| En revisión | 🔄 | Azul | Staff está revisando |
| Completo | ✅ | Verde | Etapa finalizada |
| Rechazado | ❌ | Rojo | Documento/item rechazado, requiere corrección |

### B.4 Alertas del Sistema

- **Pago pendiente**: "El participante no ha realizado el [1er/2do] pago. Documentos bloqueados."
- **Contrato no firmado**: "Para continuar, es necesario confirmar la firma del contrato con IE."
- **Doc rechazado**: "Documento [X] rechazado el [fecha]. Motivo: [motivo]"
- **Nivel inglés insuficiente**: "Nivel actual: [nivel]. Mínimo requerido: B1."
- **Docs incompletos**: "[N] documentos pendientes de presentación."

---

## C) MODELO DE DATOS MÍNIMO

### C.1 Tablas Nuevas

#### `au_pair_processes` (orquestador central)
```sql
- id
- application_id (FK → applications, UNIQUE)
- user_id (FK → users)
- enrollment_date (fecha inscripción)
- enrollment_city, enrollment_country
- current_stage ENUM('admission','application','match_visa','support','completed','cancelled')
- admission_status ENUM('pending','in_progress','docs_review','approved')
- application_status ENUM('locked','pending','in_progress','docs_review','approved')
- match_visa_status ENUM('locked','pending','in_progress','approved')
- support_status ENUM('locked','active','completed')
-- Checklist flags (Proceso B4)
- welcome_email_sent BOOL
- interview_process_email_sent BOOL
- all_docs_and_payments_complete BOOL
- itep_completed BOOL
-- Contract
- contract_signed BOOL
- contract_signed_at DATETIME
- contract_signed_confirmed_by INT (FK → users)
-- Payment gates
- payment_1_verified BOOL
- payment_2_verified BOOL
-- Global
- notes TEXT
- created_at, updated_at, deleted_at
```

#### `au_pair_documents` (docs específicos Au Pair)
```sql
- id
- au_pair_process_id (FK)
- document_type VARCHAR -- 'cedula','passport','drivers_license','profile_photo','psych_test','child_photos','presentation_video','cover_letter','vaccination_card','certifications','police_record','bachelor_degree','passport_doc','previous_visas','character_ref','childcare_ref','physician_report','interviewer_report','au_pair_agreement','enrollment_form','english_test_pdf','ds160','ds2019','participation_letter','appointment_instructions','visa_form','visa_photo'
- stage ENUM('admission','application_payment1','application_payment2','visa')
- uploaded_by_type ENUM('participant','staff')
- file_path VARCHAR
- original_filename VARCHAR
- file_size INT
- status ENUM('pending','approved','rejected')
- rejection_reason TEXT
- reviewed_by INT (FK → users)
- reviewed_at DATETIME
- is_required BOOL
- min_count INT DEFAULT 1 -- para refs: character_ref=2, childcare_ref=3
- sort_order INT
- notes TEXT
- created_at, updated_at, deleted_at
```

#### `au_pair_english_tests` (extensión del test de inglés)
```sql
- id
- au_pair_process_id (FK)
- english_evaluation_id (FK → english_evaluations, nullable)
- evaluator_name VARCHAR
- exam_name VARCHAR
- oral_score INT
- listening_score INT
- reading_score INT
- final_score INT
- cefr_level VARCHAR
- observations TEXT
- test_pdf_path VARCHAR (obligatorio)
- results_sent_to_applicant BOOL
- results_sent_at DATETIME
- attempt_number INT
- created_at, updated_at
```

#### `au_pair_visa_processes` (visa J1 específico)
```sql
- id
- au_pair_process_id (FK)
-- C1: Aplicación
- visa_email_sent BOOL
- visa_form_path VARCHAR
- visa_photo_path VARCHAR
- consular_fee_paid BOOL
- appointment_scheduled BOOL
- documents_sent_for_appointment BOOL
-- C2: Cita
- appointment_date DATE
- appointment_time TIME
- embassy VARCHAR
-- C3: Docs IE
- ds160_path VARCHAR
- ds2019_path VARCHAR
- participation_letter_path VARCHAR
- appointment_instructions_path VARCHAR
- document_check_completed BOOL
- document_check_completed_at DATETIME
-- C4: Resultado
- interview_result ENUM('pending','approved','denied','administrative_process')
- interview_result_notes TEXT
-- C5: Viaje
- departure_datetime DATETIME
- arrival_usa_datetime DATETIME
- flight_info TEXT -- JSON: airline, flight_number, stopovers
-- C6: Orientación
- pre_departure_orientation_date DATE
- pre_departure_orientation_completed BOOL
- created_at, updated_at
```

#### `au_pair_matches_extended` (extiende match existente con datos del requerimiento)
```sql
- id
- au_pair_process_id (FK)
- match_type ENUM('initial','rematch','extension')
- match_date DATE
- host_state VARCHAR
- host_city VARCHAR
- host_address TEXT
- host_mom_name VARCHAR
- host_dad_name VARCHAR
- host_email VARCHAR
- host_phone VARCHAR
- is_active BOOL
- ended_at DATE
- end_reason TEXT
- sort_order INT -- para múltiples rematches
- created_at, updated_at
```

#### `au_pair_completion` (finalización)
```sql
- id
- au_pair_process_id (FK)
- completed_successfully BOOL
- failure_reason TEXT -- si no fue exitoso
- status_change TEXT
- other_notes TEXT
- completed_at DATE
- created_at, updated_at
```

#### `au_pair_support_logs` (seguimiento post-arribo)
```sql
- id
- au_pair_process_id (FK)
- log_type ENUM('arrival_followup','monthly_followup','incident','experience_evaluation')
- title VARCHAR
- description TEXT
- log_date DATE
- follow_up_number INT -- para mensuales: 1,2,3...
- severity ENUM('low','medium','high','critical') -- para incidentes
- resolution TEXT
- resolved_at DATETIME
- logged_by INT (FK → users)
- created_at, updated_at
```

#### `program_resources` (recursos descargables)
```sql
- id
- program_type VARCHAR DEFAULT 'au_pair'
- title VARCHAR
- description TEXT
- file_path VARCHAR
- file_type VARCHAR
- sort_order INT
- is_active BOOL
- created_at, updated_at
```

### C.2 Tablas Existentes a Extender

#### `payments` — agregar campo:
```sql
- payment_number TINYINT -- 1 = inscripción, 2 = programa
```

#### `english_evaluations` — agregar campos:
```sql
- oral_score INT
- listening_score INT  
- reading_score INT
- exam_name VARCHAR
- evaluator_name VARCHAR
- test_pdf_path VARCHAR
- results_sent_to_applicant BOOL
- results_sent_at DATETIME
```

### C.3 Diagrama de Relaciones

```
User (1) ──── (1) AuPairProfile
  │                    │
  │                    └── (N) AuPairMatch (existente)
  │
  └── (N) Application (1) ──── (1) AuPairProcess
                                      │
                                      ├── (N) AuPairDocument
                                      ├── (N) AuPairEnglishTest
                                      ├── (1) AuPairVisaProcess
                                      ├── (N) AuPairMatchExtended (initial + rematches + extension)
                                      ├── (1) AuPairCompletion
                                      └── (N) AuPairSupportLog
```

---

## D) LISTA DE ENDPOINTS MÍNIMA

### D.1 Perfiles Au Pair (Admin Web)

| Método | Ruta | Acción | Descripción |
|--------|------|--------|-------------|
| GET | `/admin/au-pair/profiles` | index | Listado con filtros |
| GET | `/admin/au-pair/profiles/{id}` | show | Hub central del perfil (tabs) |
| GET | `/admin/au-pair/profiles/{id}/tab/{tab}` | showTab | Carga tab específico (AJAX) |
| PUT | `/admin/au-pair/profiles/{id}/personal-data` | updatePersonalData | Guardar datos personales (A1) |
| POST | `/admin/au-pair/profiles/{id}/documents` | uploadDocument | Subir documento (A2, B2) |
| GET | `/admin/au-pair/profiles/{id}/documents/{docId}/download` | downloadDocument | Descargar documento |
| PUT | `/admin/au-pair/profiles/{id}/documents/{docId}/review` | reviewDocument | Aprobar/rechazar doc (B3) |
| POST | `/admin/au-pair/profiles/{id}/english-test` | storeEnglishTest | Registrar test inglés (B1) |
| PUT | `/admin/au-pair/profiles/{id}/checklist` | updateChecklist | Actualizar flags proceso (B4) |
| PUT | `/admin/au-pair/profiles/{id}/advance-stage` | advanceStage | Avanzar a siguiente etapa |
| PUT | `/admin/au-pair/profiles/{id}/visa` | updateVisa | Actualizar datos visa (C1-C6) |
| POST | `/admin/au-pair/profiles/{id}/match` | storeMatch | Registrar match (C7) |
| POST | `/admin/au-pair/profiles/{id}/rematch` | storeRematch | Registrar rematch (C8) |
| POST | `/admin/au-pair/profiles/{id}/extension` | storeExtension | Registrar extensión (C9) |
| PUT | `/admin/au-pair/profiles/{id}/completion` | updateCompletion | Finalización (C10) |
| POST | `/admin/au-pair/profiles/{id}/support-log` | storeSupportLog | Agregar seguimiento (D) |
| POST | `/admin/au-pair/profiles/{id}/payments` | storePayment | Registrar pago (G) |
| PUT | `/admin/au-pair/profiles/{id}/payments/{payId}/verify` | verifyPayment | Verificar pago |

### D.2 Recursos del Programa

| Método | Ruta | Acción | Descripción |
|--------|------|--------|-------------|
| GET | `/admin/au-pair/resources` | index | Lista recursos |
| POST | `/admin/au-pair/resources` | store | Subir recurso |
| DELETE | `/admin/au-pair/resources/{id}` | destroy | Eliminar recurso |

### D.3 Reportes

| Método | Ruta | Acción | Descripción |
|--------|------|--------|-------------|
| GET | `/admin/au-pair/reports` | index | Dashboard reportes con filtros |
| GET | `/admin/au-pair/reports/export` | export | Exportar CSV/Excel |

### D.4 Payloads Ejemplo

**PUT `/admin/au-pair/profiles/{id}/personal-data`**
```json
{
  "enrollment_date": "2025-02-01",
  "enrollment_city": "Asunción",
  "enrollment_country": "Paraguay",
  "name": "María García",
  "birth_date": "2000-05-15",
  "ci_number": "4567890",
  "phone": "+595981234567",
  "address": "Av. España 1234",
  "city": "Asunción",
  "current_job": "Estudiante",
  "job_position": "N/A",
  "university": "UNA",
  "nationality": "Paraguaya",
  "marital_status": "single",
  "email": "maria@email.com"
}
```

**POST `/admin/au-pair/profiles/{id}/documents`**
```json
{
  "document_type": "cedula",
  "stage": "admission",
  "file": "[binary]",
  "notes": "Cédula vigente"
}
```

**PUT `/admin/au-pair/profiles/{id}/documents/{docId}/review`**
```json
{
  "status": "rejected",
  "rejection_reason": "Imagen borrosa, por favor subir nuevamente con mejor calidad"
}
```

**POST `/admin/au-pair/profiles/{id}/english-test`**
```json
{
  "evaluator_name": "Prof. John Smith",
  "exam_name": "IE English Assessment",
  "oral_score": 72,
  "listening_score": 68,
  "reading_score": 75,
  "final_score": 71,
  "observations": "Buen nivel conversacional",
  "test_pdf": "[binary]",
  "results_sent_to_applicant": true
}
```

**PUT `/admin/au-pair/profiles/{id}/checklist`**
```json
{
  "welcome_email_sent": true,
  "interview_process_email_sent": true,
  "all_docs_and_payments_complete": false,
  "itep_completed": false,
  "contract_signed": true
}
```

---

## E) PLAN POR FASES

### FASE 1: UX/Admin Navigation (Estimado: 4-6h)

**Objetivo**: Sidebar simplificado + scaffolding de pantallas

**Tareas**:
1. Modificar `resources/views/layouts/admin.blade.php` — reemplazar sidebar con menú accordion simplificado
2. Crear vista `au-pair/profiles/index.blade.php` — listado con filtros y columnas definidas
3. Crear vista `au-pair/profiles/show.blade.php` — layout con header + sidebar de tabs + área de contenido
4. Crear partials para cada tab (placeholders): `_tab_admission.blade.php`, `_tab_application.blade.php`, `_tab_match_visa.blade.php`, `_tab_support.blade.php`, `_tab_resources.blade.php`, `_tab_reports.blade.php`, `_tab_payments.blade.php`
5. Actualizar rutas en `web.php`

**Criterio de aceptación**:
- [x] Al hacer login, sidebar muestra solo: Tablero, Programas > Au Pair > Perfiles
- [x] Click en "Perfiles Au Pair" muestra tabla con datos mock
- [x] Click en un perfil muestra hub con 7 tabs navegables
- [x] Cada tab tiene placeholder visible

### FASE 2: Admisión - Núcleo del Perfil (Estimado: 8-10h)

**Objetivo**: Datos personales + documentos de admisión + gate de avance

**Tareas**:
1. Crear migración `au_pair_processes`
2. Crear migración `au_pair_documents` con seed de tipos de documento
3. Crear modelo `AuPairProcess` con relaciones
4. Crear modelo `AuPairDocument`
5. Crear controller `AuPairProfileController` (reemplaza parcialmente AuPairController)
6. Implementar Tab A1: formulario de datos personales (editable inline)
7. Implementar Tab A2: upload/download de documentos de admisión + verificación
8. Implementar gate: no avanzar sin docs obligatorios verificados
9. Agregar descarga/subida de formulario de inscripción firmado
10. Agregar aviso "formulario debe estar firmado manuscritamente"

**Criterio de aceptación**:
- [x] Se puede crear un perfil Au Pair y completar datos personales
- [x] Se pueden subir cédula, pasaporte, licencia, foto de perfil
- [x] Staff puede verificar/rechazar documentos con motivo
- [x] No se puede acceder a Tab B si cédula y foto no están aprobadas
- [x] Se puede descargar/subir formulario de inscripción

### FASE 3: Aplicación (Estimado: 12-16h)

**Objetivo**: Test inglés extendido + docs por pago + checklist + reglas

**Tareas**:
1. Crear migración `au_pair_english_tests`
2. Extender migración de `english_evaluations` (agregar campos)
3. Extender migración de `payments` (agregar payment_number)
4. Crear modelo `AuPairEnglishTest`
5. Implementar Tab B1: formulario test inglés con todos los campos + PDF + toggle
6. Implementar Tab B2: documentos habilitados por pago 1 (con lista completa) + candado por pago 2
7. Implementar Tab B3: checklist de documentos (presentados/aprobados/pendientes) + rechazo con motivo
8. Implementar Tab B4: checklist de proceso (welcome email, entrevistas, docs+pagos, ITEP)
9. Implementar aviso bloqueante "contactar IE para firma de contrato"
10. Implementar lógica: payment_1_verified → desbloquea docs payment1; payment_2_verified → desbloquea docs payment2
11. Registrar pagos desde Tab G con payment_number

**Criterio de aceptación**:
- [x] Test inglés se registra con todos los campos requeridos + PDF
- [x] Aviso "Nivel mínimo B1" siempre visible
- [x] Docs de pago 1 solo visibles si pago 1 verificado
- [x] Docs de pago 2 con candado hasta verificación o habilitación manual
- [x] Checklist de proceso funcional con guardado
- [x] No se avanza si contrato no confirmado

### FASE 4: Match / Visa J1 (Estimado: 10-12h)

**Objetivo**: Flujo visa completo + match + rematch + extensión + finalización

**Tareas**:
1. Crear migración `au_pair_visa_processes`
2. Crear migración `au_pair_matches_extended`
3. Crear migración `au_pair_completion`
4. Crear modelos `AuPairVisaProcess`, `AuPairMatchExtended`, `AuPairCompletion`
5. Implementar Tab C1: aplicación visa (casilla correo, uploads, checks)
6. Implementar Tab C2: cita (fecha/hora/embajada)
7. Implementar Tab C3: docs IE upload + check "chequeo realizado"
8. Implementar Tab C4: resultado entrevista (enum)
9. Implementar Tab C5: info viaje
10. Implementar Tab C6: orientación pre-partida
11. Implementar Tab C7: datos del match
12. Implementar Tab C8: rematch (lista dinámica, agregar N rematches)
13. Implementar Tab C9: extensión
14. Implementar Tab C10: finalización

**Criterio de aceptación**:
- [x] Flujo visa J1 completo y navegable
- [x] Se pueden cargar documentos IE + check "chequeo realizado"
- [x] Datos del match con todos los campos
- [x] Se pueden agregar múltiples rematches
- [x] Finalización registra éxito/fracaso con motivo

### FASE 5: Support + Recursos + Reportes (Estimado: 6-8h)

**Objetivo**: Post-arribo + recursos + filtros de reportes

**Tareas**:
1. Crear migración `au_pair_support_logs`
2. Crear migración `program_resources`
3. Crear modelo `AuPairSupportLog`, `ProgramResource`
4. Implementar Tab D: seguimiento llegada, mensuales, incidentes, evaluación
5. Implementar Tab E: repositorio de recursos (admin CRUD + descarga)
6. Implementar Tab F: reportes por perfil (timeline de actividad)
7. Implementar página de reportes globales con filtros: fecha inscripción, proceso, escolaridad, docs, edad, pagos, inglés, país
8. Seed de recursos iniciales (perfil au pair, tips entrevistas, tips visa, carta, video, derechos USA)

**Criterio de aceptación**:
- [x] Se pueden registrar seguimientos mensuales e incidentes
- [x] Recursos del programa descargables desde el perfil
- [x] Reportes con todos los filtros solicitados
- [x] Export a CSV/Excel

### FASE 6: Hardening (Estimado: 4-6h)

**Objetivo**: Permisos, validaciones, tests, performance

**Tareas**:
1. Verificar middleware admin en todas las rutas nuevas
2. Agregar Form Requests con validación robusta
3. Eager loading en listados (evitar N+1)
4. Tests feature básicos (crear perfil, subir doc, verificar, avanzar etapa)
5. Documentar endpoints en `/docs/aupair-api.md`
6. Edge cases: qué pasa si se rechaza un doc ya aprobado, rollback de etapa, etc.

**Criterio de aceptación**:
- [x] Solo admin/staff puede acceder
- [x] Validaciones server-side en todos los formularios
- [x] No hay queries N+1 en listados
- [x] Tests pasan
- [x] API documentada

---

## ASSUMPTIONS (Decisiones Tomadas, Configurables Luego)

1. **Un solo programa Au Pair activo** — si hay múltiples ediciones/años, se maneja con el campo `program_id` existente
2. **Pagos simplificados** — Solo 2 pagos (inscripción + programa), sin cuotas por ahora
3. **Documentos sin versionado** — Se sobreescribe el archivo; historial queda en activity_log
4. **Match info es carga manual** — No hay integración con agencias externas
5. **Recursos son estáticos** — PDFs subidos por admin, no generados dinámicamente
6. **Reportes sin gráficos** — Tabla filtrada + export; gráficos en fase posterior
7. **Un staff puede hacer todo** — No hay sub-roles (coordinador, evaluador); se puede agregar después
8. **Idioma del admin: Español** — Labels, mensajes y avisos en español

---

## RESUMEN DE ARCHIVOS A CREAR/MODIFICAR

### Nuevos (estimado):
- 7 migraciones
- 7 modelos
- 1 controller principal (AuPairProfileController ~800-1000 líneas)
- 1 seeder (tipos de documento)
- 8 vistas Blade (index + show + 7 tab partials)
- 1 archivo de documentación API

### Modificados:
- `resources/views/layouts/admin.blade.php` (sidebar)
- `routes/web.php` (nuevas rutas)
- Migración de extensión para `payments` y `english_evaluations`

### Total estimado: ~5,000-7,000 líneas de código nuevo
### Tiempo estimado total: 44-58 horas de desarrollo
