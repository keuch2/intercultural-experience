@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-tie me-2"></i>{{ $agent->name }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.agents.edit', $agent->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Editar
            </a>
            <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                <i class="fas fa-key me-2"></i>Resetear Contraseña
            </button>
            @if($agent->created_participants_count == 0)
            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                <i class="fas fa-trash me-2"></i>Eliminar
            </button>
            @endif
        </div>
        <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información del Agente -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user me-2"></i>Información Personal
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="fas fa-user-tie fa-5x text-info"></i>
                </div>
                
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted"><i class="fas fa-id-badge me-2"></i>ID:</td>
                        <td><strong>{{ $agent->id }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-envelope me-2"></i>Email:</td>
                        <td>{{ $agent->email }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-phone me-2"></i>Teléfono:</td>
                        <td>{{ $agent->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-flag me-2"></i>País:</td>
                        <td>{{ $agent->country ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-city me-2"></i>Ciudad:</td>
                        <td>{{ $agent->city ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-globe me-2"></i>Nacionalidad:</td>
                        <td>{{ $agent->nationality ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-calendar me-2"></i>Registro:</td>
                        <td>{{ $agent->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card shadow mt-3">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-chart-bar me-2"></i>Estadísticas
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <h3 class="text-primary">{{ $agent->created_participants_count }}</h3>
                    <p class="text-muted mb-0">Participantes Creados</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Participantes Creados -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-users me-2"></i>Participantes Creados ({{ $agent->createdParticipants->count() }})
                </h6>
            </div>
            <div class="card-body">
                @if($agent->createdParticipants->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>País</th>
                                    <th>Programas</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($agent->createdParticipants as $participant)
                                    <tr>
                                        <td>
                                            <i class="fas fa-user-circle me-2 text-primary"></i>
                                            <strong>{{ $participant->name }}</strong>
                                        </td>
                                        <td>{{ $participant->email }}</td>
                                        <td>{{ $participant->country ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-success">
                                                {{ $participant->programAssignments->count() }} programas
                                            </span>
                                        </td>
                                        <td>
                                            <small>{{ $participant->created_at->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.participants.show', $participant->id) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Sin Participantes</h5>
                        <p class="text-muted">Este agente aún no ha creado participantes</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Resetear Contraseña -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resetear Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de resetear la contraseña de <strong>{{ $agent->name }}</strong>?</p>
                <p class="text-muted">Se generará una nueva contraseña temporal que se mostrará en pantalla.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('admin.agents.reset-password', $agent->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-info">Resetear Contraseña</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
@if($agent->created_participants_count == 0)
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Eliminar Agente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de eliminar al agente <strong>{{ $agent->name }}</strong>?</p>
                <p class="text-danger">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form action="{{ route('admin.agents.destroy', $agent->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
