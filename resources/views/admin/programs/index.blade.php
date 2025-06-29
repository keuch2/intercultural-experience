@extends('layouts.admin')

@section('title', 'Gestión de Programas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Gestión de Programas</h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.programs.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Nuevo Programa
                            </a>
                            <a href="#" class="btn btn-outline-secondary">
                                <i class="fas fa-download"></i> Exportar
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="card-body border-bottom">
                    <form action="{{ route('admin.programs.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Nombre, descripción...">
                        </div>
                        <div class="col-md-2">
                            <label for="country" class="form-label">País</label>
                            <select class="form-select" id="country" name="country">
                                <option value="">Todos</option>
                                <option value="España" {{ request('country') == 'España' ? 'selected' : '' }}>España</option>
                                <option value="México" {{ request('country') == 'México' ? 'selected' : '' }}>México</option>
                                <option value="Argentina" {{ request('country') == 'Argentina' ? 'selected' : '' }}>Argentina</option>
                                <option value="Estados Unidos" {{ request('country') == 'Estados Unidos' ? 'selected' : '' }}>Estados Unidos</option>
                                <option value="Francia" {{ request('country') == 'Francia' ? 'selected' : '' }}>Francia</option>
                                <option value="Alemania" {{ request('country') == 'Alemania' ? 'selected' : '' }}>Alemania</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="category" class="form-label">Categoría</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Todas</option>
                                <option value="academic" {{ request('category') == 'academic' ? 'selected' : '' }}>Académico</option>
                                <option value="volunteer" {{ request('category') == 'volunteer' ? 'selected' : '' }}>Voluntariado</option>
                                <option value="internship" {{ request('category') == 'internship' ? 'selected' : '' }}>Prácticas</option>
                                <option value="language" {{ request('category') == 'language' ? 'selected' : '' }}>Idiomas</option>
                                <option value="cultural" {{ request('category') == 'cultural' ? 'selected' : '' }}>Cultural</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="currency" class="form-label">Moneda</label>
                            <select class="form-select" id="currency" name="currency">
                                <option value="">Todas</option>
                                @if(isset($currencies))
                                    @foreach($currencies as $currency)
                                        <option value="{{ $currency->code }}" {{ request('currency') == $currency->code ? 'selected' : '' }}>
                                            {{ $currency->code }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="is_active">
                                <option value="">Todos</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Programa</th>
                                    <th>País/Ciudad</th>
                                    <th>Categoría</th>
                                    <th>Costo</th>
                                    <th>Capacidad</th>
                                    <th>Fechas</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($programs as $program)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark">#{{ $program->id }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $program->name }}</strong>
                                            @if($program->location)
                                                <br><small class="text-muted">{{ $program->location }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ $program->country }}</span>
                                        @if($program->location)
                                            <br><small>{{ $program->location }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @switch($program->category)
                                            @case('academic')
                                                <span class="badge bg-primary">Académico</span>
                                                @break
                                            @case('volunteer')
                                                <span class="badge bg-success">Voluntariado</span>
                                                @break
                                            @case('internship')
                                                <span class="badge bg-warning">Prácticas</span>
                                                @break
                                            @case('language')
                                                <span class="badge bg-info">Idiomas</span>
                                                @break
                                            @case('cultural')
                                                <span class="badge bg-purple">Cultural</span>
                                                @break
                                            @case('research')
                                                <span class="badge bg-dark">Investigación</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ ucfirst($program->category) }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($program->cost && $program->currency)
                                            <div>
                                                <strong>{{ $program->formatted_cost }}</strong>
                                                <br>
                                                <small class="text-muted">≈ {{ $program->formatted_cost_in_pyg }}</small>
                                            </div>
                                        @elseif($program->cost)
                                            <span class="text-muted">{{ number_format($program->cost, 2) }}</span>
                                        @else
                                            <span class="text-muted">No definido</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($program->capacity)
                                            <div class="text-center">
                                                <div class="fw-bold">{{ $program->available_spots ?? $program->capacity }}/{{ $program->capacity }}</div>
                                                <small class="text-muted">disponibles</small>
                                            </div>
                                        @else
                                            <span class="text-muted">No definido</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($program->start_date)
                                            <div>
                                                <small class="text-muted">Inicio:</small><br>
                                                {{ $program->start_date->format('d/m/Y') }}
                                            </div>
                                            @if($program->application_deadline)
                                                <div class="mt-1">
                                                    <small class="text-muted">Límite:</small><br>
                                                    <small class="text-danger">{{ $program->application_deadline->format('d/m/Y') }}</small>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted">No definido</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($program->is_active)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-danger">Inactivo</span>
                                        @endif
                                        
                                        @if($program->applications_count > 0)
                                            <br><small class="text-muted">{{ $program->applications_count }} postulaciones</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm">
                                            <a href="{{ route('admin.programs.show', $program) }}" 
                                               class="btn btn-outline-info btn-sm" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.programs.edit', $program) }}" 
                                               class="btn btn-outline-warning btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteModal{{ $program->id }}" 
                                                    title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>

                                        <!-- Modal de eliminación -->
                                        <div class="modal fade" id="deleteModal{{ $program->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmar Eliminación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>¿Está seguro de que desea eliminar el programa <strong>{{ $program->name }}</strong>?</p>
                                                        @if($program->applications_count > 0)
                                                            <div class="alert alert-warning">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                Este programa tiene {{ $program->applications_count }} postulaciones asociadas.
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <form action="{{ route('admin.programs.destroy', $program) }}" method="POST" class="d-inline">
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
                                    <td colspan="9" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <p>No se encontraron programas con los filtros aplicados.</p>
                                            <a href="{{ route('admin.programs.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Crear Primer Programa
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    @if($programs->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $programs->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas rápidas -->
@if(isset($stats))
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['total'] ?? 0 }}</h4>
                        <p class="mb-0">Total Programas</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-globe fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['active'] ?? 0 }}</h4>
                        <p class="mb-0">Programas Activos</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['applications'] ?? 0 }}</h4>
                        <p class="mb-0">Total Postulaciones</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-users fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['countries'] ?? 0 }}</h4>
                        <p class="mb-0">Países Disponibles</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-flag fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.badge.bg-purple {
    background-color: #6f42c1 !important;
}
.btn-group-vertical .btn {
    margin-bottom: 2px;
}
.btn-group-vertical .btn:last-child {
    margin-bottom: 0;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form when filters change
    const filterSelects = document.querySelectorAll('#country, #category, #currency, #status');
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            // Optional: Auto-submit form on filter change
            // this.form.submit();
        });
    });
    
    // Clear search button
    const searchInput = document.getElementById('search');
    if (searchInput && searchInput.value) {
        const clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'btn btn-outline-secondary btn-sm ms-1';
        clearBtn.innerHTML = '<i class="fas fa-times"></i>';
        clearBtn.onclick = function() {
            searchInput.value = '';
            searchInput.form.submit();
        };
        searchInput.parentNode.appendChild(clearBtn);
    }
});
</script>
@endpush
