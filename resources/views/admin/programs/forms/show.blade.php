@extends('layouts.admin')

@section('title', $form->name . ' - Formulario')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-alt"></i> {{ $form->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.index') }}">Programas</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.show', $program) }}">{{ $program->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.forms.index', $program) }}">Formularios</a></li>
                    <li class="breadcrumb-item active">{{ $form->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.programs.forms.preview', [$program, $form]) }}" class="btn btn-info">
                <i class="fas fa-search"></i> Vista Previa
            </a>
            <a href="{{ route('admin.programs.forms.edit', [$program, $form]) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información Principal -->
        <div class="col-lg-8">
            <!-- Información del Formulario -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información del Formulario
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Nombre</label>
                                <div class="fw-bold">{{ $form->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted">Versión</label>
                                <div><span class="badge bg-info fs-6">v{{ $form->version }}</span></div>
                            </div>
                        </div>
                    </div>

                    @if($form->description)
                    <div class="mb-3">
                        <label class="form-label text-muted">Descripción</label>
                        <div>{{ $form->description }}</div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <div>
                                    @if($form->is_active)
                                        <span class="badge bg-success fs-6">Activo</span>
                                    @else
                                        <span class="badge bg-secondary fs-6">Inactivo</span>
                                    @endif
                                    
                                    @if($form->is_published)
                                        <span class="badge bg-primary fs-6">Publicado</span>
                                    @else
                                        <span class="badge bg-warning fs-6">Borrador</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted">Rango de Edad</label>
                                <div>
                                    @if($form->min_age || $form->max_age)
                                        {{ $form->min_age ?? 'Sin mínimo' }} - {{ $form->max_age ?? 'Sin máximo' }} años
                                    @else
                                        <span class="text-muted">No definido</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted">Firmas Requeridas</label>
                                <div>
                                    @if($form->requires_signature)
                                        <i class="fas fa-check text-success"></i> Participante
                                    @endif
                                    @if($form->requires_parent_signature)
                                        <br><i class="fas fa-check text-success"></i> Padres/Tutores
                                    @endif
                                    @if(!$form->requires_signature && !$form->requires_parent_signature)
                                        <span class="text-muted">No requeridas</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estructura del Formulario -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list"></i> Estructura del Formulario ({{ $form->fields->count() }} campos)
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($structure) > 0)
                        <div class="accordion" id="formStructureAccordion">
                            @foreach($structure as $index => $section)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }}" 
                                            type="button" 
                                            data-bs-toggle="collapse" 
                                            data-bs-target="#section{{ $index }}"
                                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                        <strong>{{ $section['title'] }}</strong>
                                        <span class="badge bg-light text-dark ms-2">{{ count($section['fields']) }} campos</span>
                                    </button>
                                </h2>
                                <div id="section{{ $index }}" 
                                     class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                     data-bs-parent="#formStructureAccordion">
                                    <div class="accordion-body">
                                        @if($section['description'])
                                            <p class="text-muted mb-3">{{ $section['description'] }}</p>
                                        @endif
                                        
                                        <div class="table-responsive">
                                            <table class="table table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>Campo</th>
                                                        <th>Tipo</th>
                                                        <th>Requerido</th>
                                                        <th>Opciones</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($section['fields'] as $field)
                                                    <tr>
                                                        <td>
                                                            <div>
                                                                <strong>{{ $field->field_label }}</strong>
                                                                @if($field->description)
                                                                    <br><small class="text-muted">{{ $field->description }}</small>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark">{{ $field->field_type }}</span>
                                                        </td>
                                                        <td>
                                                            @if($field->is_required)
                                                                <i class="fas fa-check text-success"></i>
                                                            @else
                                                                <i class="fas fa-times text-muted"></i>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($field->options && in_array($field->field_type, ['select', 'radio', 'checkbox']))
                                                                <small class="text-muted">
                                                                    {{ implode(', ', array_slice($field->options, 0, 3)) }}
                                                                    @if(count($field->options) > 3)
                                                                        ... (+{{ count($field->options) - 3 }} más)
                                                                    @endif
                                                                </small>
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted">No hay campos definidos en este formulario.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Términos y Condiciones -->
            @if($form->terms_and_conditions)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-contract"></i> Términos y Condiciones
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-muted">
                        {{ Str::limit($form->terms_and_conditions, 300) }}
                        @if(strlen($form->terms_and_conditions) > 300)
                            <button class="btn btn-sm btn-link p-0" type="button" data-bs-toggle="collapse" data-bs-target="#fullTerms">
                                Ver completo
                            </button>
                            <div class="collapse" id="fullTerms">
                                {{ substr($form->terms_and_conditions, 300) }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Panel Lateral -->
        <div class="col-lg-4">
            <!-- Estadísticas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body py-2">
                                    <h4>{{ $submissions->total() }}</h4>
                                    <small>Total Respuestas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card bg-info text-white">
                                <div class="card-body py-2">
                                    <h4>{{ $form->fields->count() }}</h4>
                                    <small>Campos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información Técnica -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog"></i> Información Técnica
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Creado</label>
                        <div>{{ $form->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Última Actualización</label>
                        <div>{{ $form->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tools"></i> Acciones Rápidas
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($submissions->total() > 0)
                            <a href="{{ route('admin.programs.forms.submissions', [$program, $form]) }}" 
                               class="btn btn-info">
                                <i class="fas fa-list"></i> Ver Respuestas ({{ $submissions->total() }})
                            </a>
                        @endif
                        
                        <form action="{{ route('admin.programs.forms.toggle-active', [$program, $form]) }}" 
                              method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-{{ $form->is_active ? 'warning' : 'success' }} w-100">
                                @if($form->is_active)
                                    <i class="fas fa-pause"></i> Desactivar
                                @else
                                    <i class="fas fa-play"></i> Activar
                                @endif
                            </button>
                        </form>
                        
                        <form action="{{ route('admin.programs.forms.toggle-published', [$program, $form]) }}" 
                              method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-{{ $form->is_published ? 'secondary' : 'primary' }} w-100">
                                @if($form->is_published)
                                    <i class="fas fa-eye-slash"></i> Despublicar
                                @else
                                    <i class="fas fa-globe"></i> Publicar
                                @endif
                            </button>
                        </form>
                        
                        <button type="button" class="btn btn-outline-info" 
                                data-bs-toggle="modal" data-bs-target="#cloneModal">
                            <i class="fas fa-copy"></i> Clonar Versión
                        </button>
                        
                        <a href="{{ route('admin.programs.forms.index', $program) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a Lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Respuestas Recientes -->
    @if($submissions->count() > 0)
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-clipboard-list"></i> Respuestas Recientes
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Estado</th>
                            <th>Completitud</th>
                            <th>Enviado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($submissions as $submission)
                        <tr>
                            <td>
                                <div>
                                    <strong>{{ $submission->user->name }}</strong>
                                    <br><small class="text-muted">{{ $submission->user->email }}</small>
                                </div>
                            </td>
                            <td>
                                @switch($submission->status)
                                    @case('draft')
                                        <span class="badge bg-secondary">Borrador</span>
                                        @break
                                    @case('submitted')
                                        <span class="badge bg-primary">Enviado</span>
                                        @break
                                    @case('reviewed')
                                        <span class="badge bg-info">Revisado</span>
                                        @break
                                    @case('approved')
                                        <span class="badge bg-success">Aprobado</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">Rechazado</span>
                                        @break
                                    @default
                                        <span class="badge bg-light text-dark">{{ ucfirst($submission->status) }}</span>
                                @endswitch
                            </td>
                            <td>
                                @php
                                    $percentage = $submission->getCompletionPercentage();
                                @endphp
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ $percentage }}%">
                                        {{ $percentage }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($submission->submitted_at)
                                    {{ $submission->submitted_at->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-muted">No enviado</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.programs.forms.submission', [$program, $form, $submission]) }}" 
                                   class="btn btn-sm btn-outline-info">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($submissions->hasPages())
                <div class="d-flex justify-content-center mt-3">
                    {{ $submissions->links() }}
                </div>
            @endif
        </div>
    </div>
    @endif
</div>

<!-- Modal para Clonar -->
<div class="modal fade" id="cloneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.programs.forms.clone', [$program, $form]) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Clonar Formulario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Crear una nueva versión del formulario <strong>{{ $form->name }}</strong></p>
                    <div class="mb-3">
                        <label for="new_version" class="form-label">Nueva Versión</label>
                        <input type="text" class="form-control" id="new_version" 
                               name="new_version" placeholder="ej: 2.0" required>
                        <div class="form-text">La nueva versión debe ser única para este programa.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Clonar Formulario</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 