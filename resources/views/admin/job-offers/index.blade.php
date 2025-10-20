@extends('layouts.admin')

@section('title', 'Gestión de Ofertas Laborales')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Gestión de Ofertas Laborales</h1>
            <p class="text-muted">Administra las ofertas de trabajo para participantes</p>
        </div>
        <a href="{{ route('admin.job-offers.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nueva Oferta
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.job-offers.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Buscar</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Posición, ID, ciudad..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Sponsor</label>
                            <select name="sponsor_id" class="form-control">
                                <option value="">Todos</option>
                                @foreach($sponsors as $sponsor)
                                    <option value="{{ $sponsor->id }}" {{ request('sponsor_id') == $sponsor->id ? 'selected' : '' }}>
                                        {{ $sponsor->code }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Empresa Host</label>
                            <select name="host_company_id" class="form-control">
                                <option value="">Todas</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('host_company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="status" class="form-control">
                                <option value="">Todos</option>
                                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Disponible</option>
                                <option value="full" {{ request('status') == 'full' ? 'selected' : '' }}>Lleno</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
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

    <!-- Job Offers Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Ofertas Laborales ({{ $offers->total() }})</h6>
        </div>
        <div class="card-body">
            @if($offers->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Posición</th>
                                <th>Sponsor</th>
                                <th>Empresa</th>
                                <th>Ubicación</th>
                                <th>Fechas</th>
                                <th>Cupos</th>
                                <th>Salario</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($offers as $offer)
                                <tr>
                                    <td><span class="badge badge-secondary">{{ $offer->job_offer_id }}</span></td>
                                    <td><strong>{{ $offer->position }}</strong></td>
                                    <td>{{ $offer->sponsor->code }}</td>
                                    <td>{{ $offer->hostCompany->name }}</td>
                                    <td>
                                        <i class="fas fa-map-marker-alt"></i> {{ $offer->city }}, {{ $offer->state }}
                                    </td>
                                    <td>
                                        <small>
                                            {{ \Carbon\Carbon::parse($offer->start_date)->format('d/m/Y') }} - 
                                            {{ \Carbon\Carbon::parse($offer->end_date)->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $offer->available_slots > 0 ? 'info' : 'warning' }}">
                                            {{ $offer->available_slots }}/{{ $offer->total_slots }}
                                        </span>
                                    </td>
                                    <td>
                                        <small>${{ number_format($offer->salary_min, 2) }} - ${{ number_format($offer->salary_max, 2) }}/hr</small>
                                    </td>
                                    <td>
                                        @if($offer->status == 'available')
                                            <span class="badge badge-success">Disponible</span>
                                        @elseif($offer->status == 'full')
                                            <span class="badge badge-warning">Lleno</span>
                                        @else
                                            <span class="badge badge-danger">Cancelado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.job-offers.show', $offer->id) }}" 
                                               class="btn btn-sm btn-info" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.job-offers.edit', $offer->id) }}" 
                                               class="btn btn-sm btn-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.job-offers.toggle-status', $offer->id) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm {{ $offer->status == 'available' ? 'btn-secondary' : 'btn-success' }}" 
                                                        title="{{ $offer->status == 'available' ? 'Cancelar' : 'Activar' }}">
                                                    <i class="fas fa-{{ $offer->status == 'available' ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('admin.job-offers.destroy', $offer->id) }}" 
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('¿Estás seguro de eliminar esta oferta?')">
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
                    {{ $offers->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No se encontraron ofertas laborales.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
