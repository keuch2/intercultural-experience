# 🎯 PLAN DE IMPLEMENTACIÓN: SISTEMA DE GESTIÓN DE PARTICIPANTES

**Fecha:** 22 de Octubre, 2025  
**Status:** 📋 **PLAN PENDIENTE DE IMPLEMENTACIÓN**  
**Prioridad:** 🔴 **CRÍTICA**

---

## 📋 PROBLEMA IDENTIFICADO

El sistema tiene **7 programas creados** pero **NO tiene forma de:**
1. ✘ Agregar participantes a programas
2. ✘ Llenar formularios de ingreso específicos por programa
3. ✘ Trackear requisitos y etapas del proceso
4. ✘ Gestionar documentos de participantes
5. ✘ Hacer seguimiento del progreso de cada participante

**Documento de referencia:** `descripcion_procesos.md` (1246 líneas) describe procesos detallados de cada programa.

---

## 🏗️ ARQUITECTURA PROPUESTA

### Opción A: **Sistema Unificado con Campos Dinámicos** ⭐ RECOMENDADO

```
┌─────────────────────────────────────────────────────┐
│         MÓDULO CENTRAL: PARTICIPANTES               │
├─────────────────────────────────────────────────────┤
│  - Crear Participante (datos base)                  │
│  - Asignar a Programa                               │
│  - Formularios dinámicos según programa             │
│  - Dashboard de seguimiento                         │
│  - Sistema de etapas configurable                   │
└─────────────────────────────────────────────────────┘
           │
           ├───> Work & Travel (5 etapas específicas)
           ├───> Au Pair (5 etapas específicas)
           ├───> Teachers (5 etapas específicas)
           ├───> Intern/Trainee
           ├───> Higher Education
           ├───> Work & Study
           └───> Language Program
```

### Opción B: **Módulos Separados por Programa**

Cada programa tiene su propio CRUD completo (ya implementado parcialmente en Au Pair, Work & Travel, Teachers).

---

## 📊 ESTRUCTURA DE DATOS PROPUESTA

### 1. Tabla Central: `applications`

Ya existe, pero necesita expansión:

