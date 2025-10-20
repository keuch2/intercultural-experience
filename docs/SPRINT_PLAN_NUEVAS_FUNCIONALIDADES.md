# SPRINT PLAN - NUEVAS FUNCIONALIDADES
## Intercultural Experience Platform

**Fecha de Inicio:** 16 de Octubre, 2025  
**Duración:** 8 semanas (4 sprints de 2 semanas)  
**Equipo:** 10 roles según TEAM_STRUCTURE.md  
**Metodología:** Scrum adaptado

---

## 🎯 RESUMEN EJECUTIVO

Este plan aborda **9 épicas principales** organizadas en 4 sprints de 2 semanas, siguiendo la metodología Scrum adaptada del equipo.

### Épicas Priorizadas:

**Sprint 1-2 (Semanas 1-4):** Roles y Notificaciones
- ✅ Épica 1: Sistema de Roles de Agentes
- ✅ Épica 2: Alertas por Email y Mensajería Masiva

**Sprint 3 (Semanas 5-6):** Importación y Auditoría
- 📦 Épica 3: Carga Masiva de Datos
- 📦 Épica 4: Registro de Actividad

**Sprint 4 (Semanas 7-8):** Deadlines y Facturación
- 📦 Épica 5: Sistema de Deadlines y Recordatorios
- 📦 Épica 6: Recibos y Cuotas de Pago
- 📦 Épica 7: Estado de Participantes
- 📦 Épica 8: Papelera de Reciclaje
- 📦 Épica 9: Declaración de Mayoría de Edad

---

## 📋 ÉPICA 1: SISTEMA DE ROLES Y GESTIÓN DE AGENTES

### User Story 1.1: Crear Rol de Agentes

**Como** administrador del sistema  
**Quiero** tener un nuevo rol de "Agente"  
**Para que** puedan crear participantes y asignarlos a programas sin tener acceso completo de administrador

### Acceptance Criteria:
- [x] Existe un rol `agent` en la tabla users (además de `admin` y `user`)
- [x] Los agentes pueden acceder a un panel específico `/agent/*`
- [x] Los agentes pueden crear nuevos participantes (usuarios con rol `user`)
- [x] Los agentes pueden asignar participantes a programas
- [x] Los agentes NO pueden acceder a funciones administrativas
- [x] Los agentes pueden ver solo los participantes que ellos crearon
- [x] Existe un CRUD de agentes en el panel administrativo

### Tareas Backend Developer:
- [x] Crear migración para agregar rol `agent` al enum de `users.role`
- [x] Crear `AgentMiddleware` para proteger rutas de agentes
- [x] Crear `AgentController` con métodos: dashboard, createParticipant, assignToProgram, myParticipants
- [x] Agregar campo `created_by_agent_id` a tabla users (nullable, foreign key)
- [x] Crear políticas (Policies) para verificar que agente solo ve sus participantes
- [x] Crear rutas en `routes/web.php` para `/agent/*`
- [x] Crear seeder para usuario agente de prueba

### Tareas Frontend Developer:
- [x] Crear layout `resources/views/layouts/agent.blade.php`
- [x] Crear vistas: `agent/dashboard.blade.php`, `agent/participants/index.blade.php`, `agent/participants/create.blade.php`
- [x] Implementar formulario de creación de participantes para agentes
- [x] Implementar selector de programas para asignación
- [x] Agregar indicador visual de "Creado por: [Nombre Agente]" en vista de participantes del admin

### Tareas UI Designer:
- [x] Diseñar interfaz del panel de agentes (diferente a admin)
- [x] Diseñar dashboard de agentes con métricas
- [x] Diseñar flujo de creación de participante simplificado

### Tareas QA Engineer:
- [x] Crear test cases para permisos de agentes
- [x] Verificar que agentes NO pueden acceder a rutas admin
- [x] Verificar que agentes solo ven sus propios participantes
- [x] Crear tests de integración

### Tareas Security Specialist:
- [x] Auditar que agentes no pueden escalar privilegios
- [x] Verificar que políticas de autorización están correctamente implementadas
- [x] Revisar que no hay SQL injection en filtros por agente

---

## 📋 ÉPICA 2: SISTEMA DE NOTIFICACIONES Y ALERTAS

### User Story 2.1: Alertas por Email

**Como** sistema  
**Quiero** enviar emails automáticos en eventos clave  
**Para** mantener informados a usuarios, agentes y administradores

### Acceptance Criteria:
- [x] Se envía email cuando se crea un nuevo usuario (admin, agente o participante)
- [x] El email incluye credenciales temporales (contraseña generada)
- [x] Se envía email de bienvenida personalizado según rol
- [x] Se envía email cuando un participante es asignado a un programa
- [x] Se envía email cuando cambia el estado de una aplicación
- [x] Se envía email cuando se verifica un pago
- [x] Todos los emails tienen diseño responsive y branding de IE

