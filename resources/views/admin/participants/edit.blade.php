@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Participante: {{ $participant->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.participants.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <a href="{{ route('admin.participants.show', $participant) }}" class="btn btn-sm btn-info">
                <i class="fas fa-eye me-1"></i> Ver Participante
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información del Participante</h6>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <h5>Por favor corrige los siguientes errores:</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.participants.update', $participant) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Información Básica -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $participant->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo Electrónico *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $participant->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contraseña (Opcional en edición) -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Nueva Contraseña (dejar vacío para mantener actual)</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation">
                        </div>
                    </div>

                    <!-- Información Personal -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $participant->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="birth_date" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                   id="birth_date" name="birth_date" value="{{ old('birth_date', $participant->birth_date ? $participant->birth_date->format('Y-m-d') : '') }}">
                            @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="city" class="form-label">Ciudad</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" value="{{ old('city', $participant->city) }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="country" class="form-label">País</label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                   id="country" name="country" value="{{ old('country', $participant->country) }}">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="address" class="form-label">Dirección</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address', $participant->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Información Académica -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="academic_level" class="form-label">Nivel Académico</label>
                            <select class="form-select @error('academic_level') is-invalid @enderror" 
                                    id="academic_level" name="academic_level">
                                <option value="">Seleccionar nivel académico</option>
                                <option value="bachiller" {{ old('academic_level', $participant->academic_level) == 'bachiller' ? 'selected' : '' }}>Bachiller</option>
                                <option value="licenciatura" {{ old('academic_level', $participant->academic_level) == 'licenciatura' ? 'selected' : '' }}>Licenciatura</option>
                                <option value="maestria" {{ old('academic_level', $participant->academic_level) == 'maestria' ? 'selected' : '' }}>Maestría</option>
                                <option value="posgrado" {{ old('academic_level', $participant->academic_level) == 'posgrado' ? 'selected' : '' }}>Posgrado</option>
                                <option value="doctorado" {{ old('academic_level', $participant->academic_level) == 'doctorado' ? 'selected' : '' }}>Doctorado</option>
                            </select>
                            @error('academic_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="english_level" class="form-label">Nivel de Inglés</label>
                            <select class="form-select @error('english_level') is-invalid @enderror" 
                                    id="english_level" name="english_level">
                                <option value="">Seleccionar nivel de inglés</option>
                                <option value="basico" {{ old('english_level', $participant->english_level) == 'basico' ? 'selected' : '' }}>Básico</option>
                                <option value="intermedio" {{ old('english_level', $participant->english_level) == 'intermedio' ? 'selected' : '' }}>Intermedio</option>
                                <option value="avanzado" {{ old('english_level', $participant->english_level) == 'avanzado' ? 'selected' : '' }}>Avanzado</option>
                                <option value="nativo" {{ old('english_level', $participant->english_level) == 'nativo' ? 'selected' : '' }}>Nativo</option>
                            </select>
                            @error('english_level')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="nationality" class="form-label">Nacionalidad</label>
                            <input type="text" class="form-control @error('nationality') is-invalid @enderror" 
                                   id="nationality" name="nationality" value="{{ old('nationality', $participant->nationality) }}">
                            @error('nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Asignación de Programa -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="program_id" class="form-label">Asignar a Programa</label>
                            <select class="form-select @error('program_id') is-invalid @enderror" 
                                    id="program_id" name="program_id">
                                <option value="">Seleccionar programa (opcional)</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                        [{{ $program->main_category }}] {{ $program->name }} - {{ $program->country }}
                                    </option>
                                @endforeach
                            </select>
                            @error('program_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Si seleccionas un programa, se creará una nueva aplicación en estado "Pendiente" para este participante.
                            </div>
                        </div>
                    </div>

                    <!-- Programas Actuales -->
                    @if($participant->applications->count() > 0)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Programas Actuales</label>
                            <div class="list-group">
                                @foreach($participant->applications as $application)
                                    <div class="list-group-item">
                                        <div class="d-flex w-100 justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1">
                                                    <span class="badge bg-{{ $application->program->main_category == 'IE' ? 'primary' : 'success' }}">
                                                        {{ $application->program->main_category }}
                                                    </span>
                                                    {{ $application->program->name }}
                                                </h6>
                                                <small class="text-muted">
                                                    {{ $application->program->country }} - 
                                                    Aplicado: {{ $application->submission_date ? $application->submission_date->format('d/m/Y') : 'N/A' }}
                                                </small>
                                            </div>
                                            <span class="badge bg-{{ 
                                                $application->status == 'approved' ? 'success' : 
                                                ($application->status == 'rejected' ? 'danger' : 'warning') 
                                            }}">
                                                {{ ucfirst($application->status) }}
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('admin.participants.index') }}" class="btn btn-secondary me-md-2">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">Información</h6>
            </div>
            <div class="card-body">
                <div class="small text-muted">
                    <p><strong>Participante desde:</strong><br>
                    {{ $participant->created_at->format('d/m/Y H:i') }}</p>
                    
                    <p><strong>Última actualización:</strong><br>
                    {{ $participant->updated_at->format('d/m/Y H:i') }}</p>
                    
                    @if($participant->email_verified_at)
                    <p><strong>Email verificado:</strong><br>
                    <span class="badge bg-success">Sí</span></p>
                    @else
                    <p><strong>Email verificado:</strong><br>
                    <span class="badge bg-warning">No</span></p>
                    @endif
                    
                    <p class="text-info">
                        <i class="fas fa-info-circle me-1"></i>
                        Deja los campos de contraseña vacíos si no deseas cambiarla.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
