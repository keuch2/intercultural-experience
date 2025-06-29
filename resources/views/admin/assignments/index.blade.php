@extends('layouts.admin')

@section('title', 'Asignaciones de Programas')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-user-plus fa-sm text-gray-400 mr-2"></i>
        Asignaciones de Programas
    </h1>
    <div class="d-sm-flex">
        <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary shadow-sm mr-2">
            <i class="fas fa-plus fa-sm"></i> Nueva Asignación
        </a>
        <button type="button" class="btn btn-success shadow-sm" data-toggle="modal" data-target="#bulkAssignModal">
            <i class="fas fa-users fa-sm"></i> Asignación Masiva
        </button>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Asignaciones</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendientes de Aplicar</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['assigned'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Aplicaciones Enviadas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['applied'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aceptados</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['accepted'] }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-filter mr-2"></i>Filtros de Búsqueda
        </h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row">
            <div class="col-md-3 mb-3">
                <label for="program_id" class="form-label">Programa</label>
                <select name="program_id" id="program_id" class="form-control">
                    <option value="">Todos los programas</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label for="status" class="form-label">Estado</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label for="user_search" class="form-label">Usuario</label>
                <input type="text" name="user_search" id="user_search" class="form-control" 
                       placeholder="Nombre o email" value="{{ request('user_search') }}">
            </div>

            <div class="col-md-3 mb-3">
                <label for="assigned_by" class="form-label">Asignado por</label>
                <select name="assigned_by" id="assigned_by" class="form-control">
                    <option value="">Todos los admins</option>
                    @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ request('assigned_by') == $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="priority" value="yes" id="priority" 
                           {{ request('priority') == 'yes' ? 'checked' : '' }}>
                    <label class="form-check-label" for="priority">
                        Solo asignaciones prioritarias
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="overdue" value="yes" id="overdue" 
                           {{ request('overdue') == 'yes' ? 'checked' : '' }}>
                    <label class="form-check-label" for="overdue">
                        Solo vencidas
                    </label>
                </div>
            </div>

            <div class="col-md-6">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Asignaciones -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Lista de Asignaciones</h6>
    </div>
    <div class="card-body">
        @if($assignments->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Programa</th>
                            <th>Estado</th>
                            <th>Asignado por</th>
                            <th>Fecha Asignación</th>
                            <th>Fecha Límite</th>
                            <th>Progreso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignment)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <div class="font-weight-bold">{{ $assignment->user->name }}</div>
                                            <div class="small text-gray-500">{{ $assignment->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="font-weight-bold">{{ $assignment->program->name }}</div>
                                        <div class="small text-gray-500">{{ $assignment->program->country }}</div>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'assigned' => 'warning',
                                            'applied' => 'info',
                                            'under_review' => 'primary',
                                            'accepted' => 'success',
                                            'rejected' => 'danger',
                                            'completed' => 'dark',
                                            'cancelled' => 'secondary'
                                        ];
                                        $color = $statusColors[$assignment->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-{{ $color }}">
                                        {{ $assignment->status_name }}
                                    </span>
                                    @if($assignment->is_priority)
                                        <span class="badge badge-warning badge-sm ml-1">
                                            <i class="fas fa-star"></i> Prioridad
                                        </span>
                                    @endif
                                    @if($assignment->is_overdue)
                                        <span class="badge badge-danger badge-sm ml-1">
                                            <i class="fas fa-exclamation-triangle"></i> Vencida
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">{{ $assignment->assignedBy->name }}</div>
                                </td>
                                <td>
                                    <div class="small">{{ $assignment->assigned_at ? $assignment->assigned_at->format('d/m/Y H:i') : 'N/A' }}</div>
                                </td>
                                <td>
                                    @if($assignment->application_deadline)
                                        <div class="small {{ $assignment->is_overdue ? 'text-danger' : '' }}">
                                            {{ $assignment->application_deadline->format('d/m/Y') }}
                                            @if($assignment->days_until_deadline !== null)
                                                <br><span class="text-muted">
                                                    @if($assignment->days_until_deadline >= 0)
                                                        {{ $assignment->days_until_deadline }} días restantes
                                                    @else
                                                        {{ abs($assignment->days_until_deadline) }} días vencida
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">Sin límite</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $color }}" role="progressbar" 
                                             style="width: {{ $assignment->progress_percentage }}%" 
                                             aria-valuenow="{{ $assignment->progress_percentage }}" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            {{ $assignment->progress_percentage }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.assignments.show', $assignment) }}" 
                                           class="btn btn-info btn-sm" title="Ver detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.assignments.edit', $assignment) }}" 
                                           class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($assignment->canBeCancelled())
                                            <form method="POST" action="{{ route('admin.assignments.destroy', $assignment) }}" 
                                                  style="display: inline;" 
                                                  onsubmit="return confirm('¿Estás seguro de eliminar esta asignación?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                    <i class="fas fa-trash"></i>
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

            <!-- Paginación -->
            <div class="d-flex justify-content-center">
                {{ $assignments->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-inbox fa-3x text-gray-300 mb-3"></i>
                <h5 class="text-gray-500">No se encontraron asignaciones</h5>
                <p class="text-gray-400">
                    @if(request()->hasAny(['program_id', 'status', 'user_search', 'assigned_by', 'priority', 'overdue']))
                        Intenta ajustar los filtros de búsqueda.
                    @else
                        Comienza creando una nueva asignación.
                    @endif
                </p>
                <a href="{{ route('admin.assignments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Asignación
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Modal de Asignación Masiva -->
<div class="modal fade" id="bulkAssignModal" tabindex="-1" role="dialog" aria-labelledby="bulkAssignModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.assignments.bulk-assign') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="bulkAssignModalLabel">
                        <i class="fas fa-users mr-2"></i>Asignación Masiva de Programas
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="bulk_program_id">Programa</label>
                        <select name="program_id" id="bulk_program_id" class="form-control" required>
                            <option value="">Selecciona un programa</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }} ({{ $program->country }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bulk_user_ids">Usuarios</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select_all_users">
                                <label class="form-check-label font-weight-bold" for="select_all_users">
                                    Seleccionar todos
                                </label>
                            </div>
                            <hr>
                            <!-- Aquí cargaremos los usuarios dinámicamente -->
                            <div id="users_list">
                                <div class="text-center">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando usuarios...
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bulk_assignment_notes">Notas de Asignación</label>
                        <textarea name="assignment_notes" id="bulk_assignment_notes" class="form-control" rows="3" 
                                  placeholder="Opcional: Notas adicionales sobre esta asignación masiva"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="bulk_application_deadline">Fecha Límite</label>
                                <input type="date" name="application_deadline" id="bulk_application_deadline" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_priority" value="1" id="bulk_is_priority">
                                    <label class="form-check-label" for="bulk_is_priority">
                                        Asignación prioritaria
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="send_notifications" value="1" id="bulk_send_notifications" checked>
                                    <label class="form-check-label" for="bulk_send_notifications">
                                        Enviar notificaciones
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-users"></i> Asignar a Usuarios Seleccionados
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cargar usuarios cuando se abre el modal
    $('#bulkAssignModal').on('shown.bs.modal', function() {
        loadUsers();
    });

    // Seleccionar/deseleccionar todos los usuarios
    document.getElementById('select_all_users').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('input[name="user_ids[]"]');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    function loadUsers() {
        fetch('/api/admin/users')
            .then(response => response.json())
            .then(data => {
                const usersList = document.getElementById('users_list');
                
                if (data.data && data.data.length > 0) {
                    let html = '';
                    data.data.forEach(user => {
                        if (user.role === 'user') {
                            html += `
                                <div class="form-check">
                                    <input class="form-check-input user-checkbox" type="checkbox" 
                                           name="user_ids[]" value="${user.id}" id="user_${user.id}">
                                    <label class="form-check-label" for="user_${user.id}">
                                        ${user.name} (${user.email})
                                    </label>
                                </div>
                            `;
                        }
                    });
                    usersList.innerHTML = html;
                } else {
                    usersList.innerHTML = '<div class="text-muted">No hay usuarios disponibles</div>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('users_list').innerHTML = 
                    '<div class="text-danger">Error al cargar usuarios</div>';
            });
    }
});
</script>
@endsection 