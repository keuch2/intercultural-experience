@extends('layouts.admin')

@section('title', 'Gestión de Empresas Host')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Gestión de Empresas Host</h1>
            <p class="text-muted">Administra las empresas que ofrecen posiciones laborales</p>
        </div>
        <a href="{{ route('admin.host-companies.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Empresa
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.host-companies.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Buscar</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Nombre, ciudad, industria..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="state" class="form-control">
                                <option value="">Todos</option>
                                <option value="California" {{ request('state') == 'California' ? 'selected' : '' }}>California</option>
                                <option value="Florida" {{ request('state') == 'Florida' ? 'selected' : '' }}>Florida</option>
                                <option value="New York" {{ request('state') == 'New York' ? 'selected' : '' }}>New York</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Industria</label>
                            <select name="industry" class="form-control">
                                <option value="">Todas</option>
                                <option value="Hospitality" {{ request('industry') == 'Hospitality' ? 'selected' : '' }}>Hospitalidad</option>
                                <option value="Retail" {{ request('industry') == 'Retail' ? 'selected' : '' }}>Retail</option>
                                <option value="Tourism" {{ request('industry') == 'Tourism' ? 'selected' : '' }}>Turismo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Rating Mínimo</label>
                            <select name="min_rating" class="form-control">
                                <option value="">Todos</option>
                                <option value="4" {{ request('min_rating') == '4' ? 'selected' : '' }}>4+ estrellas</option>
                                <option value="3" {{ request('min_rating') == '3' ? 'selected' : '' }}>3+ estrellas</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="is_active" class="form-control">
                                <option value="">Todos</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Activos</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Companies Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Empresas Host ({{ $companies->total() }})</h6>
        </div>
        <div class="card-body">
            @if($companies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Industria</th>
                                <th>Ubicación</th>
                                <th>Rating</th>
                                <th>Ofertas</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($companies as $company)
                                <tr>
                                    <td>{{ $company->id }}</td>
                                    <td><strong>{{ $company->name }}</strong></td>
                                    <td>
                                        @if($company->industry)
                                            <span class="badge badge-info">{{ $company->industry }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt"></i> {{ $company->city }}, {{ $company->state }}
                                    </td>
                                    <td>
                                        @if($company->rating)
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star {{ $i <= $company->rating ? 'text-warning' : 'text-muted' }}"></i>
                                            @endfor
                                            <small>({{ number_format($company->rating, 1) }})</small>
                                        @else
                                            <span class="text-muted">Sin rating</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $company->job_offers_count }}</span>
                                    </td>
                                    <td>
                                        @if($company->is_active)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.host-companies.show', $company->id) }}" 
                                               class="btn btn-sm btn-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.host-companies.edit', $company->id) }}" 
                                               class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.host-companies.toggle-status', $company->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm {{ $company->is_active ? 'btn-secondary' : 'btn-success' }}" 
                                                        title="{{ $company->is_active ? 'Desactivar' : 'Activar' }}">
                                                    <i class="fas fa-{{ $company->is_active ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.host-companies.destroy', $company->id) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Estás seguro de eliminar esta empresa?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
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
                <div class="d-flex justify-content-center">
                    {{ $companies->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No se encontraron empresas host.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
