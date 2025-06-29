@extends('layouts.admin')

@section('title', 'Requisitos de la Solicitud #' . $application->id)

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Requisitos de la Solicitud #{{ $application->id }}</h1>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.applications.index') }}">Solicitudes</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.applications.show', $application->id) }}">Solicitud #{{ $application->id }}</a></li>
        <li class="breadcrumb-item active">Requisitos</li>
    </ol>
    
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-user me-1"></i>
                    Información del Estudiante
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Nombre:</strong> {{ $application->user->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> {{ $application->user->email }}
                    </div>
                    <div class="mb-3">
                        <strong>Nacionalidad:</strong> {{ $application->user->nationality ?? 'No especificada' }}
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-graduation-cap me-1"></i>
                    Información del Programa
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Programa:</strong> {{ $application->program->name }}
                    </div>
                    <div class="mb-3">
                        <strong>País:</strong> {{ $application->program->country }}
                    </div>
                    <div class="mb-3">
                        <strong>Categoría:</strong> {{ $application->program->category }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-tasks me-1"></i>
                Requisitos de la Solicitud
            </div>
            <div>
                <div class="progress" style="width: 200px; height: 20px;">
                    <div class="progress-bar {{ $application->getProgressPercentage() < 50 ? 'bg-danger' : ($application->getProgressPercentage() < 100 ? 'bg-warning' : 'bg-success') }}" 
                         role="progressbar" 
                         style="width: {{ $application->getProgressPercentage() }}%;" 
                         aria-valuenow="{{ $application->getProgressPercentage() }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                        {{ $application->getProgressPercentage() }}%
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if($requisites->isEmpty())
                <div class="alert alert-info">
                    No hay requisitos definidos para este programa.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Requisito</th>
                                <th>Tipo</th>
                                <th>Obligatorio</th>
                                <th>Estado</th>
                                <th>Archivo</th>
                                <th>Observaciones</th>
                                <th style="width: 180px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requisites as $index => $requisite)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <strong>{{ $requisite->programRequisite->name }}</strong>
                                        @if($requisite->programRequisite->description)
                                            <br><small class="text-muted">{{ $requisite->programRequisite->description }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($requisite->programRequisite->type == 'document')
                                            <span class="badge bg-primary">Documento</span>
                                        @elseif($requisite->programRequisite->type == 'action')
                                            <span class="badge bg-success">Acción</span>
                                        @elseif($requisite->programRequisite->type == 'payment')
                                            <span class="badge bg-warning">Pago</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($requisite->programRequisite->is_required)
                                            <span class="badge bg-success">Sí</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($requisite->status == 'pending')
                                            <span class="badge bg-secondary">Pendiente</span>
                                        @elseif($requisite->status == 'completed')
                                            <span class="badge bg-warning">Completado</span>
                                        @elseif($requisite->status == 'verified')
                                            <span class="badge bg-success">Verificado</span>
                                        @elseif($requisite->status == 'rejected')
                                            <span class="badge bg-danger">Rechazado</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($requisite->file_path)
                                            <a href="{{ Storage::url($requisite->file_path) }}" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-file-download"></i> Ver
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $requisite->observations ?? 'Sin observaciones' }}
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($requisite->status == 'completed')
                                                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $requisite->id }}">
                                                    <i class="fas fa-check"></i> Verificar
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $requisite->id }}">
                                                    <i class="fas fa-times"></i> Rechazar
                                                </button>
                                            @elseif($requisite->status == 'verified' || $requisite->status == 'rejected')
                                                <form action="{{ route('admin.applications.requisites.reset', $requisite->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('¿Estás seguro de reiniciar este requisito?')">
                                                        <i class="fas fa-redo"></i> Reiniciar
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-muted">Esperando acción del estudiante</span>
                                            @endif
                                        </div>
                                        
                                        <!-- Modal para verificar requisito -->
                                        <div class="modal fade" id="verifyModal{{ $requisite->id }}" tabindex="-1" aria-labelledby="verifyModalLabel{{ $requisite->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.applications.requisites.verify', $requisite->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="verifyModalLabel{{ $requisite->id }}">Verificar Requisito</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>¿Estás seguro de verificar este requisito?</p>
                                                            <div class="mb-3">
                                                                <label for="observations" class="form-label">Observaciones (opcional)</label>
                                                                <textarea class="form-control" id="observations" name="observations" rows="3"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn btn-success">Verificar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Modal para rechazar requisito -->
                                        <div class="modal fade" id="rejectModal{{ $requisite->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $requisite->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form action="{{ route('admin.applications.requisites.reject', $requisite->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="rejectModalLabel{{ $requisite->id }}">Rechazar Requisito</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>¿Estás seguro de rechazar este requisito?</p>
                                                            <div class="mb-3">
                                                                <label for="observations" class="form-label">Motivo del rechazo <span class="text-danger">*</span></label>
                                                                <textarea class="form-control" id="observations" name="observations" rows="3" required></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                            <button type="submit" class="btn btn-danger">Rechazar</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
