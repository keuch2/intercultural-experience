@extends('layouts.admin')

@section('title', 'Perfiles Au Pair')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-circle"></i> Perfiles Au Pair
        </h1>
        <a href="{{ route('admin.au-pair.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Filtros
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.au-pair.profiles') }}" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <label for="status" class="mr-2">Estado:</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="matched" {{ request('status') == 'matched' ? 'selected' : '' }}>Matched</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>

                <div class="form-group mr-3 mb-2">
                    <label for="complete" class="mr-2">Perfil Completo:</label>
                    <select name="complete" id="complete" class="form-control">
                        <option value="">Todos</option>
                        <option value="yes" {{ request('complete') == 'yes' ? 'selected' : '' }}>Sí</option>
                        <option value="no" {{ request('complete') == 'no' ? 'selected' : '' }}>No</option>
                    </select>
                </div>

                <div class="form-group mr-3 mb-2">
                    <label for="search" class="mr-2">Buscar:</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           placeholder="Nombre o email..." value="{{ request('search') }}">
                </div>

                <div class="form-group mb-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.au-pair.profiles') }}" class="btn btn-secondary ml-2">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Perfiles -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Total: {{ $profiles->total() }} perfiles
            </h6>
        </div>
        <div class="card-body">
            @if($profiles->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Participante</th>
                                <th>Programa</th>
                                <th>Estado</th>
                                <th>Perfil Completo</th>
                                <th>Disponible Desde</th>
                                <th>Vistas</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($profiles as $profile)
                            <tr>
                                <td>{{ $profile->id }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $profile->user->name }}</strong><br>
                                        <small class="text-muted">{{ $profile->user->email }}</small><br>
                                        <small class="text-muted">
                                            {{ $profile->user->age ?? 'N/A' }} años | 
                                            {{ $profile->user->country ?? 'N/A' }}
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    @if($profile->application && $profile->application->program)
                                        <span class="badge badge-primary">
                                            {{ $profile->application->program->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($profile->profile_status)
                                        @case('draft')
                                            <span class="badge badge-secondary">Borrador</span>
                                            @break
                                        @case('pending')
                                            <span class="badge badge-warning">Pendiente Revisión</span>
                                            @break
                                        @case('active')
                                            <span class="badge badge-success">Activo</span>
                                            @break
                                        @case('matched')
                                            <span class="badge badge-info">Match Confirmado</span>
                                            @break
                                        @case('inactive')
                                            <span class="badge badge-dark">Inactivo</span>
                                            @break
                                        @default
                                            <span class="badge badge-light">{{ $profile->profile_status }}</span>
                                    @endswitch
                                </td>
                                <td class="text-center">
                                    @if($profile->profile_complete)
                                        <i class="fas fa-check-circle text-success"></i>
                                    @else
                                        <i class="fas fa-times-circle text-danger"></i>
                                    @endif
                                </td>
                                <td>
                                    {{ $profile->available_from ? $profile->available_from->format('d/m/Y') : 'N/A' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light">{{ $profile->profile_views }}</span>
                                </td>
                                <td>
                                    {{ $profile->created_at->format('d/m/Y') }}
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.au-pair.profile.show', $profile->id) }}" 
                                           class="btn btn-sm btn-info" title="Ver Perfil">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($profile->profile_status == 'pending')
                                        <form action="{{ route('admin.au-pair.profile.approve', $profile->id) }}" 
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" 
                                                    title="Aprobar Perfil"
                                                    onclick="return confirm('¿Aprobar este perfil para matching?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        @endif
                                        
                                        @if($profile->profile_status == 'active')
                                        <a href="{{ route('admin.au-pair.matching') }}?au_pair={{ $profile->id }}" 
                                           class="btn btn-sm btn-warning" title="Ver Matching">
                                            <i class="fas fa-heart"></i>
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
                    {{ $profiles->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No se encontraron perfiles con los filtros aplicados</p>
                    <a href="{{ route('admin.au-pair.profiles') }}" class="btn btn-primary">
                        Ver Todos los Perfiles
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Resumen de Estados -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Información de Estados
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 text-center">
                            <span class="badge badge-secondary p-2">Borrador</span>
                            <p class="small mt-2">Perfil en creación</p>
                        </div>
                        <div class="col-md-2 text-center">
                            <span class="badge badge-warning p-2">Pendiente</span>
                            <p class="small mt-2">Esperando aprobación</p>
                        </div>
                        <div class="col-md-2 text-center">
                            <span class="badge badge-success p-2">Activo</span>
                            <p class="small mt-2">Disponible para matching</p>
                        </div>
                        <div class="col-md-2 text-center">
                            <span class="badge badge-info p-2">Matched</span>
                            <p class="small mt-2">Familia asignada</p>
                        </div>
                        <div class="col-md-2 text-center">
                            <span class="badge badge-dark p-2">Inactivo</span>
                            <p class="small mt-2">No disponible</p>
                        </div>
                        <div class="col-md-2 text-center">
                            <div>
                                <i class="fas fa-check-circle text-success"></i> = Completo<br>
                                <i class="fas fa-times-circle text-danger"></i> = Incompleto
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
