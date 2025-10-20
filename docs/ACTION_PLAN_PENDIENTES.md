# PLAN DE ACCIÓN - RESOLUCIÓN DE PENDIENTES
## Panel Administrativo - Intercultural Experience

**Fecha de Creación:** 12 de Octubre, 2025  
**Prioridad:** Alta  
**Duración Estimada:** 8-12 semanas

---

## RESUMEN EJECUTIVO

Este plan aborda los **47 pendientes críticos** identificados en el análisis del panel administrativo, organizados en 4 fases de implementación con prioridades claras y estimaciones realistas.

**Recursos Necesarios:**
- 2 Desarrolladores Backend (Laravel)
- 1 Desarrollador Frontend
- 1 QA Tester
- 1 Product Owner (part-time)

---

## FASE 1: ESTABILIZACIÓN (Semanas 1-2)
**Objetivo:** Resolver problemas críticos que afectan operación diaria

### 1.1 Sistema de Exportaciones [CRÍTICO]
**Duración:** 5 días  
**Asignado a:** Backend Dev 1

**Tareas:**
- [ ] Instalar Laravel Excel package
- [ ] Crear ExportController base
- [ ] Implementar exportación de Participantes (CSV/XLSX)
- [ ] Implementar exportación de Aplicaciones
- [ ] Implementar exportación de Transacciones Financieras
- [ ] Implementar exportación de Asignaciones
- [ ] Agregar botones de exportación en vistas
- [ ] Tests de exportación

**Archivos a Modificar:**
- `composer.json` - Agregar maatwebsite/excel
- `app/Http/Controllers/Admin/AdminParticipantController.php`
- `app/Http/Controllers/Admin/AdminApplicationController.php`
- `app/Http/Controllers/Admin/AdminFinanceController.php`
- `app/Http/Controllers/Admin/AdminProgramAssignmentController.php`
- `resources/views/admin/participants/index.blade.php`
- `resources/views/admin/applications/index.blade.php`

**Criterios de Aceptación:**
- ✅ Exportar a CSV y XLSX
- ✅ Incluir filtros aplicados
- ✅ Nombres de archivo descriptivos con fecha
- ✅ Manejo de grandes volúmenes (>1000 registros)

---

### 1.2 Sistema de Notificaciones Básico [CRÍTICO]
**Duración:** 5 días  
**Asignado a:** Backend Dev 2

**Tareas:**
- [ ] Configurar Laravel Notifications
- [ ] Crear NotificationService
- [ ] Implementar notificación: Aplicación Aprobada
- [ ] Implementar notificación: Aplicación Rechazada
- [ ] Implementar notificación: Pago Verificado
- [ ] Implementar notificación: Pago Rechazado
- [ ] Implementar notificación: Programa Asignado
- [ ] Crear plantillas de email (Blade)
- [ ] Tests de notificaciones

**Archivos a Crear:**
- `app/Services/NotificationService.php`
- `app/Notifications/ApplicationApproved.php`
- `app/Notifications/ApplicationRejected.php`
- `app/Notifications/PaymentVerified.php`
- `app/Notifications/PaymentRejected.php`
- `app/Notifications/ProgramAssigned.php`
- `resources/views/emails/application-approved.blade.php`
- `resources/views/emails/application-rejected.blade.php`

**Archivos a Modificar:**
- `app/Http/Controllers/Admin/AdminApplicationController.php`
- `app/Http/Controllers/Admin/AdminFinanceController.php`
- `app/Http/Controllers/Admin/AdminProgramAssignmentController.php`

**Criterios de Aceptación:**
- ✅ Email enviado automáticamente en eventos críticos
- ✅ Plantillas profesionales y personalizadas
- ✅ Incluir información relevante (programa, monto, etc.)
- ✅ Logs de emails enviados

---

### 1.3 Validaciones Mejoradas [CRÍTICO]
**Duración:** 3 días  
**Asignado a:** Backend Dev 1

