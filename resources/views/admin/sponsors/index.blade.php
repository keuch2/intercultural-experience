@extends('layouts.admin')

@section('title', 'Gestión de Sponsors')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Gestión de Sponsors</h1>
            <p class="text-muted">Administra las organizaciones patrocinadoras (AAG, AWA, GH, etc.)</p>
        </div>
        <a href="{{ route('admin.sponsors.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Sponsor
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.sponsors.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Buscar</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Nombre, código o país..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>País</label>
                            <select name="country" class="form-control">
                                <option value="">Todos</option>
                                <option value="USA" {{ request('country') == 'USA' ? 'selected' : '' }}>USA</option>
                                <option value="Canada" {{ request('country') == 'Canada' ? 'selected' : '' }}>Canada</option>
                                <option value="UK" {{ request('country') == 'UK' ? 'selected' : '' }}>UK</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="is_active" class="form-control">
                                <option value="">Todos</option>
                                <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Activos</option>
                                <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactivos</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sponsors Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Sponsors ({{ $sponsors->total() }})
            </h6>
        </div>
        <div class="card-body">
            @if($sponsors->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>País</th>
                                <th>Contacto</th>
                                <th>Ofertas</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sponsors as $sponsor)
                                <tr>
                                    <td>{{ $sponsor->id }}</td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $sponsor->code }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $sponsor->name }}</strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-globe"></i> {{ $sponsor->country }}
                                    </td>
                                    <td>
                                        @if($sponsor->contact_email)
                                            <small>
                                                <i class="fas fa-envelope"></i> {{ $sponsor->contact_email }}<br>
                                            </small>
                                        @endif
                                        @if($sponsor->contact_phone)
                                            <small>
                                                <i class="fas fa-phone"></i> {{ $sponsor->contact_phone }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $sponsor->job_offers_count }} ofertas
                                        </span>
                                    </td>
                                    <td>
                                        @if($sponsor->is_active)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-danger">Inactivo</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.sponsors.show', $sponsor->id) }}" 
                                               class="btn btn-sm btn-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.sponsors.edit', $sponsor->id) }}" 
                                               class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.sponsors.toggle-status', $sponsor->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm {{ $sponsor->is_active ? 'btn-secondary' : 'btn-success' }}" 
                                                        title="{{ $sponsor->is_active ? 'Desactivar' : 'Activar' }}">
                                                    <i class="fas fa-{{ $sponsor->is_active ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.sponsors.destroy', $sponsor->id) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este sponsor?')">
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

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $sponsors->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No se encontraron sponsors.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
