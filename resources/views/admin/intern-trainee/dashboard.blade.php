@extends('layouts.admin')

@section('title', 'Intern/Trainee Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Intern/Trainee Program Dashboard</h1>
        <div>
            <a href="{{ route('admin.intern-trainee.validations') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-check-circle"></i> Ver Validaciones
            </a>
            <a href="{{ route('admin.intern-trainee.companies') }}" class="btn btn-success btn-sm">
                <i class="fas fa-building"></i> Ver Empresas
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row">
        <!-- Total Participants -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Participantes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_participants'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-info">
                            <i class="fas fa-graduation-cap"></i> {{ $stats['interns'] }} Interns
                        </span>
                        <span class="ml-2 text-success">
                            <i class="fas fa-briefcase"></i> {{ $stats['trainees'] }} Trainees
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Validations -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Validaciones Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending_validations'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-success">
                            <i class="fas fa-check"></i> {{ $stats['approved'] }} Aprobados
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Active Training Plans -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Training Plans Activos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_plans'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-info">
                            <i class="fas fa-check-double"></i> {{ $stats['completed_plans'] }} Completados
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Host Companies -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Empresas Activas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['host_companies'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                    <div class="mt-2 small">
                        <span class="text-muted">
                            <i class="fas fa-calendar-alt"></i> {{ number_format($stats['avg_duration'], 1) }} meses promedio
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Recent Validations -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Validaciones Recientes</h6>
                    <a href="{{ route('admin.intern-trainee.validations') }}" class="btn btn-sm btn-primary">
                        Ver Todas
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Participante</th>
                                    <th>Tipo</th>
                                    <th>Industria</th>
                                    <th>Duración</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
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
                                        @if($validation->program_type == 'intern')
                                            <span class="badge badge-info">Intern</span>
                                        @else
                                            <span class="badge badge-success">Trainee</span>
                                        @endif
                                    </td>
                                    <td>{{ $validation->industry_sector ?? 'N/A' }}</td>
                                    <td>{{ $validation->duration_months ?? 0 }} meses</td>
                                    <td>
                                        @if($validation->validation_status == 'approved')
                                            <span class="badge badge-success">Aprobado</span>
                                        @elseif($validation->validation_status == 'pending_review')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @elseif($validation->validation_status == 'rejected')
                                            <span class="badge badge-danger">Rechazado</span>
                                        @else
                                            <span class="badge badge-secondary">Incompleto</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.intern-trainee.validation.show', $validation->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
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

            <!-- Active Training Plans -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-success">Training Plans Activos</h6>
                    <a href="{{ route('admin.intern-trainee.plans') }}" class="btn btn-sm btn-success">
                        Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Plan</th>
                                    <th>Participante</th>
                                    <th>Empresa</th>
                                    <th>Progreso</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activePlans as $plan)
                                <tr>
                                    <td>
                                        <strong>{{ $plan->plan_title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $plan->position_title }}</small>
                                    </td>
                                    <td>{{ $plan->user->name ?? 'N/A' }}</td>
                                    <td>{{ $plan->hostCompany->company_name ?? 'N/A' }}</td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" 
                                                 style="width: {{ $plan->completion_percentage }}%">
                                                {{ $plan->completion_percentage }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.intern-trainee.plan.show', $plan->id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay training plans activos</td>
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
            <!-- By Industry -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Por Industria</h6>
                </div>
                <div class="card-body">
                    <canvas id="industryChart"></canvas>
                    
                    <hr>
                    
                    <div class="mt-3">
                        @foreach($byIndustry as $industry)
                        <div class="mb-2">
                            <strong>{{ $industry->industry_sector ?? 'Sin especificar' }}</strong>
                            <span class="float-right text-muted">{{ $industry->total }}</span>
                            <div class="progress" style="height: 5px;">
                                @php
                                    $percentage = ($industry->total / max($stats['total_participants'], 1)) * 100;
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
                        <a href="{{ route('admin.intern-trainee.validations') }}?status=pending_review" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-clock text-warning"></i>
                            Validaciones Pendientes
                            <span class="badge badge-warning float-right">{{ $stats['pending_validations'] }}</span>
                        </a>
                        <a href="{{ route('admin.intern-trainee.plans') }}?status=pending_sponsor_approval" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-clipboard-check text-info"></i>
                            Plans por Aprobar
                        </a>
                        <a href="{{ route('admin.intern-trainee.companies') }}?verified=0" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-building text-primary"></i>
                            Empresas por Verificar
                        </a>
                        <a href="{{ route('admin.intern-trainee.matching') }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-handshake text-success"></i>
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
    // Industry Chart
    const ctx = document.getElementById('industryChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($byIndustry->pluck('industry_sector')) !!},
                datasets: [{
                    data: {!! json_encode($byIndustry->pluck('total')) !!},
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                        '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'
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