**Tareas:**
- [ ] Crear Form Requests para cada módulo
- [ ] Validar archivos subidos (tipo, tamaño, extensión)
- [ ] Validar montos financieros
- [ ] Validar fechas (no pasadas, rangos lógicos)
- [ ] Mensajes de error descriptivos en español
- [ ] Validación de capacidad de programas
- [ ] Tests de validación

**Archivos a Crear:**
- `app/Http/Requests/StoreParticipantRequest.php`
- `app/Http/Requests/UpdateParticipantRequest.php`
- `app/Http/Requests/StoreApplicationRequest.php`
- `app/Http/Requests/StoreProgramRequest.php`
- `app/Http/Requests/StoreTransactionRequest.php`
- `app/Http/Requests/StorePaymentRequest.php`

**Archivos a Modificar:**
- Todos los controladores Admin (reemplazar validate() por Form Requests)

**Criterios de Aceptación:**
- ✅ Validación centralizada en Form Requests
- ✅ Archivos: max 5MB, tipos permitidos definidos
- ✅ Fechas lógicas (start < end)
- ✅ Mensajes en español
- ✅ No permitir sobrecapacidad en programas

---

## FASE 2: COMPLETITUD (Semanas 3-6)
**Objetivo:** Completar funcionalidades parcialmente implementadas

### 2.1 Completar Módulo de Aplicaciones [ALTA]
**Duración:** 5 días  
**Asignado a:** Backend Dev 2

**Tareas:**
- [ ] Sistema de notas/comentarios por aplicación
- [ ] Historial de cambios de estado (timeline)
- [ ] Asignación de revisor a aplicación
- [ ] Filtros por progreso de requisitos
- [ ] Vista de timeline visual
- [ ] Notificaciones integradas
- [ ] Tests

**Archivos a Crear:**
- `app/Models/ApplicationNote.php`
- `app/Models/ApplicationStatusHistory.php`
- `database/migrations/xxxx_create_application_notes_table.php`
- `database/migrations/xxxx_create_application_status_history_table.php`
- `resources/views/admin/applications/timeline.blade.php`

**Archivos a Modificar:**
- `app/Http/Controllers/Admin/AdminApplicationController.php`
- `app/Models/Application.php`
- `resources/views/admin/applications/show.blade.php`

---

### 2.2 Completar Módulo de Soporte [ALTA]
**Duración:** 4 días  
**Asignado a:** Backend Dev 1

**Tareas:**
- [ ] Sistema de respuestas/comentarios en tickets
- [ ] Adjuntar archivos a tickets
- [ ] Plantillas de respuesta rápida
- [ ] SLA básico (tiempo de respuesta)
- [ ] Escalamiento por prioridad
- [ ] Estadísticas de tiempo de respuesta
- [ ] Tests

**Archivos a Crear:**
- `app/Models/TicketReply.php`
- `app/Models/TicketTemplate.php`
- `database/migrations/xxxx_create_ticket_replies_table.php`
- `database/migrations/xxxx_create_ticket_templates_table.php`
- `resources/views/admin/support/replies.blade.php`

**Archivos a Modificar:**
- `app/Http/Controllers/Admin/AdminSupportTicketController.php`
- `app/Models/SupportTicket.php`
- `resources/views/admin/support/show.blade.php`

---

### 2.3 Mejorar Módulo de Documentos [MEDIA]
**Duración:** 3 días  
**Asignado a:** Backend Dev 2

**Tareas:**
- [ ] Motivo de rechazo obligatorio
- [ ] Historial de versiones de documentos
- [ ] Viewer de PDF en línea
- [ ] Validación estricta de formatos
- [ ] Compresión automática de imágenes
- [ ] Notificaciones de estado
- [ ] Tests

**Archivos a Modificar:**
- `app/Http/Controllers/Admin/AdminDocumentController.php`
- `app/Models/ApplicationDocument.php`
- `resources/views/admin/documents/index.blade.php`

---

