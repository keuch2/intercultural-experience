@extends('layouts.admin')

@section('title', 'Dashboard - Timeline de Aplicaciones')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line"></i> Dashboard - Timeline de Aplicaciones
        </h1>
        <a href="{{ route('admin.applications.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-list"></i> Ver Todas las Aplicaciones
        </a>
    </div>

    <!-- Status Count Cards -->
    <div class="row">
        @php
            $statusLabels = [
                'draft' => ['label' => 'Borradores', 'icon' => 'fa-file-alt', 'color' => 'secondary'],
                'submitted' => ['label' => 'Enviadas', 'icon' => 'fa-paper-plane', 'color' => 'info'],
                'under_review' => ['label' => 'En Revisión', 'icon' => 'fa-search', 'color' => 'warning'],
                'documents_pending' => ['label' => 'Docs. Pendientes', 'icon' => 'fa-file-upload', 'color' => 'warning'],
                'interview_scheduled' => ['label' => 'Entrevistas Agendadas', 'icon' => 'fa-calendar-check', 'color' => 'info'],
                'interview_completed' => ['label' => 'Entrevistas Completadas', 'icon' => 'fa-check-circle', 'color' => 'success'],
                'approved' => ['label' => 'Aprobadas', 'icon' => 'fa-thumbs-up', 'color' => 'success'],
                'rejected' => ['label' => 'Rechazadas', 'icon' => 'fa-times-circle', 'color' => 'danger'],
            ];
        @endphp

        @foreach(['submitted', 'under_review', 'approved', 'rejected'] as $status)
            @php
                $count = $statusCounts[$status] ?? 0;
                $info = $statusLabels[$status];
            @endphp
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-{{ $info['color'] }} shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-{{ $info['color'] }} text-uppercase mb-1">
                                    {{ $info['label'] }}
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $count }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas {{ $info['icon'] }} fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Status Distribution -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Distribución por Estado</h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Average Time per Status -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tiempo Promedio por Estado (días)</h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="timeChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stuck Applications Alert -->
    @if($stuckApplications->count() > 0)
        <div class="card shadow mb-4 border-left-danger">
            <div class="card-header py-3 bg-danger text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Aplicaciones Estancadas (más de 7 días sin cambios)
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Participante</th>
                                <th>Programa</th>
                                <th>Estado</th>
                                <th>Días Estancado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stuckApplications as $app)
                                <tr>
                                    <td>#{{ $app->id }}</td>
                                    <td>
                                        <strong>{{ $app->user->name }}</strong><br>
                                        <small class="text-muted">{{ $app->user->email }}</small>
                                    </td>
                                    <td>{{ $app->program->name }}</td>
                                    <td>
                                        <span class="badge badge-warning">
                                            {{ strtoupper($app->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger">
                                            {{ $app->updated_at->diffInDays(now()) }} días
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.applications.timeline', $app->id) }}" 
                                           class="btn btn-sm btn-primary" title="Ver Timeline">
                                            <i class="fas fa-project-diagram"></i>
                                        </a>
                                        <a href="{{ route('admin.applications.show', $app->id) }}" 
                                           class="btn btn-sm btn-info" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Recent Status Changes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history"></i> Cambios Recientes de Estado
            </h6>
        </div>
        <div class="card-body">
            @if($recentChanges->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Aplicación</th>
                                <th>Participante</th>
                                <th>Cambio</th>
                                <th>Notas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentChanges as $change)
                                <tr>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($change->created_at)->format('d/m/Y H:i') }}</small>
                                    </td>
                                    <td>#{{ $change->application_id }}</td>
                                    <td>{{ $change->user_name }}</td>
                                    <td>
                                        @if($change->old_status)
                                            <span class="badge badge-secondary">{{ strtoupper($change->old_status) }}</span>
                                        @else
                                            <span class="text-muted">Inicial</span>
                                        @endif
                                        <i class="fas fa-arrow-right text-muted mx-1"></i>
                                        <span class="badge badge-info">{{ strtoupper($change->new_status) }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $change->notes ? Str::limit($change->notes, 30) : '-' }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.applications.timeline', $change->app_id) }}" 
                                           class="btn btn-xs btn-primary" title="Ver Timeline">
                                            <i class="fas fa-project-diagram"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No hay cambios de estado recientes.
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estados Activos</h6>
                </div>
                <div class="card-body">
                    @php
                        $activeStatuses = array_diff_key($statusCounts, array_flip(['approved', 'rejected']));
                        $totalActive = array_sum($activeStatuses);
                    @endphp
                    <h2 class="text-center">{{ $totalActive }}</h2>
                    <p class="text-center text-muted">Aplicaciones en proceso</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tasa de Aprobación</h6>
                </div>
                <div class="card-body">
                    @php
                        $approved = $statusCounts['approved'] ?? 0;
                        $rejected = $statusCounts['rejected'] ?? 0;
                        $total = $approved + $rejected;
                        $approvalRate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;
                    @endphp
                    <h2 class="text-center text-success">{{ $approvalRate }}%</h2>
                    <p class="text-center text-muted">{{ $approved }}/{{ $total }} aprobadas</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Requieren Atención</h6>
                </div>
                <div class="card-body">
                    <h2 class="text-center text-danger">{{ $stuckApplications->count() }}</h2>
                    <p class="text-center text-muted">Estancadas > 7 días</p>
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
    // Status Distribution Chart
    const statusData = @json($statusCounts);
    const statusLabels = @json($statusLabels);
    
    const statusChartCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusChartCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData).map(key => statusLabels[key]?.label || key),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: [
                    '#858796', '#36b9cc', '#f6c23e', '#e74a3b', 
                    '#4e73df', '#1cc88a', '#1cc88a', '#e74a3b'
                ]
            }]
        },
        options: {
            responsive: true,
            animation: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Average Time Chart
    const avgTimeData = @json($avgTimeByStatus);
    const timeChartCtx = document.getElementById('timeChart').getContext('2d');
    new Chart(timeChartCtx, {
        type: 'bar',
        data: {
            labels: avgTimeData.map(item => statusLabels[item.new_status]?.label || item.new_status),
            datasets: [{
                label: 'Días Promedio',
                data: avgTimeData.map(item => item.avg_days || 0),
                backgroundColor: '#4e73df',
                borderColor: '#4e73df',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            animation: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection

@section('styles')
<style>
.btn-xs {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>
@endsection
