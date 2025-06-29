@extends('layouts.admin')

@section('title', 'Gestión de Puntos')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Puntos</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Puntos</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <!-- Resumen de Puntos -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total de Puntos en el Sistema</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalPoints) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Puntos Otorgados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($positivePoints) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plus-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Puntos Canjeados</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format(abs($negativePoints)) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-minus-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Filtros -->
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filtros
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.points.index') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="search" class="form-label">Buscar</label>
                                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="ID, usuario, razón...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Razón</label>
                                    <select class="form-select" id="reason" name="reason">
                                        <option value="">Todas las razones</option>
                                        @foreach($reasons as $reason)
                                            <option value="{{ $reason }}" {{ request('reason') == $reason ? 'selected' : '' }}>
                                                @switch($reason)
                                                    @case('application_approved')
                                                        Solicitud Aprobada
                                                        @break
                                                    @case('redemption_created')
                                                        Canje Creado
                                                        @break
                                                    @case('redemption_rejected')
                                                        Canje Rechazado
                                                        @break
                                                    @case('manual_adjustment')
                                                        Ajuste Manual
                                                        @break
                                                    @default
                                                        {{ $reason }}
                                                @endswitch
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="date_range" class="form-label">Rango de Fechas</label>
                                    <input type="text" class="form-control" id="date_range" name="date_range" value="{{ request('date_range') }}" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                                <a href="{{ route('admin.points.index') }}" class="btn btn-secondary">Limpiar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Tabla de Transacciones de Puntos -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Historial de Transacciones de Puntos
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Cambio</th>
                                    <th>Razón</th>
                                    <th>Referencia</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($points as $point)
                                    <tr>
                                        <td>{{ $point->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $point->user_id) }}">
                                                {{ $point->user->name }}
                                            </a>
                                        </td>
                                        <td class="{{ $point->change > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $point->change > 0 ? '+' : '' }}{{ $point->change }}
                                        </td>
                                        <td>
                                            @switch($point->reason)
                                                @case('application_approved')
                                                    <span class="badge bg-success">Solicitud Aprobada</span>
                                                    @break
                                                @case('redemption_created')
                                                    <span class="badge bg-danger">Canje Creado</span>
                                                    @break
                                                @case('redemption_rejected')
                                                    <span class="badge bg-warning">Canje Rechazado</span>
                                                    @break
                                                @case('manual_adjustment')
                                                    <span class="badge bg-info">Ajuste Manual</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $point->reason }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($point->related_id)
                                                @if($point->reason == 'application_approved')
                                                    <a href="{{ route('admin.applications.show', $point->related_id) }}">
                                                        Solicitud #{{ $point->related_id }}
                                                    </a>
                                                @elseif(in_array($point->reason, ['redemption_created', 'redemption_rejected']))
                                                    <a href="{{ route('admin.redemptions.show', $point->related_id) }}">
                                                        Canje #{{ $point->related_id }}
                                                    </a>
                                                @else
                                                    #{{ $point->related_id }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $point->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay transacciones de puntos disponibles.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-3">
                        {{ $points->links() }}
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Top Users y Gráfico -->
        <div class="col-lg-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-trophy me-1"></i>
                    Usuarios con Más Puntos
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Usuario</th>
                                    <th>Puntos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topUsers as $index => $user)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $user->id) }}">
                                                {{ $user->name }}
                                            </a>
                                        </td>
                                        <td>{{ number_format($user->total_points) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No hay usuarios con puntos.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Distribución de Puntos
                </div>
                <div class="card-body">
                    <canvas id="pointsDistributionChart" width="100%" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar selector de fechas
        flatpickr("#date_range", {
            mode: "range",
            dateFormat: "d/m/Y",
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                },
            }
        });
        
        // Gráfico de distribución de puntos
        var ctx = document.getElementById('pointsDistributionChart');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Puntos Otorgados', 'Puntos Canjeados'],
                datasets: [{
                    data: [{{ $positivePoints }}, {{ abs($negativePoints) }}],
                    backgroundColor: ['#1cc88a', '#e74a3b'],
                    hoverBackgroundColor: ['#17a673', '#be2617'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }]
            },
            options: {
                maintainAspectRatio: false,
                tooltips: {
                    backgroundColor: "rgb(255,255,255)",
                    bodyFontColor: "#858796",
                    borderColor: '#dddfeb',
                    borderWidth: 1,
                    xPadding: 15,
                    yPadding: 15,
                    displayColors: false,
                    caretPadding: 10,
                },
                legend: {
                    display: true,
                    position: 'bottom'
                },
                cutoutPercentage: 80,
            },
        });
    });
</script>
@endsection
