@extends('layouts.admin')

@section('title', 'Empleadores')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestión de Empleadores</h1>
        <div>
            <a href="{{ route('admin.work-travel.employer.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nuevo Empleador
            </a>
            <a href="{{ route('admin.work-travel.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-2">
                    <select name="state" class="form-control">
                        <option value="">Todos los Estados</option>
                        @foreach($states as $state)
                            <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="verified" class="form-control">
                        <option value="">Todos</option>
                        <option value="1" {{ request('verified') == '1' ? 'selected' : '' }}>Verificados</option>
                        <option value="0" {{ request('verified') == '0' ? 'selected' : '' }}>No Verificados</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="season" class="form-control">
                        <option value="">Todas las Temporadas</option>
                        <option value="summer" {{ request('season') == 'summer' ? 'selected' : '' }}>Summer</option>
                        <option value="winter" {{ request('season') == 'winter' ? 'selected' : '' }}>Winter</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Buscar..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Employers Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Empleadores ({{ $employers->total() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Ubicación</th>
                            <th>Contacto</th>
                            <th>Posiciones</th>
                            <th>Rating</th>
                            <th>Temporadas</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employers as $employer)
                        <tr>
                            <td>
                                <strong>{{ $employer->company_name }}</strong>
                                <br>
                                <small class="text-muted">{{ $employer->business_type }}</small>
                                @if($employer->years_in_program > 0)
                                    <br><small class="badge badge-info">{{ $employer->years_in_program }} años</small>
                                @endif
                            </td>
                            <td>
                                {{ $employer->city }}, {{ $employer->state }}
                                <br>
                                <small class="text-muted">{{ $employer->zip_code }}</small>
                            </td>
                            <td>
                                <strong>{{ $employer->contact_name }}</strong>
                                <br>
                                <small>{{ $employer->contact_phone }}</small>
                                <br>
                                <small>{{ $employer->contact_email }}</small>
                            </td>
                            <td class="text-center">
                                <h4>
                                    <span class="badge badge-{{ $employer->positions_available > 0 ? 'success' : 'secondary' }}">
                                        {{ $employer->positions_available }}
                                    </span>
                                </h4>
                                <small class="text-muted">disponibles</small>
                                @if($employer->participants_hired_this_year > 0)
                                    <br><small>{{ $employer->participants_hired_this_year }} este año</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($employer->rating)
                                    <h5 class="mb-0">
                                        <i class="fas fa-star text-warning"></i> {{ number_format($employer->rating, 1) }}
                                    </h5>
                                    <small class="text-muted">{{ $employer->total_reviews }} reviews</small>
                                @else
                                    <small class="text-muted">Sin calificación</small>
                                @endif
                            </td>
                            <td>
                                @if($employer->seasons_hiring)
                                    @foreach($employer->seasons_hiring as $season)
                                        @if($season == 'summer')
                                            <span class="badge badge-warning"><i class="fas fa-sun"></i> Summer</span>
                                        @else
                                            <span class="badge badge-info"><i class="fas fa-snowflake"></i> Winter</span>
                                        @endif
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @if($employer->is_verified)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Verificado</span>
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                                @endif
                                
                                @if($employer->is_blacklisted)
                                    <br><span class="badge badge-danger"><i class="fas fa-ban"></i> Bloqueado</span>
                                @endif
                                
                                @if(!$employer->is_active)
                                    <br><span class="badge badge-secondary">Inactivo</span>
                                @endif
                                
                                <br>
                                <small class="text-muted">
                                    @if($employer->e_verify_enrolled)
                                        <i class="fas fa-check text-success"></i> E-Verify
                                    @endif
                                    @if($employer->workers_comp_insurance)
                                        <i class="fas fa-check text-success"></i> Insurance
                                    @endif
                                </small>
                            </td>
                            <td>
                                <a href="{{ route('admin.work-travel.employer.show', $employer->id) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$employer->is_verified)
                                    <form action="{{ route('admin.work-travel.employer.verify', $employer->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" 
                                                onclick="return confirm('¿Verificar este empleador?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No se encontraron empleadores</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $employers->links() }}
        </div>
    </div>

    <!-- Stats -->
    <div class="row">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-primary">{{ $employers->where('is_verified', true)->count() }}</h4>
                        <small>Verificados</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-success">{{ $employers->sum('positions_available') }}</h4>
                        <small>Posiciones Totales</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-info">{{ $employers->where('rating', '>=', 4)->count() }}</h4>
                        <small>Top Rated (4+)</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-warning">{{ $employers->sum('participants_hired_this_year') }}</h4>
                        <small>Contrataciones Este Año</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
