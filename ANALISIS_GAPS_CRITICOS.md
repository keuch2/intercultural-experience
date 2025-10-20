# ANÁLISIS DE GAPS CRÍTICOS - AUDITORÍA EXTERNA
**Proyecto:** Intercultural Experience Platform  
**Auditor:** SURISO & COMPANY  
**Fecha:** 20 de Octubre, 2025

---

## TABLA COMPARATIVA: IMPLEMENTADO VS REQUERIDO

| Módulo | Estado Actual | Requerido | Gap | Prioridad | Esfuerzo |
|--------|---------------|-----------|-----|-----------|----------|
| **Gestión de Participantes** | 60% | 100% | 40% | 🔴 CRÍTICA | 3 semanas |
| **Gestión de Programas** | 40% | 100% | 60% | 🔴 CRÍTICA | 2 semanas |
| **Evaluación de Inglés** | 0% | 100% | 100% | 🔴 CRÍTICA | 1.5 semanas |
| **Job Offers** | 0% | 100% | 100% | 🔴 CRÍTICA | 2 semanas |
| **Proceso de Documentación** | 30% | 100% | 70% | 🟠 ALTA | 2.5 semanas |
| **Gestión de Visa** | 0% | 100% | 100% | 🔴 CRÍTICA | 2.5 semanas |
| **Finanzas** | 50% | 100% | 50% | 🟠 ALTA | 2 semanas |
| **Sponsors y Organizaciones** | 0% | 100% | 100% | 🟡 MEDIA | 1 semana |
| **Host Companies** | 0% | 100% | 100% | 🟡 MEDIA | 1 semana |
| **Reportes y Dashboard** | 20% | 100% | 80% | 🟠 ALTA | 1.5 semanas |

**TOTAL GAP:** 72% del sistema faltante  
**ESFUERZO TOTAL:** 18 semanas

---

## GAP 1: GESTIÓN DE PARTICIPANTES (40% FALTANTE)

### ✅ Implementado
- CRUD básico de participantes
- Campos personales básicos
- Relación con aplicaciones
- Filtros simples

### ❌ Faltante
- **Contactos de Emergencia** (tabla y CRUD)
- **Información de Salud** (declaración jurada)
  - Enfermedades
  - Alergias
  - Restricciones alimenticias
  - Trastornos de aprendizaje
  - Medicación actual
  - Limitaciones físicas
- **Información Laboral Completa**
  - Trabajo actual
  - Cargo/Función
  - Dirección laboral
  - Experiencia detallada
- **Wizard de Inscripción** (6 pasos)
- **Timeline del Proceso** (visual)
- **Historial de Cambios** (auditoría)

### Impacto
🔴 **CRÍTICO** - Sin esto no se pueden inscribir participantes correctamente

### Solución
- Crear tabla `emergency_contacts` (1:N con users)
- Agregar campos de salud a tabla `users`
- Crear tabla `employment_history` (1:N con users)
- Implementar wizard frontend (Vue.js/React)
- Crear endpoint `/api/participants/:id/timeline`

---

## GAP 2: EVALUACIÓN DE INGLÉS (100% FALTANTE)

### ❌ Completamente Faltante
- Tabla `english_evaluations`
- Límite de 3 intentos por participante
- Niveles CEFR (A1, A2, B1, B2, C1, C2)
- Clasificación automática (INSUFFICIENT/GOOD/GREAT/EXCELLENT)
- Integración con EF SET
- Historial de evaluaciones
- Validación de nivel vs requisitos de programa

### Impacto
🔴 **CRÍTICO** - Es requisito obligatorio para Work & Travel, Intern, Teachers

