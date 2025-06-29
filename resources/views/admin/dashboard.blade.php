@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Exportar</button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
            <i class="fas fa-calendar me-1"></i>
            Esta semana
        </button>
    </div>
</div>

<!-- Dashboard Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 card-dashboard card-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Usuarios Registrados</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $userCount ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 card-dashboard card-success">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Solicitudes Pendientes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingApplications ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 card-dashboard card-info">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Tickets de Soporte Abiertos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $openTickets ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 card-dashboard card-warning">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Canjes Pendientes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingRedemptions ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-gift fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Applications -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Solicitudes Recientes</h6>
        <a href="{{ url('/admin/applications') }}" class="btn btn-sm btn-primary">
            Ver Todas
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Programa</th>
                        <th>Estado</th>
                        <th>Fecha de Solicitud</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($recentApplications) && count($recentApplications) > 0)
                        @foreach($recentApplications as $application)
                        <tr>
                            <td>{{ $application->id }}</td>
                            <td>{{ $application->user->name }}</td>
                            <td>{{ $application->program->name }}</td>
                            <td>
                                @switch($application->status)
                                    @case('pending')
                                        <span class="badge bg-warning">Pendiente</span>
                                        @break
                                    @case('in_review')
                                        <span class="badge bg-info">En Revisión</span>
                                        @break
                                    @case('approved')
                                        <span class="badge bg-success">Aprobada</span>
                                        @break
                                    @case('rejected')
                                        <span class="badge bg-danger">Rechazada</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary">{{ $application->status }}</span>
                                @endswitch
                            </td>
                            <td>{{ $application->applied_at }}</td>
                            <td>
                                <a href="{{ url('/admin/applications/'.$application->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center">No hay solicitudes recientes</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Support Tickets -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Tickets de Soporte Recientes</h6>
                <a href="{{ url('/admin/support-tickets') }}" class="btn btn-sm btn-primary">
                    Ver Todos
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Asunto</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($recentTickets) && count($recentTickets) > 0)
                                @foreach($recentTickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->id }}</td>
                                    <td>{{ $ticket->user->name }}</td>
                                    <td>{{ $ticket->subject }}</td>
                                    <td>
                                        @switch($ticket->status)
                                            @case('open')
                                                <span class="badge bg-warning">Abierto</span>
                                                @break
                                            @case('in_progress')
                                                <span class="badge bg-info">En Proceso</span>
                                                @break
                                            @case('closed')
                                                <span class="badge bg-success">Cerrado</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $ticket->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <a href="{{ url('/admin/support-tickets/'.$ticket->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No hay tickets recientes</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Redemptions -->
    <div class="col-lg-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Canjes Recientes</h6>
                <a href="{{ url('/admin/redemptions') }}" class="btn btn-sm btn-primary">
                    Ver Todos
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Recompensa</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($recentRedemptions) && count($recentRedemptions) > 0)
                                @foreach($recentRedemptions as $redemption)
                                <tr>
                                    <td>{{ $redemption->id }}</td>
                                    <td>{{ $redemption->user->name }}</td>
                                    <td>{{ $redemption->reward->name }}</td>
                                    <td>
                                        @switch($redemption->status)
                                            @case('pending')
                                                <span class="badge bg-warning">Pendiente</span>
                                                @break
                                            @case('approved')
                                                <span class="badge bg-success">Aprobado</span>
                                                @break
                                            @case('rejected')
                                                <span class="badge bg-danger">Rechazado</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $redemption->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <a href="{{ url('/admin/redemptions/'.$redemption->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No hay canjes recientes</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Aquí puedes agregar scripts específicos para el dashboard
</script>
@endpush
