@extends('layouts.admin')

@section('title', 'Configuración General')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-cogs"></i> Configuración General del Sistema
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-list"></i> Todas las Configuraciones
                            </a>
                            <a href="{{ route('admin.settings.whatsapp') }}" class="btn btn-success">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> 
            <strong>Error:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Formulario de Configuración -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit"></i> Configuraciones de la Aplicación
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.general.update') }}" method="POST">
                        @csrf

                        <!-- Información de la Aplicación -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-mobile-alt"></i> Información de la Aplicación
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="app_name" class="form-label">
                                    <i class="fas fa-tag"></i> Nombre de la Aplicación
                                </label>
                                <input type="text" 
                                       class="form-control @error('app_name') is-invalid @enderror" 
                                       id="app_name" 
                                       name="app_name" 
                                       value="{{ old('app_name', $generalSettings->where('key', 'app_name')->first()?->value) }}"
                                       required>
                                @error('app_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="app_version" class="form-label">
                                    <i class="fas fa-code-branch"></i> Versión de la Aplicación
                                </label>
                                <input type="text" 
                                       class="form-control @error('app_version') is-invalid @enderror" 
                                       id="app_version" 
                                       name="app_version" 
                                       value="{{ old('app_version', $generalSettings->where('key', 'app_version')->first()?->value) }}"
                                       placeholder="1.0.0"
                                       required>
                                @error('app_version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="support_email" class="form-label">
                                    <i class="fas fa-envelope"></i> Email de Soporte
                                </label>
                                <input type="email" 
                                       class="form-control @error('support_email') is-invalid @enderror" 
                                       id="support_email" 
                                       name="support_email" 
                                       value="{{ old('support_email', $generalSettings->where('key', 'support_email')->first()?->value) }}"
                                       required>
                                <div class="form-text">
                                    Este email aparecerá en la aplicación móvil para contacto de soporte.
                                </div>
                                @error('support_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Información de Contacto -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h6 class="text-primary border-bottom pb-2 mb-3">
                                    <i class="fas fa-building"></i> Información de la Empresa
                                </h6>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="company_phone" class="form-label">
                                    <i class="fas fa-phone"></i> Teléfono Principal
                                </label>
                                <input type="text" 
                                       class="form-control @error('company_phone') is-invalid @enderror" 
                                       id="company_phone" 
                                       name="company_phone" 
                                       value="{{ old('company_phone', $contactSettings->where('key', 'company_phone')->first()?->value) }}"
                                       placeholder="+595 21 123 4567"
                                       required>
                                @error('company_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="company_address" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Dirección de la Empresa
                                </label>
                                <input type="text" 
                                       class="form-control @error('company_address') is-invalid @enderror" 
                                       id="company_address" 
                                       name="company_address" 
                                       value="{{ old('company_address', $contactSettings->where('key', 'company_address')->first()?->value) }}"
                                       placeholder="Asunción, Paraguay"
                                       required>
                                @error('company_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Configuración
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                                <i class="fas fa-undo"></i> Restablecer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Panel de Información -->
        <div class="col-lg-4">
            <!-- Estado Actual -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Estado Actual
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Aplicación:</strong><br>
                        <code>{{ $generalSettings->where('key', 'app_name')->first()?->value ?? 'No configurado' }}</code>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Versión:</strong><br>
                        <span class="badge bg-info">
                            {{ $generalSettings->where('key', 'app_version')->first()?->value ?? 'No configurado' }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Email de Soporte:</strong><br>
                        <a href="mailto:{{ $generalSettings->where('key', 'support_email')->first()?->value }}">
                            {{ $generalSettings->where('key', 'support_email')->first()?->value ?? 'No configurado' }}
                        </a>
                    </div>

                    <div class="mb-3">
                        <strong>Teléfono:</strong><br>
                        <a href="tel:{{ $contactSettings->where('key', 'company_phone')->first()?->value }}">
                            {{ $contactSettings->where('key', 'company_phone')->first()?->value ?? 'No configurado' }}
                        </a>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.settings.whatsapp') }}" class="btn btn-success btn-sm">
                            <i class="fab fa-whatsapp"></i> Configurar WhatsApp
                        </a>
                        <a href="{{ route('admin.currencies.index') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-coins"></i> Gestionar Monedas
                        </a>
                        <button type="button" class="btn btn-info btn-sm" onclick="testEmailConfig()">
                            <i class="fas fa-envelope"></i> Probar Email
                        </button>
                        <form action="{{ route('admin.settings.clearCache') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm w-100">
                                <i class="fas fa-sync"></i> Limpiar Cache
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Ayuda -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-question-circle"></i> Ayuda
                    </h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Nota:</strong> Estas configuraciones afectan la información mostrada en la aplicación móvil.
                    </div>
                    
                    <ul class="mb-0">
                        <li>El nombre de la app aparece en el header</li>
                        <li>La versión se usa para actualizaciones</li>
                        <li>El email de soporte es visible para usuarios</li>
                        <li>Los datos de contacto aparecen en el perfil</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function resetForm() {
    if (confirm('¿Estás seguro de que quieres restablecer el formulario?')) {
        document.querySelector('form').reset();
    }
}

function testEmailConfig() {
    const email = document.getElementById('support_email').value;
    if (email) {
        window.open(`mailto:${email}?subject=Prueba de configuración&body=Esta es una prueba del email de soporte configurado.`, '_blank');
    } else {
        alert('Por favor, ingresa un email primero.');
    }
}
</script>
@endpush 