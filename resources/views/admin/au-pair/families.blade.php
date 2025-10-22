@extends('layouts.admin')

@section('title', 'Familias Host')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-home"></i> Familias Host
        </h1>
        <div>
            <a href="{{ route('admin.au-pair.families.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nueva Familia
            </a>
            <a href="{{ route('admin.au-pair.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filtros de Búsqueda
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.au-pair.families') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="state">Estado:</label>
                        <select name="state" id="state" class="form-control">
                            <option value="">Todos</option>
                            @if(isset($states))
                                @foreach($states as $state)
                                    <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>
                                        {{ $state }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="has_infants">¿Tiene bebés?</label>
                        <select name="has_infants" id="has_infants" class="form-control">
                            <option value="">Todos</option>
                            <option value="yes" {{ request('has_infants') == 'yes' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ request('has_infants') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="has_special_needs">¿Necesidades especiales?</label>
                        <select name="has_special_needs" id="has_special_needs" class="form-control">
                            <option value="">Todos</option>
                            <option value="yes" {{ request('has_special_needs') == 'yes' ? 'selected' : '' }}>Sí</option>
                            <option value="no" {{ request('has_special_needs') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <label for="search">Buscar:</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               placeholder="Nombre, ciudad..." value="{{ request('search') }}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="{{ route('admin.au-pair.families') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Familias -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Total: {{ isset($families) ? $families->total() : 0 }} familias registradas
            </h6>
        </div>
        <div class="card-body">
            @if(isset($families) && $families->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Familia</th>
                                <th>Ubicación</th>
                                <th>Niños</th>
                                <th>Requisitos</th>
                                <th>Oferta</th>
                                <th>Estado Match</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($families as $family)
                            <tr>
                                <td>{{ $family->id }}</td>
                                <td>
                                    <strong>{{ $family->family_name }}</strong><br>
                                    <small>
                                        {{ $family->parent1_name }}
                                        @if($family->parent2_name)
                                            & {{ $family->parent2_name }}
                                        @endif
                                    </small><br>
                                    <small class="text-muted">
                                        <i class="fas fa-envelope"></i> {{ $family->email }}<br>
                                        <i class="fas fa-phone"></i> {{ $family->phone }}
                                    </small>
                                </td>
                                <td>
                                    <i class="fas fa-map-marker-alt"></i> {{ $family->city }}, {{ $family->state }}<br>
                                    <small class="text-muted">{{ $family->country }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $family->number_of_children }} niño(s)</span><br>
                                    <small>Edades: 
                                        @if($family->children_ages)
                                            {{ implode(', ', $family->children_ages) }} años
                                        @endif
                                    </small><br>
                                    @if($family->has_infants)
                                        <span class="badge badge-warning">Tiene bebés</span>
                                    @endif
                                    @if($family->has_special_needs)
                                        <span class="badge badge-danger">Necesidades especiales</span>
                                    @endif
                                </td>
                                <td>
                                    @if($family->required_gender != 'any')
                                        <span class="badge badge-secondary">
                                            Género: {{ ucfirst($family->required_gender) }}
                                        </span><br>
                                    @endif
                                    @if($family->drivers_license_required)
                                        <span class="badge badge-primary">
                                            <i class="fas fa-car"></i> Licencia requerida
                                        </span><br>
                                    @endif
                                    @if($family->swimming_required)
                                        <span class="badge badge-info">
                                            <i class="fas fa-swimmer"></i> Debe nadar
                                        </span><br>
                                    @endif
                                    @if($family->has_pets)
                                        <span class="badge badge-warning">
                                            <i class="fas fa-paw"></i> Tiene mascotas
                                        </span>
                                    @endif
                                    @if($family->smoking_household)
                                        <span class="badge badge-dark">
                                            <i class="fas fa-smoking"></i> Fumadores
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small>
                                        <strong>${{ number_format($family->weekly_stipend, 2) }}</strong>/semana<br>
                                        Educación: ${{ number_format($family->education_fund, 2) }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    @if($family->confirmedMatch)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i> Con Au Pair
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-search"></i> Buscando
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group-vertical" role="group">
                                        <a href="{{ route('admin.au-pair.families.show', $family->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                        <a href="{{ route('admin.au-pair.families.edit', $family->id) }}" 
                                           class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                        @if(!$family->confirmedMatch)
                                        <a href="{{ route('admin.au-pair.matching') }}?family={{ $family->id }}" 
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-heart"></i> Match
                                        </a>
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
                    {{ $families->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-home fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No se encontraron familias con los filtros aplicados</p>
                    <a href="{{ route('admin.au-pair.families.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Registrar Primera Familia
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Resumen de Estadísticas -->
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Familias Activas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ isset($stats) ? $stats['active'] : 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-home fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Con Au Pair</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ isset($stats) ? $stats['matched'] : 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Con Bebés</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ isset($stats) ? $stats['with_infants'] : 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-baby fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Estados Cubiertos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ isset($stats) ? $stats['states_count'] : 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-map-marked-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
