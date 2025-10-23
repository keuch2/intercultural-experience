@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        {{ isset($pageTitle) ? $pageTitle : 'Gestión de Usuarios' }}
        @if(isset($programType))
            <span class="badge bg-{{ $programType == 'IE' ? 'info' : 'warning' }} ms-2">{{ $programType }}</span>
        @endif
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ url('/admin/users/export') }}{{ isset($programType) ? '?program_type='.$programType : '' }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-download me-1"></i> Exportar
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
    </div>
    <div class="card-body">
        <form action="{{ url('/admin/users') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Nombre, email...">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Estado</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i> Filtrar
                </button>
                <a href="{{ url('/admin/users') }}" class="btn btn-secondary">
                    <i class="fas fa-redo me-1"></i> Reiniciar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Usuarios</h6>
        <a href="{{ url('/admin/users/create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Nuevo Usuario
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Fecha de Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($users) && count($users) > 0)
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-primary">Administrador</span>
                                @elseif($user->role === 'finance')
                                    <span class="badge bg-success">Finanzas</span>
                                @elseif($user->role === 'agent')
                                    <span class="badge bg-info">Agente</span>
                                @else
                                    <span class="badge bg-secondary">Usuario</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ url('/admin/users/'.$user->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ url('/admin/users/'.$user->id.'/edit') }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $user->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $user->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $user->id }}">Confirmar Eliminación</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                ¿Está seguro de que desea eliminar al usuario <strong>{{ $user->name }}</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <form action="{{ url('/admin/users/'.$user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center">No se encontraron usuarios</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($users) && $users->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Aquí puedes agregar scripts específicos para la gestión de usuarios
</script>
@endpush
