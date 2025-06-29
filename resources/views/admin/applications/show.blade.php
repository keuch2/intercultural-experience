@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalles de la Solicitud #{{ $application->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ url('/admin/applications') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver a la lista
        </a>
    </div>
</div>

<div class="row">
    <!-- Application Status Card -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Estado de la Solicitud</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    @switch($application->status)
                        @case('pending')
                            <div class="display-4 text-warning mb-2"><i class="fas fa-clock"></i></div>
                            <h4 class="text-warning">Pendiente</h4>
                            <p class="text-muted">La solicitud está esperando revisión.</p>
                            @break
                        @case('in_review')
                            <div class="display-4 text-info mb-2"><i class="fas fa-search"></i></div>
                            <h4 class="text-info">En Revisión</h4>
                            <p class="text-muted">La solicitud está siendo revisada.</p>
                            @break
                        @case('approved')
                            <div class="display-4 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                            <h4 class="text-success">Aprobada</h4>
                            <p class="text-muted">La solicitud ha sido aprobada.</p>
                            @break
                        @case('rejected')
                            <div class="display-4 text-danger mb-2"><i class="fas fa-times-circle"></i></div>
                            <h4 class="text-danger">Rechazada</h4>
                            <p class="text-muted">La solicitud ha sido rechazada.</p>
                            @break
                        @default
                            <div class="display-4 text-secondary mb-2"><i class="fas fa-question-circle"></i></div>
                            <h4 class="text-secondary">{{ $application->status }}</h4>
                    @endswitch
                </div>
                
                <div class="text-center">
                    <p><strong>Fecha de Solicitud:</strong> {{ $application->applied_at }}</p>
                    @if($application->completed_at)
                        <p><strong>Fecha de Finalización:</strong> {{ $application->completed_at }}</p>
                    @endif
                </div>
                
                <hr>
                
                <div class="d-grid gap-2">
                    @if($application->status == 'pending')
                        <form action="{{ url('/admin/applications/'.$application->id.'/review') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search me-1"></i> Marcar en Revisión
                            </button>
                        </form>
                    @endif
                    
                    @if($application->status == 'in_review')
                        <form action="{{ url('/admin/applications/'.$application->id.'/approve') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-check me-1"></i> Aprobar Solicitud
                            </button>
                        </form>
                        <form action="{{ url('/admin/applications/'.$application->id.'/reject') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-times me-1"></i> Rechazar Solicitud
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- Applicant Information -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información del Solicitante</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p class="mb-1 text-muted small">Nombre Completo</p>
                        <p class="mb-0 font-weight-bold">{{ $application->user->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1 text-muted small">Correo Electrónico</p>
                        <p class="mb-0 font-weight-bold">{{ $application->user->email }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1 text-muted small">Teléfono</p>
                        <p class="mb-0 font-weight-bold">{{ $application->user->phone ?? 'No especificado' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1 text-muted small">País</p>
                        <p class="mb-0 font-weight-bold">{{ $application->user->country ?? 'No especificado' }}</p>
                    </div>
                    <div class="col-12 mb-3">
                        <p class="mb-1 text-muted small">Dirección</p>
                        <p class="mb-0 font-weight-bold">{{ $application->user->address ?? 'No especificada' }}</p>
                    </div>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold mb-0">Programa Solicitado</h6>
                    <a href="{{ url('/admin/programs/'.$application->program_id) }}" class="btn btn-sm btn-outline-primary">
                        Ver Programa
                    </a>
                </div>
                
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <h5 class="card-title">{{ $application->program->name }}</h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            {{ $application->program->country }} | 
                            @switch($application->program->category)
                                @case('academic')
                                    <span class="badge bg-primary">Académico</span>
                                    @break
                                @case('volunteer')
                                    <span class="badge bg-success">Voluntariado</span>
                                    @break
                                @case('internship')
                                    <span class="badge bg-info">Prácticas</span>
                                    @break
                                @case('language')
                                    <span class="badge bg-warning">Idiomas</span>
                                    @break
                                @default
                                    <span class="badge bg-secondary">{{ $application->program->category }}</span>
                            @endswitch
                        </h6>
                        <p class="card-text">{{ Str::limit($application->program->description, 150) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Application Requisites -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Requisitos del Programa</h6>
        <a href="{{ route('admin.applications.requisites.index', $application->id) }}" class="btn btn-sm btn-primary">
            <i class="fas fa-tasks me-1"></i> Gestionar Requisitos
        </a>
    </div>
    <div class="card-body">
        @php
            $totalRequisites = $application->requisites()->count();
            $completedRequisites = $application->requisites()->whereIn('status', ['completed', 'verified'])->count();
            $verifiedRequisites = $application->requisites()->where('status', 'verified')->count();
            $pendingRequisites = $application->requisites()->where('status', 'pending')->count();
            $rejectedRequisites = $application->requisites()->where('status', 'rejected')->count();
            $progressPercentage = $totalRequisites > 0 ? round(($completedRequisites / $totalRequisites) * 100) : 0;
        @endphp
        
        <div class="mb-4">
            <h6>Progreso de la Solicitud</h6>
            <div class="progress" style="height: 25px;">
                <div class="progress-bar {{ $progressPercentage < 50 ? 'bg-danger' : ($progressPercentage < 100 ? 'bg-warning' : 'bg-success') }}" 
                     role="progressbar" 
                     style="width: {{ $progressPercentage }}%;" 
                     aria-valuenow="{{ $progressPercentage }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    {{ $progressPercentage }}%
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="card bg-primary text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ $totalRequisites }}</h3>
                                <div>Total</div>
                            </div>
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-success text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ $verifiedRequisites }}</h3>
                                <div>Verificados</div>
                            </div>
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-warning text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ $pendingRequisites }}</h3>
                                <div>Pendientes</div>
                            </div>
                            <i class="fas fa-clock fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card bg-danger text-white h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">{{ $rejectedRequisites }}</h3>
                                <div>Rechazados</div>
                            </div>
                            <i class="fas fa-times-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Application Documents -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Documentos de la Solicitud</h6>
    </div>
    <div class="card-body">
        @if(isset($documents) && count($documents) > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tipo de Documento</th>
                            <th>Archivo</th>
                            <th>Estado</th>
                            <th>Fecha de Subida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $document)
                        <tr>
                            <td>{{ $document->id }}</td>
                            <td>{{ $document->type }}</td>
                            <td>
                                <a href="{{ url('/storage/documents/'.$document->file_path) }}" target="_blank">
                                    <i class="fas fa-file-pdf me-1"></i> {{ $document->file_name }}
                                </a>
                            </td>
                            <td>
                                @switch($document->status)
                                    @case('pending')
                                        <span class="badge bg-warning">Pendiente</span>
                                        @break
                                    @case('verified')
                                        <span class="badge bg-success">Verificado</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">Rechazado</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $document->status }}</span>
                                @endswitch
                            </td>
                            <td>{{ $document->created_at }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ url('/storage/documents/'.$document->file_path) }}" class="btn btn-sm btn-info" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($document->status == 'pending')
                                        <form action="{{ url('/admin/application-documents/'.$document->id.'/verify') }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" title="Verificar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ url('/admin/application-documents/'.$document->id.'/reject') }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-danger" title="Rechazar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i> No hay documentos asociados a esta solicitud.
            </div>
        @endif
    </div>
</div>

<!-- Notes and Comments -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Notas y Comentarios</h6>
    </div>
    <div class="card-body">
        <form action="{{ url('/admin/applications/'.$application->id.'/notes') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="note" class="form-label">Agregar Nota</label>
                <textarea class="form-control" id="note" name="note" rows="3" placeholder="Escriba una nota o comentario sobre esta solicitud..."></textarea>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Guardar Nota
                </button>
            </div>
        </form>
        
        <hr>
        
        <div class="notes-container">
            @if(isset($notes) && count($notes) > 0)
                @foreach($notes as $note)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $note->user->name }}</strong>
                                <span class="text-muted small">{{ $note->created_at }}</span>
                            </div>
                            @if(auth()->id() == $note->user_id)
                                <form action="{{ url('/admin/applications/notes/'.$note->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="card-body">
                            <p class="card-text">{{ $note->content }}</p>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-1"></i> No hay notas o comentarios para esta solicitud.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