```sql
CREATE TABLE applications (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,                      -- Participante
    program_id BIGINT,                   -- Programa asignado
    
    -- Estados principales
    status ENUM('draft', 'in_review', 'approved', 'active', 'completed', 'rejected'),
    current_stage VARCHAR(50),           -- Etapa actual (dinámico por programa)
    progress_percentage INT DEFAULT 0,   -- % completado
    
    -- Fechas importantes
    applied_at DATETIME,
    started_at DATETIME,
    completed_at DATETIME,
    
    -- Datos financieros
    total_cost DECIMAL(10,2),
    amount_paid DECIMAL(10,2),
    payment_plan TEXT,                   -- JSON con plan de pagos
    
    -- Metadata
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2. Nueva Tabla: `application_stages`

Tracking de etapas por aplicación:

```sql
CREATE TABLE application_stages (
    id BIGINT PRIMARY KEY,
    application_id BIGINT,
    stage_name VARCHAR(100),             -- "ETAPA 1: INSCRIPCIÓN"
    stage_order INT,                     -- 1, 2, 3, 4, 5
    status ENUM('pending', 'in_progress', 'completed', 'blocked'),
    started_at DATETIME,
    completed_at DATETIME,
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    INDEX idx_application_stage (application_id, stage_order)
);
```

### 3. Nueva Tabla: `application_fields`

Campos dinámicos por programa:

```sql
CREATE TABLE application_fields (
    id BIGINT PRIMARY KEY,
    application_id BIGINT,
    field_name VARCHAR(100),             -- "university_name", "childcare_experience"
    field_value TEXT,                    -- Valor del campo (puede ser JSON)
    field_type VARCHAR(50),              -- text, number, date, json, file
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application_field (application_id, field_name)
);
```

### 4. Nueva Tabla: `application_requirements`

Requisitos específicos por aplicación:

```sql
CREATE TABLE application_requirements (
    id BIGINT PRIMARY KEY,
    application_id BIGINT,
    requirement_name VARCHAR(200),       -- "Constancia universitaria", "Carta de referencia #1"
    requirement_type ENUM('document', 'data', 'payment', 'interview', 'evaluation'),
    status ENUM('pending', 'submitted', 'approved', 'rejected'),
    file_path VARCHAR(255),              -- Si es documento
    submitted_at DATETIME,
    approved_at DATETIME,
    approved_by BIGINT,                  -- user_id del admin que aprobó
    rejection_reason TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    INDEX idx_application_requirements (application_id, status)
);
```

### 5. Tabla Existente: `application_documents`

Ya existe, mantener para documentos generales.

---

## 🎨 INTERFACES DE USUARIO PROPUESTAS

### 1. **Dashboard Principal de Participantes**

```
┌────────────────────────────────────────────────────────────┐
│  📊 GESTIÓN DE PARTICIPANTES                               │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  [+ Nuevo Participante]  [🔍 Buscar...]  [📊 Reportes]    │
│                                                             │
│  Filtros:                                                   │
│  [Programa ▼] [Estado ▼] [Etapa ▼] [Fecha ▼]             │
│                                                             │
│  ┌─────────────────────────────────────────────────────┐  │
│  │ Nombre       Programa       Estado      Etapa   %   │  │
│  ├─────────────────────────────────────────────────────┤  │
│  │ Juan Pérez   Work & Travel  Activo   Etapa 3  65%  │  │
│  │ María López  Au Pair        Review   Etapa 2  45%  │  │
│  │ Pedro Gómez  Teachers       Activo   Etapa 4  80%  │  │
│  └─────────────────────────────────────────────────────┘  │
│                                                             │
│  [1] [2] [3] ... [10]                                      │
└────────────────────────────────────────────────────────────┘
```

### 2. **Formulario de Creación de Participante (Paso 1: Datos Base)**

```
┌────────────────────────────────────────────────────────────┐
│  📝 NUEVO PARTICIPANTE - Paso 1/3: Datos Básicos          │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  INFORMACIÓN PERSONAL                                       │
│  ┌──────────────────────┐  ┌──────────────────────┐      │
│  │ Nombre Completo*     │  │ Email*               │      │
│  │ [Juan Pérez Silva]   │  │ [juan@email.com]     │      │
│  └──────────────────────┘  └──────────────────────┘      │
│                                                             │
│  ┌──────────────────────┐  ┌──────────────────────┐      │
│  │ Fecha Nacimiento*    │  │ Teléfono*            │      │
│  │ [15/03/2000]         │  │ [+595 981 123 456]   │      │
│  └──────────────────────┘  └──────────────────────┘      │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Cédula de Identidad*                                 │ │
│  │ [1234567]                                            │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                             │
│  PASAPORTE                                                  │
│  ┌──────────────────────┐  ┌──────────────────────┐      │
│  │ Número*              │  │ Vencimiento*         │      │
│  │ [AB123456]           │  │ [15/03/2030]         │      │
│  └──────────────────────┘  └──────────────────────┘      │
│                                                             │
│  [Cancelar]                      [Siguiente: Programa →]   │
└────────────────────────────────────────────────────────────┘
```

### 3. **Selección de Programa (Paso 2)**

```
┌────────────────────────────────────────────────────────────┐
│  📝 NUEVO PARTICIPANTE - Paso 2/3: Seleccionar Programa   │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  Seleccione el programa al que desea inscribir al          │
│  participante: Juan Pérez Silva                            │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │  ○ Work & Travel USA                                 │ │
│  │     Trabajo temporal de verano - Visa J1             │ │
│  │     Duración: 3-4 meses | Costo: USD 2,400          │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │  ○ Au Pair USA                                       │ │
│  │     Cuidado de niños en familia estadounidense       │ │
│  │     Duración: 12 meses | Costo: USD 1,800           │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │  ○ Teachers Program                                  │ │
│  │     Profesor de intercambio con visa J1              │ │
│  │     Duración: 1-3 años | Costo: USD 3,000           │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                             │
│  [← Atrás]                        [Siguiente: Datos →]     │
└────────────────────────────────────────────────────────────┘
```

### 4. **Formulario Dinámico por Programa (Paso 3)**

**Ejemplo: Work & Travel**

```
┌────────────────────────────────────────────────────────────┐
│  📝 NUEVO PARTICIPANTE - Paso 3/3: Datos del Programa     │
│  Programa: Work & Travel USA                               │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  DATOS ACADÉMICOS (Obligatorio para Work & Travel)         │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Universidad*                                         │ │
│  │ [Universidad Nacional de Asunción ▼]                 │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                             │
│  ┌──────────────────────┐  ┌──────────────────────┐      │
│  │ Carrera*             │  │ Año/Semestre*        │      │
│  │ [Ingeniería]         │  │ [3er Año]            │      │
│  └──────────────────────┘  └──────────────────────┘      │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Modalidad*                                           │ │
│  │ ● Presencial  ○ Virtual  ○ Semipresencial          │ │
│  │ ⚠️ Solo estudiantes presenciales califican          │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                             │
│  CONTACTOS DE EMERGENCIA (Mínimo 2)                        │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ Contacto #1                                          │ │
│  │ Nombre: [María Pérez]  Relación: [Madre]            │ │
│  │ Teléfono: [+595 981 111 222]                         │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                             │
│  [+ Agregar otro contacto]                                 │
│                                                             │
│  EVALUACIÓN DE INGLÉS                                       │
│  ┌──────────────────────┐  ┌──────────────────────┐      │
│  │ Nivel Autoestimado   │  │ EF SET ID            │      │
│  │ [Intermedio ▼]       │  │ [opcional]           │      │
│  └──────────────────────┘  └──────────────────────┘      │
│                                                             │
│  [← Atrás]                         [Crear Participante]    │
└────────────────────────────────────────────────────────────┘
```

### 5. **Vista Detallada del Participante (Profile)**

```
┌────────────────────────────────────────────────────────────┐
│  👤 Juan Pérez Silva  #P-2025-001                          │
│  Work & Travel USA 2025                                    │
├────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌──────────────────────┐  ┌──────────────────────────┐   │
│  │ PROGRESO GENERAL     │  │ ESTADO ACTUAL            │   │
│  │  ████████░░  65%     │  │  🟡 Etapa 3: Pago       │   │
│  │                      │  │     Confirmación         │   │
│  └──────────────────────┘  └──────────────────────────┘   │
│                                                             │
│  TIMELINE DE ETAPAS                                         │
│  ┌──────────────────────────────────────────────────────┐ │
│  │  ✅ Etapa 1: Inscripción        (15/01/2025)        │ │
│  │  ✅ Etapa 2: Selección Job      (20/02/2025)        │ │
│  │  🟡 Etapa 3: Confirmación       (En Progreso)       │ │
│  │  ⏳ Etapa 4: Proceso Visa       (Pendiente)         │ │
│  │  ⏳ Etapa 5: Pre-Viaje          (Pendiente)         │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                             │
│  TABS: [Datos] [Documentos] [Pagos] [Timeline] [Notas]    │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ DATOS PERSONALES                                     │ │
│  │ Email: juan@email.com                                │ │
│  │ Teléfono: +595 981 123 456                           │ │
│  │ CI: 1234567                                          │ │
│  │ Pasaporte: AB123456 (Vence: 15/03/2030)             │ │
│  │                                                       │ │
│  │ [Editar Datos]                                       │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                             │
│  ┌──────────────────────────────────────────────────────┐ │
│  │ REQUISITOS Y DOCUMENTOS                              │ │
│  │ ✅ Pasaporte vigente                 (Aprobado)      │ │
│  │ ✅ Constancia universitaria          (Aprobado)      │ │
│  │ ⏳ CV actualizado                    (Pendiente)     │ │
│  │ ⏳ Contratos firmados                (Pendiente)     │ │
│  │                                                       │ │
│  │ [Ver Todos los Requisitos]                           │ │
│  └──────────────────────────────────────────────────────┘ │
│                                                             │
│  [Editar] [Cambiar Estado] [Ver Timeline Completo]        │
└────────────────────────────────────────────────────────────┘
```

---

## 📋 PLAN DE IMPLEMENTACIÓN

### **FASE 1: Estructura Base (1-2 días)** 🔴 CRÍTICO

#### Tareas:
1. ✅ Revisar y expandir modelo `Application`
2. ✅ Crear migración para nuevos campos en `applications`
3. ✅ Crear tabla `application_stages`
4. ✅ Crear tabla `application_fields`
5. ✅ Crear tabla `application_requirements`
6. ✅ Crear modelos Eloquent correspondientes

#### Archivos a crear/modificar:
```
database/migrations/
  - 2025_10_22_create_application_stages_table.php
  - 2025_10_22_create_application_fields_table.php
  - 2025_10_22_create_application_requirements_table.php
  - 2025_10_22_add_fields_to_applications_table.php

