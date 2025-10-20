@extends('layouts.agent')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-users me-2"></i>Mis Participantes
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('agent.participants.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i>Crear Participante
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('agent.participants.index') }}" class="row g-3">
            <div class="col-md-5">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Nombre, email o teléfono...">
            </div>
            <div class="col-md-4">
                <label for="country" class="form-label">País</label>
                <select class="form-select" id="country" name="country">
                    <option value="">Todos los países</option>
                    @foreach($countries as $country)
                        <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                            {{ $country }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label d-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Participantes -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>Lista de Participantes ({{ $participants->total() }})
        </h6>
    </div>
    <div class="card-body">
        @if($participants->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>País</th>
                            <th>Teléfono</th>
                            <th>Programas</th>
                            <th>Fecha de Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($participants as $participant)
                            <tr>
                                <td>
                                    <i class="fas fa-user-circle me-2 text-primary"></i>
                                    <strong>{{ $participant->name }}</strong>
                                </td>
                                <td>{{ $participant->email }}</td>
                                <td>
                                    <i class="fas fa-flag me-1"></i>{{ $participant->country ?? 'N/A' }}
                                </td>
                                <td>{{ $participant->phone ?? 'N/A' }}</td>
                                <td>
                                    @if($participant->programAssignments->count() > 0)
                                        <span class="badge bg-success">
                                            {{ $participant->programAssignments->count() }} 
                                            {{ $participant->programAssignments->count() == 1 ? 'programa' : 'programas' }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Sin programas</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $participant->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('agent.participants.show', $participant->id) }}" 
                                           class="btn btn-outline-primary"
                                           title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('agent.participants.assign-program', $participant->id) }}" 
                                           class="btn btn-outline-success"
                                           title="Asignar programa">
                                            <i class="fas fa-plus-circle"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Mostrando {{ $participants->firstItem() }} a {{ $participants->lastItem() }} 
                    de {{ $participants->total() }} participantes
                </div>
                <div>
                    {{ $participants->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay participantes registrados</h5>
                <p class="text-muted">Comienza creando tu primer participante</p>
                <a href="{{ route('agent.participants.create') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-user-plus me-2"></i>Crear Primer Participante
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
