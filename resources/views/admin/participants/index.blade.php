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
        <div class="small text-muted">
            Estado de cada postulación:
            <span class="badge bg-warning text-dark">En curso</span>
            <span class="badge bg-success text-white">Aprobada</span>
            <span class="badge bg-danger text-white">Rechazada</span>
            <span class="badge bg-secondary text-white">Finalizada</span>
        </div>
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
                        <th>Programas</th>
                        <th>Fecha de Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $participant)
                    <tr>
                        <td>{{ $participant->id }}</td>
                        <td>
                            <a href="{{ route('admin.participants.show', $participant->id) }}" class="text-decoration-none">
                                {{ $participant->name }}
                            </a>
                        </td>
                        <td>{{ $participant->email ?? 'N/A' }}</td>
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
                            {{-- Una etiqueta por postulación; el color indica el estado de esa
                                 postulación (verde aprobada, amarillo en curso, rojo rechazada). --}}
                            @forelse($participant->applications as $application)
                                @php
                                    $status = $application->completed_at ? 'completed' : $application->status;
                                    $color = match ($status) {
                                        'approved' => 'success',
                                        'rejected', 'cancelled', 'withdrawn' => 'danger',
                                        'completed' => 'secondary',
                                        default => 'warning',
                                    };
                                    $label = match ($status) {
                                        'approved' => 'Aprobada',
                                        'in_review' => 'En revisión',
                                        'rejected' => 'Rechazada',
                                        'cancelled' => 'Cancelada',
                                        'withdrawn' => 'Retirada',
                                        'completed' => 'Finalizada',
                                        'pending' => 'Pendiente',
                                        default => ucfirst((string) $status),
                                    };
                                @endphp
                                <span class="badge bg-{{ $color }} {{ $color === 'warning' ? 'text-dark' : 'text-white' }} mb-1"
                                      title="{{ optional($application->program)->name ?? 'Sin programa' }} — {{ $label }}">
                                    {{ optional($application->program)->name ?? 'Sin programa' }}
                                </span>
                            @empty
                                <span class="text-muted">Sin programa</span>
                            @endforelse
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
                                <form action="{{ route('admin.participants.destroy', $participant) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar este participante?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">No se encontraron participantes</td>
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
