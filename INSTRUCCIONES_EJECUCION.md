# 🚀 INSTRUCCIONES DE EJECUCIÓN

**Sistema:** Intercultural Experience - 7 Programas Completos  
**Fecha:** 22 de Octubre, 2025  
**Status:** Production Ready

---

## 📋 PASOS PARA EJECUTAR EL SISTEMA

### 1. Ejecutar las Migraciones

```bash
cd /opt/homebrew/var/www/intercultural-experience

# Ejecutar todas las migraciones
php artisan migrate
```

**Resultado esperado:**
- ✅ 27 tablas creadas
- ✅ Todas las relaciones establecidas
- ✅ Índices configurados

---

### 2. Ejecutar los Seeders

```bash
# Seeder 1: Higher Education (Programa 5)
php artisan db:seed --class=HigherEducationSeeder

# Seeder 2: Work & Study (Programa 6)
php artisan db:seed --class=WorkStudySeeder

# Seeder 3: Language Program (Programa 7)
php artisan db:seed --class=LanguageProgramSeeder
```

**Nota:** Los seeders de Au Pair, Work & Travel y Teachers ya fueron ejecutados previamente.

**Resultado esperado:**
- ✅ Higher Education: 3 universidades, 2 becas, 6 aplicaciones
- ✅ Work & Study: 3 empleadores, 6 programas, 2 placements
- ✅ Language Program: 8 programas en diferentes estados

---

### 3. Verificar el Sistema

```bash
# Limpiar cache
php artisan optimize:clear

# Regenerar rutas
php artisan route:list | grep admin
```

**Deberías ver 111 rutas registradas:**
- 21 rutas Au Pair
- 18 rutas Work & Travel
- 22 rutas Teachers
- 13 rutas Intern/Trainee
- 13 rutas Higher Education
- 15 rutas Work & Study
- 9 rutas Language Program

---

### 4. Acceder a los Dashboards

Abre tu navegador y accede a:

#### Dashboard Higher Education
```
http://localhost/admin/higher-education/dashboard
```

**Features disponibles:**
- Ver universidades
- Gestionar aplicaciones
- Sistema de becas
- Matching estudiantes-universidades

#### Dashboard Work & Study
```
http://localhost/admin/work-study/dashboard
```

**Features disponibles:**
- Ver programas
- Gestionar empleadores
- Colocaciones (placements)
- Matching estudiantes-empleadores

#### Dashboard Language Program
```
http://localhost/admin/language-program/dashboard
```

**Features disponibles:**
- Ver programas de idiomas
- Tracking de progreso
- Estadísticas
- Reporte de escuelas

---

## 🎯 RUTAS PRINCIPALES POR MÓDULO

### Higher Education
```
GET  /admin/higher-education/dashboard
GET  /admin/higher-education/universities
GET  /admin/higher-education/universities/{id}
GET  /admin/higher-education/applications
GET  /admin/higher-education/applications/{id}
POST /admin/higher-education/applications/{id}/status
POST /admin/higher-education/applications/{id}/i20
GET  /admin/higher-education/scholarships
GET  /admin/higher-education/scholarship-applications
POST /admin/higher-education/scholarship-applications/{id}/award
GET  /admin/higher-education/matching
```

### Work & Study
```
GET  /admin/work-study/dashboard
GET  /admin/work-study/programs
GET  /admin/work-study/programs/{id}
POST /admin/work-study/programs/{id}/status
POST /admin/work-study/programs/{id}/i20
GET  /admin/work-study/employers
GET  /admin/work-study/employers/{id}
POST /admin/work-study/employers/{id}/verify
GET  /admin/work-study/placements
POST /admin/work-study/placements/{id}/activate
POST /admin/work-study/placements/{id}/complete
GET  /admin/work-study/matching
```

### Language Program
```
GET  /admin/language-program/dashboard
GET  /admin/language-program/programs
GET  /admin/language-program/programs/{id}
POST /admin/language-program/programs/{id}/status
POST /admin/language-program/programs/{id}/progress
POST /admin/language-program/programs/{id}/certificate
GET  /admin/language-program/statistics
GET  /admin/language-program/schools
```

---

## 🔍 VERIFICACIÓN DE DATOS

### Verificar Higher Education
```sql
-- Universidades creadas
SELECT university_name, city, state, type, is_partner_university 
FROM universities;

-- Aplicaciones
SELECT a.application_number, u.name as student, uni.university_name, a.degree_level, a.application_status
FROM higher_education_applications a
JOIN users u ON a.user_id = u.id
JOIN universities uni ON a.university_id = uni.id;

-- Becas
SELECT scholarship_name, university_id, award_type, awards_remaining
FROM scholarships;
```

