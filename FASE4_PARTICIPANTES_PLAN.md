# 📋 FASE 4: MEJORAS PARTICIPANTES - PLAN DE ACCIÓN

**Fecha:** 20 de Octubre, 2025  
**Objetivo:** Completar información de participantes según auditoría externa  
**Gap Actual:** 60% implementado - Falta salud, emergencia, laboral  

---

## 🎯 ANÁLISIS DE GAPS

### Información Existente ✅
- Datos personales básicos (nombre, email, teléfono)
- Dirección y nacionalidad
- Nivel académico e inglés
- Información bancaria (cifrada)
- Aplicaciones y solicitudes
- Sistema de puntos

### Información Faltante ❌
1. **Información de Salud**
   - Condiciones médicas
   - Alergias
   - Medicamentos
   - Seguro médico
   - Contacto médico de emergencia

2. **Contactos de Emergencia**
   - Nombre completo
   - Relación
   - Teléfono principal
   - Teléfono alternativo
   - Email
   - Dirección

3. **Experiencia Laboral**
   - Empresa/Organización
   - Cargo/Posición
   - Fecha inicio
   - Fecha fin
   - Descripción
   - Referencia

---

## 📊 PLAN DE IMPLEMENTACIÓN

### Paso 1: Base de Datos (30 min)
- [ ] Crear migración para campos de salud en `users`
- [ ] Crear tabla `emergency_contacts`
- [ ] Crear tabla `work_experiences`
- [ ] Ejecutar migraciones

### Paso 2: Modelos (20 min)
- [ ] Actualizar modelo `User` con fillable
- [ ] Crear modelo `EmergencyContact`
- [ ] Crear modelo `WorkExperience`
- [ ] Definir relaciones

### Paso 3: Vistas Admin (2 horas)
- [ ] Mejorar `show.blade.php` con tabs
  - Tab: Información General
  - Tab: Salud
  - Tab: Contactos de Emergencia
  - Tab: Experiencia Laboral
  - Tab: Aplicaciones
  - Tab: Evaluaciones de Inglés
  - Tab: Job Offers
  - Tab: Proceso de Visa
- [ ] Actualizar `edit.blade.php` con nuevos campos
- [ ] Crear formularios parciales reutilizables

### Paso 4: Controllers (30 min)
- [ ] Actualizar `AdminUserController@update`
- [ ] Crear métodos para gestionar contactos
- [ ] Crear métodos para gestionar experiencia

### Paso 5: Rutas (10 min)
- [ ] Agregar rutas para contactos de emergencia
- [ ] Agregar rutas para experiencia laboral

---

## 🗄️ ESTRUCTURA DE BASE DE DATOS

### Tabla: users (campos adicionales)
```sql
- medical_conditions (text, nullable)
- allergies (text, nullable)
- medications (text, nullable)
- health_insurance (string, nullable)
- health_insurance_number (string, nullable)
- emergency_medical_contact (string, nullable)
- emergency_medical_phone (string, nullable)
- blood_type (enum: A+, A-, B+, B-, AB+, AB-, O+, O-)
```

### Tabla: emergency_contacts
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key)
- name (string)
- relationship (string)
- phone (string)
- alternative_phone (string, nullable)
- email (string, nullable)
- address (text, nullable)
- is_primary (boolean, default false)
- created_at, updated_at
```

### Tabla: work_experiences
```sql
- id (bigint, primary key)
- user_id (bigint, foreign key)
- company (string)
- position (string)
- start_date (date)
- end_date (date, nullable)
- is_current (boolean, default false)
- description (text, nullable)
- reference_name (string, nullable)
- reference_phone (string, nullable)
- reference_email (string, nullable)
- created_at, updated_at
```

---

## 🎨 DISEÑO DE VISTAS

### Vista: show.blade.php (Tabs)

```
┌─────────────────────────────────────────────────┐
│ [General] [Salud] [Emergencia] [Laboral]       │
│ [Aplicaciones] [Inglés] [Job Offers] [Visa]    │
├─────────────────────────────────────────────────┤
│                                                 │
│  Contenido del tab seleccionado                │
│                                                 │
└─────────────────────────────────────────────────┘
```

#### Tab 1: Información General
- Datos personales
- Foto de perfil
- Estadísticas generales

#### Tab 2: Información de Salud
- Condiciones médicas
- Alergias
- Medicamentos actuales
- Seguro médico
- Tipo de sangre
- Contacto médico de emergencia

#### Tab 3: Contactos de Emergencia
- Lista de contactos
- Botón agregar nuevo
- Editar/Eliminar contactos
- Marcar como principal

#### Tab 4: Experiencia Laboral
- Lista de experiencias
- Timeline visual
- Botón agregar nueva
- Editar/Eliminar experiencias

#### Tab 5: Aplicaciones
- Lista de solicitudes
- Estados
- Programas

#### Tab 6: Evaluaciones de Inglés
- Historial de intentos
- Mejor resultado
- Nivel CEFR

#### Tab 7: Job Offers
- Ofertas reservadas
- Ofertas confirmadas
- Matching score

#### Tab 8: Proceso de Visa
- Estado actual
- Timeline de 15 estados
- Progreso visual

---

## 📝 VALIDACIONES

### Información de Salud
- medical_conditions: opcional, texto
- allergies: opcional, texto
- medications: opcional, texto
- health_insurance: opcional, string max 100
- blood_type: opcional, enum válido

### Contactos de Emergencia
- name: requerido, string max 100
- relationship: requerido, string max 50
- phone: requerido, formato teléfono
- email: opcional, formato email
- Solo un contacto puede ser principal

### Experiencia Laboral
- company: requerido, string max 100
- position: requerido, string max 100
- start_date: requerido, fecha válida
- end_date: opcional, fecha > start_date
- Si is_current = true, end_date debe ser null

---

## 🚀 ORDEN DE EJECUCIÓN

1. ✅ Crear migración campos salud (5 min)
2. ✅ Crear migración emergency_contacts (5 min)
3. ✅ Crear migración work_experiences (5 min)
4. ✅ Ejecutar migraciones (2 min)
5. ✅ Crear modelo EmergencyContact (5 min)
6. ✅ Crear modelo WorkExperience (5 min)
7. ✅ Actualizar modelo User (5 min)
8. ✅ Mejorar vista show.blade.php con tabs (1 hora)
9. ✅ Actualizar vista edit.blade.php (30 min)
10. ✅ Actualizar controller (20 min)
11. ✅ Agregar rutas (5 min)
12. ✅ Commit y push (5 min)

**Tiempo Total Estimado:** 2.5 - 3 horas

---

## 📊 IMPACTO ESPERADO

### Antes
- Gestión de Participantes: 60%
- Gap: 72% → 58%

### Después
- Gestión de Participantes: 95%
- Gap: 58% → 52% (reducción 6%)

---

## ✅ CRITERIOS DE ACEPTACIÓN

1. ✅ Campos de salud agregados a users
2. ✅ Tabla emergency_contacts creada
3. ✅ Tabla work_experiences creada
4. ✅ Modelos creados con relaciones
5. ✅ Vista show.blade.php con 8 tabs
6. ✅ Vista edit.blade.php actualizada
7. ✅ Controller con métodos CRUD
8. ✅ Rutas funcionando
9. ✅ Validaciones implementadas
10. ✅ Diseño responsive

---

**Estado:** ⏳ PENDIENTE  
**Prioridad:** ALTA  
**Asignado a:** Backend Developer  
**Sprint:** Sprint 3-4  
