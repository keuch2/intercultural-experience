@extends('layouts.admin')

@section('title', 'Procesos de Visa')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-passport"></i> Procesos de Visa
        </h1>
        <div>
            <a href="{{ route('admin.visa.dashboard') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="{{ route('admin.visa.calendar') }}" class="btn btn-sm btn-info">
                <i class="fas fa-calendar"></i> Calendario
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.visa.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Buscar Participante</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Nombre o email...">
                </div>
                <div class="col-md-3">
                    <label for="current_step" class="form-label">Etapa Actual</label>
                    <select class="form-control" id="current_step" name="current_step">
                        <option value="">Todas</option>
                        <option value="documentation" {{ request('current_step') == 'documentation' ? 'selected' : '' }}>Documentación</option>
                        <option value="sponsor_interview" {{ request('current_step') == 'sponsor_interview' ? 'selected' : '' }}>Sponsor Interview</option>
                        <option value="job_interview" {{ request('current_step') == 'job_interview' ? 'selected' : '' }}>Job Interview</option>
                        <option value="ds160" {{ request('current_step') == 'ds160' ? 'selected' : '' }}>DS-160</option>
                        <option value="ds2019" {{ request('current_step') == 'ds2019' ? 'selected' : '' }}>DS-2019</option>
                        <option value="sevis" {{ request('current_step') == 'sevis' ? 'selected' : '' }}>SEVIS</option>
                        <option value="consular_fee" {{ request('current_step') == 'consular_fee' ? 'selected' : '' }}>Tasa Consular</option>
                        <option value="appointment" {{ request('current_step') == 'appointment' ? 'selected' : '' }}>Cita Consular</option>
                        <option value="result" {{ request('current_step') == 'result' ? 'selected' : '' }}>Resultado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Resultado</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">Todos</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprobada</option>
                        <option value="correspondence" {{ request('status') == 'correspondence' ? 'selected' : '' }}>Correspondencia</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazada</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.visa.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Procesos -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Procesos de Visa ({{ $visaProcesses->total() }})
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Participante</th>
                            <th>Etapa Actual</th>
                            <th>Progreso</th>
                            <th>Cita Consular</th>
                            <th>Resultado</th>
                            <th>Actualizado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($visaProcesses as $visa)
                        <tr>
                            <td>{{ $visa->id }}</td>
                            <td>
                                <a href="{{ route('admin.participants.show', $visa->user_id) }}">
                                    {{ $visa->user->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ ucfirst(str_replace('_', ' ', $visa->current_step)) }}
                                </span>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" role="progressbar" 
                                         style="width: {{ $visa->progress_percentage }}%" 
                                         aria-valuenow="{{ $visa->progress_percentage }}" 
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ $visa->progress_percentage }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($visa->consular_appointment_scheduled)
                                    <i class="fas fa-calendar-check text-success"></i>
                                    {{ $visa->consular_appointment_date->format('d/m/Y') }}
                                @else
                                    <span class="text-muted">Sin cita</span>
                                @endif
                            </td>
                            <td>
                                @if($visa->visa_result && $visa->visa_result != 'pending')
                                    <span class="badge badge-{{ $visa->visa_result == 'approved' ? 'success' : ($visa->visa_result == 'rejected' ? 'danger' : 'warning') }}">
                                        {{ ucfirst($visa->visa_result) }}
                                    </span>
                                @else
                                    <span class="text-muted">En proceso</span>
                                @endif
                            </td>
                            <td>{{ $visa->updated_at->diffForHumans() }}</td>
                            <td>
                                <a href="{{ route('admin.visa.timeline', $visa->user_id) }}" 
                                   class="btn btn-sm btn-primary" title="Ver Timeline">
                                    <i class="fas fa-timeline"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No se encontraron procesos de visa
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($visaProcesses->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $visaProcesses->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
