@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Editar Participante</h1>
        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs card-header-tabs" id="editTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">
                            <i class="fas fa-user"></i> General
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="health-tab" data-toggle="tab" href="#health" role="tab">
                            <i class="fas fa-heartbeat"></i> Salud
                        </a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <!-- Tab panes -->
                <div class="tab-content" id="editTabsContent">
                    <!-- Tab 1: Información General -->
                    <div class="tab-pane fade show active" id="general" role="tabpanel">
                        <h5 class="mb-4">Información Personal</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Teléfono</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="birth_date" class="form-label">Fecha de Nacimiento</label>
                                <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                       id="birth_date" name="birth_date" 
                                       value="{{ old('birth_date', $user->birth_date ? $user->birth_date->format('Y-m-d') : '') }}">
                                @error('birth_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nationality" class="form-label">Nacionalidad</label>
                                <input type="text" class="form-control @error('nationality') is-invalid @enderror" 
                                       id="nationality" name="nationality" value="{{ old('nationality', $user->nationality) }}">
                                @error('nationality')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="city" class="form-label">Ciudad</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                       id="city" name="city" value="{{ old('city', $user->city) }}">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="country" class="form-label">País</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                       id="country" name="country" value="{{ old('country', $user->country) }}">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="academic_level" class="form-label">Nivel Académico</label>
                                <select class="form-control @error('academic_level') is-invalid @enderror" 
                                        id="academic_level" name="academic_level">
                                    <option value="">Seleccionar...</option>
                                    <option value="high_school" {{ old('academic_level', $user->academic_level) == 'high_school' ? 'selected' : '' }}>Secundaria</option>
                                    <option value="bachelor" {{ old('academic_level', $user->academic_level) == 'bachelor' ? 'selected' : '' }}>Licenciatura</option>
                                    <option value="master" {{ old('academic_level', $user->academic_level) == 'master' ? 'selected' : '' }}>Maestría</option>
                                    <option value="phd" {{ old('academic_level', $user->academic_level) == 'phd' ? 'selected' : '' }}>Doctorado</option>
                                </select>
                                @error('academic_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="english_level" class="form-label">Nivel de Inglés</label>
                                <select class="form-control @error('english_level') is-invalid @enderror" 
                                        id="english_level" name="english_level">
                                    <option value="">Seleccionar...</option>
                                    <option value="A1" {{ old('english_level', $user->english_level) == 'A1' ? 'selected' : '' }}>A1 - Principiante</option>
                                    <option value="A2" {{ old('english_level', $user->english_level) == 'A2' ? 'selected' : '' }}>A2 - Elemental</option>
                                    <option value="B1" {{ old('english_level', $user->english_level) == 'B1' ? 'selected' : '' }}>B1 - Intermedio</option>
                                    <option value="B1+" {{ old('english_level', $user->english_level) == 'B1+' ? 'selected' : '' }}>B1+ - Intermedio Alto</option>
                                    <option value="B2" {{ old('english_level', $user->english_level) == 'B2' ? 'selected' : '' }}>B2 - Intermedio Avanzado</option>
                                    <option value="C1" {{ old('english_level', $user->english_level) == 'C1' ? 'selected' : '' }}>C1 - Avanzado</option>
                                    <option value="C2" {{ old('english_level', $user->english_level) == 'C2' ? 'selected' : '' }}>C2 - Dominio</option>
                                </select>
                                @error('english_level')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="role" class="form-label">Rol <span class="text-danger">*</span></label>
                                <select class="form-control @error('role') is-invalid @enderror" 
                                        id="role" name="role" required>
                                    <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Participante</option>
                                    <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Administrador</option>
                                    <option value="agent" {{ old('role', $user->role) == 'agent' ? 'selected' : '' }}>Agente</option>
                                    <option value="finance" {{ old('role', $user->role) == 'finance' ? 'selected' : '' }}>Finanzas</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Dirección</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="2">{{ old('address', $user->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="bio" class="form-label">Biografía</label>
                            <textarea class="form-control @error('bio') is-invalid @enderror" 
                                      id="bio" name="bio" rows="3">{{ old('bio', $user->bio) }}</textarea>
                            @error('bio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Cambiar Contraseña</h5>
                        <p class="text-muted small">Dejar en blanco para mantener la contraseña actual</p>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Nueva Contraseña</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" 
                                       id="password_confirmation" name="password_confirmation">
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Información de Salud -->
                    <div class="tab-pane fade" id="health" role="tabpanel">
                        <h5 class="mb-4">Información de Salud</h5>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="blood_type" class="form-label">Tipo de Sangre</label>
                                <select class="form-control @error('blood_type') is-invalid @enderror" 
                                        id="blood_type" name="blood_type">
                                    <option value="">Seleccionar...</option>
                                    <option value="A+" {{ old('blood_type', $user->blood_type) == 'A+' ? 'selected' : '' }}>A+</option>
                                    <option value="A-" {{ old('blood_type', $user->blood_type) == 'A-' ? 'selected' : '' }}>A-</option>
                                    <option value="B+" {{ old('blood_type', $user->blood_type) == 'B+' ? 'selected' : '' }}>B+</option>
                                    <option value="B-" {{ old('blood_type', $user->blood_type) == 'B-' ? 'selected' : '' }}>B-</option>
                                    <option value="AB+" {{ old('blood_type', $user->blood_type) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                    <option value="AB-" {{ old('blood_type', $user->blood_type) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                    <option value="O+" {{ old('blood_type', $user->blood_type) == 'O+' ? 'selected' : '' }}>O+</option>
                                    <option value="O-" {{ old('blood_type', $user->blood_type) == 'O-' ? 'selected' : '' }}>O-</option>
                                </select>
                                @error('blood_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="health_insurance" class="form-label">Seguro Médico</label>
                                <input type="text" class="form-control @error('health_insurance') is-invalid @enderror" 
                                       id="health_insurance" name="health_insurance" 
                                       value="{{ old('health_insurance', $user->health_insurance) }}">
                                @error('health_insurance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="health_insurance_number" class="form-label">Número de Póliza</label>
                            <input type="text" class="form-control @error('health_insurance_number') is-invalid @enderror" 
                                   id="health_insurance_number" name="health_insurance_number" 
                                   value="{{ old('health_insurance_number', $user->health_insurance_number) }}">
                            @error('health_insurance_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="medical_conditions" class="form-label">Condiciones Médicas</label>
                            <textarea class="form-control @error('medical_conditions') is-invalid @enderror" 
                                      id="medical_conditions" name="medical_conditions" rows="3" 
                                      placeholder="Ej: Diabetes, Hipertensión, etc.">{{ old('medical_conditions', $user->medical_conditions) }}</textarea>
                            @error('medical_conditions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Describir cualquier condición médica relevante</small>
                        </div>

                        <div class="mb-3">
                            <label for="allergies" class="form-label">Alergias</label>
                            <textarea class="form-control @error('allergies') is-invalid @enderror" 
                                      id="allergies" name="allergies" rows="2" 
                                      placeholder="Ej: Penicilina, Polen, Maní, etc.">{{ old('allergies', $user->allergies) }}</textarea>
                            @error('allergies')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Listar todas las alergias conocidas</small>
                        </div>

                        <div class="mb-3">
                            <label for="medications" class="form-label">Medicamentos Actuales</label>
                            <textarea class="form-control @error('medications') is-invalid @enderror" 
                                      id="medications" name="medications" rows="2" 
                                      placeholder="Ej: Aspirina 100mg diaria, etc.">{{ old('medications', $user->medications) }}</textarea>
                            @error('medications')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Medicamentos que toma regularmente</small>
                        </div>

                        <hr class="my-4">
                        <h6 class="mb-3">Contacto Médico de Emergencia</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="emergency_medical_contact" class="form-label">Nombre del Médico</label>
                                <input type="text" class="form-control @error('emergency_medical_contact') is-invalid @enderror" 
                                       id="emergency_medical_contact" name="emergency_medical_contact" 
                                       value="{{ old('emergency_medical_contact', $user->emergency_medical_contact) }}">
                                @error('emergency_medical_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="emergency_medical_phone" class="form-label">Teléfono del Médico</label>
                                <input type="text" class="form-control @error('emergency_medical_phone') is-invalid @enderror" 
                                       id="emergency_medical_phone" name="emergency_medical_phone" 
                                       value="{{ old('emergency_medical_phone', $user->emergency_medical_phone) }}">
                                @error('emergency_medical_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-secondary me-2">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </form>

    <!-- Sección de Contactos de Emergencia -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Contactos de Emergencia</h6>
            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addContactModal">
                <i class="fas fa-plus"></i> Agregar Contacto
            </button>
        </div>
        <div class="card-body">
            @if($user->emergencyContacts && $user->emergencyContacts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Relación</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Principal</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->emergencyContacts as $contact)
                                <tr>
                                    <td>{{ $contact->name }}</td>
                                    <td>{{ $contact->relationship }}</td>
                                    <td>{{ $contact->phone }}</td>
                                    <td>{{ $contact->email ?? 'N/A' }}</td>
                                    <td>
                                        @if($contact->is_primary)
                                            <span class="badge badge-primary">Sí</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="alert('Función por implementar')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay contactos de emergencia registrados.
                </div>
            @endif
        </div>
    </div>

    <!-- Sección de Experiencia Laboral -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Experiencia Laboral</h6>
            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addWorkModal">
                <i class="fas fa-plus"></i> Agregar Experiencia
            </button>
        </div>
        <div class="card-body">
            @if($user->workExperiences && $user->workExperiences->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Posición</th>
                                <th>Periodo</th>
                                <th>Actual</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($user->workExperiences as $experience)
                                <tr>
                                    <td>{{ $experience->company }}</td>
                                    <td>{{ $experience->position }}</td>
                                    <td>
                                        {{ $experience->start_date->format('M Y') }} - 
                                        {{ $experience->is_current ? 'Presente' : $experience->end_date->format('M Y') }}
                                    </td>
                                    <td>
                                        @if($experience->is_current)
                                            <span class="badge badge-success">Sí</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger" onclick="alert('Función por implementar')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No hay experiencia laboral registrada.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para agregar contacto de emergencia -->
<div class="modal fade" id="addContactModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Contacto de Emergencia</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Esta funcionalidad se implementará próximamente mediante AJAX.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar experiencia laboral -->
<div class="modal fade" id="addWorkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Experiencia Laboral</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p class="text-muted">Esta funcionalidad se implementará próximamente mediante AJAX.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection
