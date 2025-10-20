@extends('layouts.admin')

@section('title', 'Programas IE')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Programas IE</h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.ie-programs.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Nuevo Programa IE
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Filtros -->
                <div class="card-body border-bottom">
                    <form action="{{ route('admin.ie-programs.index') }}" method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Nombre, descripción...">
                        </div>
                        <div class="col-md-2">
                            <label for="country" class="form-label">País</label>
                            <select class="form-select" id="country" name="country">
                                <option value="">Todos</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country }}" {{ request('country') == $country ? 'selected' : '' }}>
                                        {{ $country }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="subcategory" class="form-label">Subcategoría</label>
                            <select class="form-select" id="subcategory" name="subcategory">
                                <option value="">Todas</option>
                                @foreach($subcategories as $subcategory)
                                    <option value="{{ $subcategory }}" {{ request('subcategory') == $subcategory ? 'selected' : '' }}>
                                        {{ $subcategory }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="is_active" class="form-label">Estado</label>
                            <select class="form-select" id="is_active" name="is_active">
                                <option value="">Todos</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-outline-primary me-2">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.ie-programs.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="card-body">
                    @if($programs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>País</th>
                                        <th>Subcategoría</th>
                                        <th>Aplicaciones</th>
                                        <th>Costo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($programs as $program)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div>
                                                        <h6 class="mb-0">{{ $program->name }}</h6>
                                                        <small class="text-muted">{{ Str::limit($program->description, 50) }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark">{{ $program->country }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $program->subcategory ?: 'Sin categoría' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $program->applications_count }}</span>
                                            </td>
                                            <td>
                                                @if($program->cost)
                                                    <span class="fw-bold">{{ $program->formatted_cost }}</span>
                                                @else
                                                    <span class="text-muted">No especificado</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($program->is_active)
                                                    <span class="badge bg-success">Activo</span>
                                                @else
                                                    <span class="badge bg-danger">Inactivo</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('admin.ie-programs.show', $program) }}" 
                                                       class="btn btn-sm btn-outline-info" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.programs.requisites.index', $program) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="Requisitos">
                                                        <i class="fas fa-list-check"></i>
                                                    </a>
                                                    <a href="{{ route('admin.ie-programs.edit', $program) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.ie-programs.destroy', $program) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                title="Eliminar" onclick="return confirm('¿Estás seguro?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                Mostrando {{ $programs->firstItem() }} - {{ $programs->lastItem() }} de {{ $programs->total() }} programas
                            </div>
                            {{ $programs->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay programas IE registrados</h5>
                            <p class="text-muted">Comienza creando tu primer programa IE.</p>
                            <a href="{{ route('admin.ie-programs.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Crear Programa IE
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 5">
        <div class="toast show" role="alert">
            <div class="toast-header">
                <strong class="me-auto text-success">Éxito</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
            </div>
            <div class="toast-body">
                {{ session('success') }}
            </div>
        </div>
    </div>
@endif
@endsection