### 2.4 Completar Reportes [ALTA]
**Duración:** 5 días  
**Asignado a:** Backend Dev 1 + Frontend Dev

**Tareas:**
- [ ] Exportación de reportes a Excel
- [ ] Exportación de reportes a PDF
- [ ] Gráficos con Chart.js
- [ ] Reporte de conversión (funnel)
- [ ] Comparación entre períodos
- [ ] Filtros avanzados por fecha
- [ ] Tests

**Archivos a Crear:**
- `app/Services/ReportService.php`
- `app/Exports/FinancialReportExport.php`
- `resources/views/admin/reports/charts.blade.php`

**Archivos a Modificar:**
- `app/Http/Controllers/Admin/AdminReportController.php`
- `resources/views/admin/reports/index.blade.php`
- `resources/views/admin/finance/report.blade.php`

**Dependencias:**
- Laravel Excel (ya instalado en Fase 1)
- barryvdh/laravel-dompdf para PDFs
- Chart.js para gráficos

---

## FASE 3: CALIDAD Y TESTS (Semanas 7-9)
**Objetivo:** Garantizar estabilidad y calidad del código

### 3.1 Tests Automatizados [CRÍTICO]
**Duración:** 10 días  
**Asignado a:** Backend Dev 1 + Backend Dev 2 + QA

**Tareas:**
- [ ] Configurar PHPUnit
- [ ] Tests de modelos (relaciones, métodos)
- [ ] Tests de controladores (CRUD)
- [ ] Tests de Form Requests
- [ ] Tests de servicios
- [ ] Tests de notificaciones
- [ ] Tests de exportaciones
- [ ] Feature tests de flujos completos
- [ ] Coverage mínimo 60%

**Archivos a Crear:**
- `tests/Unit/Models/*Test.php` (24 archivos)
- `tests/Feature/Admin/*Test.php` (21 archivos)
- `tests/Feature/ExportTest.php`
- `tests/Feature/NotificationTest.php`

**Estructura de Tests:**
```
tests/
├── Unit/
│   ├── Models/
│   │   ├── UserTest.php
│   │   ├── ProgramTest.php
│   │   ├── ApplicationTest.php
│   │   └── ... (21 más)
│   └── Services/
│       ├── NotificationServiceTest.php
│       └── ReportServiceTest.php
└── Feature/
    ├── Admin/
    │   ├── DashboardTest.php
    │   ├── ParticipantTest.php
    │   ├── ApplicationTest.php
    │   └── ... (18 más)
    ├── ExportTest.php
    └── NotificationTest.php
```

---

### 3.2 Optimización de Performance [MEDIA]
**Duración:** 4 días  
**Asignado a:** Backend Dev 2

**Tareas:**
- [ ] Identificar N+1 queries
- [ ] Implementar eager loading
- [ ] Agregar índices en BD
- [ ] Cache de configuraciones
- [ ] Cache de reportes frecuentes
- [ ] Lazy loading de relaciones
- [ ] Query optimization
- [ ] Tests de performance

**Archivos a Modificar:**
- Todos los controladores (agregar eager loading)
- `database/migrations/xxxx_add_indexes.php`
- `config/cache.php`

**Métricas Objetivo:**
- Dashboard: < 500ms
- Listados: < 300ms
- Reportes: < 2s

---

### 3.3 Documentación Técnica [MEDIA]
**Duración:** 3 días  
**Asignado a:** Backend Dev 1

**Tareas:**
- [ ] Documentar API con Swagger
- [ ] Crear Postman collection
- [ ] Documentar modelos y relaciones
- [ ] Documentar servicios
- [ ] Guía de desarrollo
- [ ] Guía de deployment

**Archivos a Crear:**
- `docs/API_DOCUMENTATION.md`
- `docs/DATABASE_SCHEMA.md`
- `docs/DEVELOPMENT_GUIDE.md`
- `docs/DEPLOYMENT_GUIDE.md`
- `postman/IE_Admin_API.json`

---