app/Models/
  - ApplicationStage.php (nuevo)
  - ApplicationField.php (nuevo)
  - ApplicationRequirement.php (nuevo)
  - Application.php (actualizar relaciones)
```

---

### **FASE 2: Controladores y Rutas (1 día)** 🟡

#### Tareas:
1. ✅ Crear `ParticipantController` (gestión centralizada)
2. ✅ Actualizar rutas en `web.php`
3. ✅ Crear endpoints para:
   - Listar participantes
   - Crear participante (wizard de 3 pasos)
   - Ver detalle de participante
   - Editar datos
   - Cambiar estado/etapa
   - Gestionar requisitos

#### Archivos a crear/modificar:
```
app/Http/Controllers/Admin/
  - ParticipantController.php (nuevo)

routes/
  - web.php (agregar rutas de participantes)
```

---

### **FASE 3: Vistas Blade (2-3 días)** 🟡

#### Tareas:
1. ✅ Vista: Dashboard de participantes (`index.blade.php`)
2. ✅ Vista: Formulario wizard de creación (3 pasos)
3. ✅ Vista: Perfil detallado del participante (`show.blade.php`)
4. ✅ Vista: Gestión de etapas
5. ✅ Vista: Gestión de requisitos/documentos
6. ✅ Vista: Timeline de actividades

#### Archivos a crear:
```
resources/views/admin/participants/
  - index.blade.php
  - create/
    - step1-basic-data.blade.php
    - step2-select-program.blade.php
    - step3-program-specific-data.blade.php
  - show.blade.php
  - edit.blade.php
  - partials/
    - stages-timeline.blade.php
    - requirements-checklist.blade.php
    - documents-upload.blade.php
