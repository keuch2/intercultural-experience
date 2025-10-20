@extends('layouts.admin')

@section('title', 'Editar Sponsor')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Editar Sponsor</h1>
            <p class="text-muted">{{ $sponsor->name }} ({{ $sponsor->code }})</p>
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
                    <form action="{{ route('admin.sponsors.update', $sponsor->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="name">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $sponsor->name) }}" 
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
                                           value="{{ old('code', $sponsor->code) }}" 
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
                                <option value="USA" {{ old('country', $sponsor->country) == 'USA' ? 'selected' : '' }}>Estados Unidos</option>
                                <option value="Canada" {{ old('country', $sponsor->country) == 'Canada' ? 'selected' : '' }}>Canadá</option>
                                <option value="UK" {{ old('country', $sponsor->country) == 'UK' ? 'selected' : '' }}>Reino Unido</option>
                                <option value="Australia" {{ old('country', $sponsor->country) == 'Australia' ? 'selected' : '' }}>Australia</option>
                                <option value="New Zealand" {{ old('country', $sponsor->country) == 'New Zealand' ? 'selected' : '' }}>Nueva Zelanda</option>
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
                                           value="{{ old('contact_email', $sponsor->contact_email) }}">
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
                                           value="{{ old('contact_phone', $sponsor->contact_phone) }}">
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
                                   value="{{ old('website', $sponsor->website) }}">
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
                                      rows="4">{{ old('terms_and_conditions', $sponsor->terms_and_conditions) }}</textarea>
                            @error('terms_and_conditions')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active" 
                                       value="1" 
                                       {{ old('is_active', $sponsor->is_active) ? 'checked' : '' }}>
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
                                <i class="fas fa-save"></i> Actualizar Sponsor
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
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Creado</small>
                        <p class="mb-0">{{ $sponsor->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Última actualización</small>
                        <p class="mb-0">{{ $sponsor->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h3 class="text-primary">{{ $sponsor->jobOffers()->count() }}</h3>
                        <small class="text-muted">Ofertas Laborales</small>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-trash"></i> Zona de Peligro
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        Eliminar este sponsor es permanente y no se puede deshacer.
                    </p>
                    <form action="{{ route('admin.sponsors.destroy', $sponsor->id) }}" 
                          method="POST"
                          onsubmit="return confirm('¿Estás seguro de eliminar este sponsor? Esta acción no se puede deshacer.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Eliminar Sponsor
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
