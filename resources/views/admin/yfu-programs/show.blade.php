@extends('layouts.admin')

@section('title', 'Programa YFU: ' . $program->name)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="card-title">{{ $program->name }}</h3>
                            <p class="text-muted mb-0">
                                <span class="badge bg-warning me-2">YFU</span>
                                <span class="badge bg-secondary">{{ $program->subcategory ?: 'Sin categoría' }}</span>
                            </p>
                        </div>
                        <div class="btn-group">
                            <a href="{{ route('admin.yfu-programs.edit', $program) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="{{ route('admin.yfu-programs.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <!-- Información Principal -->
                        <div class="col-md-8">
                            <!-- Detalles Básicos -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Información Básica</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>País:</strong> {{ $program->country }}</p>
                                            <p><strong>Ubicación:</strong> {{ $program->location ?: 'No especificada' }}</p>
                                            <p><strong>Subcategoría:</strong> {{ $program->subcategory ?: 'No especificada' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Estado:</strong> 
                                                @if($program->is_active)
                                                    <span class="badge bg-success">Activo</span>
                                                @else
                                                    <span class="badge bg-danger">Inactivo</span>
                                                @endif
                                            </p>
                                            <p><strong>Capacidad:</strong> {{ $program->capacity ?: 'Sin límite' }}</p>
                                            <p><strong>Duración:</strong> {{ $program->duration ? $program->duration . ' días' : 'No especificada' }}</p>
                                        </div>
                                    </div>
                                    
                                    @if($program->description)
                                        <hr>
                                        <h6>Descripción</h6>
                                        <p class="text-muted">{{ $program->description }}</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Fechas -->
                            @if($program->start_date || $program->end_date || $program->application_deadline)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Fechas Importantes</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @if($program->start_date)
                                                <div class="col-md-4">
                                                    <p><strong>Inicio:</strong><br>{{ $program->start_date->format('d/m/Y') }}</p>
                                                </div>
                                            @endif
                                            @if($program->end_date)
                                                <div class="col-md-4">
                                                    <p><strong>Finalización:</strong><br>{{ $program->end_date->format('d/m/Y') }}</p>
                                                </div>
                                            @endif
                                            @if($program->application_deadline)
                                                <div class="col-md-4">
                                                    <p><strong>Fecha Límite:</strong><br>{{ $program->application_deadline->format('d/m/Y') }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Información Financiera -->
                            @if($program->cost || $program->currency)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Información Financiera</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Costo:</strong> 
                                                    @if($program->cost && $program->currency)
                                                        {{ $program->formatted_cost }}
                                                    @elseif($program->cost)
                                                        {{ number_format($program->cost, 2) }}
                                                    @else
                                                        No especificado
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Moneda:</strong> {{ $program->currency->name ?? 'No especificada' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Institución -->
                            @if($program->institution)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Institución Asociada</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>{{ $program->institution->name }}</strong></p>
                                        @if($program->institution->description)
                                            <p class="text-muted">{{ $program->institution->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Sidebar con Estadísticas -->
                        <div class="col-md-4">
                            <!-- Estadísticas Rápidas -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Estadísticas</h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <h3 class="text-warning">{{ $program->applications->count() }}</h3>
                                        <p class="mb-0">Solicitudes Totales</p>
                                    </div>
                                    
                                    @if($program->capacity)
                                        <div class="progress mb-2">
                                            @php
                                                $percentage = $program->capacity > 0 ? ($program->applications->count() / $program->capacity) * 100 : 0;
                                                $percentage = min($percentage, 100);
                                            @endphp
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $percentage }}%">
                                                {{ number_format($percentage, 1) }}%
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ $program->applications->count() }} de {{ $program->capacity }} plazas</small>
                                    @endif
                                </div>
                            </div>

                            <!-- Estado de Solicitudes -->
                            @if($program->applications->count() > 0)
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Estado de Solicitudes</h5>
                                    </div>
                                    <div class="card-body">
                                        @php
                                            $pending = $program->applications->where('status', 'pending')->count();
                                            $approved = $program->applications->where('status', 'approved')->count();
                                            $rejected = $program->applications->where('status', 'rejected')->count();
                                        @endphp
                                        
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Pendientes</span>
                                                <span class="badge bg-warning">{{ $pending }}</span>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Aprobadas</span>
                                                <span class="badge bg-success">{{ $approved }}</span>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Rechazadas</span>
                                                <span class="badge bg-danger">{{ $rejected }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Metadatos -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Información del Sistema</h5>
                                </div>
                                <div class="card-body">
                                    <p><small class="text-muted">
                                        <strong>Creado:</strong><br>{{ $program->created_at->format('d/m/Y H:i') }}
                                    </small></p>
                                    <p><small class="text-muted">
                                        <strong>Actualizado:</strong><br>{{ $program->updated_at->format('d/m/Y H:i') }}
                                    </small></p>
                                    <p><small class="text-muted">
                                        <strong>ID:</strong> {{ $program->id }}
                                    </small></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Solicitudes Recientes -->
    @if($program->applications->count() > 0)
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Solicitudes Recientes</h5>
                            <a href="#" class="btn btn-sm btn-outline-primary">Ver Todas</a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Participante</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Progreso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($program->applications->take(5) as $application)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                        {{ strtoupper(substr($application->user->name, 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $application->user->name }}</h6>
                                                        <small class="text-muted">{{ $application->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $application->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                @switch($application->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning">Pendiente</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge bg-success">Aprobada</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge bg-danger">Rechazada</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $application->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 4px;">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $application->progress_percentage ?? 0 }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $application->progress_percentage ?? 0 }}%</small>
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 12px;
}
</style>
@endpush
