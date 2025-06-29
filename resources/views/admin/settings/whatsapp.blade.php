@extends('layouts.admin')

@section('title', 'Configuración de WhatsApp')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fab fa-whatsapp text-success"></i> Configuración de WhatsApp
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.settings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver a Configuraciones
                            </a>
                            <button type="button" class="btn btn-info" onclick="testWhatsApp()">
                                <i class="fas fa-mobile-alt"></i> Probar WhatsApp
                            </button>
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
                        <i class="fas fa-cogs"></i> Configuración de Soporte por WhatsApp
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.whatsapp.update') }}" method="POST">
                        @csrf
                        
                        <!-- Habilitar/Deshabilitar WhatsApp -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="whatsapp_support_enabled" 
                                       name="whatsapp_support_enabled" value="1"
                                       {{ $whatsappSettings->where('key', 'whatsapp_support_enabled')->first()?->value ? 'checked' : '' }}>
                                <label class="form-check-label" for="whatsapp_support_enabled">
                                    <strong>Habilitar Soporte por WhatsApp</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Cuando esté habilitado, aparecerá el botón flotante de WhatsApp en la aplicación móvil.
                            </small>
                        </div>

                        <!-- Número de WhatsApp -->
                        <div class="mb-4">
                            <label for="whatsapp_support_number" class="form-label">
                                <i class="fas fa-phone"></i> Número de WhatsApp
                            </label>
                            <input type="text" 
                                   class="form-control @error('whatsapp_support_number') is-invalid @enderror" 
                                   id="whatsapp_support_number" 
                                   name="whatsapp_support_number" 
                                   value="{{ old('whatsapp_support_number', $whatsappSettings->where('key', 'whatsapp_support_number')->first()?->value) }}"
                                   placeholder="+595981234567"
                                   required>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> 
                                Incluye el código de país. Ejemplo: +595981234567 (Paraguay)
                            </div>
                            @error('whatsapp_support_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Mensaje de Bienvenida -->
                        <div class="mb-4">
                            <label for="whatsapp_welcome_message" class="form-label">
                                <i class="fas fa-comment"></i> Mensaje de Bienvenida
                            </label>
                            <textarea class="form-control @error('whatsapp_welcome_message') is-invalid @enderror" 
                                      id="whatsapp_welcome_message" 
                                      name="whatsapp_welcome_message" 
                                      rows="3"
                                      maxlength="500"
                                      required>{{ old('whatsapp_welcome_message', $whatsappSettings->where('key', 'whatsapp_welcome_message')->first()?->value) }}</textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle"></i> 
                                Este mensaje se enviará automáticamente cuando un usuario haga clic en el botón de WhatsApp.
                                <span class="float-end">
                                    <span id="messageCount">0</span>/500 caracteres
                                </span>
                            </div>
                            @error('whatsapp_welcome_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Botones -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
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
            <!-- Vista Previa -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-eye"></i> Vista Previa
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <div class="btn btn-success btn-lg rounded-circle" style="width: 60px; height: 60px; display: inline-flex; align-items: center; justify-content: center;">
                                <i class="fab fa-whatsapp fa-2x"></i>
                            </div>
                        </div>
                        <p class="text-muted">Botón flotante de WhatsApp</p>
                        <small class="text-muted">Aparecerá en la esquina inferior derecha de la app móvil</small>
                    </div>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Estado Actual:</strong>
                        <span class="badge {{ $whatsappSettings->where('key', 'whatsapp_support_enabled')->first()?->value ? 'bg-success' : 'bg-danger' }}">
                            {{ $whatsappSettings->where('key', 'whatsapp_support_enabled')->first()?->value ? 'Habilitado' : 'Deshabilitado' }}
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Número Actual:</strong><br>
                        <code>{{ $whatsappSettings->where('key', 'whatsapp_support_number')->first()?->value ?? 'No configurado' }}</code>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-lightbulb"></i>
                        <strong>Consejo:</strong> Asegúrate de que el número de WhatsApp esté activo y pueda recibir mensajes.
                    </div>
                </div>
            </div>

            <!-- Instrucciones -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-question-circle"></i> Instrucciones
                    </h6>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Ingresa el número de WhatsApp con código de país</li>
                        <li>Personaliza el mensaje de bienvenida</li>
                        <li>Habilita la función</li>
                        <li>Guarda los cambios</li>
                        <li>Prueba la funcionalidad</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Contador de caracteres para el mensaje
    const messageTextarea = document.getElementById('whatsapp_welcome_message');
    const messageCount = document.getElementById('messageCount');
    
    function updateMessageCount() {
        const count = messageTextarea.value.length;
        messageCount.textContent = count;
        
        if (count > 450) {
            messageCount.className = 'text-warning';
        } else if (count > 500) {
            messageCount.className = 'text-danger';
        } else {
            messageCount.className = 'text-muted';
        }
    }
    
    messageTextarea.addEventListener('input', updateMessageCount);
    updateMessageCount(); // Inicializar contador
});

function testWhatsApp() {
    const number = document.getElementById('whatsapp_support_number').value;
    const message = document.getElementById('whatsapp_welcome_message').value;
    
    if (!number) {
        alert('Por favor, ingresa un número de WhatsApp primero.');
        return;
    }
    
    const cleanNumber = number.replace(/[^\d]/g, '');
    const encodedMessage = encodeURIComponent(message);
    const whatsappUrl = `https://wa.me/${cleanNumber}?text=${encodedMessage}`;
    
    window.open(whatsappUrl, '_blank');
}

function resetForm() {
    if (confirm('¿Estás seguro de que quieres restablecer el formulario?')) {
        document.querySelector('form').reset();
        document.getElementById('messageCount').textContent = '0';
    }
}
</script>
@endpush 