@extends('layouts.admin')

@section('title', 'Work & Travel Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Work & Travel Dashboard</h1>
        <div>
            <a href="{{ route('admin.work-travel.validations') }}" class="btn btn-primary">
                <i class="fas fa-check-circle"></i> Validaciones
            </a>
            <a href="{{ route('admin.work-travel.employers') }}" class="btn btn-info">
                <i class="fas fa-building"></i> Empleadores
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
                                Total Participantes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_participants']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                Validados Presencial
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['presencial_validated']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-double fa-2x text-gray-300"></i>
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
                                Contratos Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['active_contracts']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-contract fa-2x text-gray-300"></i>
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

    <!-- Season Distribution -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución por Temporada</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 text-center">
                            <h4 class="text-warning">
                                <i class="fas fa-sun"></i> Summer
                            </h4>
                            <h2>{{ $stats['summer_participants'] }}</h2>
                            <small class="text-muted">Participantes</small>
                        </div>
                        <div class="col-6 text-center">
                            <h4 class="text-info">
                                <i class="fas fa-snowflake"></i> Winter
                            </h4>
                            <h2>{{ $stats['winter_participants'] }}</h2>
                            <small class="text-muted">Participantes</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estado de Contratos</h6>
                </div>
                <div class="card-body">
                    <canvas id="contractsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Validations & Top Employers -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Validaciones Recientes</h6>
                    <a href="{{ route('admin.work-travel.validations') }}" class="btn btn-sm btn-primary">
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
                                    <th>Tipo</th>
                                    <th>Temporada</th>
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
                                    <td>{{ $validation->university_name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $validation->study_type == 'presencial' ? 'success' : 'warning' }}">
                                            {{ ucfirst($validation->study_type) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($validation->season == 'summer')
                                            <span class="text-warning"><i class="fas fa-sun"></i> Summer</span>
                                        @else
                                            <span class="text-info"><i class="fas fa-snowflake"></i> Winter</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($validation->validation_status == 'approved')
                                            <span class="badge badge-success">Aprobado</span>
                                        @elseif($validation->validation_status == 'rejected')
                                            <span class="badge badge-danger">Rechazado</span>
                                        @else
                                            <span class="badge badge-warning">Pendiente</span>
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
                    <h6 class="m-0 font-weight-bold text-primary">Top Empleadores</h6>
                    <a href="{{ route('admin.work-travel.employers') }}" class="btn btn-sm btn-info">
                        Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    @forelse($topEmployers as $employer)
                    <div class="mb-3 pb-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $employer->company_name }}</h6>
                                <small class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> {{ $employer->city }}, {{ $employer->state }}
                                </small>
                                <br>
                                <small>
                                    <i class="fas fa-star text-warning"></i> {{ number_format($employer->rating, 1) }}
                                    ({{ $employer->total_reviews }} reviews)
                                </small>
                            </div>
                            <div class="text-right">
                                <span class="badge badge-primary">{{ $employer->positions_available }} pos.</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-muted">No hay empleadores registrados</p>
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
// Contracts Status Chart
const contractsCtx = document.getElementById('contractsChart').getContext('2d');
new Chart(contractsCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_map('ucfirst', array_keys($contractsByStatus->toArray()))) !!},
        datasets: [{
            data: {!! json_encode(array_values($contractsByStatus->toArray())) !!},
            backgroundColor: [
                '#4e73df',
                '#1cc88a',
                '#36b9cc',
                '#f6c23e',
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
            label: 'Contratos',
            data: {!! json_encode($monthlyPlacements->pluck('count')) !!},
            borderColor: '#4e73df',
            backgroundColor: 'rgba(78, 115, 223, 0.1)',
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
