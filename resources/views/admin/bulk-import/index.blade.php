@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-file-import me-2"></i>Importación Masiva de Datos
    </h1>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Formulario de Importación -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-upload me-2"></i>Subir Archivo
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.bulk-import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-4">
                        <label for="type" class="form-label">Tipo de Importación <span class="text-danger">*</span></label>
                        <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                            <option value="">Seleccionar...</option>
                            <option value="participants">Participantes</option>
                            <option value="agents">Agentes</option>
                        </select>
                        @error('type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Selecciona el tipo de usuarios que deseas importar
                        </small>
                    </div>

                    <div class="mb-4">
                        <label for="file" class="form-label">Archivo Excel/CSV <span class="text-danger">*</span></label>
                        <input type="file" 
                               class="form-control @error('file') is-invalid @enderror" 
                               id="file" 
                               name="file" 
                               accept=".xlsx,.xls,.csv" 
                               required>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Formatos aceptados: .xlsx, .xls, .csv (Máximo 10MB)
                        </small>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Importante:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Asegúrate de que el archivo tenga el formato correcto</li>
                            <li>La primera fila debe contener los encabezados</li>
                            <li>Los campos obligatorios están marcados con (*)</li>
                            <li>Los emails deben ser únicos</li>
                        </ul>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-search me-2"></i>Vista Previa y Validación
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instrucciones -->
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle me-2"></i>Instrucciones
                </h6>
            </div>
            <div class="card-body">
                <h5>Pasos para importar datos:</h5>
                <ol>
                    <li><strong>Descarga la plantilla</strong> correspondiente al tipo de usuario que deseas importar</li>
                    <li><strong>Completa la plantilla</strong> con los datos de los usuarios</li>
                    <li><strong>Sube el archivo</strong> y espera la validación</li>
                    <li><strong>Revisa la vista previa</strong> y corrige errores si los hay</li>
                    <li><strong>Confirma la importación</strong> y descarga el reporte</li>
                </ol>

                <h5 class="mt-4">Campos requeridos:</h5>
                <ul>
                    <li><strong>Participantes:</strong> Nombre, Email, Teléfono, País, Nacionalidad, Fecha de Nacimiento</li>
                    <li><strong>Agentes:</strong> Nombre, Email, Teléfono, País</li>
                </ul>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-key me-2"></i>
                    <strong>Contraseñas:</strong> Se generarán contraseñas temporales automáticamente para todos los usuarios importados. Recibirás un reporte con las credenciales.
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Plantillas -->
        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-success text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-download me-2"></i>Descargar Plantillas
                </h6>
            </div>
            <div class="card-body">
                <p>Descarga las plantillas de ejemplo:</p>
                
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.bulk-import.template', 'participants') }}" 
                       class="btn btn-outline-success">
                        <i class="fas fa-file-excel me-2"></i>Plantilla Participantes
                    </a>
                    
                    <a href="{{ route('admin.bulk-import.template', 'agents') }}" 
                       class="btn btn-outline-success">
                        <i class="fas fa-file-excel me-2"></i>Plantilla Agentes
                    </a>
                </div>

                <div class="alert alert-secondary mt-3 mb-0">
                    <small>
                        <i class="fas fa-lightbulb me-1"></i>
                        Las plantillas incluyen una fila de ejemplo para guiarte.
                    </small>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="card shadow">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-chart-bar me-2"></i>Estadísticas
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Participantes:</span>
                        <strong>{{ \App\Models\User::where('role', 'user')->count() }}</strong>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <span>Agentes:</span>
                        <strong>{{ \App\Models\User::where('role', 'agent')->count() }}</strong>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between">
                        <span>Administradores:</span>
                        <strong>{{ \App\Models\User::where('role', 'admin')->count() }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
