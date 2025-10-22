# ROADMAP DE DESARROLLO - IE INTERCULTURAL EXPERIENCE

**Fecha inicio:** 21 de Octubre, 2025  
**Duración:** 8 sprints (8 semanas)  
**Metodología:** Scrum - sprints de 1 semana  

---

## RESUMEN EJECUTIVO

### Totales:
- **Duración:** 40 días laborables (~8 semanas)  
- **Esfuerzo:** 314 horas  
- **Equipo:** 2 developers full time + 1 QA part time  

### Hitos Críticos:
- **Semana 2:** Módulo VISA funcional ✅
- **Semana 4:** Formulario wizard + vista detallada ✅
- **Semana 6:** Documentos y Job Offers ✅
- **Semana 8:** Sistema completo ✅

---

## SPRINTS OVERVIEW

| Sprint | Objetivo | Duración | Esfuerzo | Prioridad |
|--------|----------|----------|----------|-----------|
| 0 | Preparación | 3 días | 18h | Setup |
| 1 | Módulo VISA | 5 días | 40h | CRÍTICA |
| 2 | Wizard Create | 5 días | 40h | CRÍTICA |
| 3 | Show Tabs | 5 días | 40h | CRÍTICA |
| 4 | Inglés | 3 días | 24h | Alta |
| 5 | Docs + Jobs | 5 días | 40h | Alta |
| 6 | Dashboard | 5 días | 40h | Alta |
| 7 | Comunicaciones | 4 días | 32h | Media |
| 8 | Testing | 5 días | 40h | CRÍTICA |

---

## SPRINT 0 - PREPARACIÓN (3 días - 18h)

### Backend:
- [ ] Crear migración `visa_processes` table
- [ ] Crear migración `english_evaluations` table
- [ ] Crear modelos: VisaProcess, EnglishEvaluation
- [ ] Validar migraciones existentes

### Frontend:
- [ ] Crear componentes base:
  - `x-wizard-step`
  - `x-status-badge`
  - `x-visa-status-badge`
  - `x-english-level-badge`
  - `x-timeline-item`
- [ ] CSS para wizard
- [ ] CSS para timeline visual
- [ ] Actualizar layout admin

---

## SPRINT 1 - MÓDULO VISA (5 días - 40h) 🔴 CRÍTICO

### Día 1-2: Backend (16h)
- [ ] Migración completa
- [ ] Modelo con relaciones
- [ ] Controlador AdminVisaController
- [ ] Rutas

### Día 3-4: Vistas (16h)
- [ ] `visa/dashboard.blade.php` - KPIs + gráficos
- [ ] `visa/timeline.blade.php` - Timeline visual 9 pasos
- [ ] `visa/calendar.blade.php` - Calendario citas consulares

### Día 5: Testing (8h)
- [ ] Pruebas funcionales
- [ ] Validaciones
- [ ] Responsive

---

## SPRINT 2 - WIZARD PARTICIPANTS/CREATE (5 días - 40h) 🔴 CRÍTICO

### Día 1: Pasos 1-3 (8h)
- [ ] Paso 1: Datos Personales (11 campos)
- [ ] Paso 2: Académicos y Laborales
- [ ] Paso 3: Contactos Emergencia (dinámico)

### Día 2: Pasos 4-5 (8h)
- [ ] Paso 4: Salud (declaración jurada)
- [ ] Paso 5: Programa (campos dinámicos)

### Día 3: Paso 6 (8h)
- [ ] Datos por programa (condicionales):
  - Work & Travel
  - Teachers
  - Intern/Trainee
  - Au Pair
  - Higher Education

### Día 4: Pasos 7-9 (8h)
- [ ] Paso 7: Financiero
- [ ] Paso 8: Términos
- [ ] Paso 9: Revisión

### Día 5: Features (8h)
- [ ] Indicador progreso
- [ ] Auto-guardado
- [ ] Validaciones
- [ ] Responsive
- [ ] Testing

---

## SPRINT 3 - SHOW TABS (5 días - 40h) 🔴 CRÍTICO

### Día 1: Overview + Inglés (8h)
- [ ] Tab 1: Overview (nuevo)
- [ ] Tab 4: Evaluación Inglés (nuevo)

### Día 2: Job Offer (8h)
- [ ] Tab 5: Job Offer (nuevo - W&T)
  - Modal asignación
  - Modal cambio
  - Historial

### Día 3: Visa (8h)
- [ ] Tab 6: Proceso Visa (nuevo)
  - Timeline 9 pasos
  - Upload docs
  - Notas

### Día 4: Log + Comunicaciones + Config (8h)
- [ ] Tab 9: Log (nuevo)
- [ ] Tab 10: Comunicaciones (nuevo)
- [ ] Tab 11: Configuración (nuevo)

### Día 5: Mejoras + Testing (8h)
- [ ] Mejorar Tab 7: Documentos
- [ ] Mejorar Tab 8: Pagos
- [ ] Testing completo

---

## SPRINT 4 - EVALUACIÓN INGLÉS (3 días - 24h)

- [ ] Backend: modelo + controlador
- [ ] Frontend: registro + historial
- [ ] Dashboard evaluaciones
- [ ] Límite 3 intentos
- [ ] Niveles CEFR automáticos

---

## SPRINT 5 - DOCUMENTOS + JOB OFFERS (5 días - 40h)

### Documentos (2 días - 16h):
- [ ] `documents/review.blade.php`
- [ ] Preview PDFs
- [ ] Aprobar/Rechazar
- [ ] Alertas vencimiento

### Job Offers (2 días - 16h):
- [ ] Validar cupos
- [ ] Validar requisitos
- [ ] Sistema penalidades
- [ ] Modal asignación

### Testing (1 día - 8h)

---

## SPRINT 6 - DASHBOARD + REPORTES (5 días - 40h)

### Dashboard (2 días - 16h):
- [ ] Validar KPIs
- [ ] Gráficos interactivos
- [ ] Alertas
- [ ] Quick actions

### Reportes (3 días - 24h):
- [ ] Participantes (filtros + export)
- [ ] Financiero (gráficos)
- [ ] Visa (conversión)
- [ ] Funnel

---

## SPRINT 7 - COMUNICACIONES + SETTINGS (4 días - 32h)

### Comunicaciones (2 días - 16h):
- [ ] Email masivo
- [ ] Templates
- [ ] Historial

### Settings (2 días - 16h):
- [ ] Config general
- [ ] Programas
- [ ] Costos
- [ ] Usuarios

---

## SPRINT 8 - TESTING + DOCUMENTACIÓN (5 días - 40h)

### Testing (2 días - 16h):
- [ ] Funcional completo
- [ ] Navegadores
- [ ] Responsive
- [ ] Performance

### Optimización (1 día - 8h):
- [ ] Queries N+1
- [ ] Minify assets
- [ ] Cache

### Documentación (2 días - 16h):
- [ ] Manual usuario admin
- [ ] Manual agentes
- [ ] Docs técnica
- [ ] README

---

**Ver detalles completos en:**
- `SPRINT_1_VISA_DETALLADO.md`
- `SPRINT_2_WIZARD_DETALLADO.md`
- `SPRINT_3_TABS_DETALLADO.md`