### Verificar Work & Study
```sql
-- Empleadores
SELECT employer_name, city, business_type, is_verified, students_current
FROM work_study_employers;

-- Programas
SELECT ws.program_number, u.name as student, ws.school_name, ws.status, ws.weeks_duration
FROM work_study_programs ws
JOIN users u ON ws.user_id = u.id;

-- Placements activos
SELECT p.placement_number, u.name as student, e.employer_name, p.job_title, p.status
FROM work_study_placements p
JOIN users u ON p.user_id = u.id
JOIN work_study_employers e ON p.employer_id = e.id
WHERE p.status = 'active';
```

### Verificar Language Program
```sql
-- Programas por idioma
SELECT language, COUNT(*) as total, AVG(weeks_duration) as avg_weeks
FROM language_programs
GROUP BY language;

-- Programas activos
SELECT lp.program_number, u.name as student, lp.school_name, lp.language, lp.program_type, lp.status
FROM language_programs lp
JOIN users u ON lp.user_id = u.id
WHERE lp.status IN ('active', 'enrolled');

-- Escuelas más populares
SELECT school_name, school_city, COUNT(*) as students
FROM language_programs
GROUP BY school_name, school_city
ORDER BY students DESC;
```

---

## 🧪 TESTING MANUAL

### Test 1: Higher Education Application Flow
1. Ir a `/admin/higher-education/applications`
2. Seleccionar una aplicación "submitted"
3. Click en Ver/Editar
4. Cambiar estado a "accepted"
5. Verificar que aparece fecha de aceptación

### Test 2: Work & Study Placement Activation
1. Ir a `/admin/work-study/placements`
2. Seleccionar placement "approved"
3. Click en Activate
4. Verificar que:
   - Status cambió a "active"
   - Employer student_count incrementó
   - Activation_date fue establecida

### Test 3: Language Program Progress Update
1. Ir a `/admin/language-program/programs`
2. Seleccionar programa "active"
3. Actualizar completed_weeks
4. Agregar teacher feedback
5. Verificar que progress se guardó

### Test 4: Matching Systems
1. **Higher Ed Matching:** `/admin/higher-education/matching`
   - Ver top 5 scholarships por aplicante
   - Score mínimo 60%

2. **Work & Study Matching:** `/admin/work-study/matching`
   - Ver top 5 employers por estudiante
   - Score mínimo 50%

---

## 📊 ESTADÍSTICAS ESPERADAS

### Higher Education Dashboard
- Total aplicaciones: 6
- Aceptadas: 2
- Universidades: 3
- Becas activas: 2

### Work & Study Dashboard
- Total programas: 6
- Activos: 2
- Empleadores: 3
- Placements activos: 2

### Language Program Dashboard
- Total programas: 8
- Activos: 3
- Enrolled: 2
- Completados: 0

---

## 🐛 TROUBLESHOOTING

### Error: "Class not found"
```bash
composer dump-autoload
php artisan config:clear
```

### Error: "Table doesn't exist"
```bash
php artisan migrate:fresh
php artisan db:seed --class=HigherEducationSeeder
php artisan db:seed --class=WorkStudySeeder
php artisan db:seed --class=LanguageProgramSeeder
```

### Error: "Route not found"
```bash
php artisan route:clear
php artisan route:cache
```

### Error: "View not found"
```bash
php artisan view:clear
```

---

## ✅ CHECKLIST DE VERIFICACIÓN

- [ ] Migraciones ejecutadas sin errores
- [ ] Seeders ejecutados correctamente
- [ ] 111 rutas registradas
- [ ] Dashboards accesibles
- [ ] Datos de prueba visibles
- [ ] Filtros funcionando
- [ ] Acciones (approve, activate, etc) funcionan
- [ ] Matching algorithms retornan resultados
- [ ] Estadísticas calculadas correctamente
- [ ] Gráficos Chart.js renderizados

---

## 🎉 SISTEMA LISTO

Una vez completados todos los pasos, el sistema estará 100% funcional con:

✅ **7 programas completos**  
✅ **111 endpoints activos**  
✅ **27 tablas pobladas**  
✅ **100+ registros de prueba**  
✅ **6 algoritmos de matching**  
✅ **10 dashboards con estadísticas**  

**¡El sistema está listo para ser usado y probado!**

---

**Última actualización:** 22 Oct 2025 - 2:00 AM  
**Status:** ✅ Ready to Execute
