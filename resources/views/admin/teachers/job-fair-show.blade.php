@extends('layouts.admin')

@section('title', 'Detalle del Job Fair')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $jobFair->event_name }}</h1>
        <div>
            @if($jobFair->status == 'published')
                <form action="{{ route('admin.teachers.job-fair.open', $jobFair->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-door-open"></i> Abrir Registro
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.teachers.job-fairs') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    <div class="alert alert-{{ $jobFair->status == 'completed' ? 'success' : ($jobFair->status == 'cancelled' ? 'danger' : 'info') }}">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="alert-heading mb-0">
                    Estado: <strong>{{ strtoupper(str_replace('_', ' ', $jobFair->status)) }}</strong>
                </h5>
            </div>
            <div class="col-md-4 text-right">
                @if($jobFair->status == 'completed')
                    <strong>{{ $jobFair->successful_placements }}</strong> colocaciones exitosas
                @elseif($jobFair->status == 'registration_open')
                    <strong>{{ $jobFair->registered_participants }}</strong>/{{ $jobFair->max_participants ?: '∞' }} registrados
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-primary">{{ $jobFair->registered_participants }}</h4>
                        <small>Profesores Registrados</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-success">{{ $jobFair->registered_schools }}</h4>
                        <small>Escuelas Registradas</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-info">{{ $jobFair->total_interviews }}</h4>
                        <small>Entrevistas Realizadas</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-warning">{{ $jobFair->successful_placements }}</h4>
                        <small>Colocaciones</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Event Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detalles del Evento</h6>
                </div>
                <div class="card-body">
                    <p class="lead">{{ $jobFair->description }}</p>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Fecha y Hora</h6>
                            <p>
                                <i class="fas fa-calendar"></i> {{ $jobFair->event_date->format('d/m/Y') }}<br>
                                <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($jobFair->start_time)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($jobFair->end_time)->format('H:i') }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Tipo de Evento</h6>
                            <p>
                                @if($jobFair->event_type == 'virtual')
                                    <span class="badge badge-info badge-lg">
                                        <i class="fas fa-video"></i> Virtual
                                    </span>
                                @elseif($jobFair->event_type == 'presencial')
                                    <span class="badge badge-primary badge-lg">
                                        <i class="fas fa-users"></i> Presencial
                                    </span>
                                @else
                                    <span class="badge badge-success badge-lg">
                                        <i class="fas fa-sync"></i> Híbrido
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($jobFair->event_type != 'virtual')
                        <hr>
                        <h6>Ubicación Presencial</h6>
                        <address>
                            <strong>{{ $jobFair->venue_name }}</strong><br>
                            {{ $jobFair->venue_address }}<br>
                            {{ $jobFair->city }}, {{ $jobFair->state }}
                        </address>
                    @endif

                    @if($jobFair->event_type != 'presencial')
                        <hr>
                        <h6>Acceso Virtual</h6>
                        <p>
                            <strong>Plataforma:</strong> {{ $jobFair->virtual_platform }}<br>
                            @if($jobFair->meeting_link)
                                <strong>Link:</strong> <a href="{{ $jobFair->meeting_link }}" target="_blank">{{ $jobFair->meeting_link }}</a>
                            @endif
                        </p>
                    @endif
                </div>
            </div>

            <!-- Registration Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información de Registro</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Registro Abre:</th>
                                    <td>{{ $jobFair->registration_opens->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Registro Cierra:</th>
                                    <td>{{ $jobFair->registration_closes->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Requiere MEC:</th>
                                    <td>
                                        @if($jobFair->requires_mec_validation)
                                            <span class="badge badge-warning">Sí</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Requiere Pago:</th>
                                    <td>
                                        @if($jobFair->requires_payment)
                                            <span class="badge badge-info">Sí - ${{ number_format($jobFair->registration_fee, 2) }}</span>
                                        @else
                                            <span class="badge badge-success">Gratis</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Máx. Participantes:</th>
                                    <td>{{ $jobFair->max_participants ?: 'Sin límite' }}</td>
                                </tr>
                                <tr>
                                    <th>Máx. Escuelas:</th>
                                    <td>{{ $jobFair->max_schools ?: 'Sin límite' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($jobFair->required_documents && count($jobFair->required_documents) > 0)
                        <hr>
                        <h6>Documentos Requeridos</h6>
                        <ul>
                            @foreach($jobFair->required_documents as $doc)
                                <li>{{ $doc }}</li>
                            @endforeach
                        </ul>
                    @endif

                    @if($jobFair->special_instructions)
                        <hr>
                        <h6>Instrucciones Especiales</h6>
                        <p>{{ $jobFair->special_instructions }}</p>
                    @endif
                </div>
            </div>

            <!-- Registered Teachers -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Profesores Registrados ({{ $jobFair->registrations->where('participant_type', 'teacher')->count() }})
                    </h6>
                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addTeacherModal">
                        <i class="fas fa-plus"></i> Agregar Profesor
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Profesor</th>
                                    <th>MEC</th>
                                    <th>Experiencia</th>
                                    <th>Entrevistas</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jobFair->registrations->where('participant_type', 'teacher') as $registration)
                                <tr>
                                    <td>
                                        <strong>{{ $registration->teacher->user->name ?? 'N/A' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $registration->teacher->user->email ?? '' }}</small>
                                    </td>
                                    <td>
                                        @if($registration->teacher->has_mec_validation)
                                            <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                        @else
                                            <span class="badge badge-warning">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>{{ $registration->teacher->teaching_years_verified ?? 0 }} años</td>
                                    <td class="text-center">{{ $registration->interviews_scheduled }}</td>
                                    <td>
                                        @if($registration->placement_status == 'confirmed')
                                            <span class="badge badge-success">Colocado</span>
                                        @elseif($registration->placement_status == 'pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @else
                                            <span class="badge badge-secondary">Registrado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.teachers.validation.show', $registration->teacher_id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay profesores registrados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Registered Schools -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Escuelas Registradas ({{ $jobFair->registrations->where('participant_type', 'school')->count() }})
                    </h6>
                    <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#addSchoolModal">
                        <i class="fas fa-plus"></i> Agregar Escuela
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Escuela</th>
                                    <th>Tipo</th>
                                    <th>Ubicación</th>
                                    <th>Posiciones</th>
                                    <th>Entrevistas</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($jobFair->registrations->where('participant_type', 'school') as $registration)
                                <tr>
                                    <td>
                                        <strong>{{ $registration->school->school_name ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $registration->school->school_type == 'public' ? 'primary' : 'info' }}">
                                            {{ ucfirst($registration->school->school_type ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $registration->school->city ?? 'N/A' }}, {{ $registration->school->state ?? '' }}
                                    </td>
                                    <td class="text-center">{{ $registration->school->positions_available ?? 0 }}</td>
                                    <td class="text-center">{{ $registration->interviews_scheduled }}</td>
                                    <td>
                                        <a href="{{ route('admin.teachers.school.show', $registration->school_id) }}" 
                                           class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay escuelas registradas</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estado del Evento</h6>
                </div>
                <div class="card-body">
                    <h4 class="mb-3">
                        @if($jobFair->status == 'draft')
                            <span class="badge badge-secondary badge-lg">Borrador</span>
                        @elseif($jobFair->status == 'published')
                            <span class="badge badge-info badge-lg">Publicado</span>
                        @elseif($jobFair->status == 'registration_open')
                            <span class="badge badge-success badge-lg">Registro Abierto</span>
                        @elseif($jobFair->status == 'registration_closed')
                            <span class="badge badge-warning badge-lg">Registro Cerrado</span>
                        @elseif($jobFair->status == 'in_progress')
                            <span class="badge badge-primary badge-lg">En Progreso</span>
                        @elseif($jobFair->status == 'completed')
                            <span class="badge badge-dark badge-lg">Completado</span>
                        @else
                            <span class="badge badge-danger badge-lg">Cancelado</span>
                        @endif
                    </h4>

                    <p class="small text-muted">
                        Creado: {{ $jobFair->created_at->format('d/m/Y') }}
                    </p>

                    @if($jobFair->cancellation_reason)
                        <div class="alert alert-danger">
                            <strong>Razón de Cancelación:</strong><br>
                            {{ $jobFair->cancellation_reason }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Capacity -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Capacidad</h6>
                </div>
                <div class="card-body">
                    <h6>Participantes</h6>
                    <div class="progress mb-2">
                        @php
                            $participantPercentage = $jobFair->max_participants > 0 ? 
                                ($jobFair->registered_participants / $jobFair->max_participants) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-primary" style="width: {{ min($participantPercentage, 100) }}%">
                            {{ $jobFair->registered_participants }}/{{ $jobFair->max_participants ?: '∞' }}
                        </div>
                    </div>

                    <h6 class="mt-3">Escuelas</h6>
                    <div class="progress mb-2">
                        @php
                            $schoolPercentage = $jobFair->max_schools > 0 ? 
                                ($jobFair->registered_schools / $jobFair->max_schools) * 100 : 0;
                        @endphp
                        <div class="progress-bar bg-success" style="width: {{ min($schoolPercentage, 100) }}%">
                            {{ $jobFair->registered_schools }}/{{ $jobFair->max_schools ?: '∞' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            @if($jobFair->status == 'completed' || $jobFair->status == 'in_progress')
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Resultados</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Entrevistas:</th>
                            <td>{{ $jobFair->total_interviews }}</td>
                        </tr>
                        <tr>
                            <th>Ofertas:</th>
                            <td>{{ $jobFair->total_offers }}</td>
                        </tr>
                        <tr>
                            <th>Colocaciones:</th>
                            <td><strong class="text-success">{{ $jobFair->successful_placements }}</strong></td>
                        </tr>
                    </table>

                    @if($jobFair->total_interviews > 0)
                        <hr>
                        <p class="small mb-0">
                            <strong>Tasa de Éxito:</strong> 
                            {{ number_format(($jobFair->successful_placements / $jobFair->total_interviews) * 100, 1) }}%
                        </p>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
