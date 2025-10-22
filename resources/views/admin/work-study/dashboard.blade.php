@extends('layouts.admin')

@section('title', 'Work & Study Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Work & Study Program Dashboard</h1>
        <div>
            <a href="{{ route('admin.work-study.programs') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-graduation-cap"></i> Ver Programas
            </a>
            <a href="{{ route('admin.work-study.employers') }}" class="btn btn-success btn-sm">
                <i class="fas fa-building"></i> Ver Empleadores
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row">
        <!-- Total Programs -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Programas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_programs'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-success">
                            <i class="fas fa-check"></i> {{ $stats['active_programs'] }} Activos
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Programs -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Programas Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_programs'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employers -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Empleadores
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_employers'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-success">
                            <i class="fas fa-shield-alt"></i> {{ $stats['verified_employers'] }} Verificados
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Placements -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Colocaciones Activas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_placements'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-briefcase fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-muted">
                            Total: {{ $stats['total_placements'] }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Programs -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Programas Recientes</h6>
                    <a href="{{ route('admin.work-study.programs') }}" class="btn btn-sm btn-primary">
                        Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Escuela</th>
                                    <th>Ciudad</th>
                                    <th>Programa</th>
                                    <th>Inicio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPrograms as $program)
                                <tr>
                                    <td>
                                        <strong>{{ $program->user->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $program->program_number }}</small>
                                    </td>
                                    <td>{{ Str::limit($program->language_school_name, 25) }}</td>
                                    <td>{{ $program->school_city }}, {{ $program->school_state }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst(str_replace('_', ' ', $program->program_type)) }}
                                        </span>
                                        <br>
                                        <small>{{ $program->weeks_of_study }} semanas</small>
                                    </td>
                                    <td>{{ $program->program_start_date?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $program->status_badge_color }}">
                                            {{ ucfirst(str_replace('_', ' ', $program->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.work-study.program.show', $program->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay programas recientes</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Active Placements -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Colocaciones Activas</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Empleador</th>
                                    <th>Posición</th>
                                    <th>Salario/hora</th>
                                    <th>Progreso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activePlacements as $placement)
                                <tr>
                                    <td>
                                        <strong>{{ $placement->user->name ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ $placement->employer->employer_name ?? 'N/A' }}</td>
                                    <td>{{ $placement->job_title }}</td>
                                    <td>${{ number_format($placement->hourly_wage, 2) }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" 
                                                 role="progressbar" 
                                                 style="width: {{ $placement->completion_percentage }}%"
                                                 aria-valuenow="{{ $placement->completion_percentage }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                                {{ $placement->completion_percentage }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.work-study.placement.show', $placement->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay colocaciones activas</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 mb-4">
            <!-- By City -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ciudades Más Populares</h6>
                </div>
                <div class="card-body">
                    <canvas id="cityChart"></canvas>
                    
                    <hr>
                    
                    <div class="mt-3">
                        @foreach($byCity as $city)
                        <div class="mb-2">
                            <strong>{{ $city->school_city }}</strong>
                            <span class="float-right text-muted">{{ $city->total }}</span>
                            <div class="progress" style="height: 5px;">
                                @php
                                    $maxTotal = $byCity->max('total');
                                    $percentage = ($city->total / max($maxTotal, 1)) * 100;
                                @endphp
                                <div class="progress-bar bg-primary" 
                                     style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('admin.work-study.programs') }}?status=submitted" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-clock text-warning"></i>
                            Programas Nuevos
                            <span class="badge badge-warning float-right">{{ $stats['pending_programs'] }}</span>
                        </a>
                        <a href="{{ route('admin.work-study.programs') }}?status=active" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-check text-success"></i>
                            Programas Activos
                            <span class="badge badge-success float-right">{{ $stats['active_programs'] }}</span>
                        </a>
                        <a href="{{ route('admin.work-study.employers') }}?verified=0" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-shield-alt text-danger"></i>
                            Empleadores sin Verificar
                        </a>
                        <a href="{{ route('admin.work-study.placements') }}?status=active" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-briefcase text-info"></i>
                            Colocaciones Activas
                            <span class="badge badge-info float-right">{{ $stats['active_placements'] }}</span>
                        </a>
                        <a href="{{ route('admin.work-study.matching') }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-magic text-warning"></i>
                            Sistema de Matching
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3"></script>
<script>
    // City Chart
    const ctx = document.getElementById('cityChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($byCity->pluck('school_city')) !!},
                datasets: [{
                    data: {!! json_encode($byCity->pluck('total')) !!},
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'
                    ]
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
</script>
@endpush
@endsection
