@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Participante: {{ $participant->name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.participants.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver a Lista
            </a>
            <a href="{{ route('admin.participants.edit', $participant) }}" class="btn btn-sm btn-warning">
                <i class="fas fa-edit me-1"></i> Editar
            </a>
            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="fas fa-trash me-1"></i> Eliminar
            </button>
        </div>
    </div>
</div>

<div class="row">
    <!-- Información Personal -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información Personal</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="font-weight-bold" width="40%">ID:</td>
                        <td>{{ $participant->id }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Nombre:</td>
                        <td>{{ $participant->name }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Email:</td>
                        <td>{{ $participant->email }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Teléfono:</td>
                        <td>{{ $participant->phone ?? 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Fecha de Nacimiento:</td>
                        <td>{{ $participant->birth_date ? $participant->birth_date->format('d/m/Y') : 'No especificada' }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Nacionalidad:</td>
                        <td>{{ $participant->nationality ?? 'No especificada' }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Email Verificado:</td>
                        <td>
                            @if($participant->email_verified_at)
                                <span class="badge bg-success">Sí - {{ $participant->email_verified_at->format('d/m/Y') }}</span>
                            @else
                                <span class="badge bg-warning">No verificado</span>
                            @endif
                        </td>
                    </tr>
                    @if($participant->created_by_agent_id)
                    <tr>
                        <td class="font-weight-bold">Creado por:</td>
                        <td>
                            <span class="badge bg-info">
                                <i class="fas fa-user-tie me-1"></i>
                                Agente: {{ $participant->createdByAgent->name ?? 'Agente eliminado' }}
                            </span>
                        </td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Información de Ubicación -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">Ubicación y Contacto</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="font-weight-bold" width="40%">Ciudad:</td>
                        <td>{{ $participant->city ?? 'No especificada' }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">País:</td>
                        <td>{{ $participant->country ?? 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Dirección:</td>
                        <td>
                            @if($participant->address)
                                {{ $participant->address }}
                            @else
                                <span class="text-muted">No especificada</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Registro:</td>
                        <td>{{ $participant->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Última Actualización:</td>
                        <td>{{ $participant->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Información Académica -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">Información Académica</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="font-weight-bold" width="40%">Nivel Académico:</td>
                        <td>
                            @if($participant->academic_level)
                                <span class="badge bg-info">{{ ucfirst($participant->academic_level) }}</span>
                            @else
                                <span class="text-muted">No especificado</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Nivel de Inglés:</td>
                        <td>
                            @if($participant->english_level)
                                <span class="badge bg-success">{{ ucfirst($participant->english_level) }}</span>
                            @else
                                <span class="text-muted">No especificado</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">Estadísticas</h6>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td class="font-weight-bold" width="40%">Aplicaciones:</td>
                        <td>
                            <span class="badge bg-secondary">{{ $participant->applications->count() ?? 0 }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Puntos Totales:</td>
                        <td>
                            <span class="badge bg-primary">{{ $participant->total_points ?? 0 }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">Puntos Disponibles:</td>
                        <td>
                            <span class="badge bg-success">{{ $participant->available_points ?? 0 }}</span>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Applications Section -->
@if($participant->applications->count() > 0)
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Aplicaciones del Participante</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Programa</th>
                        <th>Estado</th>
                        <th>Fecha de Aplicación</th>
                        <th>Progreso</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($participant->applications as $application)
                    <tr>
                        <td>{{ $application->program->name ?? 'Programa no encontrado' }}</td>
                        <td>
                            <span class="badge {{ $application->status === 'approved' ? 'bg-success' : ($application->status === 'rejected' ? 'bg-danger' : 'bg-warning') }}">
                                {{ ucfirst($application->status) }}
                            </span>
                        </td>
                        <td>{{ $application->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $application->progress_percentage ?? 0 }}%">
                                    {{ $application->progress_percentage ?? 0 }}%
                                </div>
                            </div>
                        </td>
                        <td>
                            <a href="{{ route('admin.applications.show', $application) }}" class="btn btn-sm btn-primary">
                                Ver Detalles
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Está seguro de que desea eliminar al participante <strong>{{ $participant->name }}</strong>?
                <br><br>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    <strong>Advertencia:</strong> Esta acción eliminará también:
                    <ul class="mb-0 mt-2">
                        <li>Todas las aplicaciones del participante ({{ $participant->applications->count() }})</li>
                        <li>Historial de puntos y recompensas</li>
                        <li>Documentos subidos</li>
                    </ul>
                </div>
                <small class="text-muted">Esta acción no se puede deshacer.</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('admin.participants.destroy', $participant) }}" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar Participante</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