### Tareas Backend Developer:
- [x] Crear Mailable classes: WelcomeUser, CredentialsSent, ProgramAssigned, ApplicationStatusChanged, PaymentVerified
- [x] Crear Notification classes usando Laravel Notifications
- [x] Implementar eventos: UserCreated, ParticipantAssignedToProgram, ApplicationStatusChanged, PaymentVerified
- [x] Crear listeners para cada evento que envíen emails
- [x] Configurar queue para envío asíncrono de emails
- [x] Crear comando `php artisan emails:test` para probar envíos
- [x] Agregar configuración en .env para habilitar/deshabilitar emails

### User Story 2.2: Mensajería Masiva

**Como** administrador  
**Quiero** enviar notificaciones masivas a usuarios  
**Para** comunicar información importante de forma eficiente

### Acceptance Criteria:
- [x] Existe módulo "Mensajería Masiva" en panel admin
- [x] Puedo seleccionar destinatarios: todos, por rol, por programa, por estado
- [x] Puedo enviar por: Email, Push Notification (app móvil), o ambos
- [x] Puedo programar envío para fecha/hora específica
- [x] Puedo ver historial de mensajes enviados
- [x] Puedo ver estadísticas: enviados, entregados, leídos, fallidos
- [x] Los usuarios pueden ver notificaciones en la app

### Tareas Backend Developer:
- [x] Crear modelo MassMessage con campos requeridos
- [x] Crear migración para tabla mass_messages
- [x] Crear tabla mass_message_recipients (pivot) para tracking
- [x] Crear MassMessageController con CRUD
- [x] Implementar job SendMassMessageJob para procesamiento asíncrono
- [x] Integrar con Firebase Cloud Messaging para push notifications
- [x] Crear API endpoint para app móvil: GET /api/notifications
- [x] Implementar filtros: por rol, por programa, por estado

---

## 📋 ÉPICA 3: CARGA MASIVA DE DATOS

### User Story 3.1: Importación Masiva de Usuarios

**Como** administrador  
**Quiero** importar usuarios masivamente desde Excel/CSV  
**Para** agilizar la carga inicial de datos

### Acceptance Criteria:
- [ ] Existe módulo "Importación Masiva" en panel admin
- [ ] Puedo descargar plantillas de ejemplo
- [ ] Puedo subir archivo Excel (.xlsx) o CSV
- [ ] El sistema valida datos antes de importar
- [ ] Se muestra preview de datos con errores resaltados
- [ ] Se genera reporte de importación
- [ ] Se envían emails a usuarios importados

---

## 📋 ÉPICA 4: SISTEMA DE AUDITORÍA

### User Story 4.1: Registro de Actividad de Usuarios

**Como** administrador  
**Quiero** ver un registro de todas las acciones de usuarios  
**Para** auditar el sistema y detectar problemas

### Acceptance Criteria:
- [ ] Se registran todas las acciones importantes
- [ ] Cada registro incluye: usuario, acción, modelo, fecha/hora, IP
- [ ] Existe vista de "Registro de Actividad" en panel admin
- [ ] Puedo filtrar por: usuario, acción, fecha, modelo
- [ ] El registro es inmutable

---

## 📋 ÉPICA 5: SISTEMA DE DEADLINES

### User Story 5.1: Deadlines para Requisitos

**Como** administrador  
**Quiero** establecer fechas límite para requisitos  
**Para que** participantes sepan cuándo deben completarlos

### Acceptance Criteria:
- [ ] Puedo establecer deadline al crear/editar un requisito
- [ ] El deadline se muestra en la app del participante
- [ ] Se envían recordatorios automáticos: 30, 15, 7, 3, 1 días antes
- [ ] Después del deadline, el requisito se marca como "Vencido"

---

## 📋 ÉPICA 6: SISTEMA DE FACTURACIÓN

### User Story 6.1: Generación de Recibos de Pago

**Como** participante  
**Quiero** descargar recibos de mis pagos  
**Para** tener comprobante de pago

### User Story 6.2: Sistema de Cuotas de Pago

**Como** administrador  
**Quiero** crear planes de cuotas para participantes  
**Para** facilitar el pago de programas

---

## 📋 ÉPICA 7: GESTIÓN DE ESTADO DE PARTICIPANTES

### User Story 7.1: Estado de Participante en Programas

**Como** administrador  
**Quiero** ver y gestionar el estado de participantes  
**Para** saber quiénes están activos, culminaron o son ex-participantes

---

## 📋 ÉPICA 8: PAPELERA DE RECICLAJE

### User Story 8.1: Soft Delete con Papelera

**Como** administrador  
**Quiero** que los registros eliminados vayan a una papelera  
**Para** poder recuperarlos si fue un error

---

## 📋 ÉPICA 9: DECLARACIÓN DE MAYORÍA DE EDAD