```

---

### **FASE 4: Formularios Dinámicos por Programa (2-3 días)** 🟢

#### Tareas:
1. ✅ Crear componentes de formulario dinámico
2. ✅ Definir campos específicos por programa (basado en `descripcion_procesos.md`)
3. ✅ Implementar validación dinámica
4. ✅ Sistema de guardado de campos personalizados

#### Programas a configurar:
- Work & Travel (datos universitarios, job offer, visa J1)
- Au Pair (experiencia con niños, referencias, matching)
- Teachers (validación MEC, job fairs, escuelas)
- Intern/Trainee
- Higher Education
- Work & Study
- Language Program

---

### **FASE 5: Sistema de Etapas/Workflow (2 días)** 🟢

#### Tareas:
1. ✅ Crear seeder de etapas por programa
2. ✅ Implementar lógica de transición entre etapas
3. ✅ Validaciones de etapa (no puede avanzar sin cumplir requisitos)
4. ✅ Sistema de notificaciones por etapa

#### Ejemplo de configuración de etapas (Work & Travel):
```php
$workTravelStages = [
    ['name' => 'Inscripción y Evaluación', 'order' => 1],
    ['name' => 'Selección de Job Offer', 'order' => 2],
    ['name' => 'Confirmación y Pago', 'order' => 3],
    ['name' => 'Proceso de Visa J1', 'order' => 4],
    ['name' => 'Pre-Viaje', 'order' => 5],
];
```

---

### **FASE 6: Gestión de Requisitos y Documentos (1-2 días)** 🟢

#### Tareas:
1. ✅ Sistema de upload de documentos por requisito
2. ✅ Checklist visual de requisitos pendientes
3. ✅ Aprobación/rechazo de documentos por admin
4. ✅ Notificaciones de documentos pendientes

---

### **FASE 7: Reportes y Dashboard (1 día)** 🔵

#### Tareas:
1. ✅ Dashboard con estadísticas de participantes
2. ✅ Reportes por programa
3. ✅ Reportes por etapa
4. ✅ Export a Excel

---

## 📊 RESUMEN DE ESTIMACIÓN

| Fase | Descripción | Tiempo | Prioridad |
|------|-------------|--------|-----------|
| 1 | Estructura Base | 1-2 días | 🔴 CRÍTICA |
| 2 | Controladores y Rutas | 1 día | 🟡 Alta |
| 3 | Vistas Blade | 2-3 días | 🟡 Alta |
| 4 | Formularios Dinámicos | 2-3 días | 🟢 Media |
| 5 | Sistema de Etapas | 2 días | 🟢 Media |
| 6 | Requisitos/Documentos | 1-2 días | 🟢 Media |
| 7 | Reportes | 1 día | 🔵 Baja |

**TOTAL ESTIMADO:** 10-15 días de desarrollo

---

## 🎯 QUICK WINS (Implementación Rápida)

Si necesitas algo funcional YA, podemos hacer:

### **MVP: Sistema Básico de Participantes (2-3 días)**

1. ✅ Formulario simple de creación de participante
2. ✅ Lista de participantes con filtros básicos
3. ✅ Vista de perfil simple
4. ✅ Upload de 3-4 documentos clave
5. ✅ Estados básicos (Inscrito, En Proceso, Aprobado, Rechazado)

Esto te permitirá empezar a registrar participantes mientras se construye el sistema completo.

---

## 🤔 DECISIÓN REQUERIDA

**¿Qué prefieres?**

### Opción A: **Sistema Unificado** (Recomendado)
- Un solo lugar para gestionar todos los participantes
- Formularios dinámicos adaptados a cada programa
- Más complejo pero más escalable
- Tiempo: 10-15 días completo / 2-3 días MVP

### Opción B: **Usar módulos existentes**
- Continuar con módulos separados (Au Pair, Work & Travel, etc.)
- Más rápido de implementar
- Menos consistencia entre programas
- Tiempo: 5-7 días

### Opción C: **MVP + Expansión gradual**
- Implementar MVP en 2-3 días
- Ir agregando funcionalidades por programa
- Balance entre rapidez y calidad
- Tiempo: 3 días MVP + 1-2 días por programa

---

## 📝 PRÓXIMOS PASOS

**Si decides continuar, dime cuál opción prefieres y empiezo con:**

1. ✅ Crear migraciones de estructura base
2. ✅ Crear modelos Eloquent
3. ✅ Crear controlador básico
4. ✅ Crear vista de listado
5. ✅ Crear formulario de creación simple

---

**Documento creado:** 22 de Octubre, 2025  
**Referencia:** descripcion_procesos.md  
**Status:** ⏸️ Esperando decisión del usuario
