@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-tie me-2"></i>Gestión de Agentes
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.agents.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Crear Agente
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.agents.index') }}" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Nombre o email...">
            </div>
            <div class="col-md-3">
                <label class="form-label d-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Buscar
                </button>
            </div>
            <div class="col-md-3">
                <label class="form-label d-block">&nbsp;</label>
                <a href="{{ route('admin.agents.index') }}" class="btn btn-secondary w-100">
                    <i class="fas fa-times me-2"></i>Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Agentes -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>Lista de Agentes ({{ $agents->total() }})
        </h6>
    </div>
    <div class="card-body">
        @if($agents->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>País</th>
                            <th>Participantes</th>
                            <th>Fecha de Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agents as $agent)
                            <tr>
                                <td>{{ $agent->id }}</td>
                                <td>
                                    <i class="fas fa-user-tie me-2 text-info"></i>
                                    <strong>{{ $agent->name }}</strong>
                                </td>
                                <td>{{ $agent->email }}</td>
                                <td>{{ $agent->phone ?? 'N/A' }}</td>
                                <td>
                                    <i class="fas fa-flag me-1"></i>{{ $agent->country ?? 'N/A' }}
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        {{ $agent->created_participants_count }} participantes
                                    </span>
                                </td>
                                <td>
                                    <small>{{ $agent->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.agents.show', $agent->id) }}" 
                                           class="btn btn-outline-primary"
                                           title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.agents.edit', $agent->id) }}" 
                                           class="btn btn-outline-warning"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-outline-info"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#resetPasswordModal{{ $agent->id }}"
                                                title="Resetear contraseña">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        @if($agent->created_participants_count == 0)
                                        <button type="button" 
                                                class="btn btn-outline-danger"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal{{ $agent->id }}"
                                                title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Resetear Contraseña -->
                            <div class="modal fade" id="resetPasswordModal{{ $agent->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Resetear Contraseña</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro de resetear la contraseña de <strong>{{ $agent->name }}</strong>?</p>
                                            <p class="text-muted">Se generará una nueva contraseña temporal.</p>
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
                            <div class="modal fade" id="deleteModal{{ $agent->id }}" tabindex="-1">
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
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Mostrando {{ $agents->firstItem() }} a {{ $agents->lastItem() }} 
                    de {{ $agents->total() }} agentes
                </div>
                <div>
                    {{ $agents->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay agentes registrados</h5>
                <p class="text-muted">Comienza creando el primer agente</p>
                <a href="{{ route('admin.agents.create') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i>Crear Primer Agente
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
