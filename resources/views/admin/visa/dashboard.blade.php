@extends('layouts.admin')

@section('title', 'Dashboard Proceso de Visa')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-passport"></i> Proceso de Visa
        </h1>
        <div>
            <a href="{{ route('admin.visa.index') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-list"></i> Ver Todos
            </a>
            <a href="{{ route('admin.visa.calendar') }}" class="btn btn-sm btn-info">
                <i class="fas fa-calendar"></i> Calendario
            </a>
        </div>
    </div>

    <!-- KPIs Row -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">En Proceso</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalInProcess }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Aprobadas (Este Mes)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $approvedThisMonth }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rechazadas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $rejectedTotal }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Citas Próximas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $upcomingAppointments->count() }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Pendientes por Etapa -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pendientes por Etapa</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>DS-160</span>
                            <span class="badge badge-info">{{ $pendingDs160 }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-info" style="width: {{ $totalInProcess > 0 ? ($pendingDs160 / $totalInProcess * 100) : 0 }}%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>DS-2019</span>
                            <span class="badge badge-warning">{{ $pendingDs2019 }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" style="width: {{ $totalInProcess > 0 ? ($pendingDs2019 / $totalInProcess * 100) : 0 }}%"></div>
                        </div>
                    </div>

                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span>SEVIS</span>
                            <span class="badge badge-primary">{{ $pendingSevis }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $totalInProcess > 0 ? ($pendingSevis / $totalInProcess * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Próximas Citas Consulares -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Próximas Citas Consulares (7 días)</h6>
                </div>
                <div class="card-body">
                    @if($upcomingAppointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Participante</th>
                                        <th>Fecha/Hora</th>
                                        <th>Ubicación</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($upcomingAppointments as $visa)
                                        <tr>
                                            <td>
                                                <a href="{{ route('admin.visa.timeline', $visa->user_id) }}">
                                                    {{ $visa->user->name }}
                                                </a>
                                            </td>
                                            <td>
                                                {{ $visa->consular_appointment_date->format('d/m/Y H:i') }}
                                                <br>
                                                <small class="text-muted">
                                                    {{ $visa->consular_appointment_date->diffForHumans() }}
                                                </small>
                                            </td>
                                            <td>{{ $visa->consular_appointment_location ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('admin.visa.timeline', $visa->user_id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">
                            <i class="fas fa-info-circle"></i> No hay citas consulares programadas en los próximos 7 días.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
