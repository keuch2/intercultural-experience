@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        @if(isset($programCategory) && $programCategory == 'IE')
            Participantes IE
        @elseif(isset($programCategory) && $programCategory == 'YFU')
            Participantes YFU
        @else
            Gestión de Participantes
        @endif
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.participants.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Nuevo Participante
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
        <form action="{{ route('admin.participants.index') }}" method="GET" class="row g-3">
            @if(request('program_category'))
                <input type="hidden" name="program_category" value="{{ request('program_category') }}">
            @endif
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Nombre, email, ciudad...">
            </div>
            <div class="col-md-3">
                <label for="country" class="form-label">País</label>
                <input type="text" class="form-control" id="country" name="country" value="{{ request('country') }}" placeholder="País">
            </div>
            <div class="col-md-5 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i> Filtrar
                </button>
                <a href="{{ route('admin.participants.index') }}" class="btn btn-secondary">
                    <i class="fas fa-redo me-1"></i> Reiniciar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Participants Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Participantes ({{ $participants->total() }})</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Ciudad/País</th>
                        <th>Programa</th>
                        <th>Estado</th>
                        <th>Fecha de Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $participant)
                    <tr>
                        <td>{{ $participant->id }}</td>
                        <td>{{ $participant->name }}</td>
                        <td>{{ $participant->email }}</td>
                        <td>
                            @if($participant->city && $participant->country)
                                {{ $participant->city }}, {{ $participant->country }}
                            @elseif($participant->city)
                                {{ $participant->city }}
                            @elseif($participant->country)
                                {{ $participant->country }}
                            @else
                                <span class="text-muted">No especificado</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $latestApplication = $participant->applications()->with('program')->latest()->first();
                            @endphp
                            @if($latestApplication && $latestApplication->program)
                                <a href="{{ route('admin.applications.show', $latestApplication->id) }}" class="text-decoration-none">
                                    {{ $latestApplication->program->name }}
                                </a>
                            @else
                                <span class="text-muted">Sin aplicación</span>
                            @endif
                        </td>
                        <td>
                            @if($latestApplication)
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'in_progress' => 'info',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'Pendiente',
                                        'in_progress' => 'En Proceso',
                                        'approved' => 'Aprobado',
                                        'rejected' => 'Rechazado',
                                    ];
                                    $color = $statusColors[$latestApplication->status] ?? 'secondary';
                                    $label = $statusLabels[$latestApplication->status] ?? ucfirst($latestApplication->status);
                                @endphp
                                <span class="badge bg-{{ $color }}">{{ $label }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $participant->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.participants.show', $participant) }}" class="btn btn-sm btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.participants.edit', $participant) }}" class="btn btn-sm btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.assignments.create', ['user_id' => $participant->id]) }}" class="btn btn-sm btn-success" title="Asignar Programa">
                                    <i class="fas fa-user-plus"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $participant->id }}" title="Eliminar">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>

                            <!-- Delete Modal -->
                            <div class="modal fade" id="deleteModal{{ $participant->id }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $participant->id }}" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="deleteModalLabel{{ $participant->id }}">Confirmar Eliminación</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            ¿Está seguro de que desea eliminar al participante <strong>{{ $participant->name }}</strong>?
                                            <br><small class="text-muted">Esta acción no se puede deshacer.</small>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            <form action="{{ route('admin.participants.destroy', $participant) }}" method="POST" class="d-inline">
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
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">No se encontraron participantes</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($participants->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $participants->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
