@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Recompensas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ url('/admin/rewards/export') }}" class="btn btn-sm btn-outline-secondary">
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
        <form action="{{ url('/admin/rewards') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Nombre, descripción...">
            </div>
            <div class="col-md-4">
                <label for="cost_min" class="form-label">Costo Mínimo</label>
                <input type="number" class="form-control" id="cost_min" name="cost_min" value="{{ request('cost_min') }}" placeholder="Costo mínimo">
            </div>
            <div class="col-md-4">
                <label for="cost_max" class="form-label">Costo Máximo</label>
                <input type="number" class="form-control" id="cost_max" name="cost_max" value="{{ request('cost_max') }}" placeholder="Costo máximo">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Estado</label>
                <select class="form-select" id="status" name="is_active">
                    <option value="">Todos los estados</option>
                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="col-12 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i> Filtrar
                </button>
                <a href="{{ url('/admin/rewards') }}" class="btn btn-secondary">
                    <i class="fas fa-redo me-1"></i> Reiniciar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Rewards Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Recompensas</h6>
        <a href="{{ url('/admin/rewards/create') }}" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> Nueva Recompensa
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Costo (Puntos)</th>
                        <th>Estado</th>
                        <th>Fecha de Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($rewards) && count($rewards) > 0)
                        @foreach($rewards as $reward)
                        <tr>
                            <td>{{ $reward->id }}</td>
                            <td>{{ $reward->name }}</td>
                            <td>{{ Str::limit($reward->description, 50) }}</td>
                            <td>{{ $reward->cost }}</td>
                            <td>
                                @if($reward->is_active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td>{{ $reward->created_at }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ url('/admin/rewards/'.$reward->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ url('/admin/rewards/'.$reward->id.'/edit') }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $reward->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $reward->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $reward->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="deleteModalLabel{{ $reward->id }}">Confirmar Eliminación</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                ¿Está seguro de que desea eliminar la recompensa <strong>{{ $reward->name }}</strong>?
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <form action="{{ url('/admin/rewards/'.$reward->id) }}" method="POST" class="d-inline">
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
                            <td colspan="7" class="text-center">No se encontraron recompensas</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($rewards) && $rewards->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $rewards->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