### User Story 9.1: Checkbox de Mayor de Edad

**Como** sistema  
**Quiero** que usuarios confirmen ser mayores de edad  
**Para** cumplir con requisitos legales

---

## 🔄 FLUJO DE TRABAJO Y CHECKPOINTS

### Checkpoint 1: Diseño (Día 2)
- **UI Designer** → Entrega mockups
- **Revisores:** UX Researcher, Frontend Dev, End User
- **Criterio:** Aprobación de 3/3 revisores

### Checkpoint 2: Backend (Día 7)
- **Backend Developer** → Crea PR
- **Revisores:** Code Reviewer, Security Specialist
- **Criterio:** 0 vulnerabilidades críticas, tests >70%

### Checkpoint 3: Frontend (Día 10)
- **Frontend Developer** → Crea PR
- **Revisores:** UI Designer, Code Reviewer
- **Criterio:** Fidelidad al diseño >95%

### Checkpoint 4: Integración (Día 12)
- **DevOps Engineer** → Deploy a staging
- **QA Engineer** → Ejecuta test plan completo
- **Criterio:** 0 bugs bloqueantes

### Checkpoint 5: UAT (Día 14)
- **End User** → Valida funcionalidad
- **Project Manager** → Aprueba para producción
- **Criterio:** Cumple 100% acceptance criteria

---

## 📊 DEFINITION OF DONE

Cada user story debe cumplir:

### Código:
- [ ] Implementado según acceptance criteria
- [ ] Sigue estándares PSR-12 (PHP) / ESLint (JS)
- [ ] Sin warnings en logs

### Testing:
- [ ] Tests unitarios >70% cobertura
- [ ] Tests E2E para flujos críticos
- [ ] QA manual completado

### Seguridad:
- [ ] Security review aprobado
- [ ] Sin vulnerabilidades críticas/altas
- [ ] Validación de inputs implementada

### Documentación:
- [ ] Código comentado (PHPDoc/JSDoc)
- [ ] README actualizado
- [ ] API documentada (si aplica)

### Deployment:
- [ ] Desplegado en staging
- [ ] Smoke tests pasados
- [ ] Migraciones ejecutadas

### Aprobación:
- [ ] Code review aprobado
- [ ] UAT aprobado por End User
- [ ] Sign-off de Project Manager

---

## 📈 MATRIZ DE RESPONSABILIDADES

| Épica | Backend | Frontend Web | Frontend Mobile | UI Designer | QA | Security | DevOps |
|-------|---------|--------------|-----------------|-------------|----|---------|----|
| Roles Agentes | ✅ Lead | ✅ Support | ❌ | ✅ Support | ✅ | ✅ | ❌ |
| Notificaciones | ✅ Lead | ✅ Support | ✅ Support | ✅ Support | ✅ | ✅ | ✅ Lead |
| Carga Masiva | ✅ Lead | ✅ Support | ❌ | ✅ Support | ✅ | ❌ | ❌ |
| Auditoría | ✅ Lead | ✅ Support | ❌ | ✅ Support | ✅ | ✅ | ❌ |
| Deadlines | ✅ Lead | ✅ Support | ✅ Support | ✅ Support | ✅ | ❌ | ✅ Support |
| Facturación | ✅ Lead | ✅ Support | ✅ Support | ✅ Lead | ✅ | ✅ | ❌ |
| Estado Participantes | ✅ Lead | ✅ Support | ✅ Support | ✅ Support | ✅ | ❌ | ❌ |
| Papelera | ✅ Lead | ✅ Support | ❌ | ✅ Support | ✅ | ✅ | ❌ |
| Mayor de Edad | ✅ Support | ✅ Support | ✅ Lead | ✅ Support | ✅ | ✅ | ❌ |

---

## 🚀 CRONOGRAMA

### Sprint 1 (Semanas 1-2): Épica 1 - Sistema de Agentes
- **Día 1-2:** Diseño (UI Designer)
- **Día 3-7:** Backend (Backend Developer)
- **Día 8-10:** Frontend (Frontend Developer)
- **Día 11-12:** Testing QA
- **Día 13-14:** UAT y Deploy

### Sprint 2 (Semanas 3-4): Épica 2 - Notificaciones
- **Día 1-2:** Diseño (UI Designer)
- **Día 3-7:** Backend + DevOps (Firebase)
- **Día 8-10:** Frontend Web + Mobile
- **Día 11-12:** Testing QA
- **Día 13-14:** UAT y Deploy

### Sprint 3 (Semanas 5-6): Épicas 3-4
- Carga Masiva + Auditoría

### Sprint 4 (Semanas 7-8): Épicas 5-9
- Deadlines + Facturación + Estado + Papelera + Mayor de Edad

---

**Documento creado:** 16 de Octubre, 2025  
**Responsable:** Project Manager  
**Próxima revisión:** Semanal durante ejecución