## FASE 4: MEJORAS Y OPTIMIZACIÓN (Semanas 10-12)
**Objetivo:** Mejorar experiencia de usuario y automatización

### 4.1 Dashboard Mejorado [MEDIA]
**Duración:** 5 días  
**Asignado a:** Frontend Dev + Backend Dev 1

**Tareas:**
- [ ] Integrar Chart.js
- [ ] Gráficos de tendencias
- [ ] Métricas en tiempo real
- [ ] Filtros por rango de fechas
- [ ] Comparación con períodos anteriores
- [ ] Widgets configurables
- [ ] Exportación de dashboard

**Archivos a Modificar:**
- `app/Http/Controllers/Admin/AdminDashboardController.php`
- `resources/views/admin/dashboard.blade.php`
- Agregar `public/js/dashboard-charts.js`

---

### 4.2 Sistema de Permisos Granular [ALTA]
**Duración:** 6 días  
**Asignado a:** Backend Dev 2

**Tareas:**
- [ ] Implementar Spatie Permission
- [ ] Definir roles (Super Admin, Admin, Revisor, Financiero)
- [ ] Definir permisos por módulo
- [ ] Middleware de permisos
- [ ] UI para gestión de roles
- [ ] Asignación de permisos a usuarios
- [ ] Tests de autorización

**Archivos a Crear:**
- `database/seeders/RolesAndPermissionsSeeder.php`
- `app/Http/Middleware/CheckPermission.php`
- `resources/views/admin/roles/index.blade.php`

**Roles Propuestos:**
- **Super Admin:** Acceso total
- **Admin:** Gestión de programas y aplicaciones
- **Revisor:** Solo revisión de aplicaciones
- **Financiero:** Solo módulo financiero

---

### 4.3 Workflow Automation [MEDIA]
**Duración:** 5 días  
**Asignado a:** Backend Dev 1

**Tareas:**
- [ ] Configurar Laravel Queues
- [ ] Jobs para recordatorios automáticos
- [ ] Recordatorio de deadline de aplicación
- [ ] Recordatorio de requisitos pendientes
- [ ] Recordatorio de pagos pendientes
- [ ] Escalamiento automático de tickets
- [ ] Scheduler para tareas recurrentes
- [ ] Tests de jobs

**Archivos a Crear:**
- `app/Jobs/SendApplicationDeadlineReminder.php`
- `app/Jobs/SendPendingRequisitesReminder.php`
- `app/Jobs/SendPaymentReminder.php`
- `app/Jobs/EscalateTicket.php`
- `app/Console/Commands/SendReminders.php`

**Archivos a Modificar:**
- `app/Console/Kernel.php` (schedule)
- `config/queue.php`

---

## CRONOGRAMA DETALLADO

### Semana 1-2: FASE 1 - Estabilización
| Día | Backend Dev 1 | Backend Dev 2 |
|-----|---------------|---------------|
| 1-5 | Exportaciones | Notificaciones |
| 6-8 | Validaciones | Soporte a Dev 1 |

### Semana 3-4: FASE 2A - Completitud
| Día | Backend Dev 1 | Backend Dev 2 |
|-----|---------------|---------------|
| 1-4 | Soporte | Aplicaciones |
| 5-7 | Reportes (backend) | Documentos |

### Semana 5-6: FASE 2B - Completitud
| Día | Backend Dev 1 | Backend Dev 2 | Frontend Dev |
|-----|---------------|---------------|--------------|
| 1-5 | Reportes (cont.) | Tests iniciales | Reportes (UI) |

### Semana 7-9: FASE 3 - Calidad
| Día | Backend Dev 1 | Backend Dev 2 | QA |
|-----|---------------|---------------|-----|
| 1-10 | Tests (50%) | Tests (50%) + Performance | Testing manual |
| 11-13 | Documentación | Optimización | Regression tests |

