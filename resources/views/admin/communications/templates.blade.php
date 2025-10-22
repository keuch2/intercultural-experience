@extends('layouts.admin')

@section('title', 'Templates de Comunicación')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-alt"></i> Templates de Comunicación
        </h1>
        <a href="{{ route('admin.communications.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Nota:</strong> Estos son templates predefinidos que puedes usar al crear una nueva comunicación. 
        Puedes modificarlos antes de enviar.
    </div>

    <div class="row">
        @foreach($templates as $template)
            <div class="col-lg-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-envelope"></i> {{ $template['name'] }}
                        </h6>
                        <button type="button" class="btn btn-sm btn-primary" 
                                onclick="useTemplate({{ json_encode($template) }})">
                            <i class="fas fa-check"></i> Usar
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Asunto:</strong><br>
                            <code>{{ $template['subject'] }}</code>
                        </div>
                        <div class="mb-3">
                            <strong>Mensaje:</strong>
                            <div class="border p-3 bg-light" style="white-space: pre-wrap; font-size: 0.9rem;">{{ $template['message'] }}</div>
                        </div>
                        <div class="alert alert-secondary mb-0">
                            <small>
                                <i class="fas fa-lightbulb"></i>
                                <strong>Variables:</strong> Este template usa variables dinámicas que se reemplazarán automáticamente.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Guía de Variables -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-code"></i> Guía de Variables Disponibles
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Datos del Participante</h6>
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><code>{nombre}</code></td>
                                <td>Nombre completo del participante</td>
                            </tr>
                            <tr>
                                <td><code>{email}</code></td>
                                <td>Email del participante</td>
                            </tr>
                            <tr>
                                <td><code>{telefono}</code></td>
                                <td>Teléfono del participante</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">Datos del Programa</h6>
                    <table class="table table-sm">
                        <tbody>
                            <tr>
                                <td><code>{programa}</code></td>
                                <td>Nombre del programa</td>
                            </tr>
                            <tr>
                                <td><code>{categoria_programa}</code></td>
                                <td>Categoría (IE o YFU)</td>
                            </tr>
                            <tr>
                                <td><code>{nivel_ingles}</code></td>
                                <td>Nivel CEFR (A2-C2)</td>
                            </tr>
                            <tr>
                                <td><code>{puntaje_ingles}</code></td>
                                <td>Puntaje de inglés (0-100)</td>
                            </tr>
                            <tr>
                                <td><code>{fecha}</code></td>
                                <td>Fecha actual</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="alert alert-info mt-3 mb-0">
                <i class="fas fa-info-circle"></i>
                <strong>Ejemplo de uso:</strong><br>
                <code>Hola {nombre}, bienvenido al programa {programa}.</code><br>
                Se convertirá en: <em>Hola Juan Pérez, bienvenido al programa Work and Travel.</em>
            </div>
        </div>
    </div>

    <!-- Crear Template Personalizado -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-success text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-plus"></i> ¿Necesitas un Template Personalizado?
            </h6>
        </div>
        <div class="card-body">
            <p>
                Puedes crear mensajes personalizados directamente desde la sección de 
                <strong>Nueva Comunicación</strong>. También puedes usar estos templates como 
                base y modificarlos según tus necesidades.
            </p>
            <a href="{{ route('admin.communications.create') }}" class="btn btn-success">
                <i class="fas fa-paper-plane"></i> Crear Nueva Comunicación
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function useTemplate(template) {
    if (confirm('¿Ir a crear comunicación con este template?')) {
        // Store in sessionStorage to populate in create page
        sessionStorage.setItem('selectedTemplate', JSON.stringify(template));
        window.location.href = '{{ route("admin.communications.create") }}';
    }
}

// Check if there's a template selected from previous page
$(document).ready(function() {
    const selectedTemplate = sessionStorage.getItem('selectedTemplate');
    if (selectedTemplate) {
        const template = JSON.parse(selectedTemplate);
        $('input[name="subject"]').val(template.subject);
        $('textarea[name="message"]').val(template.message);
        sessionStorage.removeItem('selectedTemplate');
    }
});
</script>
@endsection