### Solución
```sql
CREATE TABLE english_evaluations (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    ef_set_id VARCHAR(50),
    cefr_level ENUM('A1','A2','B1','B2','C1','C2'),
    classification ENUM('INSUFFICIENT','GOOD','GREAT','EXCELLENT'),
    score INT,
    attempt_number INT CHECK (attempt_number <= 3),
    evaluated_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Reglas de Negocio
1. Máximo 3 intentos por participante
2. Solo se considera el mejor resultado
3. Validar nivel vs requisitos de job offer
4. Alertar si nivel insuficiente

---

## GAP 3: JOB OFFERS (100% FALTANTE)

### ❌ Completamente Faltante
- Tabla `job_offers`
- Tabla `job_offer_reservations`
- Catálogo de ofertas laborales
- Filtros avanzados (sponsor, ciudad, nivel inglés, género)
- Control de cupos en tiempo real
- Sistema de reservas (pago USD 800)
- Penalidades por cancelación (USD 100)
- Algoritmo de matching automático
- Estados (disponible, agotado, cancelado)

### Impacto
🔴 **CRÍTICO** - Es el core de Work & Travel (programa principal)

### Solución
```sql
CREATE TABLE job_offers (
    id BIGINT PRIMARY KEY,
    job_offer_id VARCHAR(50) UNIQUE,
    sponsor_id BIGINT,
    host_company_id BIGINT,
    position VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    salary_min DECIMAL(10,2),
    salary_max DECIMAL(10,2),
    housing_type ENUM('provided','assisted','not_provided'),
    total_slots INT,
    available_slots INT,
    required_english_level ENUM('B1','B1+','B2','C1','C2'),
    required_gender ENUM('male','female','any'),
    start_date DATE,
    end_date DATE,
    status ENUM('available','full','cancelled'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE job_offer_reservations (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    job_offer_id BIGINT,
    reservation_fee DECIMAL(10,2) DEFAULT 800.00,
    cancellation_fee DECIMAL(10,2) DEFAULT 100.00,
    status ENUM('reserved','confirmed','cancelled'),
    reserved_at TIMESTAMP,
    confirmed_at TIMESTAMP,
    cancelled_at TIMESTAMP
);
```

### Algoritmo de Matching
```
MATCH_SCORE = 
    (english_level_match ? 40 : 0) +
    (gender_match ? 20 : 0) +
    (dates_available ? 30 : 0) +
    (location_preference ? 10 : 0)

IF MATCH_SCORE >= 70 THEN "Highly Compatible"
IF MATCH_SCORE >= 50 THEN "Compatible"
ELSE "Not Recommended"
```

---

## GAP 4: PROCESO DE VISA (100% FALTANTE)

### ❌ Completamente Faltante
- Tabla `visa_processes`
- 15 estados del proceso
- Timeline visual interactivo
- Documentos DS160, DS2019
- Pagos SEVIS y tasa consular
- Agenda de citas consulares
- Seguimiento de aprobaciones
- Notificaciones automáticas por estado

### Impacto
🔴 **CRÍTICO** - Sin esto no se puede trackear el proceso más importante

### Solución
```sql
CREATE TABLE visa_processes (
    id BIGINT PRIMARY KEY,
    application_id BIGINT,
    current_status ENUM(
        'documentation_pending',
        'sponsor_interview_pending',
        'sponsor_interview_approved',
        'job_interview_pending',
        'job_interview_approved',
        'ds160_pending',
        'ds160_completed',
        'ds2019_pending',
        'ds2019_received',
        'sevis_paid',
        'consular_fee_paid',
        'appointment_scheduled',
        'in_correspondence',
        'visa_approved',
        'visa_rejected'
    ),
    ds160_number VARCHAR(50),
    ds2019_number VARCHAR(50),
    sevis_id VARCHAR(50),
    sevis_paid_at TIMESTAMP,
    consular_fee_paid_at TIMESTAMP,
    appointment_date TIMESTAMP,
    interview_result ENUM('approved','rejected','pending'),
    notes TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE visa_status_history (
    id BIGINT PRIMARY KEY,
    visa_process_id BIGINT,
    from_status VARCHAR(50),
    to_status VARCHAR(50),
    changed_by BIGINT,
    notes TEXT,
    changed_at TIMESTAMP
);
```

### Timeline Frontend
- Componente visual con pasos
- Estado actual destacado
- Pasos completados (check verde)
- Acción requerida (botón CTA)
- Fechas de cada transición

---

## GAP 5: DOCUMENTACIÓN (70% FALTANTE)

### ✅ Implementado
- Tabla `documents` básica
- Upload simple

### ❌ Faltante
- **Tipos de documento por programa**
- **Validación de vigencia** (pasaportes)
- **Versionado** de documentos
- **Estados** (pendiente, recibido, aprobado, rechazado)
- **Alertas de vencimiento**
- **Checklist visual** por programa
- **Drag & drop upload**
- **Vista previa** de documentos
- **Firma digital** de contratos

### Impacto
🟠 **ALTA** - Documentación es crítica pero parcialmente implementada

### Solución
```sql
ALTER TABLE documents ADD COLUMN document_type VARCHAR(50);
ALTER TABLE documents ADD COLUMN status ENUM('pending','received','approved','rejected');
ALTER TABLE documents ADD COLUMN version INT DEFAULT 1;
ALTER TABLE documents ADD COLUMN expires_at DATE;
ALTER TABLE documents ADD COLUMN approved_by BIGINT;
ALTER TABLE documents ADD COLUMN approved_at TIMESTAMP;
ALTER TABLE documents ADD COLUMN rejection_reason TEXT;

CREATE TABLE document_types_by_program (
    id BIGINT PRIMARY KEY,
    program_type VARCHAR(50),
    document_type VARCHAR(50),
    is_required BOOLEAN,
    description TEXT
);
```

### Alertas Automáticas
- Pasaportes que vencen en < 6 meses
- Documentos pendientes de aprobación > 7 días
- Documentos rechazados sin re-upload
- Checklist incompleto antes de deadline

---

## GAP 6: FINANZAS (50% FALTANTE)

### ✅ Implementado
- Tabla `financial_transactions`
- Registro básico de pagos
- Facturación simple

### ❌ Faltante
- **Plan de pagos automático** por programa
- **Conversión multi-moneda** (PYG-USD en tiempo real)
- **Promociones aplicables**
  - 2x1 EndOfTheYear
  - IE CUE 2X1
  - Promo Charla WAT
  - Aniversario
  - Descuentos universitarios
- **Costos no reembolsables** marcados
- **Pagos parciales** con tracking
- **Notas de crédito** (devoluciones)
- **Reportes financieros** avanzados

### Impacto
🟠 **ALTA** - Finanzas es crítico para el negocio

### Solución
```sql
CREATE TABLE payment_plans (
    id BIGINT PRIMARY KEY,
    application_id BIGINT,
    total_amount_usd DECIMAL(10,2),
    total_amount_pyg DECIMAL(15,2),
    promotion_applied VARCHAR(100),
    discount_percentage DECIMAL(5,2),
    created_at TIMESTAMP
);

CREATE TABLE payment_installments (
    id BIGINT PRIMARY KEY,
    payment_plan_id BIGINT,
    installment_number INT,
    amount_usd DECIMAL(10,2),
    amount_pyg DECIMAL(15,2),
    due_date DATE,
    paid_at TIMESTAMP,
    exchange_rate DECIMAL(10,4),
    is_refundable BOOLEAN DEFAULT TRUE,
    status ENUM('pending','paid','overdue','cancelled')
);

CREATE TABLE promotions (
    id BIGINT PRIMARY KEY,
    code VARCHAR(50) UNIQUE,
    name VARCHAR(255),
    discount_type ENUM('percentage','fixed_amount'),
    discount_value DECIMAL(10,2),
    valid_from DATE,
    valid_until DATE,
    applicable_programs TEXT,
    is_active BOOLEAN
);
```

### Conversión Multi-Moneda
- API externa para tipo de cambio (BCP, SET)
- Guardar tipo de cambio histórico en cada transacción
- Reportes en ambas monedas
- Cálculo automático de saldos

---

## GAP 7: SPONSORS Y HOST COMPANIES (100% FALTANTE)

### ❌ Completamente Faltante
- Tabla `sponsors`
- Tabla `host_companies`
- CRUD de sponsors (AAG, AWA, GH, etc.)
- CRUD de host companies
- Relación con job offers
- Términos y condiciones específicos
- Contactos
- Historial de participantes

### Impacto
🟡 **MEDIA** - Necesario pero no bloqueante inicialmente

### Solución
```sql
CREATE TABLE sponsors (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    code VARCHAR(50) UNIQUE,
    country VARCHAR(100),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    terms_and_conditions TEXT,
    is_active BOOLEAN,
    created_at TIMESTAMP
);

CREATE TABLE host_companies (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    industry VARCHAR(100),
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100),
    contact_person VARCHAR(255),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    rating DECIMAL(3,2),
    total_participants INT DEFAULT 0,
    is_active BOOLEAN,
    created_at TIMESTAMP
);
```

---

## GAP 8: REPORTES Y DASHBOARD (80% FALTANTE)

### ✅ Implementado
- Dashboard básico con contadores

### ❌ Faltante
- **KPIs principales**
  - Total participantes activos
  - Por programa y estado
  - Visas aprobadas/pendientes
  - Ingresos del mes
  - Conversión de leads
- **Reportes operativos**
  - Participantes por agente
  - Job offers disponibles
  - Documentación pendiente
  - Citas consulares próximas
- **Reportes financieros**
  - Ingresos por programa
  - Cuentas por cobrar
  - Promociones más usadas
- **Gráficos interactivos**
- **Exportación** (Excel, PDF, CSV)

### Impacto
🟠 **ALTA** - Necesario para toma de decisiones

### Solución
- Dashboard con Chart.js o Recharts
- Queries optimizadas con índices
- Cache de reportes pesados (Redis)
- Exportación con PhpSpreadsheet
- Filtros personalizados por fecha, programa, agente

---

## PRIORIZACIÓN DE GAPS

### Sprint 1-2 (Semanas 1-4) - CRÍTICO
1. ✅ Evaluación de Inglés (100%)
2. ✅ Job Offers (100%)
3. ✅ Proceso de Visa (100%)

### Sprint 3-4 (Semanas 5-8) - ALTA
4. ✅ Gestión de Participantes completa (40%)
5. ✅ Documentación avanzada (70%)
6. ✅ Finanzas multi-moneda (50%)

### Sprint 5-6 (Semanas 9-12) - MEDIA
7. ✅ Sponsors y Host Companies (100%)
8. ✅ Reportes y Dashboard (80%)
9. ✅ Testing exhaustivo

---

## ESTIMACIÓN DE ESFUERZO POR ROL

| Rol | Horas Totales | Semanas (40h) |
|-----|---------------|---------------|
| Backend Developer | 320h | 8 semanas |
| Frontend Developer | 280h | 7 semanas |
| QA Engineer | 160h | 4 semanas |
| UI Designer | 80h | 2 semanas |
| DevOps Engineer | 80h | 2 semanas |
| Security Specialist | 40h | 1 semana |
| Code Reviewer | 80h | 2 semanas (distribuido) |
| Project Manager | 160h | 4 semanas (distribuido) |

**TOTAL:** 1,200 horas de trabajo

---

## CONCLUSIÓN

El análisis revela que **72% del sistema está faltante** o incompleto. Los gaps más críticos son:

1. 🔴 **Evaluación de Inglés** - 0% implementado
2. 🔴 **Job Offers** - 0% implementado
3. 🔴 **Proceso de Visa** - 0% implementado

Estos 3 módulos son **bloqueantes** y deben priorizarse en los primeros 2 sprints.

Con el plan de acción propuesto, podemos cerrar todos los gaps en **18 semanas** trabajando con metodología ágil y el equipo completo.