### Semana 10-12: FASE 4 - Mejoras
| Día | Backend Dev 1 | Backend Dev 2 | Frontend Dev |
|-----|---------------|---------------|--------------|
| 1-5 | Dashboard (backend) | Permisos | Dashboard (UI) |
| 6-10 | Automation | Soporte a Dev 1 | Ajustes finales |

---

## DEPENDENCIAS Y RIESGOS

### Dependencias Técnicas:
1. **Exportaciones** → Notificaciones (usar en exports)
2. **Validaciones** → Todos los módulos
3. **Tests** → Todas las funcionalidades previas
4. **Permisos** → Después de tests

### Riesgos Identificados:

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|--------------|---------|------------|
| Falta de recursos | Media | Alto | Priorizar Fase 1 y 2 |
| Bugs en producción | Alta | Medio | Tests exhaustivos en Fase 3 |
| Cambios de requerimientos | Media | Medio | Revisiones semanales con PO |
| Performance issues | Baja | Alto | Optimización en Fase 3 |
| Integración compleja | Media | Medio | Pair programming |

---

## MÉTRICAS DE ÉXITO

### KPIs por Fase:

**Fase 1:**
- ✅ 4 módulos con exportación funcional
- ✅ 5 tipos de notificaciones automáticas
- ✅ 0 errores de validación en producción

**Fase 2:**
- ✅ 100% de módulos completos (sin "parcial")
- ✅ Tiempo promedio de respuesta a tickets < 24h
- ✅ Reportes exportables en 3 formatos

**Fase 3:**
- ✅ Cobertura de tests > 60%
- ✅ Tiempo de carga de dashboard < 500ms
- ✅ Documentación completa al 100%

**Fase 4:**
- ✅ 4 roles con permisos configurados
- ✅ 4 tipos de recordatorios automáticos
- ✅ Dashboard con 5+ gráficos interactivos

---

## ENTREGABLES POR FASE

### Fase 1:
- [ ] Sistema de exportaciones funcional
- [ ] 5 notificaciones automáticas
- [ ] Form Requests implementados
- [ ] Documento de validaciones

### Fase 2:
- [ ] Módulo de aplicaciones completo
- [ ] Módulo de soporte completo
- [ ] Módulo de documentos mejorado
- [ ] Reportes con exportación

### Fase 3:
- [ ] Suite de tests (60% coverage)
- [ ] Performance optimizado
- [ ] Documentación técnica completa
- [ ] Postman collection

### Fase 4:
- [ ] Dashboard con gráficos
- [ ] Sistema de permisos
- [ ] Workflow automation
- [ ] Guía de usuario actualizada

---

## RECURSOS Y PRESUPUESTO

### Equipo Necesario:
- **2 Backend Developers (Laravel):** 12 semanas full-time
- **1 Frontend Developer:** 6 semanas (Fase 2 y 4)
- **1 QA Tester:** 4 semanas (Fase 3)
- **1 Product Owner:** 12 semanas part-time (20%)

### Herramientas:
- Laravel Excel (gratis)
- Chart.js (gratis)
- Spatie Permission (gratis)
- DomPDF (gratis)
- PHPUnit (incluido)

**Costo Estimado:** Depende de tarifas del equipo

---

## PRÓXIMOS PASOS INMEDIATOS

1. **Esta Semana:**
   - [ ] Revisar y aprobar este plan
   - [ ] Asignar equipo
   - [ ] Configurar entorno de desarrollo
   - [ ] Crear branch `feature/phase-1-stabilization`

2. **Semana 1:**
   - [ ] Kickoff meeting
   - [ ] Instalar dependencias (Laravel Excel)
   - [ ] Iniciar Fase 1: Exportaciones
   - [ ] Daily standups

3. **Seguimiento:**
   - [ ] Reuniones semanales de progreso
   - [ ] Demos al final de cada fase
   - [ ] Retrospectivas cada 2 semanas

---

**Documento creado:** 12 de Octubre, 2025  
**Próxima revisión:** Semanal durante ejecución  
**Responsable:** Product Owner

