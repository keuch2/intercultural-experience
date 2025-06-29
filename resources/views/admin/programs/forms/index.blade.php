@extends('layouts.admin')

@section('title', 'Formularios - ' . $program->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-alt"></i> Formularios del Programa
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.index') }}">Programas</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.programs.show', $program) }}">{{ $program->name }}</a></li>
                    <li class="breadcrumb-item active">Formularios</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.programs.forms.create', $program) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Crear Formulario
        </a>
    </div>

    <!-- Información del Programa -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="card-title mb-1">{{ $program->name }}</h5>
                    <p class="text-muted mb-0">
                        <i class="fas fa-map-marker-alt"></i> {{ $program->country }}
                        @if($program->location)
                            - {{ $program->location }}
                        @endif
                        <span class="mx-2">|</span>
                        <i class="fas fa-tag"></i> {{ $program->category }}
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('admin.programs.show', $program) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Programa
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($forms->count() > 0)
        <!-- Lista de Formularios -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list"></i> Formularios Disponibles ({{ $forms->total() }})
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Versión</th>
                                <th>Estado</th>
                                <th>Campos</th>
                                <th>Respuestas</th>
                                <th>Creado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($forms as $form)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $form->name }}</strong>
                                        @if($form->description)
                                            <br><small class="text-muted">{{ Str::limit($form->description, 60) }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-info">v{{ $form->version }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        @if($form->is_active)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">Inactivo</span>
                                        @endif
                                        
                                        @if($form->is_published)
                                            <span class="badge bg-primary">Publicado</span>
                                        @else
                                            <span class="badge bg-warning">Borrador</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $form->fields_count ?? $form->fields->count() }} campos</span>
                                </td>
                                <td>
                                    @php
                                        $submissionsCount = $form->submissions_count ?? $form->submissions->count();
                                    @endphp
                                    @if($submissionsCount > 0)
                                        <a href="{{ route('admin.programs.forms.submissions', [$program, $form]) }}" 
                                           class="badge bg-info text-decoration-none">
                                            {{ $submissionsCount }} respuestas
                                        </a>
                                    @else
                                        <span class="text-muted">Sin respuestas</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $form->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.programs.forms.show', [$program, $form]) }}" 
                                           class="btn btn-sm btn-outline-info" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <a href="{{ route('admin.programs.forms.preview', [$program, $form]) }}" 
                                           class="btn btn-sm btn-outline-secondary" title="Vista Previa">
                                            <i class="fas fa-search"></i>
                                        </a>
                                        
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" 
                                                    data-bs-toggle="dropdown" title="Más Acciones">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" 
                                                       href="{{ route('admin.programs.forms.edit', [$program, $form]) }}">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('admin.programs.forms.toggle-active', [$program, $form]) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            @if($form->is_active)
                                                                <i class="fas fa-pause"></i> Desactivar
                                                            @else
                                                                <i class="fas fa-play"></i> Activar
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.programs.forms.toggle-published', [$program, $form]) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            @if($form->is_published)
                                                                <i class="fas fa-eye-slash"></i> Despublicar
                                                            @else
                                                                <i class="fas fa-globe"></i> Publicar
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#cloneModal{{ $form->id }}">
                                                        <i class="fas fa-copy"></i> Clonar Versión
                                                    </button>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <button type="button" class="dropdown-item text-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal{{ $form->id }}">
                                                        <i class="fas fa-trash"></i> Eliminar
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($forms->hasPages())
                    <div class="d-flex justify-content-center mt-3">
                        {{ $forms->links() }}
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Estado Vacío -->
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay formularios creados</h5>
                <p class="text-muted mb-4">
                    Crea el primer formulario para que los usuarios puedan postularse a este programa.
                </p>
                <a href="{{ route('admin.programs.forms.create', $program) }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Crear Primer Formulario
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Modales para Clonar -->
@foreach($forms as $form)
<div class="modal fade" id="cloneModal{{ $form->id }}" tabindex="-1">
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
                        <label for="new_version{{ $form->id }}" class="form-label">Nueva Versión</label>
                        <input type="text" class="form-control" id="new_version{{ $form->id }}" 
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
@endforeach

<!-- Modales para Eliminar -->
@foreach($forms as $form)
<div class="modal fade" id="deleteModal{{ $form->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar el formulario <strong>{{ $form->name }}</strong>?</p>
                @php
                    $submissionsCount = $form->submissions_count ?? $form->submissions->count();
                @endphp
                @if($submissionsCount > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Este formulario tiene {{ $submissionsCount }} respuestas que también se eliminarán.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('admin.programs.forms.destroy', [$program, $form]) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar Formulario</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection 