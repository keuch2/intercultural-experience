@extends('layouts.admin')

@section('title', 'Language Program Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Language Program Dashboard</h1>
        <div>
            <a href="{{ route('admin.language-program.programs') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-book"></i> Ver Programas
            </a>
            <a href="{{ route('admin.language-program.statistics') }}" class="btn btn-info btn-sm">
                <i class="fas fa-chart-bar"></i> Estadísticas
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
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Programs -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Programas Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_programs'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completed Programs -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Completados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['completed_programs'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-certificate fa-2x text-gray-300"></i>
                        </div>
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
                                Pendientes
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
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Programs -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Programas Recientes</h6>
                    <a href="{{ route('admin.language-program.programs') }}" class="btn btn-sm btn-primary">
                        Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Idioma</th>
                                    <th>Programa</th>
                                    <th>Escuela</th>
                                    <th>Duración</th>
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
                                    <td>
                                        <span class="badge badge-primary">
                                            {{ $program->language_name }}
                                        </span>
                                    </td>
                                    <td>{{ $program->program_type_name }}</td>
                                    <td>
                                        {{ Str::limit($program->school_name, 25) }}
                                        <br>
                                        <small class="text-muted">{{ $program->school_city }}, {{ $program->school_state }}</small>
                                    </td>
                                    <td>
                                        {{ $program->weeks_duration }} semanas
                                        <br>
                                        <small class="text-muted">{{ $program->hours_per_week }}h/semana</small>
                                    </td>
                                    <td>{{ $program->start_date?->format('M d, Y') ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $program->status_badge_color }}">
                                            {{ ucfirst(str_replace('_', ' ', $program->status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.language-program.program.show', $program->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay programas recientes</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- By City -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Ciudades Más Populares</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Ciudad</th>
                                    <th>Estado</th>
                                    <th>Estudiantes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byCity as $city)
                                <tr>
                                    <td><strong>{{ $city->school_city }}</strong></td>
                                    <td>{{ $city->school_state }}</td>
                                    <td>
                                        <span class="badge badge-primary">{{ $city->total }}</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 mb-4">
            <!-- By Language -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Por Idioma</h6>
                </div>
                <div class="card-body">
                    <canvas id="languageChart"></canvas>
                    
                    <hr>
                    
                    <div class="mt-3">
                        @foreach($byLanguage as $lang)
                        <div class="mb-2">
                            <strong>{{ ucfirst($lang->language) }}</strong>
                            <span class="float-right text-muted">{{ $lang->total }}</span>
                            <div class="progress" style="height: 5px;">
                                @php
                                    $maxTotal = $byLanguage->max('total');
                                    $percentage = ($lang->total / max($maxTotal, 1)) * 100;
                                @endphp
                                <div class="progress-bar bg-primary" 
                                     style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- By Program Type -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Por Tipo de Programa</h6>
                </div>
                <div class="card-body">
                    @foreach($byProgramType as $type)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <small class="font-weight-bold">
                                {{ ucfirst(str_replace('_', ' ', $type->program_type)) }}
                            </small>
                            <small class="text-muted">{{ $type->total }}</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            @php
                                $maxTotal = $byProgramType->max('total');
                                $percentage = ($type->total / max($maxTotal, 1)) * 100;
                            @endphp
                            <div class="progress-bar bg-info" 
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="{{ route('admin.language-program.programs') }}?status=submitted" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-clock text-warning"></i>
                            Programas Nuevos
                            <span class="badge badge-warning float-right">{{ $stats['pending_programs'] }}</span>
                        </a>
                        <a href="{{ route('admin.language-program.programs') }}?status=active" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-play text-success"></i>
                            Programas Activos
                            <span class="badge badge-success float-right">{{ $stats['active_programs'] }}</span>
                        </a>
                        <a href="{{ route('admin.language-program.programs') }}?status=completed" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-certificate text-info"></i>
                            Completados
                            <span class="badge badge-info float-right">{{ $stats['completed_programs'] }}</span>
                        </a>
                        <a href="{{ route('admin.language-program.schools') }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-school text-primary"></i>
                            Reporte de Escuelas
                        </a>
                        <a href="{{ route('admin.language-program.statistics') }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-chart-line text-warning"></i>
                            Estadísticas Completas
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
    // Language Chart
    const ctx = document.getElementById('languageChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($byLanguage->pluck('language')->map(fn($l) => ucfirst($l))) !!},
                datasets: [{
                    data: {!! json_encode($byLanguage->pluck('total')) !!},
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'
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
