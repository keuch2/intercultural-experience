@extends('layouts.agent')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-circle me-2"></i>{{ $participant->name }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('agent.participants.assign-program', $participant->id) }}" class="btn btn-success">
                <i class="fas fa-plus-circle me-2"></i>Asignar Programa
            </a>
        </div>
        <a href="{{ route('agent.participants.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información Personal -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user me-2"></i>Información Personal
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <i class="fas fa-user-circle fa-5x text-primary"></i>
                </div>
                
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted"><i class="fas fa-envelope me-2"></i>Email:</td>
                        <td><strong>{{ $participant->email }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-phone me-2"></i>Teléfono:</td>
                        <td>{{ $participant->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-birthday-cake me-2"></i>Nacimiento:</td>
                        <td>{{ $participant->birth_date ? $participant->birth_date->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-flag me-2"></i>Nacionalidad:</td>
                        <td>{{ $participant->nationality ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-map-marker-alt me-2"></i>País:</td>
                        <td>{{ $participant->country ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-city me-2"></i>Ciudad:</td>
                        <td>{{ $participant->city ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-home me-2"></i>Dirección:</td>
                        <td>{{ $participant->address ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card shadow mt-3">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-graduation-cap me-2"></i>Información Académica
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted"><i class="fas fa-university me-2"></i>Nivel:</td>
                        <td><strong>{{ $participant->academic_level ?? 'N/A' }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-language me-2"></i>Inglés:</td>
                        <td>{{ $participant->english_level ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card shadow mt-3">
            <div class="card-header py-3 bg-secondary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-info-circle me-2"></i>Información del Sistema
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted"><i class="fas fa-user-tag me-2"></i>Rol:</td>
                        <td><span class="badge bg-primary">Participante</span></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-user-tie me-2"></i>Creado por:</td>
                        <td><strong>{{ Auth::user()->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="fas fa-calendar me-2"></i>Fecha de registro:</td>
                        <td>{{ $participant->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Programas Asignados -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-graduation-cap me-2"></i>Programas Asignados
                </h6>
            </div>
            <div class="card-body">
                @if($participant->programAssignments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Programa</th>
                                    <th>Estado</th>
                                    <th>Fecha de Asignación</th>
                                    <th>Notas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($participant->programAssignments as $assignment)
                                    <tr>
                                        <td>
                                            <strong>{{ $assignment->program->name }}</strong><br>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $assignment->program->location }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($assignment->status === 'active')
                                                <span class="badge bg-success">Activo</span>
                                            @elseif($assignment->status === 'pending')
                                                <span class="badge bg-warning">Pendiente</span>
                                            @elseif($assignment->status === 'completed')
                                                <span class="badge bg-info">Completado</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($assignment->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $assignment->created_at->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            @if($assignment->notes)
                                                <small>{{ Str::limit($assignment->notes, 50) }}</small>
                                            @else
                                                <small class="text-muted">Sin notas</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Sin programas asignados</h5>
                        <p class="text-muted">Este participante aún no ha sido asignado a ningún programa</p>
                        <a href="{{ route('agent.participants.assign-program', $participant->id) }}" 
                           class="btn btn-success mt-3">
                            <i class="fas fa-plus-circle me-2"></i>Asignar Primer Programa
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Aplicaciones -->
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-alt me-2"></i>Aplicaciones
                </h6>
            </div>
            <div class="card-body">
                @if($participant->applications->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Programa</th>
                                    <th>Estado</th>
                                    <th>Progreso</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($participant->applications as $application)
                                    <tr>
                                        <td><strong>{{ $application->program->name }}</strong></td>
                                        <td>
                                            @if($application->status === 'approved')
                                                <span class="badge bg-success">Aprobada</span>
                                            @elseif($application->status === 'pending')
                                                <span class="badge bg-warning">Pendiente</span>
                                            @elseif($application->status === 'rejected')
                                                <span class="badge bg-danger">Rechazada</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($application->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $application->progress }}%;" 
                                                     aria-valuenow="{{ $application->progress }}" 
                                                     aria-valuemin="0" aria-valuemax="100">
                                                    {{ $application->progress }}%
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $application->created_at->format('d/m/Y') }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-file-alt fa-2x text-muted mb-2"></i>
                        <p class="text-muted">No hay aplicaciones registradas</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
