{{-- Formulario Específico: Au Pair USA --}}

<div class="card mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="bi bi-heart-fill me-2"></i>
            Datos Específicos - Au Pair USA
        </h5>
    </div>
    <div class="card-body">
        
        {{-- Experiencia con Niños --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-success mb-3">
                <i class="bi bi-people-fill me-2"></i>
                Experiencia con Niños (CRÍTICO)
            </h6>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="ap_childcare_experience" class="form-label">
                        Experiencia Detallada <span class="text-danger">*</span>
                    </label>
                    <textarea class="form-control" id="ap_childcare_experience" 
                              name="au_pair[childcare_experience_detailed]" rows="4" required
                              placeholder="Describe tu experiencia cuidando niños (mínimo 200 horas)...">{{ old('au_pair.childcare_experience_detailed', $auPairData->childcare_experience_detailed ?? '') }}</textarea>
                    <small class="text-muted">Mínimo requerido: 200 horas de experiencia</small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="ap_ages_cared_for" class="form-label">Edades Cuidadas</label>
                    <input type="text" class="form-control" id="ap_ages_cared_for" 
                           name="au_pair[ages_cared_for]" 
                           value="{{ old('au_pair.ages_cared_for', $auPairData->ages_cared_for ?? '') }}"
                           placeholder="0-2, 3-5, 6-12 años">
                </div>
                <div class="col-md-4">
                    <label for="ap_experience_durations" class="form-label">Duraciones</label>
                    <input type="text" class="form-control" id="ap_experience_durations" 
                           name="au_pair[experience_durations]" 
                           value="{{ old('au_pair.experience_durations', $auPairData->experience_durations ?? '') }}"
                           placeholder="6 meses, 1 año, etc.">
                </div>
                <div class="col-md-4">
                    <label for="ap_care_types" class="form-label">Tipos de Cuidado</label>
                    <input type="text" class="form-control" id="ap_care_types" 
                           name="au_pair[care_types]" 
                           value="{{ old('au_pair.care_types', $auPairData->care_types ?? '') }}"
                           placeholder="Niñera, maestra, familiar">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="ap_cared_for_babies" 
                               name="au_pair[cared_for_babies]" value="1"
                               {{ ($auPairData->cared_for_babies ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="ap_cared_for_babies">
                            <strong>He cuidado bebés (< 2 años)</strong>
                        </label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="ap_special_needs_experience" 
                               name="au_pair[special_needs_experience]" value="1"
                               {{ ($auPairData->special_needs_experience ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="ap_special_needs_experience">
                            <strong>Experiencia con necesidades especiales</strong>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row mb-3" id="special_needs_details_container" 
                 style="display: {{ ($auPairData->special_needs_experience ?? false) ? 'block' : 'none' }};">
                <div class="col-md-12">
                    <label for="ap_special_needs_details" class="form-label">Detalles de Necesidades Especiales</label>
                    <textarea class="form-control" id="ap_special_needs_details" 
                              name="au_pair[special_needs_details]" rows="2"
                              placeholder="Describe tu experiencia...">{{ old('au_pair.special_needs_details', $auPairData->special_needs_details ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Certificaciones --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-success mb-3">
                <i class="bi bi-patch-check-fill me-2"></i>
                Certificaciones (OBLIGATORIAS)
            </h6>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="ap_has_first_aid" 
                               name="au_pair[has_first_aid_cert]" value="1"
                               {{ ($auPairData->has_first_aid_cert ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="ap_has_first_aid">
                            <strong>Certificado de Primeros Auxilios</strong>
                        </label>
                    </div>
                    <input type="file" class="form-control mb-2" name="au_pair[first_aid_cert]" 
                           accept=".pdf,.jpg,.jpeg,.png">
                    @if(!empty($auPairData->first_aid_cert_path ?? ''))
                        <small class="text-success">
                            <i class="bi bi-check-circle-fill"></i>
                            <a href="{{ Storage::url($auPairData->first_aid_cert_path) }}" target="_blank">Ver certificado</a>
                        </small>
                    @endif
                    <input type="date" class="form-control" name="au_pair[first_aid_cert_expiry]" 
                           value="{{ old('au_pair.first_aid_cert_expiry', $auPairData->first_aid_cert_expiry ?? '') }}"
                           placeholder="Fecha de vencimiento">
                </div>

                <div class="col-md-6">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="ap_has_cpr" 
                               name="au_pair[has_cpr_cert]" value="1"
                               {{ ($auPairData->has_cpr_cert ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="ap_has_cpr">
                            <strong>Certificado CPR</strong>
                        </label>
                    </div>
                    <input type="file" class="form-control mb-2" name="au_pair[cpr_cert]" 
                           accept=".pdf,.jpg,.jpeg,.png">
                    @if(!empty($auPairData->cpr_cert_path ?? ''))
                        <small class="text-success">
                            <i class="bi bi-check-circle-fill"></i>
                            <a href="{{ Storage::url($auPairData->cpr_cert_path) }}" target="_blank">Ver certificado</a>
                        </small>
                    @endif
                    <input type="date" class="form-control" name="au_pair[cpr_cert_expiry]" 
                           value="{{ old('au_pair.cpr_cert_expiry', $auPairData->cpr_cert_expiry ?? '') }}"
                           placeholder="Fecha de vencimiento">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="ap_other_certifications" class="form-label">Otras Certificaciones</label>
                    <textarea class="form-control" id="ap_other_certifications" 
                              name="au_pair[other_certifications]" rows="2"
                              placeholder="Ej: Educación infantil, natación, música...">{{ old('au_pair.other_certifications', $auPairData->other_certifications ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Fotos y Video --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-success mb-3">
                <i class="bi bi-camera-fill me-2"></i>
                Fotos y Video de Presentación
            </h6>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label class="form-label">
                        Fotos de Perfil <span class="text-danger">*</span>
                        <small class="text-muted">(Mínimo 6 fotos)</small>
                    </label>
                    <input type="file" class="form-control" name="au_pair[photos][]" 
                           accept="image/*" multiple>
                    <small class="text-muted">
                        Selecciona múltiples fotos de alta calidad mostrando tu personalidad
                    </small>
                    
                    @if(!empty($auPairData->photos ?? []))
                        <div class="mt-2">
                            <small class="text-success">
                                <i class="bi bi-check-circle-fill"></i>
                                {{ count($auPairData->photos) }} fotos cargadas
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="ap_presentation_video" class="form-label">
                        Video de Presentación <span class="text-danger">*</span>
                    </label>
                    <input type="file" class="form-control" id="ap_presentation_video" 
                           name="au_pair[presentation_video]" accept="video/*">
                    @if(!empty($auPairData->presentation_video_path ?? ''))
                        <small class="text-success">
                            <i class="bi bi-check-circle-fill"></i>
                            <a href="{{ Storage::url($auPairData->presentation_video_path) }}" target="_blank">Ver video</a>
                        </small>
                    @endif
                </div>

                <div class="col-md-6">
                    <label for="ap_dear_family_letter" class="form-label">
                        Carta "Dear Host Family" <span class="text-danger">*</span>
                    </label>
                    <input type="file" class="form-control" id="ap_dear_family_letter" 
                           name="au_pair[dear_host_family_letter]" accept=".pdf,.doc,.docx">
                    @if(!empty($auPairData->dear_host_family_letter_path ?? ''))
                        <small class="text-success">
                            <i class="bi bi-check-circle-fill"></i>
                            <a href="{{ Storage::url($auPairData->dear_host_family_letter_path) }}" target="_blank">Ver carta</a>
                        </small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Licencia de Conducir --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-success mb-3">
                <i class="bi bi-car-front-fill me-2"></i>
                Licencia de Conducir
            </h6>

            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="ap_has_drivers_license" 
                               name="au_pair[has_drivers_license]" value="1"
                               {{ ($auPairData->has_drivers_license ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="ap_has_drivers_license">
                            <strong>Tengo licencia de conducir válida</strong>
                        </label>
                    </div>
                </div>
            </div>

            <div class="row mb-3" id="license_details_container" 
                 style="display: {{ ($auPairData->has_drivers_license ?? false) ? 'block' : 'none' }};">
                <div class="col-md-4">
                    <label for="ap_license_number" class="form-label">Número de Licencia</label>
                    <input type="text" class="form-control" id="ap_license_number" 
                           name="au_pair[drivers_license_number]" 
                           value="{{ old('au_pair.drivers_license_number', $auPairData->drivers_license_number ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label for="ap_license_country" class="form-label">País</label>
                    <input type="text" class="form-control" id="ap_license_country" 
                           name="au_pair[drivers_license_country]" 
                           value="{{ old('au_pair.drivers_license_country', $auPairData->drivers_license_country ?? 'Paraguay') }}">
                </div>
                <div class="col-md-4">
                    <label for="ap_license_expiry" class="form-label">Vencimiento</label>
                    <input type="date" class="form-control" id="ap_license_expiry" 
                           name="au_pair[drivers_license_expiry]" 
                           value="{{ old('au_pair.drivers_license_expiry', $auPairData->drivers_license_expiry ?? '') }}">
                </div>
            </div>
        </div>

        {{-- Familia Host --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-success mb-3">
                <i class="bi bi-house-heart-fill me-2"></i>
                Familia Host (Post-matching)
            </h6>

            <div class="alert alert-info">
                <i class="bi bi-info-circle-fill me-2"></i>
                Esta sección se completa después del matching con una familia
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="ap_host_family_city" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="ap_host_family_city" 
                           name="au_pair[host_family_city]" 
                           value="{{ old('au_pair.host_family_city', $auPairData->host_family_city ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label for="ap_host_family_state" class="form-label">Estado</label>
                    <input type="text" class="form-control" id="ap_host_family_state" 
                           name="au_pair[host_family_state]" 
                           value="{{ old('au_pair.host_family_state', $auPairData->host_family_state ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label for="ap_number_of_children" class="form-label">Número de Niños</label>
                    <input type="number" class="form-control" id="ap_number_of_children" 
                           name="au_pair[number_of_children]" min="1"
                           value="{{ old('au_pair.number_of_children', $auPairData->number_of_children ?? '') }}">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="ap_start_date_with_family" class="form-label">Fecha Inicio con Familia</label>
                    <input type="date" class="form-control" id="ap_start_date_with_family" 
                           name="au_pair[start_date_with_family]" 
                           value="{{ old('au_pair.start_date_with_family', $auPairData->start_date_with_family ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label for="ap_matched_at" class="form-label">Fecha de Match</label>
                    <input type="date" class="form-control" id="ap_matched_at" 
                           name="au_pair[matched_at]" 
                           value="{{ old('au_pair.matched_at', $auPairData->matched_at ?? '') }}">
                </div>
            </div>
        </div>

        {{-- Estado del Perfil --}}
        <div class="border-bottom pb-3 mb-4">
            <h6 class="text-success mb-3">
                <i class="bi bi-check2-circle me-2"></i>
                Estado del Perfil
            </h6>

            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="ap_profile_active" 
                               name="au_pair[profile_active]" value="1"
                               {{ ($auPairData->profile_active ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="ap_profile_active">
                            <strong>Perfil Activo en Agencia</strong>
                        </label>
                    </div>
                </div>
                <div class="col-md-4">
                    <label for="ap_profile_activated_at" class="form-label">Fecha Activación</label>
                    <input type="date" class="form-control" id="ap_profile_activated_at" 
                           name="au_pair[profile_activated_at]" 
                           value="{{ old('au_pair.profile_activated_at', $auPairData->profile_activated_at ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label for="ap_family_interviews" class="form-label">Entrevistas con Familias</label>
                    <input type="number" class="form-control" id="ap_family_interviews" 
                           name="au_pair[family_interviews]" min="0"
                           value="{{ old('au_pair.family_interviews', $auPairData->family_interviews ?? 0) }}">
                </div>
            </div>
        </div>

        {{-- Etapa Actual --}}
        <div class="pb-3">
            <h6 class="text-success mb-3">
                <i class="bi bi-graph-up-arrow me-2"></i>
                Etapa Actual del Proceso
            </h6>

            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="ap_current_stage" class="form-label">Etapa Específica de Au Pair</label>
                    <select class="form-select" id="ap_current_stage" name="au_pair[current_stage]">
                        @php
                            $apStages = [
                                'registration' => 'Inscripción',
                                'profile_creation' => 'Creación de Perfil',
                                'documentation' => 'Documentación',
                                'profile_review' => 'Revisión de Perfil',
                                'profile_active' => 'Perfil Activo',
                                'matching' => 'Búsqueda de Familia',
                                'family_interviews' => 'Entrevistas con Familias',
                                'match_confirmed' => 'Match Confirmado',
                                'visa_process' => 'Proceso de Visa',
                                'training' => 'Entrenamiento',
                                'travel' => 'Viaje',
                                'in_program' => 'En Programa',
                                'completed' => 'Completado'
                            ];
                        @endphp
                        @foreach($apStages as $value => $label)
                            <option value="{{ $value }}" 
                                {{ ($auPairData->current_stage ?? 'registration') == $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle special needs details
    const specialNeedsCheckbox = document.getElementById('ap_special_needs_experience');
    const specialNeedsContainer = document.getElementById('special_needs_details_container');
    
    if (specialNeedsCheckbox) {
        specialNeedsCheckbox.addEventListener('change', function() {
            specialNeedsContainer.style.display = this.checked ? 'block' : 'none';
        });
    }

    // Toggle license details
    const licenseCheckbox = document.getElementById('ap_has_drivers_license');
    const licenseContainer = document.getElementById('license_details_container');
    
    if (licenseCheckbox) {
        licenseCheckbox.addEventListener('change', function() {
            licenseContainer.style.display = this.checked ? 'block' : 'none';
        });
    }
});
</script>
