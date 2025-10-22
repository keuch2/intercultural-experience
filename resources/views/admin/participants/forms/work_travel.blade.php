{{-- Formulario Específico: Work & Travel USA --}}

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-briefcase-fill me-2"></i>
            Datos Específicos - Work & Travel USA
        </h5>
    </div>
    <div class="card-body">
        
        {{-- Datos Académicos --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-primary mb-3">
                <i class="bi bi-mortarboard-fill me-2"></i>
                Datos Académicos (OBLIGATORIOS)
            </h6>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="wt_university" class="form-label">Universidad <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="wt_university" 
                           name="work_travel[university]" 
                           value="{{ old('work_travel.university', $workTravelData->university ?? '') }}" required>
                </div>
                <div class="col-md-6">
                    <label for="wt_career" class="form-label">Carrera <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="wt_career" 
                           name="work_travel[career]" 
                           value="{{ old('work_travel.career', $workTravelData->career ?? '') }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="wt_year_semester" class="form-label">Año/Semestre</label>
                    <input type="text" class="form-control" id="wt_year_semester" 
                           name="work_travel[year_semester]" 
                           value="{{ old('work_travel.year_semester', $workTravelData->year_semester ?? '') }}"
                           placeholder="Ej: 4to año, 2do semestre">
                </div>
                <div class="col-md-6">
                    <label for="wt_modality" class="form-label">Modalidad <span class="text-danger">*</span></label>
                    <select class="form-select" id="wt_modality" name="work_travel[modality]" required>
                        <option value="presencial" {{ ($workTravelData->modality ?? '') == 'presencial' ? 'selected' : '' }}>
                            Presencial
                        </option>
                        <option value="virtual" {{ ($workTravelData->modality ?? '') == 'virtual' ? 'selected' : '' }}>
                            Virtual
                        </option>
                        <option value="mixto" {{ ($workTravelData->modality ?? '') == 'mixto' ? 'selected' : '' }}>
                            Mixto
                        </option>
                    </select>
                    <small class="text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>
                        Solo modalidad PRESENCIAL es elegible
                    </small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="wt_university_certificate" class="form-label">Constancia Universitaria</label>
                    <input type="file" class="form-control" id="wt_university_certificate" 
                           name="work_travel[university_certificate]" accept=".pdf,.jpg,.jpeg,.png">
                    @if(!empty($workTravelData->university_certificate_path ?? ''))
                        <small class="text-success">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            <a href="{{ Storage::url($workTravelData->university_certificate_path) }}" target="_blank">
                                Ver documento actual
                            </a>
                        </small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Evaluación de Inglés --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-primary mb-3">
                <i class="bi bi-translate me-2"></i>
                Evaluación de Inglés
            </h6>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="wt_english_level_self" class="form-label">Nivel Auto-evaluado</label>
                    <select class="form-select" id="wt_english_level_self" name="work_travel[english_level_self]">
                        <option value="">Seleccionar...</option>
                        <option value="basic" {{ ($workTravelData->english_level_self ?? '') == 'basic' ? 'selected' : '' }}>
                            Básico
                        </option>
                        <option value="intermediate" {{ ($workTravelData->english_level_self ?? '') == 'intermediate' ? 'selected' : '' }}>
                            Intermedio
                        </option>
                        <option value="advanced" {{ ($workTravelData->english_level_self ?? '') == 'advanced' ? 'selected' : '' }}>
                            Avanzado
                        </option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="wt_cefr_level" class="form-label">Nivel CEFR (EF SET)</label>
                    <select class="form-select" id="wt_cefr_level" name="work_travel[cefr_level]">
                        <option value="">Sin evaluar</option>
                        @foreach(['A1', 'A2', 'B1', 'B2', 'C1', 'C2'] as $level)
                            <option value="{{ $level }}" 
                                {{ ($workTravelData->cefr_level ?? '') == $level ? 'selected' : '' }}>
                                {{ $level }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-info">Mínimo requerido: B1</small>
                </div>
                <div class="col-md-4">
                    <label for="wt_efset_id" class="form-label">ID de EF SET</label>
                    <input type="text" class="form-control" id="wt_efset_id" 
                           name="work_travel[efset_id]" 
                           value="{{ old('work_travel.efset_id', $workTravelData->efset_id ?? '') }}"
                           placeholder="Código de certificado">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="wt_english_attempts" class="form-label">Intentos Realizados</label>
                    <input type="number" class="form-control" id="wt_english_attempts" 
                           name="work_travel[english_attempts]" min="0" max="3"
                           value="{{ old('work_travel.english_attempts', $workTravelData->english_attempts ?? 0) }}"
                           readonly>
                    <small class="text-muted">Máximo: 3 intentos</small>
                </div>
                <div class="col-md-6">
                    <label for="wt_last_english_evaluation" class="form-label">Última Evaluación</label>
                    <input type="date" class="form-control" id="wt_last_english_evaluation" 
                           name="work_travel[last_english_evaluation]" 
                           value="{{ old('work_travel.last_english_evaluation', $workTravelData->last_english_evaluation ?? '') }}">
                </div>
            </div>
        </div>

        {{-- Job Offer --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-primary mb-3">
                <i class="bi bi-building me-2"></i>
                Job Offer (Post-selección)
            </h6>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="wt_sponsor" class="form-label">Sponsor</label>
                    <select class="form-select" id="wt_sponsor" name="work_travel[sponsor]">
                        <option value="">Sin asignar</option>
                        @foreach(['AAG', 'AWA', 'GH', 'OTHER'] as $sponsor)
                            <option value="{{ $sponsor }}" 
                                {{ ($workTravelData->sponsor ?? '') == $sponsor ? 'selected' : '' }}>
                                {{ $sponsor }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-8">
                    <label for="wt_host_company_name" class="form-label">Empresa Host</label>
                    <input type="text" class="form-control" id="wt_host_company_name" 
                           name="work_travel[host_company_name]" 
                           value="{{ old('work_travel.host_company_name', $workTravelData->host_company_name ?? '') }}"
                           placeholder="Nombre de la empresa">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="wt_job_position" class="form-label">Posición</label>
                    <input type="text" class="form-control" id="wt_job_position" 
                           name="work_travel[job_position]" 
                           value="{{ old('work_travel.job_position', $workTravelData->job_position ?? '') }}"
                           placeholder="Ej: Camarero, Recepcionista">
                </div>
                <div class="col-md-6">
                    <label for="wt_hourly_rate" class="form-label">Pago por Hora (USD)</label>
                    <input type="number" step="0.01" class="form-control" id="wt_hourly_rate" 
                           name="work_travel[hourly_rate]" 
                           value="{{ old('work_travel.hourly_rate', $workTravelData->hourly_rate ?? '') }}"
                           placeholder="15.00">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="wt_job_city" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="wt_job_city" 
                           name="work_travel[job_city]" 
                           value="{{ old('work_travel.job_city', $workTravelData->job_city ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label for="wt_job_state" class="form-label">Estado</label>
                    <input type="text" class="form-control" id="wt_job_state" 
                           name="work_travel[job_state]" 
                           value="{{ old('work_travel.job_state', $workTravelData->job_state ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label for="wt_housing" class="form-label">Alojamiento</label>
                    <select class="form-select" id="wt_housing" name="work_travel[housing]">
                        <option value="">No especificado</option>
                        <option value="provided" {{ ($workTravelData->housing ?? '') == 'provided' ? 'selected' : '' }}>
                            Proporcionado
                        </option>
                        <option value="assisted" {{ ($workTravelData->housing ?? '') == 'assisted' ? 'selected' : '' }}>
                            Asistido
                        </option>
                        <option value="not_provided" {{ ($workTravelData->housing ?? '') == 'not_provided' ? 'selected' : '' }}>
                            No proporcionado
                        </option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="wt_program_start_date" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="wt_program_start_date" 
                           name="work_travel[program_start_date]" 
                           value="{{ old('work_travel.program_start_date', $workTravelData->program_start_date ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label for="wt_program_end_date" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="wt_program_end_date" 
                           name="work_travel[program_end_date]" 
                           value="{{ old('work_travel.program_end_date', $workTravelData->program_end_date ?? '') }}">
                </div>
            </div>
        </div>

        {{-- Entrevistas --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-primary mb-3">
                <i class="bi bi-chat-dots-fill me-2"></i>
                Proceso de Entrevistas
            </h6>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="wt_sponsor_interview_status" class="form-label">Estado Entrevista Sponsor</label>
                    <select class="form-select" id="wt_sponsor_interview_status" 
                            name="work_travel[sponsor_interview_status]">
                        @foreach(['pending' => 'Pendiente', 'scheduled' => 'Programada', 'approved' => 'Aprobada', 'rejected' => 'Rechazada'] as $value => $label)
                            <option value="{{ $value }}" 
                                {{ ($workTravelData->sponsor_interview_status ?? 'pending') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="wt_sponsor_interview_date" class="form-label">Fecha/Hora Entrevista</label>
                    <input type="datetime-local" class="form-control" id="wt_sponsor_interview_date" 
                           name="work_travel[sponsor_interview_date]" 
                           value="{{ old('work_travel.sponsor_interview_date', $workTravelData->sponsor_interview_date ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="wt_job_interview_status" class="form-label">Estado Entrevista Trabajo</label>
                    <select class="form-select" id="wt_job_interview_status" 
                            name="work_travel[job_interview_status]">
                        @foreach(['pending' => 'Pendiente', 'scheduled' => 'Programada', 'approved' => 'Aprobada', 'rejected' => 'Rechazada'] as $value => $label)
                            <option value="{{ $value }}" 
                                {{ ($workTravelData->job_interview_status ?? 'pending') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="wt_job_interview_date" class="form-label">Fecha/Hora Entrevista</label>
                    <input type="datetime-local" class="form-control" id="wt_job_interview_date" 
                           name="work_travel[job_interview_date]" 
                           value="{{ old('work_travel.job_interview_date', $workTravelData->job_interview_date ?? '') }}">
                </div>
            </div>
        </div>

        {{-- Etapa Actual Específica --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-primary mb-3">
                <i class="bi bi-graph-up-arrow me-2"></i>
                Etapa Actual del Proceso
            </h6>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="wt_current_stage" class="form-label">Etapa Específica de Work & Travel</label>
                    <select class="form-select" id="wt_current_stage" name="work_travel[current_stage]">
                        @php
                            $wtStages = [
                                'registration' => 'Inscripción',
                                'english_evaluation' => 'Evaluación de Inglés',
                                'documentation' => 'Documentación',
                                'job_selection' => 'Selección de Trabajo',
                                'sponsor_interview' => 'Entrevista Sponsor',
                                'job_interview' => 'Entrevista Trabajo',
                                'job_confirmed' => 'Trabajo Confirmado',
                                'visa_process' => 'Proceso de Visa',
                                'pre_departure' => 'Pre-Viaje',
                                'in_program' => 'En Programa',
                                'completed' => 'Completado'
                            ];
                        @endphp
                        @foreach($wtStages as $value => $label)
                            <option value="{{ $value }}" 
                                {{ ($workTravelData->current_stage ?? 'registration') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Expectativas --}}
        <div class="pb-3">
            <h6 class="text-primary mb-3">
                <i class="bi bi-star-fill me-2"></i>
                Expectativas y Actitud
            </h6>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="wt_program_expectations" class="form-label">Expectativas del Programa</label>
                    <textarea class="form-control" id="wt_program_expectations" 
                              name="work_travel[program_expectations]" rows="3"
                              placeholder="¿Qué espera lograr con este programa?">{{ old('work_travel.program_expectations', $workTravelData->program_expectations ?? '') }}</textarea>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="wt_tolerant_to_demands" 
                               name="work_travel[tolerant_to_demands]" value="1"
                               {{ ($workTravelData->tolerant_to_demands ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="wt_tolerant_to_demands">
                            Tolerante a demandas laborales
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="wt_flexible_to_changes" 
                               name="work_travel[flexible_to_changes]" value="1"
                               {{ ($workTravelData->flexible_to_changes ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="wt_flexible_to_changes">
                            Flexible a cambios
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="wt_willing_share_accommodation" 
                               name="work_travel[willing_share_accommodation]" value="1"
                               {{ ($workTravelData->willing_share_accommodation ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="wt_willing_share_accommodation">
                            Dispuesto a compartir alojamiento
                        </label>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning" role="alert">
                <strong>⚠️ Advertencia:</strong>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="wt_intention_to_stay" 
                           name="work_travel[intention_to_stay]" value="1"
                           {{ ($workTravelData->intention_to_stay ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="wt_intention_to_stay">
                        <strong>Tiene intención de quedarse en USA</strong> (descalifica automáticamente)
                    </label>
                </div>
            </div>
        </div>

    </div>
</div>
