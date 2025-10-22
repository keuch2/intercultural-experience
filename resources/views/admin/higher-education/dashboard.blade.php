@extends('layouts.admin')

@section('title', 'Higher Education Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Higher Education Program Dashboard</h1>
        <div>
            <a href="{{ route('admin.higher-education.applications') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-file-alt"></i> Ver Aplicaciones
            </a>
            <a href="{{ route('admin.higher-education.universities') }}" class="btn btn-success btn-sm">
                <i class="fas fa-university"></i> Ver Universidades
            </a>
        </div>
    </div>

    <!-- Stats Row 1 -->
    <div class="row">
        <!-- Total Applications -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Aplicaciones
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_applications'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-warning">
                            <i class="fas fa-clock"></i> {{ $stats['pending_applications'] }} Pendientes
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accepted Applications -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Aplicaciones Aceptadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['accepted_applications'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-success">
                            @php
                                $rate = $stats['total_applications'] > 0 ? round(($stats['accepted_applications'] / $stats['total_applications']) * 100, 1) : 0;
                            @endphp
                            <i class="fas fa-percentage"></i> {{ $rate }}% tasa de aceptación
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Universities -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Universidades
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['universities'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-university fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-primary">
                            <i class="fas fa-handshake"></i> {{ $stats['partner_universities'] }} Partners
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scholarships -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Becas Activas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_scholarships'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-success">
                            <i class="fas fa-award"></i> {{ $stats['awarded_scholarships'] }} Otorgadas
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Applications -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Aplicaciones Recientes</h6>
                    <a href="{{ route('admin.higher-education.applications') }}" class="btn btn-sm btn-primary">
                        Ver Todas
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>Universidad</th>
                                    <th>Nivel</th>
                                    <th>Programa</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentApplications as $app)
                                <tr>
                                    <td>
                                        <strong>{{ $app->user->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $app->user->email ?? '' }}</small>
                                    </td>
                                    <td>{{ $app->university->university_name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst($app->degree_level) }}
                                        </span>
                                    </td>
                                    <td>{{ $app->major_field }}</td>
                                    <td>
                                        <span class="badge badge-{{ $app->status_badge_color }}">
                                            {{ ucfirst(str_replace('_', ' ', $app->application_status)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.higher-education.application.show', $app->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay aplicaciones recientes</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Top Universities -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Universidades Más Populares</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Universidad</th>
                                    <th>Estado</th>
                                    <th>Tipo</th>
                                    <th>Aplicaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topUniversities as $uni)
                                <tr>
                                    <td>
                                        <strong>{{ $uni->university_name }}</strong>
                                        @if($uni->is_partner_university)
                                            <span class="badge badge-success badge-sm">Partner</span>
                                        @endif
                                    </td>
                                    <td>{{ $uni->city }}, {{ $uni->state }}</td>
                                    <td>
                                        <span class="badge badge-secondary">{{ $uni->type_name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">{{ $uni->applications_count }}</span>
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
            <!-- By Degree Level -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Por Nivel de Grado</h6>
                </div>
                <div class="card-body">
                    <canvas id="degreeLevelChart"></canvas>
                    
                    <hr>
                    
                    <div class="mt-3">
                        @foreach($byDegreeLevel as $level)
                        <div class="mb-2">
                            <strong>{{ ucfirst($level->degree_level) }}</strong>
                            <span class="float-right text-muted">{{ $level->total }}</span>
                            <div class="progress" style="height: 5px;">
                                @php
                                    $percentage = ($level->total / max($stats['total_applications'], 1)) * 100;
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
                        <a href="{{ route('admin.higher-education.applications') }}?status=submitted" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-clock text-warning"></i>
                            Aplicaciones Nuevas
                            <span class="badge badge-warning float-right">{{ $stats['pending_applications'] }}</span>
                        </a>
                        <a href="{{ route('admin.higher-education.applications') }}?status=accepted" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-check text-success"></i>
                            Aceptadas
                            <span class="badge badge-success float-right">{{ $stats['accepted_applications'] }}</span>
                        </a>
                        <a href="{{ route('admin.higher-education.scholarships') }}?active=1" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-graduation-cap text-info"></i>
                            Becas Disponibles
                            <span class="badge badge-info float-right">{{ $stats['active_scholarships'] }}</span>
                        </a>
                        <a href="{{ route('admin.higher-education.scholarship-applications') }}?status=submitted" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-award text-primary"></i>
                            Aplicaciones de Becas
                        </a>
                        <a href="{{ route('admin.higher-education.matching') }}" 
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
    // Degree Level Chart
    const ctx = document.getElementById('degreeLevelChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($byDegreeLevel->pluck('degree_level')->map(fn($l) => ucfirst($l))) !!},
                datasets: [{
                    data: {!! json_encode($byDegreeLevel->pluck('total')) !!},
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e'
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
