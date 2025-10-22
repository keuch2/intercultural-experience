{{-- Formulario Específico: Teacher's Program --}}

<div class="card mb-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="bi bi-book-fill me-2"></i>
            Datos Específicos - Teacher's Program
        </h5>
    </div>
    <div class="card-body">
        
        {{-- Formación Académica --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-info mb-3">
                <i class="bi bi-mortarboard-fill me-2"></i>
                Formación Académica (OBLIGATORIO)
            </h6>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="t_degree_title" class="form-label">
                        Título Universitario <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="t_degree_title" 
                           name="teacher[degree_title]" required
                           value="{{ old('teacher.degree_title', $teacherData->degree_title ?? '') }}"
                           placeholder="Ej: Lic. en Educación, Prof. en Matemáticas">
                </div>
                <div class="col-md-6">
                    <label for="t_educational_institution" class="form-label">
                        Institución Educativa <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="t_educational_institution" 
                           name="teacher[educational_institution]" required
                           value="{{ old('teacher.educational_institution', $teacherData->educational_institution ?? '') }}"
                           placeholder="Universidad Nacional de Asunción">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="t_graduation_year" class="form-label">Año de Graduación</label>
                    <input type="number" class="form-control" id="t_graduation_year" 
                           name="teacher[graduation_year]" min="1980" max="2025"
                           value="{{ old('teacher.graduation_year', $teacherData->graduation_year ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label for="t_degree_certificate" class="form-label">
                        Título Apostillado <span class="text-danger">*</span>
                    </label>
                    <input type="file" class="form-control" id="t_degree_certificate" 
                           name="teacher[degree_certificate]" accept=".pdf">
                    @if(!empty($teacherData->degree_certificate_path ?? ''))
                        <small class="text-success">
                            <i class="bi bi-check-circle-fill"></i>
                            <a href="{{ Storage::url($teacherData->degree_certificate_path) }}" target="_blank">Ver título</a>
                        </small>
                    @endif
                </div>
                <div class="col-md-4">
                    <label for="t_academic_transcript" class="form-label">
                        Certificado de Estudios Apostillado
                    </label>
                    <input type="file" class="form-control" id="t_academic_transcript" 
                           name="teacher[academic_transcript]" accept=".pdf">
                    @if(!empty($teacherData->academic_transcript_path ?? ''))
                        <small class="text-success">
                            <i class="bi bi-check-circle-fill"></i>
                            <a href="{{ Storage::url($teacherData->academic_transcript_path) }}" target="_blank">Ver certificado</a>
                        </small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Registro MEC --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-info mb-3">
                <i class="bi bi-shield-check me-2"></i>
                Registro MEC (OBLIGATORIO)
            </h6>

            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Crítico:</strong> El registro MEC es obligatorio para participar en el programa
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="t_mec_registration_number" class="form-label">
                        Número de Registro MEC <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control" id="t_mec_registration_number" 
                           name="teacher[mec_registration_number]" required
                           value="{{ old('teacher.mec_registration_number', $teacherData->mec_registration_number ?? '') }}"
                           placeholder="MEC-XXXXX">
                </div>
                <div class="col-md-6">
                    <label for="t_mec_registration_date" class="form-label">Fecha de Registro</label>
                    <input type="date" class="form-control" id="t_mec_registration_date" 
                           name="teacher[mec_registration_date]"
                           value="{{ old('teacher.mec_registration_date', $teacherData->mec_registration_date ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="t_mec_certificate" class="form-label">
                        Certificado MEC <span class="text-danger">*</span>
                    </label>
                    <input type="file" class="form-control" id="t_mec_certificate" 
                           name="teacher[mec_certificate]" accept=".pdf">
                    @if(!empty($teacherData->mec_certificate_path ?? ''))
                        <small class="text-success">
                            <i class="bi bi-check-circle-fill"></i>
                            <a href="{{ Storage::url($teacherData->mec_certificate_path) }}" target="_blank">Ver certificado</a>
                        </small>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="t_mec_validated" 
                               name="teacher[mec_validated]" value="1"
                               {{ ($teacherData->mec_validated ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="t_mec_validated">
                            <strong>MEC Validado por IE</strong>
                        </label>
                    </div>
                    <input type="date" class="form-control mt-2" name="teacher[mec_validation_date]"
                           value="{{ old('teacher.mec_validation_date', $teacherData->mec_validation_date ?? '') }}"
                           placeholder="Fecha de validación">
                </div>
            </div>
        </div>

        {{-- Experiencia Docente --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-info mb-3">
                <i class="bi bi-briefcase-fill me-2"></i>
                Experiencia Docente (CRÍTICO)
            </h6>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="t_teaching_experience" class="form-label">
                        Experiencia Docente Detallada <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="t_teaching_experience" 
                              name="teacher[teaching_experience_detailed]" rows="4" required
                              placeholder="Describe tu experiencia docente, instituciones, niveles y materias...">{{ old('teacher.teaching_experience_detailed', $teacherData->teaching_experience_detailed ?? '') }}</textarea>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="t_years_experience" class="form-label">
                        Años de Experiencia <span class="text-danger">*</span>
                    </label>
                    <input type="number" class="form-control" id="t_years_experience" 
                           name="teacher[years_experience]" min="0" required
                           value="{{ old('teacher.years_experience', $teacherData->years_experience ?? '') }}">
                    <small class="text-muted">Mínimo: 2 años</small>
                </div>
                <div class="col-md-4">
                    <label for="t_weekly_hours" class="form-label">Carga Horaria Semanal</label>
                    <input type="number" class="form-control" id="t_weekly_hours" 
                           name="teacher[weekly_hours]" min="0"
                           value="{{ old('teacher.weekly_hours', $teacherData->weekly_hours ?? '') }}"
                           placeholder="20, 30, 40 horas">
                </div>
                <div class="col-md-4">
                    <label for="t_institution_type" class="form-label">Tipo de Institución</label>
                    <select class="form-select" id="t_institution_type" name="teacher[institution_type]">
                        <option value="">Seleccionar...</option>
                        <option value="public" {{ ($teacherData->institution_type ?? '') == 'public' ? 'selected' : '' }}>
                            Pública
                        </option>
                        <option value="private" {{ ($teacherData->institution_type ?? '') == 'private' ? 'selected' : '' }}>
                            Privada
                        </option>
                        <option value="both" {{ ($teacherData->institution_type ?? '') == 'both' ? 'selected' : '' }}>
                            Ambas
                        </option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="t_subjects_taught" class="form-label">Materias Impartidas</label>
                    <textarea class="form-control" id="t_subjects_taught" 
                              name="teacher[subjects_taught]" rows="2"
                              placeholder="Matemáticas, Lengua, Ciencias...">{{ old('teacher.subjects_taught', $teacherData->subjects_taught ?? '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label for="t_educational_levels" class="form-label">Niveles Educativos</label>
                    <textarea class="form-control" id="t_educational_levels" 
                              name="teacher[educational_levels]" rows="2"
                              placeholder="Inicial, Primaria, Secundaria...">{{ old('teacher.educational_levels', $teacherData->educational_levels ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Evaluación de Inglés --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-info mb-3">
                <i class="bi bi-translate me-2"></i>
                Evaluación de Inglés (C1 OBLIGATORIO)
            </h6>

            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Requisito estricto:</strong> Nivel C1 o C2 del CEFR es obligatorio. Plazo: 30 de Julio
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="t_cefr_level" class="form-label">
                        Nivel CEFR <span class="text-danger">*</span>
                    </label>
                    <select class="form-select" id="t_cefr_level" name="teacher[cefr_level]" required>
                        <option value="">Sin evaluar</option>
                        @foreach(['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level)
                            <option value="{{ $level }}" 
                                {{ ($teacherData->cefr_level ?? '') == $level ? 'selected' : '' }}
                                {{ in_array($level, ['A1', 'A2', 'B1', 'B2']) ? 'class=text-danger' : '' }}>
                                {{ $level }} {{ in_array($level, ['C1', 'C2']) ? '✓' : '✗' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="t_efset_id" class="form-label">ID de EF SET</label>
                    <input type="text" class="form-control" id="t_efset_id" 
                           name="teacher[efset_id]"
                           value="{{ old('teacher.efset_id', $teacherData->efset_id ?? '') }}"
                           placeholder="Código de certificado">
                </div>
                <div class="col-md-4">
                    <label for="t_english_attempts" class="form-label">Intentos Realizados</label>
                    <input type="number" class="form-control" id="t_english_attempts" 
                           name="teacher[english_attempts]" min="0" max="3" readonly
                           value="{{ old('teacher.english_attempts', $teacherData->english_attempts ?? 0) }}">
                    <small class="text-muted">Máximo: 3 intentos</small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="t_last_english_evaluation" class="form-label">Última Evaluación</label>
                    <input type="date" class="form-control" id="t_last_english_evaluation" 
                           name="teacher[last_english_evaluation]"
                           value="{{ old('teacher.last_english_evaluation', $teacherData->last_english_evaluation ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label for="t_english_deadline" class="form-label">Plazo Límite (30 Julio)</label>
                    <input type="date" class="form-control" id="t_english_deadline" 
                           name="teacher[english_deadline]"
                           value="{{ old('teacher.english_deadline', $teacherData->english_deadline ?? '2025-07-30') }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="t_english_requirement_met" 
                               name="teacher[english_requirement_met]" value="1"
                               {{ ($teacherData->english_requirement_met ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="t_english_requirement_met">
                            <strong>Requisito de Inglés Cumplido (C1/C2)</strong>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        {{-- Posición Laboral --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-info mb-3">
                <i class="bi bi-building me-2"></i>
                Posición Laboral (Post-Job Fair)
            </h6>

            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill me-2"></i>
                Esta sección se completa después del Job Fair y las entrevistas con distritos escolares
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="t_specific_school" class="form-label">Escuela Asignada</label>
                    <input type="text" class="form-control" id="t_specific_school" 
                           name="teacher[specific_school]"
                           value="{{ old('teacher.specific_school', $teacherData->specific_school ?? '') }}"
                           placeholder="Nombre de la escuela">
                </div>
                <div class="col-md-6">
                    <label for="t_education_level" class="form-label">Nivel Educativo</label>
                    <select class="form-select" id="t_education_level" name="teacher[education_level]">
                        <option value="">No asignado</option>
                        <option value="elementary" {{ ($teacherData->education_level ?? '') == 'elementary' ? 'selected' : '' }}>
                            Elementary School
                        </option>
                        <option value="middle" {{ ($teacherData->education_level ?? '') == 'middle' ? 'selected' : '' }}>
                            Middle School
                        </option>
                        <option value="high_school" {{ ($teacherData->education_level ?? '') == 'high_school' ? 'selected' : '' }}>
                            High School
                        </option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="t_school_city" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="t_school_city" 
                           name="teacher[school_city]"
                           value="{{ old('teacher.school_city', $teacherData->school_city ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label for="t_school_state" class="form-label">Estado</label>
                    <input type="text" class="form-control" id="t_school_state" 
                           name="teacher[school_state]"
                           value="{{ old('teacher.school_state', $teacherData->school_state ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label for="t_annual_salary" class="form-label">Salario Anual (USD)</label>
                    <input type="number" step="0.01" class="form-control" id="t_annual_salary" 
                           name="teacher[annual_salary]"
                           value="{{ old('teacher.annual_salary', $teacherData->annual_salary ?? '') }}"
                           placeholder="35000.00">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="t_teaching_start_date" class="form-label">Fecha Inicio (Agosto)</label>
                    <input type="date" class="form-control" id="t_teaching_start_date" 
                           name="teacher[teaching_start_date]"
                           value="{{ old('teacher.teaching_start_date', $teacherData->teaching_start_date ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label for="t_teaching_end_date" class="form-label">Fecha Fin (Mayo/Junio)</label>
                    <input type="date" class="form-control" id="t_teaching_end_date" 
                           name="teacher[teaching_end_date]"
                           value="{{ old('teacher.teaching_end_date', $teacherData->teaching_end_date ?? '') }}">
                </div>
            </div>
        </div>

        {{-- Job Fair --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-info mb-3">
                <i class="bi bi-calendar-event me-2"></i>
                Job Fair
            </h6>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="t_participated_job_fair" 
                               name="teacher[participated_job_fair]" value="1"
                               {{ ($teacherData->participated_job_fair ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="t_participated_job_fair">
                            <strong>Participó en Job Fair</strong>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="t_job_offer_received_at" class="form-label">Fecha Oferta de Trabajo</label>
                    <input type="date" class="form-control" id="t_job_offer_received_at" 
                           name="teacher[job_offer_received_at]"
                           value="{{ old('teacher.job_offer_received_at', $teacherData->job_offer_received_at ?? '') }}">
                </div>
            </div>
        </div>

        {{-- Etapa Actual --}}
        <div class="pb-3">
            <h6 class="text-info mb-3">
                <i class="bi bi-graph-up-arrow me-2"></i>
                Etapa Actual del Proceso
            </h6>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="t_current_stage" class="form-label">Etapa Específica de Teachers</label>
                    <select class="form-select" id="t_current_stage" name="teacher[current_stage]">
                        @php
                            $tStages = [
                                'registration' => 'Inscripción',
                                'english_evaluation' => 'Evaluación de Inglés',
                                'documentation' => 'Documentación',
                                'mec_validation' => 'Validación MEC',
                                'application_review' => 'Revisión de Aplicación',
                                'job_fair' => 'Job Fair',
                                'district_interviews' => 'Entrevistas con Distritos',
                                'job_offer' => 'Oferta de Trabajo',
                                'position_confirmed' => 'Posición Confirmada',
                                'visa_process' => 'Proceso de Visa',
                                'pre_departure' => 'Pre-Viaje',
                                'in_program' => 'En Programa',
                                'completed' => 'Completado'
                            ];
                        @endphp
                        @foreach($tStages as $value => $label)
                            <option value="{{ $value }}" 
                                {{ ($teacherData->current_stage ?? 'registration') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

    </div>
</div>
