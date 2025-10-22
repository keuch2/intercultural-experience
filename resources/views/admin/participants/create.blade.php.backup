@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Crear Nuevo Participante</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.participants.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
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

                <form action="{{ route('admin.participants.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Foto de Perfil -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="profile_photo" class="form-label">Foto de Perfil</label>
                            <input type="file" class="form-control @error('profile_photo') is-invalid @enderror" 
                                   id="profile_photo" name="profile_photo" accept="image/*">
                            <div class="form-text">Formatos permitidos: JPG, JPEG, PNG, GIF. Tamaño máximo: 2MB</div>
                            @error('profile_photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Información Básica -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo Electrónico *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirmar Contraseña *</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" required>
                        </div>
                    </div>

                    <!-- Información Personal -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="birth_date" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                   id="birth_date" name="birth_date" value="{{ old('birth_date') }}">
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
                                   id="city" name="city" value="{{ old('city') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="country" class="form-label">País</label>
                            <select class="form-select @error('country') is-invalid @enderror" 
                                    id="country" name="country">
                                <option value="">Seleccionar país</option>
                                <option value="Paraguay" {{ old('country') == 'Paraguay' ? 'selected' : '' }}>Paraguay</option>
                                <option value="Argentina" {{ old('country') == 'Argentina' ? 'selected' : '' }}>Argentina</option>
                                <option value="Brasil" {{ old('country') == 'Brasil' ? 'selected' : '' }}>Brasil</option>
                                <option value="Uruguay" {{ old('country') == 'Uruguay' ? 'selected' : '' }}>Uruguay</option>
                                <option value="Chile" {{ old('country') == 'Chile' ? 'selected' : '' }}>Chile</option>
                                <option value="Bolivia" {{ old('country') == 'Bolivia' ? 'selected' : '' }}>Bolivia</option>
                                <option value="Perú" {{ old('country') == 'Perú' ? 'selected' : '' }}>Perú</option>
                                <option value="Colombia" {{ old('country') == 'Colombia' ? 'selected' : '' }}>Colombia</option>
                                <option value="Ecuador" {{ old('country') == 'Ecuador' ? 'selected' : '' }}>Ecuador</option>
                                <option value="Venezuela" {{ old('country') == 'Venezuela' ? 'selected' : '' }}>Venezuela</option>
                                <option value="México" {{ old('country') == 'México' ? 'selected' : '' }}>México</option>
                                <option value="Estados Unidos" {{ old('country') == 'Estados Unidos' ? 'selected' : '' }}>Estados Unidos</option>
                                <option value="Canadá" {{ old('country') == 'Canadá' ? 'selected' : '' }}>Canadá</option>
                                <option value="España" {{ old('country') == 'España' ? 'selected' : '' }}>España</option>
                                <option value="Francia" {{ old('country') == 'Francia' ? 'selected' : '' }}>Francia</option>
                                <option value="Alemania" {{ old('country') == 'Alemania' ? 'selected' : '' }}>Alemania</option>
                                <option value="Italia" {{ old('country') == 'Italia' ? 'selected' : '' }}>Italia</option>
                                <option value="Reino Unido" {{ old('country') == 'Reino Unido' ? 'selected' : '' }}>Reino Unido</option>
                                <option value="Portugal" {{ old('country') == 'Portugal' ? 'selected' : '' }}>Portugal</option>
                                <option value="Países Bajos" {{ old('country') == 'Países Bajos' ? 'selected' : '' }}>Países Bajos</option>
                                <option value="Bélgica" {{ old('country') == 'Bélgica' ? 'selected' : '' }}>Bélgica</option>
                                <option value="Suiza" {{ old('country') == 'Suiza' ? 'selected' : '' }}>Suiza</option>
                                <option value="Austria" {{ old('country') == 'Austria' ? 'selected' : '' }}>Austria</option>
                                <option value="Australia" {{ old('country') == 'Australia' ? 'selected' : '' }}>Australia</option>
                                <option value="Nueva Zelanda" {{ old('country') == 'Nueva Zelanda' ? 'selected' : '' }}>Nueva Zelanda</option>
                                <option value="Japón" {{ old('country') == 'Japón' ? 'selected' : '' }}>Japón</option>
                                <option value="Corea del Sur" {{ old('country') == 'Corea del Sur' ? 'selected' : '' }}>Corea del Sur</option>
                                <option value="China" {{ old('country') == 'China' ? 'selected' : '' }}>China</option>
                                <option value="India" {{ old('country') == 'India' ? 'selected' : '' }}>India</option>
                                <option value="Otro" {{ old('country') == 'Otro' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="address" class="form-label">Dirección</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address') }}</textarea>
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
                                <option value="bachiller" {{ old('academic_level') == 'bachiller' ? 'selected' : '' }}>Bachiller</option>
                                <option value="licenciatura" {{ old('academic_level') == 'licenciatura' ? 'selected' : '' }}>Licenciatura</option>
                                <option value="maestria" {{ old('academic_level') == 'maestria' ? 'selected' : '' }}>Maestría</option>
                                <option value="posgrado" {{ old('academic_level') == 'posgrado' ? 'selected' : '' }}>Posgrado</option>
                                <option value="doctorado" {{ old('academic_level') == 'doctorado' ? 'selected' : '' }}>Doctorado</option>
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
                                <option value="basico" {{ old('english_level') == 'basico' ? 'selected' : '' }}>Básico</option>
                                <option value="intermedio" {{ old('english_level') == 'intermedio' ? 'selected' : '' }}>Intermedio</option>
                                <option value="avanzado" {{ old('english_level') == 'avanzado' ? 'selected' : '' }}>Avanzado</option>
                                <option value="nativo" {{ old('english_level') == 'nativo' ? 'selected' : '' }}>Nativo</option>
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
                                   id="nationality" name="nationality" value="{{ old('nationality') }}">
                            @error('nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Asignación de Programa -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="program_id" class="form-label">Asignar a Programa (Opcional)</label>
                            <select class="form-select @error('program_id') is-invalid @enderror" 
                                    id="program_id" name="program_id">
                                <option value="">Sin asignar</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }} ({{ $program->main_category }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Puedes asignar al participante a un programa específico</div>
                            @error('program_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('admin.participants.index') }}" class="btn btn-secondary me-md-2">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Crear Participante
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
                    <p><strong>Campos Obligatorios:</strong></p>
                    <ul>
                        <li>Nombre completo</li>
                        <li>Correo electrónico</li>
                        <li>Contraseña</li>
                    </ul>
                    
                    <p><strong>Campos de la App Móvil:</strong></p>
                    <ul>
                        <li>Ciudad</li>
                        <li>País</li>
                        <li>Nivel académico</li>
                        <li>Nivel de inglés</li>
                    </ul>
                    
                    <p class="text-info">
                        <i class="fas fa-info-circle me-1"></i>
                        Este participante podrá acceder a la aplicación móvil con sus credenciales.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
