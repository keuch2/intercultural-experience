# FORMULARIOS ESPECÍFICOS POR PROGRAMA

Fecha: 22 de Octubre, 2025
Status: DOCUMENTACIÓN Y PLAN

## PROBLEMA IDENTIFICADO

Actualmente todos los participantes usan el mismo formulario genérico.
Cada programa requiere campos específicos diferentes según descripcion_procesos.md.

## CAMPOS ESPECÍFICOS POR PROGRAMA

### 1. WORK & TRAVEL USA

**Campos Base (todos los programas):**
- Datos personales
- Contacto de emergencia
- Salud (declaración jurada)
- Experiencia USA

**Campos Específicos W&T:**
- Universidad (nombre, carrera, año/semestre)
- Modalidad PRESENCIAL (obligatorio)
- Constancia universitaria
- Evaluación de inglés (nivel B1+, máx 3 intentos)
- EF SET ID
- Job Offer (post-selección):
  - Sponsor (AAG, AWA, GH)
  - Host Company
  - Posición laboral
  - Ciudad/Estado
  - Remuneración por hora
  - Housing (Proveído/Asistido/No)
  - Fechas inicio/fin
- Proceso de entrevistas:
  - Sponsor Interview (estado)
  - Job Interview (estado)
- Proceso Visa J1 (15 estados):
  - DS160, DS2019
  - SEVIS, Tasa consular
  - Cita consular
- Expectativas y actitud (cuestionario)

### 2. AU PAIR USA

**Campos Específicos:**
- Experiencia con niños (CRÍTICO):
  - Edades cuidadas
  - Duración de experiencias
  - Tipo de cuidado
  - Bebés (< 2 años)
  - Necesidades especiales
  - Certificaciones (primeros auxilios, CPR)
- Cartas de referencia (mínimo 3)
- Fotos (6+)
- Video de presentación
- Licencia de conducir
- Familia host (post-matching):
  - Ubicación (ciudad/estado)
  - Cantidad niños
  - Edades niños
  - Necesidades especiales
  - Mascotas
  - Horario
  - Fecha inicio
- Evaluación inglés (intermedio)

### 3. TEACHERS PROGRAM

**Campos Específicos:**
- Formación académica (OBLIGATORIO):
  - Título universitario
  - Institución
  - Año graduación
  - Título apostillado
  - Certificado estudios apostillado
  - Número Registro Docente MEC (obligatorio)
  - Especializaciones/Diplomados
- Experiencia docente (CRÍTICO):
  - Instituciones
  - Segmento etario
  - Materias impartidas
  - Carga horaria
  - Años experiencia
  - Tipo institución (pública/privada)
  - Niveles educativos
  - Metodologías
- Cartas referencia profesional (mínimo 2)
- Evaluación inglés (C1 avanzado, obligatorio antes 30.07)
- Posición laboral (post-matching):
  - Distrito escolar
  - Escuela
  - Ciudad/Estado
  - Nivel educativo (Elementary/Middle/High)
  - Materia(s) a enseñar
  - Fechas
  - Salario
- Job Fair (participación)

### 4. INTERN & TRAINEE

**Campos Específicos:**
- Área de especialización
- Universidad/Grado académico
- Experiencia laboral detallada
- Tipo de práctica (Intern/Trainee)
- Training plan requerido
- Host company (post-matching)

### 5. HIGHER EDUCATION

**Campos Específicos:**
- Institución destino
- Programa académico
- Grado (Undergrad/Graduate/PhD)
- GPA/Promedio académico
- Test estandarizados (SAT, TOEFL, GRE)
- Carta de aceptación
- I-20 (en lugar de DS2019)

### 6. WORK & STUDY

**Campos Específicos:**
- Programa de idiomas
- Escuela destino
- Duración curso
- Nivel de inglés inicial
- Tipo de trabajo permitido
- Disponibilidad horaria

### 7. LANGUAGE PROGRAM

**Campos Específicos:**
- Escuela de idiomas
- Nivel actual
- Objetivo de nivel
- Duración curso
- Tipo de alojamiento
- Actividades extracurriculares

## ESTRATEGIA DE IMPLEMENTACIÓN

### FASE 1: FORMULARIOS DINÁMICOS (PRIORIDAD ALTA)

1. Crear tablas específicas por programa:
   - work_travel_data
   - au_pair_data
   - teacher_data
   - intern_trainee_data
   - higher_education_data
   - work_study_data
   - language_program_data

2. Crear vistas parciales por programa:
   - resources/views/admin/participants/forms/work_travel.blade.php
   - resources/views/admin/participants/forms/au_pair.blade.php
   - resources/views/admin/participants/forms/teacher.blade.php
   - etc.

3. Actualizar ParticipantController:
   - create() método que carga formulario según programa
   - store() que guarda en tabla específica

### FASE 2: MÓDULOS COMPLEMENTARIOS

1. Sistema de evaluación de inglés (3 intentos)
2. Job Pool / Job Offers (catálogo)
3. Sistema de matching (familia, escuela, empresa)
4. Proceso de visa (15 estados)
5. Sistema de documentos (versionado, validación)

## PRÓXIMOS PASOS

1. Crear migraciones para tablas específicas
2. Crear modelos Eloquent con relaciones
3. Crear vistas parciales de formularios
4. Actualizar controlador para manejar programa específico
5. Agregar validaciones por programa
