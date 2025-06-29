@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detalles del Usuario</h1>
        <div>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-edit fa-sm text-white-50"></i> Editar Usuario
            </a>
            <a href="{{ route('admin.users.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm ml-2">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver a la Lista
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Información del Usuario -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información Personal</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img class="img-profile rounded-circle" src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=4e73df&color=ffffff&size=200" width="150" height="150">
                        <h4 class="mt-3">{{ $user->name }}</h4>
                        <p class="text-muted">
                            <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-primary' }}">
                                {{ $user->role === 'admin' ? 'Administrador' : 'Usuario' }}
                            </span>
                        </p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Email:</h6>
                        <p>{{ $user->email }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Teléfono:</h6>
                        <p>{{ $user->phone ?? 'No especificado' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Nacionalidad:</h6>
                        <p>{{ $user->nationality ?? 'No especificada' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Dirección:</h6>
                        <p>{{ $user->address ?? 'No especificada' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Fecha de Registro:</h6>
                        <p>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="font-weight-bold">Última Actualización:</h6>
                        <p>{{ $user->updated_at ? $user->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Estadísticas y Resumen -->
        <div class="col-xl-8 col-lg-7">
            <!-- Tarjetas de Resumen -->
            <div class="row">
                <div class="col-xl-6 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Solicitudes Totales</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $applicationStats['total'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Puntos Acumulados</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalPoints ?? 0 }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-coins fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Detalles de Solicitudes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Solicitudes</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card bg-primary text-white shadow">
                                <div class="card-body">
                                    Pendientes
                                    <div class="text-white-50 small">{{ $applicationStats['pending'] }} solicitudes</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-success text-white shadow">
                                <div class="card-body">
                                    Aprobadas
                                    <div class="text-white-50 small">{{ $applicationStats['approved'] }} solicitudes</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-danger text-white shadow">
                                <div class="card-body">
                                    Rechazadas
                                    <div class="text-white-50 small">{{ $applicationStats['rejected'] }} solicitudes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($user->applications->count() > 0)
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Programa</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->applications as $application)
                                <tr>
                                    <td>{{ $application->id }}</td>
                                    <td>{{ $application->program->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($application->status == 'pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @elseif($application->status == 'in_review')
                                            <span class="badge badge-info">En Revisión</span>
                                        @elseif($application->status == 'approved')
                                            <span class="badge badge-success">Aprobada</span>
                                        @elseif($application->status == 'rejected')
                                            <span class="badge badge-danger">Rechazada</span>
                                        @endif
                                    </td>
                                    <td>{{ $application->created_at ? $application->created_at->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('admin.applications.show', $application->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mt-3">
                        Este usuario no tiene solicitudes registradas.
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Detalles de Canjes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Canjes de Recompensas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card bg-primary text-white shadow">
                                <div class="card-body">
                                    Pendientes
                                    <div class="text-white-50 small">{{ $redemptionStats['pending'] }} canjes</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-success text-white shadow">
                                <div class="card-body">
                                    Aprobados
                                    <div class="text-white-50 small">{{ $redemptionStats['approved'] }} canjes</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card bg-danger text-white shadow">
                                <div class="card-body">
                                    Rechazados
                                    <div class="text-white-50 small">{{ $redemptionStats['rejected'] }} canjes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($user->redemptions->count() > 0)
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Recompensa</th>
                                    <th>Puntos</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($user->redemptions as $redemption)
                                <tr>
                                    <td>{{ $redemption->id }}</td>
                                    <td>{{ $redemption->reward->name ?? 'N/A' }}</td>
                                    <td>{{ $redemption->reward->points ?? 'N/A' }}</td>
                                    <td>
                                        @if($redemption->status == 'pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @elseif($redemption->status == 'approved')
                                            <span class="badge badge-success">Aprobado</span>
                                        @elseif($redemption->status == 'rejected')
                                            <span class="badge badge-danger">Rechazado</span>
                                        @elseif($redemption->status == 'delivered')
                                            <span class="badge badge-info">Entregado</span>
                                        @endif
                                    </td>
                                    <td>{{ $redemption->created_at ? $redemption->created_at->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('admin.redemptions.show', $redemption->id) }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="alert alert-info mt-3">
                        Este usuario no tiene canjes de recompensas registrados.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
