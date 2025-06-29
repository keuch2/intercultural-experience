@extends('layouts.admin')

@section('title', 'Detalles del Programa')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title mb-0">{{ $program->name }}</h3>
                            <small class="text-muted">
                                <i class="fas fa-map-marker-alt"></i> {{ $program->country }}
                                @if($program->location)
                                    - {{ $program->location }}
                                @endif
                            </small>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('admin.programs.edit', $program) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="{{ route('admin.programs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Información Principal -->
                <div class="col-lg-8">
                    <!-- Descripción -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Descripción del Programa
                            </h5>
                        </div>
                        <div class="card-body">
                            <p>{{ $program->description }}</p>
                        </div>
                    </div>

                    <!-- Fechas y Duración -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-calendar-alt"></i> Fechas y Duración
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Fecha de Inicio</label>
                                        <div class="fw-bold">
                                            @if($program->start_date)
                                                {{ $program->start_date->format('d/m/Y') }}
                                            @else
                                                <span class="text-muted">No definida</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Fecha de Finalización</label>
                                        <div class="fw-bold">
                                            @if($program->end_date)
                                                {{ $program->end_date->format('d/m/Y') }}
                                            @else
                                                <span class="text-muted">No definida</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Límite de Postulación</label>
                                        <div class="fw-bold">
                                            @if($program->application_deadline)
                                                <span class="text-danger">{{ $program->application_deadline->format('d/m/Y') }}</span>
                                            @else
                                                <span class="text-muted">No definida</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Duración</label>
                                        <div class="fw-bold">
                                            {{ $program->duration ?? 'No definida' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Créditos Académicos</label>
                                        <div class="fw-bold">
                                            {{ $program->credits ?? 'No definidos' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estadísticas de Aplicaciones -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-chart-bar"></i> Estadísticas de Postulaciones
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $applicationStats['total'] }}</h3>
                                            <small>Total</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $applicationStats['pending'] }}</h3>
                                            <small>Pendientes</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $applicationStats['approved'] }}</h3>
                                            <small>Aprobadas</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $applicationStats['rejected'] }}</h3>
                                            <small>Rechazadas</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel Lateral -->
                <div class="col-lg-4">
                    <!-- Imagen del Programa -->
                    @if($program->image_url)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-image"></i> Imagen del Programa
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            <img src="{{ $program->image_url }}" 
                                 alt="{{ $program->name }}" 
                                 class="img-fluid rounded" 
                                 style="max-height: 250px;">
                        </div>
                    </div>
                    @endif

                    <!-- Información General -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info"></i> Información General
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Estado</label>
                                <div>
                                    @if($program->is_active)
                                        <span class="badge bg-success fs-6">Activo</span>
                                    @else
                                        <span class="badge bg-danger fs-6">Inactivo</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label text-muted">Categoría</label>
                                <div>
                                    @switch($program->category)
                                        @case('academic')
                                            <span class="badge bg-primary fs-6">Académico</span>
                                            @break
                                        @case('volunteer')
                                            <span class="badge bg-success fs-6">Voluntariado</span>
                                            @break
                                        @case('internship')
                                            <span class="badge bg-warning fs-6">Prácticas</span>
                                            @break
                                        @case('language')
                                            <span class="badge bg-info fs-6">Idiomas</span>
                                            @break
                                        @case('cultural')
                                            <span class="badge bg-purple fs-6">Cultural</span>
                                            @break
                                        @case('research')
                                            <span class="badge bg-dark fs-6">Investigación</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary fs-6">{{ ucfirst($program->category) }}</span>
                                    @endswitch
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted">Creado</label>
                                <div>{{ $program->created_at->format('d/m/Y H:i') }}</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted">Última Actualización</label>
                                <div>{{ $program->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Costo y Capacidad -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-dollar-sign"></i> Costo y Capacidad
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label text-muted">Costo del Programa</label>
                                <div>
                                    @if($program->cost && $program->currency)
                                        <div class="fw-bold fs-5 text-primary">{{ $program->formatted_cost }}</div>
                                        <small class="text-muted">≈ {{ $program->formatted_cost_in_pyg }}</small>
                                    @elseif($program->cost)
                                        <div class="fw-bold fs-5 text-primary">${{ number_format($program->cost, 2) }}</div>
                                    @else
                                        <span class="text-muted">No definido</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label text-muted">Capacidad</label>
                                <div>
                                    @if($program->capacity)
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <div class="fw-bold">{{ $program->capacity }} participantes</div>
                                                <small class="text-muted">Capacidad total</small>
                                            </div>
                                            <div class="text-end">
                                                <div class="fw-bold text-success">{{ $program->available_spots ?? $program->capacity }}</div>
                                                <small class="text-muted">disponibles</small>
                                            </div>
                                        </div>
                                        
                                        @if($program->capacity > 0)
                                            @php
                                                $occupiedSpots = $program->capacity - ($program->available_spots ?? $program->capacity);
                                                $occupancyPercentage = ($occupiedSpots / $program->capacity) * 100;
                                            @endphp
                                            <div class="progress mt-2" style="height: 8px;">
                                                <div class="progress-bar bg-success" 
                                                     role="progressbar" 
                                                     style="width: {{ $occupancyPercentage }}%">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ number_format($occupancyPercentage, 1) }}% ocupado</small>
                                        @endif
                                    @else
                                        <span class="text-muted">No definida</span>
                                    @endif
                                </div>
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
                                <a href="{{ route('admin.applications.index', ['program_id' => $program->id]) }}" 
                                   class="btn btn-info">
                                    <i class="fas fa-users"></i> Ver Postulaciones
                                </a>
                                
                                <a href="{{ route('admin.programs.forms.index', $program) }}" 
                                   class="btn btn-success">
                                    <i class="fas fa-file-alt"></i> Gestionar Formularios
                                </a>
                                
                                @if($program->requisites()->count() > 0)
                                    <a href="{{ route('admin.programs.requisites.index', $program) }}" 
                                       class="btn btn-outline-primary">
                                        <i class="fas fa-list-check"></i> Gestionar Requisitos
                                    </a>
                                @else
                                    <a href="{{ route('admin.programs.requisites.create', $program) }}" 
                                       class="btn btn-outline-warning">
                                        <i class="fas fa-plus"></i> Añadir Requisitos
                                    </a>
                                @endif
                                
                                <button type="button" class="btn btn-outline-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal">
                                    <i class="fas fa-trash"></i> Eliminar Programa
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lista de Postulaciones -->
            @if($applications->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clipboard-list"></i> Postulaciones Recientes
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($applications as $application)
                                <tr>
                                    <td>{{ $application->user->name }}</td>
                                    <td>{{ $application->user->email }}</td>
                                    <td>
                                        @switch($application->status)
                                            @case('pending')
                                                <span class="badge bg-warning">Pendiente</span>
                                                @break
                                            @case('in_review')
                                                <span class="badge bg-info">En Revisión</span>
                                                @break
                                            @case('approved')
                                                <span class="badge bg-success">Aprobada</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger">Rechazada</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($application->status) }}</span>
                                        @endswitch
                                    </td>
                                    <td>{{ $application->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('admin.applications.show', $application) }}" 
                                           class="btn btn-sm btn-outline-info">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($applications->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $applications->links() }}
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal de Eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar el programa <strong>{{ $program->name }}</strong>?</p>
                @if($applicationStats['total'] > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Este programa tiene {{ $applicationStats['total'] }} postulaciones asociadas que también se eliminarán.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('admin.programs.destroy', $program) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar Programa</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.badge.bg-purple {
    background-color: #6f42c1 !important;
}
.progress {
    background-color: #e9ecef;
}
</style>
@endpush
