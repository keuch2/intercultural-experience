@extends('layouts.agent')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-tachometer-alt me-2"></i>Dashboard del Agente
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('agent.participants.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-user-plus"></i> Crear Participante
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-dashboard card-agent-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Participantes Creados
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $participantsCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-dashboard card-agent-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Aplicaciones Activas
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeApplicationsCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-dashboard card-agent-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Aplicaciones Pendientes
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingApplicationsCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card card-dashboard card-agent-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Programas Activos
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $programsCount }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-graduation-cap fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-users me-2"></i>Participantes Recientes
                </h6>
                <a href="{{ route('agent.participants.index') }}" class="btn btn-sm btn-outline-primary">
                    Ver Todos
                </a>
            </div>
            <div class="card-body">
                @if($recentParticipants->count() > 0)
                    <div class="list-group">
                        @foreach($recentParticipants as $participant)
                            <a href="{{ route('agent.participants.show', $participant->id) }}" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">
                                        <i class="fas fa-user-circle me-2"></i>{{ $participant->name }}
                                    </h6>
                                    <small class="text-muted">{{ $participant->created_at->diffForHumans() }}</small>
                                </div>
                                <p class="mb-1">
                                    <small>
                                        <i class="fas fa-envelope me-1"></i>{{ $participant->email }}<br>
                                        <i class="fas fa-flag me-1"></i>{{ $participant->country }}
                                    </small>
                                </p>
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-muted py-4">
                        <i class="fas fa-info-circle me-2"></i>No hay participantes creados aún.
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-clock me-2"></i>Aplicaciones Pendientes
                </h6>
            </div>
            <div class="card-body">
                @if($pendingApplications->count() > 0)
                    <div class="list-group">
                        @foreach($pendingApplications as $application)
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">{{ $application->user->name }}</h6>
                                    <small>
                                        <span class="badge bg-warning text-dark">Pendiente</span>
                                    </small>
                                </div>
                                <p class="mb-1">
                                    <small>
                                        <i class="fas fa-graduation-cap me-1"></i>
                                        <strong>{{ $application->program->name }}</strong>
                                    </small>
                                </p>
                                <p class="mb-0">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        Enviada {{ $application->created_at->diffForHumans() }}
                                    </small>
                                </p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-muted py-4">
                        <i class="fas fa-info-circle me-2"></i>No hay aplicaciones pendientes.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle me-2"></i>Información del Agente
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-user me-2"></i>Nombre:</strong> {{ Auth::user()->name }}</p>
                        <p><strong><i class="fas fa-envelope me-2"></i>Email:</strong> {{ Auth::user()->email }}</p>
                        <p><strong><i class="fas fa-phone me-2"></i>Teléfono:</strong> {{ Auth::user()->phone ?? 'No especificado' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><i class="fas fa-flag me-2"></i>País:</strong> {{ Auth::user()->country ?? 'No especificado' }}</p>
                        <p><strong><i class="fas fa-city me-2"></i>Ciudad:</strong> {{ Auth::user()->city ?? 'No especificado' }}</p>
                        <p><strong><i class="fas fa-id-badge me-2"></i>Rol:</strong> <span class="badge bg-info">Agente</span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
