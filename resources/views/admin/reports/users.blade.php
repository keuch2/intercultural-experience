@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Reporte de Usuarios</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ url('/admin/reports/users/export') }}" class="btn btn-sm btn-outline-secondary">
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
                <li><a class="dropdown-item" href="{{ url('/admin/reports/users?period=week') }}">Esta semana</a></li>
                <li><a class="dropdown-item" href="{{ url('/admin/reports/users?period=month') }}">Este mes</a></li>
                <li><a class="dropdown-item" href="{{ url('/admin/reports/users?period=quarter') }}">Este trimestre</a></li>
                <li><a class="dropdown-item" href="{{ url('/admin/reports/users?period=year') }}">Este año</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ url('/admin/reports/users?period=custom') }}">Personalizado</a></li>
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
        <form action="{{ url('/admin/reports/users') }}" method="GET" class="row g-3">
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
                <a href="{{ url('/admin/reports/users') }}" class="btn btn-secondary">
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
                            Total de Usuarios</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalUsers ?? 0 }}</div>
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
                            Nuevos Usuarios</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $newUsers ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-plus fa-2x text-gray-300"></i>
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
                            Usuarios Activos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeUsers ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-check fa-2x text-gray-300"></i>
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
                            Usuarios Inactivos</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $inactiveUsers ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Registration Trend Chart -->
<div class="row">
    <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Tendencia de Registro de Usuarios</h6>
            </div>
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="userRegistrationTrendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- User by Role Chart -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Usuarios por Rol</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4 pb-2">
                    <canvas id="usersByRoleChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="me-2">
                        <i class="fas fa-circle text-primary"></i> Administradores
                    </span>
                    <span class="me-2">
                        <i class="fas fa-circle text-secondary"></i> Usuarios
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Users by Country Chart -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Usuarios por Nacionalidad</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar">
                    <canvas id="usersByCountryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- User Activity Chart -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Actividad de Usuarios</h6>
            </div>
            <div class="card-body">
                <div class="chart-bar">
                    <canvas id="userActivityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Top Users Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Usuarios Más Activos</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Fecha de Registro</th>
                        <th>Solicitudes</th>
                        <th>Canjes</th>
                        <th>Puntos</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($topUsers) && count($topUsers) > 0)
                        @foreach($topUsers as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at }}</td>
                            <td>{{ $user->applications_count }}</td>
                            <td>{{ $user->redemptions_count }}</td>
                            <td>{{ $user->total_points }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center">No hay datos disponibles para el período seleccionado</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Recent Registrations Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Registros Recientes</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>País</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha de Registro</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($recentUsers) && count($recentUsers) > 0)
                        @foreach($recentUsers as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->country ?? 'No especificado' }}</td>
                            <td>
                                @if($user->role === 'admin')
                                    <span class="badge bg-primary">Administrador</span>
                                @else
                                    <span class="badge bg-secondary">Usuario</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7" class="text-center">No hay datos disponibles para el período seleccionado</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if(isset($recentUsers) && $recentUsers->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $recentUsers->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Datos para los gráficos (estos datos deberían venir del controlador)
    const roleData = {
        labels: ['Administradores', 'Usuarios'],
        datasets: [{
            data: [
                {{ $adminUsers ?? 0 }}, 
                {{ $regularUsers ?? 0 }}
            ],
            backgroundColor: ['#4e73df', '#858796'],
            hoverBackgroundColor: ['#2e59d9', '#717384'],
            hoverBorderColor: "rgba(234, 236, 244, 1)",
        }]
    };

    // Configuración para el gráfico de roles
    const roleConfig = {
        type: 'doughnut',
        data: roleData,
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
        // Gráfico de usuarios por rol
        var roleCtx = document.getElementById('usersByRoleChart');
        if (roleCtx) {
            new Chart(roleCtx, roleConfig);
        }

        // Gráfico de usuarios por nacionalidad
        var countryCtx = document.getElementById('usersByCountryChart');
        if (countryCtx) {
            var countryLabels = [];
            var countryData = [];
            
            @foreach($usersByCountry as $item)
                countryLabels.push('{{ $item->nationality ?? "No especificado" }}');
                countryData.push({{ $item->total }});
            @endforeach
            
            new Chart(countryCtx, {
                type: 'bar',
                data: {
                    labels: countryLabels,
                    datasets: [{
                        label: 'Usuarios',
                        data: countryData,
                        backgroundColor: '#4e73df',
                        borderColor: '#4e73df',
                        borderWidth: 1
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
        
        // Gráfico de actividad de usuarios
        var activityCtx = document.getElementById('userActivityChart');
        if (activityCtx) {
            var monthLabels = [];
            var monthData = [];
            
            @foreach($usersByMonth as $item)
                monthLabels.push('{{ $item["month"] }}');
                monthData.push({{ $item["total"] }});
            @endforeach
            
            new Chart(activityCtx, {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Nuevos usuarios',
                        data: monthData,
                        backgroundColor: 'rgba(78, 115, 223, 0.05)',
                        borderColor: '#4e73df',
                        pointBackgroundColor: '#4e73df',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#4e73df',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
