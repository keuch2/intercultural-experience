@extends('layouts.admin')

@section('title', 'Dashboard - Evaluaciones de Inglés')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-bar"></i> Dashboard - Evaluaciones de Inglés
        </h1>
        <div>
            <a href="{{ route('admin.english-evaluations.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-list"></i> Ver Todas
            </a>
            <a href="{{ route('admin.english-evaluations.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Nueva Evaluación
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Evaluaciones
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalEvaluations }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
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
                                Participantes Únicos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $uniqueParticipants }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Promedio General
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                        {{ $averageScore }}/100
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: {{ $averageScore }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                Requieren Re-evaluación
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $needReevaluation->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Distribución por Nivel CEFR -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución por Nivel CEFR</h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="cefrChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Distribución por Clasificación -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución por Clasificación</h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="classificationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Evolución Mensual -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Evolución Mensual (Últimos 6 Meses)</h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="evolutionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Mejores Evaluaciones Recientes -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Top 10 Evaluaciones (Últimos 30 Días)
                    </h6>
                </div>
                <div class="card-body">
                    @if($recentBest->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Participante</th>
                                        <th>Puntaje</th>
                                        <th>Nivel</th>
                                        <th>Fecha</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentBest as $index => $eval)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <a href="{{ route('admin.english-evaluations.show', $eval->id) }}">
                                                    {{ $eval->user->name }}
                                                </a>
                                            </td>
                                            <td>
                                                <strong class="text-success">{{ $eval->score }}/100</strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $eval->cefr_level }}</span>
                                            </td>
                                            <td>{{ $eval->evaluated_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No hay evaluaciones recientes en los últimos 30 días.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Necesitan Re-evaluación -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Requieren Re-evaluación (Score < 60)
                    </h6>
                </div>
                <div class="card-body">
                    @if($needReevaluation->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Participante</th>
                                        <th>Puntaje</th>
                                        <th>Intentos</th>
                                        <th>Última Eval.</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($needReevaluation as $eval)
                                        <tr>
                                            <td>{{ $eval->user->name }}</td>
                                            <td>
                                                <strong class="text-danger">{{ $eval->score }}/100</strong>
                                            </td>
                                            <td>
                                                <span class="badge badge-warning">
                                                    {{ $eval->user->englishEvaluations->count() }}/3
                                                </span>
                                            </td>
                                            <td>{{ $eval->evaluated_at->format('d/m/Y') }}</td>
                                            <td>
                                                @if($eval->user->englishEvaluations->count() < 3)
                                                    <a href="{{ route('admin.english-evaluations.create') }}" 
                                                       class="btn btn-sm btn-primary">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">Límite alcanzado</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle"></i>
                            Todos los participantes tienen evaluaciones satisfactorias.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Gráfico de Niveles CEFR
    const cefrData = @json($cefrDistribution);
    const cefrCtx = document.getElementById('cefrChart').getContext('2d');
    new Chart(cefrCtx, {
        type: 'doughnut',
        data: {
            labels: cefrData.map(item => item.cefr_level),
            datasets: [{
                data: cefrData.map(item => item.count),
                backgroundColor: [
                    '#e74a3b', // A1
                    '#fd7e14', // A2
                    '#f6c23e', // B1
                    '#1cc88a', // B2
                    '#36b9cc', // C1
                    '#4e73df'  // C2
                ]
            }]
        },
        options: {
            responsive: true,
            animation: false,
            plugins: {
                legend: {
                    position: 'right',
                }
            }
        }
    });

    // Gráfico de Clasificación
    const classData = @json($classificationDistribution);
    const classCtx = document.getElementById('classificationChart').getContext('2d');
    new Chart(classCtx, {
        type: 'bar',
        data: {
            labels: classData.map(item => item.classification),
            datasets: [{
                label: 'Cantidad',
                data: classData.map(item => item.count),
                backgroundColor: [
                    '#1cc88a', // EXCELLENT
                    '#4e73df', // GREAT
                    '#36b9cc', // GOOD
                    '#f6c23e'  // INSUFFICIENT
                ]
            }]
        },
        options: {
            responsive: true,
            animation: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Gráfico de Evolución Mensual
    const evolutionData = @json($monthlyEvolution);
    const evolutionCtx = document.getElementById('evolutionChart').getContext('2d');
    new Chart(evolutionCtx, {
        type: 'line',
        data: {
            labels: evolutionData.map(item => item.month),
            datasets: [{
                label: 'Promedio de Puntaje',
                data: evolutionData.map(item => item.avg_score),
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                fill: true,
                tension: 0.3
            }, {
                label: 'Cantidad de Evaluaciones',
                data: evolutionData.map(item => item.count),
                borderColor: '#1cc88a',
                backgroundColor: 'rgba(28, 200, 138, 0.1)',
                fill: true,
                tension: 0.3,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            animation: false,
            scales: {
                y: {
                    beginAtZero: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Puntaje Promedio'
                    }
                },
                y1: {
                    beginAtZero: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Cantidad'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            }
        }
    });
});
</script>
@endsection
