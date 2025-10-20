@extends('layouts.admin')

@section('title', 'Crear Sponsor')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Crear Nuevo Sponsor</h1>
            <p class="text-muted">Registra una nueva organización patrocinadora</p>
        </div>
        <a href="{{ route('admin.sponsors.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información del Sponsor</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sponsors.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="code">Código <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code') }}" 
                                           placeholder="AAG, AWA, GH..."
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="country">País <span class="text-danger">*</span></label>
                            <select class="form-control @error('country') is-invalid @enderror" 
                                    id="country" 
                                    name="country" 
                                    required>
                                <option value="">Seleccionar...</option>
                                <option value="USA" {{ old('country') == 'USA' ? 'selected' : '' }}>Estados Unidos</option>
                                <option value="Canada" {{ old('country') == 'Canada' ? 'selected' : '' }}>Canadá</option>
                                <option value="UK" {{ old('country') == 'UK' ? 'selected' : '' }}>Reino Unido</option>
                                <option value="Australia" {{ old('country') == 'Australia' ? 'selected' : '' }}>Australia</option>
                                <option value="New Zealand" {{ old('country') == 'New Zealand' ? 'selected' : '' }}>Nueva Zelanda</option>
                            </select>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <h6 class="font-weight-bold text-primary mb-3">Información de Contacto</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_email">Email de Contacto</label>
                                    <input type="email" 
                                           class="form-control @error('contact_email') is-invalid @enderror" 
                                           id="contact_email" 
                                           name="contact_email" 
                                           value="{{ old('contact_email') }}">
                                    @error('contact_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_phone">Teléfono de Contacto</label>
                                    <input type="text" 
                                           class="form-control @error('contact_phone') is-invalid @enderror" 
                                           id="contact_phone" 
                                           name="contact_phone" 
                                           value="{{ old('contact_phone') }}">
                                    @error('contact_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="website">Sitio Web</label>
                            <input type="url" 
                                   class="form-control @error('website') is-invalid @enderror" 
                                   id="website" 
                                   name="website" 
                                   value="{{ old('website') }}"
                                   placeholder="https://...">
                            @error('website')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <div class="form-group">
                            <label for="terms_and_conditions">Términos y Condiciones Específicos</label>
                            <textarea class="form-control @error('terms_and_conditions') is-invalid @enderror" 
                                      id="terms_and_conditions" 
                                      name="terms_and_conditions" 
                                      rows="4">{{ old('terms_and_conditions') }}</textarea>
                            @error('terms_and_conditions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Términos específicos que aplican a este sponsor
                            </small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_active">
                                    Sponsor Activo
                                </label>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.sponsors.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Sponsor
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Información
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">¿Qué es un Sponsor?</h6>
                    <p class="small text-muted">
                        Los sponsors son organizaciones que patrocinan programas de intercambio cultural 
                        y ofrecen oportunidades laborales a participantes.
                    </p>

                    <h6 class="font-weight-bold mt-3">Ejemplos de Sponsors:</h6>
                    <ul class="small text-muted">
                        <li><strong>AAG:</strong> Alliance Abroad Group</li>
                        <li><strong>AWA:</strong> American Work Abroad</li>
                        <li><strong>GH:</strong> Global Horizons</li>
                    </ul>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Importante:</strong> El código del sponsor debe ser único.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
