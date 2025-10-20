@extends('layouts.agent')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-plus me-2"></i>Crear Nuevo Participante
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('agent.participants.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-edit me-2"></i>Datos del Participante
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('agent.participants.store') }}">
                    @csrf

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Se generará automáticamente una contraseña temporal que deberás compartir con el participante.
                    </div>

                    <!-- Datos Personales -->
                    <h5 class="mb-3"><i class="fas fa-user me-2"></i>Datos Personales</h5>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone') }}" 
                                   placeholder="+1234567890" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="birth_date" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                   id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required>
                            @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <h5 class="mb-3 mt-4"><i class="fas fa-map-marker-alt me-2"></i>Ubicación</h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="country" class="form-label">País <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                   id="country" name="country" value="{{ old('country') }}" required>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="city" class="form-label">Ciudad</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" value="{{ old('city') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nationality" class="form-label">Nacionalidad <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nationality') is-invalid @enderror" 
                                   id="nationality" name="nationality" value="{{ old('nationality') }}" required>
                            @error('nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="address" class="form-label">Dirección</label>
                            <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                   id="address" name="address" value="{{ old('address') }}">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Información Académica -->
                    <h5 class="mb-3 mt-4"><i class="fas fa-graduation-cap me-2"></i>Información Académica</h5>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="academic_level" class="form-label">Nivel Académico</label>
                            <select class="form-select @error('academic_level') is-invalid @enderror" 
                                    id="academic_level" name="academic_level">
                                <option value="">Seleccionar...</option>
                                <option value="Secundaria" {{ old('academic_level') == 'Secundaria' ? 'selected' : '' }}>Secundaria</option>
                                <option value="Universidad en curso" {{ old('academic_level') == 'Universidad en curso' ? 'selected' : '' }}>Universidad en curso</option>
                                <option value="Universitario" {{ old('academic_level') == 'Universitario' ? 'selected' : '' }}>Universitario</option>
                                <option value="Postgrado" {{ old('academic_level') == 'Postgrado' ? 'selected' : '' }}>Postgrado</option>
                            </select>
                            @error('academic_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="english_level" class="form-label">Nivel de Inglés</label>
                            <select class="form-select @error('english_level') is-invalid @enderror" 
                                    id="english_level" name="english_level">
                                <option value="">Seleccionar...</option>
                                <option value="Básico" {{ old('english_level') == 'Básico' ? 'selected' : '' }}>Básico</option>
                                <option value="Intermedio" {{ old('english_level') == 'Intermedio' ? 'selected' : '' }}>Intermedio</option>
                                <option value="Avanzado" {{ old('english_level') == 'Avanzado' ? 'selected' : '' }}>Avanzado</option>
                                <option value="Nativo" {{ old('english_level') == 'Nativo' ? 'selected' : '' }}>Nativo</option>
                            </select>
                            @error('english_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Asignación de Programa (Opcional) -->
                    <h5 class="mb-3 mt-4"><i class="fas fa-graduation-cap me-2"></i>Asignación de Programa (Opcional)</h5>
                    
                    <div class="alert alert-secondary">
                        <i class="fas fa-info-circle me-2"></i>
                        Puedes asignar un programa ahora o hacerlo más tarde desde el perfil del participante.
                    </div>

                    <div class="mb-3">
                        <label for="program_id" class="form-label">Programa</label>
                        <select class="form-select @error('program_id') is-invalid @enderror" 
                                id="program_id" name="program_id">
                            <option value="">No asignar programa ahora</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }} ({{ $program->available_slots }} cupos disponibles)
                                </option>
                            @endforeach
                        </select>
                        @error('program_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="{{ route('agent.participants.index') }}" class="btn btn-secondary me-md-2">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Crear Participante
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-3">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-info-circle me-2"></i>Información Importante
                </h6>
            </div>
            <div class="card-body">
                <h6><i class="fas fa-check-circle text-success me-2"></i>Campos Obligatorios</h6>
                <ul class="small">
                    <li>Nombre completo</li>
                    <li>Email</li>
                    <li>Teléfono</li>
                    <li>Fecha de nacimiento</li>
                    <li>País</li>
                    <li>Nacionalidad</li>
                </ul>

                <hr>

                <h6><i class="fas fa-key text-warning me-2"></i>Contraseña Temporal</h6>
                <p class="small">
                    Al crear el participante, se generará automáticamente una contraseña segura que deberás 
                    compartir con él/ella para su primer acceso.
                </p>

                <hr>

                <h6><i class="fas fa-envelope text-primary me-2"></i>Notificación</h6>
                <p class="small">
                    El participante recibirá un email de bienvenida con sus credenciales de acceso.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
