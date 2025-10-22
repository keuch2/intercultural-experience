@extends('layouts.admin')

@section('title', 'Teachers Program Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Teachers Program Dashboard</h1>
        <div>
            <a href="{{ route('admin.teachers.validations') }}" class="btn btn-primary">
                <i class="fas fa-check-circle"></i> Validaciones
            </a>
            <a href="{{ route('admin.teachers.job-fairs') }}" class="btn btn-success">
                <i class="fas fa-calendar-alt"></i> Job Fairs
            </a>
            <a href="{{ route('admin.teachers.schools') }}" class="btn btn-info">
                <i class="fas fa-school"></i> Escuelas
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Profesores
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_teachers']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chalkboard-teacher fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                MEC Aprobados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['mec_approved']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-certificate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Escuelas Activas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['active_schools']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-school fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Posiciones Disponibles
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['available_positions']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-briefcase fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Job Fair Stats -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Job Fair Stats</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <h2 class="text-success">{{ $stats['upcoming_job_fairs'] }}</h2>
                        <p class="mb-3">Próximos Job Fairs</p>
                        
                        <div class="row">
                            <div class="col-6">
                                <h4>{{ $stats['job_fair_registered'] }}</h4>
                                <small class="text-muted">Registrados</small>
                            </div>
                            <div class="col-6">
                                <h4 class="text-success">{{ $stats['successful_placements'] }}</h4>
                                <small class="text-muted">Colocados</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estado de Profesores</h6>
                </div>
                <div class="card-body">
                    <canvas id="teachersStatusChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Validación MEC</h6>
                </div>
                <div class="card-body">
                    <div class="progress mb-3">
                        @php
                            $mecPercentage = $stats['total_teachers'] > 0 ? 
                                ($stats['mec_approved'] / $stats['total_teachers']) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $mecPercentage }}%">
                            {{ number_format($mecPercentage, 1) }}%
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-6">
                            <h5 class="text-success">{{ $stats['mec_approved'] }}</h5>
                            <small>Aprobados</small>
                        </div>
                        <div class="col-6">
                            <h5 class="text-warning">{{ $stats['total_teachers'] - $stats['mec_approved'] }}</h5>
                            <small>Pendientes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Validations & Upcoming Job Fairs -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Validaciones Recientes</h6>
                    <a href="{{ route('admin.teachers.validations') }}" class="btn btn-sm btn-primary">
                        Ver Todas
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Profesor</th>
                                    <th>Universidad</th>
                                    <th>MEC</th>
                                    <th>Experiencia</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentValidations as $validation)
                                <tr>
                                    <td>
                                        <strong>{{ $validation->user->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $validation->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        {{ $validation->university_name }}
                                        <br>
                                        <small class="text-muted">{{ $validation->teaching_degree_title }}</small>
                                    </td>
                                    <td>
                                        @if($validation->has_mec_validation)
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Aprobado
                                            </span>
                                            <br>
                                            <small>{{ $validation->mec_registration_number }}</small>
                                        @else
                                            <span class="badge badge-warning">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $validation->teaching_years_verified }} años
                                        @if($validation->has_tefl_certification || $validation->has_tesol_certification)
                                            <br>
                                            <small>
                                                @if($validation->has_tefl_certification)
                                                    <span class="badge badge-info">TEFL</span>
                                                @endif
                                                @if($validation->has_tesol_certification)
                                                    <span class="badge badge-info">TESOL</span>
                                                @endif
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($validation->validation_status == 'approved')
                                            <span class="badge badge-success">Aprobado</span>
                                        @elseif($validation->validation_status == 'rejected')
                                            <span class="badge badge-danger">Rechazado</span>
                                        @elseif($validation->validation_status == 'pending_review')
                                            <span class="badge badge-warning">En Revisión</span>
                                        @else
                                            <span class="badge badge-secondary">Incompleto</span>
                                        @endif
                                    </td>
                                    <td>{{ $validation->created_at->format('d/m/Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay validaciones recientes</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Próximos Job Fairs</h6>
                    <a href="{{ route('admin.teachers.job-fairs') }}" class="btn btn-sm btn-success">
                        Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    @forelse($upcomingJobFairs as $jobFair)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $jobFair->event_name }}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-calendar"></i> {{ $jobFair->event_date->format('d/m/Y') }}
                                </small>
                                <br>
                                <small>
                                    @if($jobFair->event_type == 'virtual')
                                        <span class="badge badge-info">Virtual</span>
                                    @elseif($jobFair->event_type == 'presencial')
                                        <span class="badge badge-primary">Presencial</span>
                                    @else
                                        <span class="badge badge-success">Híbrido</span>
                                    @endif
                                    
                                    @if($jobFair->city)
                                        <i class="fas fa-map-marker-alt ml-2"></i> {{ $jobFair->city }}, {{ $jobFair->state }}
                                    @endif
                                </small>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-primary">
                                    {{ $jobFair->registered_participants }}/{{ $jobFair->max_participants ?: '∞' }}
                                </span>
                                <br>
                                <small class="text-muted">Registrados</small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-muted">No hay job fairs programados</p>
                    @endforelse
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Top Escuelas</h6>
                    <a href="{{ route('admin.teachers.schools') }}" class="btn btn-sm btn-info">
                        Ver Todas
                    </a>
                </div>
                <div class="card-body">
                    @forelse($topSchools as $school)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $school->school_name }}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> {{ $school->city }}, {{ $school->state }}
                                </small>
                                <br>
                                <small>
                                    <span class="badge badge-{{ $school->school_type == 'public' ? 'primary' : 'info' }}">
                                        {{ ucfirst($school->school_type) }}
                                    </span>
                                    @if($school->rating)
                                        <i class="fas fa-star text-warning ml-2"></i> {{ number_format($school->rating, 1) }}
                                    @endif
                                </small>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-success">{{ $school->positions_available }} pos.</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-muted">No hay escuelas registradas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Placements Chart -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Colocaciones Mensuales</h6>
        </div>
        <div class="card-body">
            <canvas id="placementsChart"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Teachers Status Chart
const teachersCtx = document.getElementById('teachersStatusChart').getContext('2d');
new Chart(teachersCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_map(function($key) {
            return $key == 'incomplete' ? 'Incompleto' : 
                   ($key == 'pending_review' ? 'En Revisión' : 
                   ($key == 'approved' ? 'Aprobado' : 'Rechazado'));
        }, array_keys($teachersByStatus->toArray()))) !!},
        datasets: [{
            data: {!! json_encode(array_values($teachersByStatus->toArray())) !!},
            backgroundColor: [
                '#f6c23e',
                '#36b9cc',
                '#1cc88a',
                '#e74a3b'
            ]
        }]
    },
    options: {
        maintainAspectRatio: false,
        legend: {
            display: true,
            position: 'bottom'
        }
    }
});

// Monthly Placements Chart
const placementsCtx = document.getElementById('placementsChart').getContext('2d');
new Chart(placementsCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($monthlyPlacements->pluck('month')) !!},
        datasets: [{
            label: 'Colocaciones',
            data: {!! json_encode($monthlyPlacements->pluck('count')) !!},
            borderColor: '#1cc88a',
            backgroundColor: 'rgba(28, 200, 138, 0.1)',
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
@endpush
@endsection
