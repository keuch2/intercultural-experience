@extends('layouts.admin')

@section('title', 'Timeline - Aplicación #' . $application->id)

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-project-diagram"></i> Timeline de Aplicación
        </h1>
        <div>
            <a href="{{ route('admin.applications.timeline-dashboard') }}" class="btn btn-sm btn-info">
                <i class="fas fa-chart-line"></i> Dashboard Timeline
            </a>
            <a href="{{ route('admin.applications.show', $application->id) }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a Aplicación
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Application Info Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Información de la Aplicación</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>ID:</strong> #{{ $application->id }}<br>
                    <strong>Participante:</strong> {{ $application->user->name }}<br>
                    <strong>Email:</strong> {{ $application->user->email }}
                </div>
                <div class="col-md-3">
                    <strong>Programa:</strong> {{ $application->program->name }}<br>
                    <strong>Categoría:</strong> {{ $application->program->main_category }}<br>
                    <strong>Fecha de Aplicación:</strong> {{ $application->applied_at ? $application->applied_at->format('d/m/Y') : '-' }}
                </div>
                <div class="col-md-3">
                    <strong>Estado Actual:</strong><br>
                    <span class="badge badge-lg badge-{{ 
                        $application->status == 'approved' ? 'success' : 
                        ($application->status == 'rejected' ? 'danger' : 
                        ($application->status == 'under_review' ? 'warning' : 'info'))
                    }}">
                        {{ strtoupper($application->status) }}
                    </span><br>
                    <small class="text-muted">Hace {{ $daysInStatus }} día(s)</small>
                </div>
                <div class="col-md-3">
                    <strong>Progreso General:</strong><br>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-{{ $progress >= 75 ? 'success' : ($progress >= 50 ? 'info' : 'warning') }}" 
                             style="width: {{ $progress }}%">
                            {{ round($progress) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Flowchart -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Workflow Visual</h6>
            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updateStatusModal">
                <i class="fas fa-edit"></i> Actualizar Estado
            </button>
        </div>
        <div class="card-body">
            <div class="timeline-container">
                @foreach($steps as $index => $step)
                    @php
                        $isCompleted = array_search($application->status, array_column($steps, 'key')) > $index;
                        $isCurrent = $application->status == $step['key'];
                        $isPending = array_search($application->status, array_column($steps, 'key')) < $index;
                    @endphp

                    <div class="timeline-item {{ $isCompleted ? 'completed' : ($isCurrent ? 'current' : 'pending') }}">
                        <div class="timeline-icon bg-{{ $step['color'] }}">
                            <i class="fas {{ $step['icon'] }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <h5 class="mb-1">
                                    {{ $step['label'] }}
                                    @if($isCurrent)
                                        <span class="badge badge-{{ $step['color'] }} ml-2">ACTUAL</span>
                                    @elseif($isCompleted)
                                        <i class="fas fa-check-circle text-success ml-2"></i>
                                    @endif
                                </h5>
                                <small class="text-muted">
                                    Paso {{ $index + 1 }} de {{ count($steps) }}
                                </small>
                            </div>
                            @if($isCurrent)
                                <div class="alert alert-{{ $step['color'] }} mt-2 mb-0">
                                    <i class="fas fa-clock"></i>
                                    En este estado por <strong>{{ $daysInStatus }} día(s)</strong>
                                    @if($daysInStatus > 7)
                                        <span class="badge badge-danger ml-2">⚠ Requiere atención</span>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($index < count($steps) - 1)
                        <div class="timeline-connector {{ $isCompleted ? 'completed' : '' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Status History -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history"></i> Historial de Cambios
            </h6>
        </div>
        <div class="card-body">
            @if(isset($application->statusHistory) && $application->statusHistory->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Estado Anterior</th>
                                <th>Nuevo Estado</th>
                                <th>Cambiado Por</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($application->statusHistory as $history)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($history->old_status)
                                            <span class="badge badge-secondary">{{ strtoupper($history->old_status) }}</span>
                                        @else
                                            <span class="text-muted">Inicial</span>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-info">{{ strtoupper($history->new_status) }}</span></td>
                                    <td>{{ $history->changedBy->name ?? 'Sistema' }}</td>
                                    <td>{{ $history->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No hay historial de cambios de estado aún.
                </div>
            @endif
        </div>
    </div>

    <!-- Related Documents -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-alt"></i> Documentos ({{ $application->documents->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($application->documents->count() > 0)
                        <div class="list-group">
                            @foreach($application->documents as $doc)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}</strong><br>
                                        <small class="text-muted">{{ $doc->created_at->format('d/m/Y') }}</small>
                                    </div>
                                    <span class="badge badge-{{ $doc->status == 'verified' ? 'success' : ($doc->status == 'rejected' ? 'danger' : 'warning') }} badge-pill">
                                        {{ strtoupper($doc->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No hay documentos adjuntos.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tasks"></i> Acciones Pendientes
                    </h6>
                </div>
                <div class="card-body">
                    @if($application->status == 'documents_pending')
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Documentos pendientes de subir
                        </div>
                    @elseif($application->status == 'interview_scheduled')
                        <div class="alert alert-info">
                            <i class="fas fa-calendar-check"></i>
                            Entrevista programada - Pendiente de realizar
                        </div>
                    @elseif($application->status == 'under_review')
                        <div class="alert alert-warning">
                            <i class="fas fa-search"></i>
                            En proceso de revisión
                        </div>
                    @elseif($application->status == 'approved')
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            Aplicación aprobada - Proceso completado
                        </div>
                    @elseif($application->status == 'rejected')
                        <div class="alert alert-danger">
                            <i class="fas fa-times-circle"></i>
                            Aplicación rechazada
                        </div>
                    @else
                        <p class="text-muted">No hay acciones pendientes en este momento.</p>
                    @endif

                    @if($daysInStatus > 7 && !in_array($application->status, ['approved', 'rejected']))
                        <div class="alert alert-danger mt-3">
                            <strong><i class="fas fa-exclamation-circle"></i> Atención:</strong><br>
                            Esta aplicación lleva <strong>{{ $daysInStatus }} días</strong> en el estado actual.
                            Considera actualizar el estado o contactar al participante.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Update Status -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.applications.update-status', $application->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Actualizar Estado</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nuevo Estado *</label>
                        <select name="status" class="form-control" required>
                            @foreach($steps as $step)
                                <option value="{{ $step['key'] }}" {{ $application->status == $step['key'] ? 'selected' : '' }}>
                                    {{ $step['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notas (Opcional)</label>
                        <textarea name="notes" class="form-control" rows="4" 
                                  placeholder="Agrega notas sobre este cambio de estado..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.timeline-container {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    position: relative;
    display: flex;
    align-items: flex-start;
    margin-bottom: 30px;
}

.timeline-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    flex-shrink: 0;
    z-index: 2;
    box-shadow: 0 0 0 4px white;
}

.timeline-content {
    flex: 1;
    margin-left: 20px;
    padding: 15px 20px;
    background: #f8f9fc;
    border-radius: 8px;
    border-left: 3px solid #e3e6f0;
}

.timeline-item.current .timeline-content {
    background: #fff4e6;
    border-left-color: #f6c23e;
}

.timeline-item.completed .timeline-content {
    background: #e8f5e9;
    border-left-color: #1cc88a;
}

.timeline-connector {
    position: absolute;
    left: 25px;
    width: 2px;
    height: 60px;
    background: #e3e6f0;
    top: 50px;
}

.timeline-connector.completed {
    background: #1cc88a;
}

.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endsection
