@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Reporte de Solicitudes</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ url('/admin/reports/applications/export') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-download me-1"></i> Exportar
            </a>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print();">
                <i class="fas fa-print me-1"></i> Imprimir
            </button>
        </div>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-calendar me-1"></i> Período
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ url('/admin/reports/applications?period=week') }}">Esta semana</a></li>
                <li><a class="dropdown-item" href="{{ url('/admin/reports/applications?period=month') }}">Este mes</a></li>
                <li><a class="dropdown-item" href="{{ url('/admin/reports/applications?period=quarter') }}">Este trimestre</a></li>
                <li><a class="dropdown-item" href="{{ url('/admin/reports/applications?period=year') }}">Este año</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ url('/admin/reports/applications?period=custom') }}">Personalizado</a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Custom Date Range (Only visible when period=custom) -->
@if(request('period') == 'custom')
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Rango de Fechas Personalizado</h6>
    </div>
    <div class="card-body">
        <form action="{{ url('/admin/reports/applications') }}" method="GET" class="row g-3">
            <input type="hidden" name="period" value="custom">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Fecha de Inicio</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">Fecha de Fin</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-filter me-1"></i> Aplicar
                </button>
                <a href="{{ url('/admin/reports/applications') }}" class="btn btn-secondary">
                    <i class="fas fa-redo me-1"></i> Reiniciar
                </a>
            </div>
        </form>
    </div>
</div>
@endif

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 card-dashboard card-primary">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total de Solicitudes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalApplications ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-file-alt fa-2x text-gray-300"></i>
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
                            Solicitudes Aprobadas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $approvedApplications ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            Solicitudes Pendientes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingApplications ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card shadow h-100 py-2 card-dashboard" style="border-left-color: #e74a3b;">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Solicitudes Rechazadas</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $rejectedApplications ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Applications by Status Chart -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Solicitudes por Estado</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="applicationsByStatusChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="me-2">
                        <i class="fas fa-circle text-warning"></i> Pendientes
                    </span>
                    <span class="me-2">
                        <i class="fas fa-circle text-info"></i> En Revisión
                    </span>
                    <span class="me-2">
                        <i class="fas fa-circle text-success"></i> Aprobadas
                    </span>
                    <span class="me-2">
                        <i class="fas fa-circle text-danger"></i> Rechazadas
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications by Program Chart -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Solicitudes por Programa</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar">
                    <canvas id="applicationsByProgramChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Applications by Country Chart -->
<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Solicitudes por País</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="applicationsByCountryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications Trend Chart -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tendencia de Solicitudes</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="applicationsTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Applications Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Detalle de Solicitudes</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Programa</th>
                        <th>País</th>
                        <th>Estado</th>
                        <th>Fecha de Solicitud</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($applications) && count($applications) > 0)
                        @foreach($applications as $application)
                        <tr>
                            <td>{{ $application->id }}</td>
                            <td>{{ $application->user->name }}</td>
                            <td>{{ $application->program->name }}</td>
                            <td>{{ $application->program->country }}</td>
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
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center">No hay datos disponibles para el período seleccionado</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($applications) && $applications->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $applications->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Datos para los gráficos (estos datos deberían venir del controlador)
    const statusData = {
        labels: ['Pendientes', 'En Revisión', 'Aprobadas', 'Rechazadas'],
        datasets: [{
            data: [
                {{ $pendingApplications ?? 0 }}, 
                {{ $inReviewApplications ?? 0 }}, 
                {{ $approvedApplications ?? 0 }}, 
                {{ $rejectedApplications ?? 0 }}
            ],
            backgroundColor: ['#f6c23e', '#36b9cc', '#1cc88a', '#e74a3b'],
            hoverBackgroundColor: ['#e0b138', '#2fa8b8', '#17a673', '#d13b2a'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }]
    };

    // Configuración para el gráfico de estado
    const statusConfig = {
        type: 'doughnut',
        data: statusData,
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
                display: false
            },
            cutoutPercentage: 80,
        },
    };

    // Inicializar gráficos cuando el documento esté listo
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de solicitudes por estado
        var statusCtx = document.getElementById('applicationsByStatusChart');
        if (statusCtx) {
            new Chart(statusCtx, statusConfig);
        }

        // Aquí irían las inicializaciones de los otros gráficos
        // con datos que deberían venir del controlador
    });
</script>
@endpush
