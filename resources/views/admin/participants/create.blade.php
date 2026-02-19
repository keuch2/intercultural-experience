@extends('layouts.admin')

@section('title', 'Crear Nuevo Participante')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-plus"></i> Crear Nuevo Participante
        </h1>
        <a href="{{ route('admin.participants.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <h5><i class="fas fa-exclamation-triangle"></i> Por favor corrige los siguientes errores:</h5>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        <!-- Wizard Steps Sidebar -->
        <div class="col-lg-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pasos del Registro</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action active" data-step="1">
                            <i class="fas fa-user"></i> 1. Datos Personales
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-step="2">
                            <i class="fas fa-envelope"></i> 2. Contacto y Acceso
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-step="3">
                            <i class="fas fa-map-marker-alt"></i> 3. Dirección
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-step="4">
                            <i class="fas fa-phone-alt"></i> 4. Contactos de Emergencia
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-step="5">
                            <i class="fas fa-graduation-cap"></i> 5. Información Académica
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-step="6">
                            <i class="fas fa-briefcase"></i> 6. Experiencia Laboral
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-step="7">
                            <i class="fas fa-heartbeat"></i> 7. Información de Salud
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-step="8">
                            <i class="fas fa-globe"></i> 8. Programa e Idioma
                        </a>
                        <a href="#" class="list-group-item list-group-item-action" data-step="9">
                            <i class="fas fa-check-circle"></i> 9. Revisión Final
                        </a>
                    </div>
                </div>
            </div>

            <!-- Progress Card -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <h6 class="font-weight-bold text-primary mb-3">Progreso</h6>
                    <div class="progress mb-2">
                        <div id="wizard-progress" class="progress-bar bg-success" role="progressbar" style="width: 11%">11%</div>
                    </div>
                    <small class="text-muted">Paso <span id="current-step-number">1</span> de 9</small>
                </div>
            </div>
        </div>

        <!-- Wizard Form -->
        <div class="col-lg-9">
            <form action="{{ route('admin.participants.store') }}" method="POST" enctype="multipart/form-data" id="participant-wizard-form">
                @csrf

                <!-- STEP 1: Datos Personales -->
                <div class="wizard-step" data-step="1">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-user"></i> Paso 1: Datos Personales
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Nombre Completo *</label>
                                    <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="birth_date" class="form-label">Fecha de Nacimiento *</label>
                                    <input type="date" class="form-control" id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="gender" class="form-label">Género *</label>
                                    <select class="form-control" id="gender" name="gender">
                                        <option value="">Seleccionar...</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Masculino</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Femenino</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="nationality" class="form-label">Nacionalidad *</label>
                                    <input type="text" class="form-control" id="nationality" name="nationality" value="{{ old('nationality') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="profile_photo" class="form-label">Foto de Perfil</label>
                                    <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
                                    <div class="form-text">Formatos: JPG, PNG, GIF. Máximo 2MB</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Contacto y Acceso -->
                <div class="wizard-step" data-step="2" style="display: none;">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-envelope"></i> Paso 2: Contacto y Acceso
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Correo Electrónico *</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Teléfono *</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Contraseña *</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="form-text">Mínimo 8 caracteres</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">Confirmar Contraseña *</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Dirección -->
                <div class="wizard-step" data-step="3" style="display: none;">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-map-marker-alt"></i> Paso 3: Dirección
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="address" class="form-label">Dirección Completa</label>
                                    <textarea class="form-control" id="address" name="address" rows="3">{{ old('address') }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="city" class="form-label">Ciudad *</label>
                                    <input type="text" class="form-control" id="city" name="city" value="{{ old('city') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="state" class="form-label">Estado/Provincia</label>
                                    <input type="text" class="form-control" id="state" name="state" value="{{ old('state') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="postal_code" class="form-label">Código Postal</label>
                                    <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="country" class="form-label">País *</label>
                                    <select class="form-control" id="country" name="country">
                                        <option value="">Seleccionar...</option>
                                        <option value="Paraguay" {{ old('country') == 'Paraguay' ? 'selected' : '' }}>Paraguay</option>
                                        <option value="Argentina" {{ old('country') == 'Argentina' ? 'selected' : '' }}>Argentina</option>
                                        <option value="Brasil" {{ old('country') == 'Brasil' ? 'selected' : '' }}>Brasil</option>
                                        <option value="Estados Unidos" {{ old('country') == 'Estados Unidos' ? 'selected' : '' }}>Estados Unidos</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Contactos de Emergencia -->
                <div class="wizard-step" data-step="4" style="display: none;">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-phone-alt"></i> Paso 4: Contactos de Emergencia
                            </h6>
                        </div>
                        <div class="card-body">
                            <h6 class="text-info mb-3">Contacto Principal</h6>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nombre Completo</label>
                                    <input type="text" class="form-control" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Relación</label>
                                    <input type="text" class="form-control" name="emergency_contact_relationship" value="{{ old('emergency_contact_relationship') }}" placeholder="Ej: Madre, Padre, Hermano...">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="emergency_contact_email" value="{{ old('emergency_contact_email') }}">
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Puedes agregar más contactos después de crear el participante.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: Información Académica -->
                <div class="wizard-step" data-step="5" style="display: none;">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-graduation-cap"></i> Paso 5: Información Académica
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="academic_level" class="form-label">Nivel Académico</label>
                                    <select class="form-control" id="academic_level" name="academic_level">
                                        <option value="">Seleccionar...</option>
                                        <option value="high_school" {{ old('academic_level') == 'high_school' ? 'selected' : '' }}>Secundaria</option>
                                        <option value="bachelor" {{ old('academic_level') == 'bachelor' ? 'selected' : '' }}>Licenciatura</option>
                                        <option value="master" {{ old('academic_level') == 'master' ? 'selected' : '' }}>Maestría</option>
                                        <option value="doctorate" {{ old('academic_level') == 'doctorate' ? 'selected' : '' }}>Doctorado</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="institution" class="form-label">Institución Educativa</label>
                                    <input type="text" class="form-control" id="institution" name="institution" value="{{ old('institution') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="field_of_study" class="form-label">Campo de Estudio</label>
                                    <input type="text" class="form-control" id="field_of_study" name="field_of_study" value="{{ old('field_of_study') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="graduation_year" class="form-label">Año de Graduación</label>
                                    <input type="number" class="form-control" id="graduation_year" name="graduation_year" value="{{ old('graduation_year') }}" min="1950" max="2030">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 6: Experiencia Laboral -->
                <div class="wizard-step" data-step="6" style="display: none;">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-briefcase"></i> Paso 6: Experiencia Laboral
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="work_experience" class="form-label">Experiencia Laboral</label>
                                    <textarea class="form-control" id="work_experience" name="work_experience" rows="4" placeholder="Describe tu experiencia laboral relevante...">{{ old('work_experience') }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="current_occupation" class="form-label">Ocupación Actual</label>
                                    <input type="text" class="form-control" id="current_occupation" name="current_occupation" value="{{ old('current_occupation') }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="years_of_experience" class="form-label">Años de Experiencia</label>
                                    <input type="number" class="form-control" id="years_of_experience" name="years_of_experience" value="{{ old('years_of_experience') }}" min="0" max="50">
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> Puedes agregar experiencias laborales específicas después en el perfil del participante.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 7: Información de Salud -->
                <div class="wizard-step" data-step="7" style="display: none;">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-heartbeat"></i> Paso 7: Información de Salud
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="blood_type" class="form-label">Tipo de Sangre</label>
                                    <select class="form-control" id="blood_type" name="blood_type">
                                        <option value="">Seleccionar...</option>
                                        <option value="A+" {{ old('blood_type') == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('blood_type') == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('blood_type') == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('blood_type') == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="AB+" {{ old('blood_type') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('blood_type') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        <option value="O+" {{ old('blood_type') == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('blood_type') == 'O-' ? 'selected' : '' }}>O-</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="health_insurance" class="form-label">Seguro Médico</label>
                                    <input type="text" class="form-control" id="health_insurance" name="health_insurance" value="{{ old('health_insurance') }}">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="allergies" class="form-label">Alergias</label>
                                    <textarea class="form-control" id="allergies" name="allergies" rows="2" placeholder="Describe cualquier alergia conocida...">{{ old('allergies') }}</textarea>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="medical_conditions" class="form-label">Condiciones Médicas</label>
                                    <textarea class="form-control" id="medical_conditions" name="medical_conditions" rows="2" placeholder="Describe cualquier condición médica relevante...">{{ old('medical_conditions') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 8: Programa e Idioma -->
                <div class="wizard-step" data-step="8" style="display: none;">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-globe"></i> Paso 8: Programa e Idioma
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label for="program_id" class="form-label">Asignar a Programa (Opcional)</label>
                                    <select class="form-control" id="program_id" name="program_id">
                                        <option value="">Sin asignar</option>
                                        @foreach($programs ?? [] as $program)
                                            <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                                {{ $program->name }} ({{ $program->main_category }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="form-text">Puedes asignar al participante más tarde</div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="english_level" class="form-label">Nivel de Inglés</label>
                                    <select class="form-control" id="english_level" name="english_level">
                                        <option value="">Seleccionar...</option>
                                        <option value="beginner" {{ old('english_level') == 'beginner' ? 'selected' : '' }}>Principiante</option>
                                        <option value="intermediate" {{ old('english_level') == 'intermediate' ? 'selected' : '' }}>Intermedio</option>
                                        <option value="advanced" {{ old('english_level') == 'advanced' ? 'selected' : '' }}>Avanzado</option>
                                        <option value="native" {{ old('english_level') == 'native' ? 'selected' : '' }}>Nativo</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="other_languages" class="form-label">Otros Idiomas</label>
                                    <input type="text" class="form-control" id="other_languages" name="other_languages" value="{{ old('other_languages') }}" placeholder="Ej: Francés, Alemán">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 9: Revisión Final -->
                <div class="wizard-step" data-step="9" style="display: none;">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-check-circle"></i> Paso 9: Revisión Final
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-success">
                                <h5><i class="fas fa-check-circle"></i> ¡Casi listo!</h5>
                                <p>Revisa la información antes de crear el participante. Podrás editar todos estos datos posteriormente.</p>
                            </div>

                            <div id="review-summary">
                                <!-- Summary will be populated by JavaScript -->
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="terms_accepted" name="terms_accepted" required>
                                <label class="form-check-label" for="terms_accepted">
                                    Confirmo que la información proporcionada es correcta *
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" id="prev-btn" style="display: none;">
                                <i class="fas fa-arrow-left"></i> Anterior
                            </button>
                            <div></div>
                            <div>
                                <button type="button" class="btn btn-primary" id="next-btn">
                                    Siguiente <i class="fas fa-arrow-right"></i>
                                </button>
                                <button type="submit" class="btn btn-success" id="submit-btn" style="display: none;">
                                    <i class="fas fa-save"></i> Crear Participante
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 9;

    function showStep(step) {
        document.querySelectorAll('.wizard-step').forEach(el => el.style.display = 'none');
        const active = document.querySelector(`.wizard-step[data-step="${step}"]`);
        if (active) active.style.display = '';

        // Update sidebar
        document.querySelectorAll('.list-group-item[data-step]').forEach(el => el.classList.remove('active'));
        const sideItem = document.querySelector(`.list-group-item[data-step="${step}"]`);
        if (sideItem) sideItem.classList.add('active');

        // Update progress
        const progress = Math.round((step / totalSteps) * 100);
        const bar = document.getElementById('wizard-progress');
        if (bar) { bar.style.width = progress + '%'; bar.textContent = progress + '%'; }
        const stepNum = document.getElementById('current-step-number');
        if (stepNum) stepNum.textContent = step;

        // Update buttons
        document.getElementById('prev-btn').style.display = step === 1 ? 'none' : '';
        document.getElementById('next-btn').style.display = step === totalSteps ? 'none' : '';
        document.getElementById('submit-btn').style.display = step === totalSteps ? '' : 'none';

        if (step === totalSteps) populateReview();
        window.scrollTo(0, 0);
    }

    function val(id) { const el = document.getElementById(id); return el ? (el.value || 'N/A') : 'N/A'; }

    function populateReview() {
        let html = '<div class="row">';
        html += '<div class="col-md-6"><h6 class="text-primary">Datos Personales</h6><ul class="list-unstyled">';
        html += `<li><strong>Nombre:</strong> ${val('name')}</li>`;
        html += `<li><strong>Email:</strong> ${val('email')}</li>`;
        html += `<li><strong>Teléfono:</strong> ${val('phone')}</li>`;
        html += `<li><strong>Nacionalidad:</strong> ${val('nationality')}</li>`;
        html += '</ul></div>';
        html += '<div class="col-md-6"><h6 class="text-primary">Dirección</h6><ul class="list-unstyled">';
        html += `<li><strong>Ciudad:</strong> ${val('city')}</li>`;
        html += `<li><strong>País:</strong> ${val('country')}</li>`;
        html += '</ul></div>';
        html += '</div>';
        document.getElementById('review-summary').innerHTML = html;
    }

    document.getElementById('next-btn').addEventListener('click', function() {
        if (currentStep < totalSteps) { currentStep++; showStep(currentStep); }
    });

    document.getElementById('prev-btn').addEventListener('click', function() {
        if (currentStep > 1) { currentStep--; showStep(currentStep); }
    });

    document.querySelectorAll('.list-group-item[data-step]').forEach(function(item) {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            currentStep = parseInt(this.dataset.step);
            showStep(currentStep);
        });
    });

    // Initialize
    showStep(1);
});
</script>
@endpush
