@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Perfil del Participante</h1>
        <div>
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar con info básica -->
        <div class="col-md-3">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <img class="img-profile rounded-circle mb-3" 
                         src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=4e73df&color=ffffff&size=200" 
                         width="150" height="150">
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>
                    <span class="badge {{ $user->role === 'admin' ? 'badge-danger' : 'badge-primary' }}">
                        {{ $user->role === 'admin' ? 'Administrador' : 'Participante' }}
                    </span>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estadísticas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Solicitudes</small>
                        <h5>{{ $applicationStats['total'] ?? 0 }}</h5>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Puntos</small>
                        <h5>{{ $totalPoints ?? 0 }}</h5>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Registro</small>
                        <small>{{ $user->created_at->format('d/m/Y') }}</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido principal con tabs -->
        <div class="col-md-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs card-header-tabs" id="userTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="general-tab" data-toggle="tab" href="#general" role="tab">
                                <i class="fas fa-user"></i> General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="health-tab" data-toggle="tab" href="#health" role="tab">
                                <i class="fas fa-heartbeat"></i> Salud
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="emergency-tab" data-toggle="tab" href="#emergency" role="tab">
                                <i class="fas fa-phone"></i> Emergencia
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="work-tab" data-toggle="tab" href="#work" role="tab">
                                <i class="fas fa-briefcase"></i> Laboral
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="applications-tab" data-toggle="tab" href="#applications" role="tab">
                                <i class="fas fa-file-alt"></i> Aplicaciones
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <!-- Tab panes -->
                    <div class="tab-content" id="userTabsContent">
                        <!-- Tab 1: Información General -->
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <h5 class="mb-3">Información Personal</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Nombre Completo:</strong><br>
                                    {{ $user->name }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Email:</strong><br>
                                    {{ $user->email }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Teléfono:</strong><br>
                                    {{ $user->phone ?? 'No especificado' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Fecha de Nacimiento:</strong><br>
                                    {{ $user->birth_date ? $user->birth_date->format('d/m/Y') : 'No especificada' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Nacionalidad:</strong><br>
                                    {{ $user->nationality ?? 'No especificada' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Ciudad:</strong><br>
                                    {{ $user->city ?? 'No especificada' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>País:</strong><br>
                                    {{ $user->country ?? 'No especificado' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Nivel Académico:</strong><br>
                                    {{ $user->academic_level ?? 'No especificado' }}
                                </div>
                                <div class="col-md-12 mb-3">
                                    <strong>Dirección:</strong><br>
                                    {{ $user->address ?? 'No especificada' }}
                                </div>
                                @if($user->bio)
                                <div class="col-md-12 mb-3">
                                    <strong>Biografía:</strong><br>
                                    {{ $user->bio }}
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Tab 2: Información de Salud -->
                        <div class="tab-pane fade" id="health" role="tabpanel">
                            <h5 class="mb-3">Información de Salud</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Tipo de Sangre:</strong><br>
                                    @if($user->blood_type)
                                        <span class="badge badge-danger">{{ $user->blood_type }}</span>
                                    @else
                                        <span class="text-muted">No especificado</span>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Seguro Médico:</strong><br>
                                    {{ $user->health_insurance ?? 'No especificado' }}
                                    @if($user->health_insurance_number)
                                        <br><small class="text-muted">Nº {{ $user->health_insurance_number }}</small>
                                    @endif
                                </div>
                                <div class="col-md-12 mb-3">
                                    <strong>Condiciones Médicas:</strong><br>
                                    {{ $user->medical_conditions ?? 'Ninguna reportada' }}
                                </div>
                                <div class="col-md-12 mb-3">
                                    <strong>Alergias:</strong><br>
                                    {{ $user->allergies ?? 'Ninguna reportada' }}
                                </div>
                                <div class="col-md-12 mb-3">
                                    <strong>Medicamentos Actuales:</strong><br>
                                    {{ $user->medications ?? 'Ninguno' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Contacto Médico de Emergencia:</strong><br>
                                    {{ $user->emergency_medical_contact ?? 'No especificado' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Teléfono Médico:</strong><br>
                                    {{ $user->emergency_medical_phone ?? 'No especificado' }}
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Contactos de Emergencia -->
                        <div class="tab-pane fade" id="emergency" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Contactos de Emergencia</h5>
                                <button class="btn btn-sm btn-primary" onclick="alert('Función por implementar')">
                                    <i class="fas fa-plus"></i> Agregar Contacto
                                </button>
                            </div>
                            
                            @if($user->emergencyContacts && $user->emergencyContacts->count() > 0)
                                <div class="row">
                                    @foreach($user->emergencyContacts as $contact)
                                        <div class="col-md-6 mb-3">
                                            <div class="card {{ $contact->is_primary ? 'border-primary' : '' }}">
                                                <div class="card-body">
                                                    @if($contact->is_primary)
                                                        <span class="badge badge-primary float-right">Principal</span>
                                                    @endif
                                                    <h6 class="card-title">{{ $contact->name }}</h6>
                                                    <p class="card-text">
                                                        <strong>Relación:</strong> {{ $contact->relationship }}<br>
                                                        <strong>Teléfono:</strong> {{ $contact->phone }}<br>
                                                        @if($contact->alternative_phone)
                                                            <strong>Tel. Alternativo:</strong> {{ $contact->alternative_phone }}<br>
                                                        @endif
                                                        @if($contact->email)
                                                            <strong>Email:</strong> {{ $contact->email }}<br>
                                                        @endif
                                                        @if($contact->address)
                                                            <strong>Dirección:</strong> {{ $contact->address }}
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No hay contactos de emergencia registrados.
                                </div>
                            @endif
                        </div>

                        <!-- Tab 4: Experiencia Laboral -->
                        <div class="tab-pane fade" id="work" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Experiencia Laboral</h5>
                                <button class="btn btn-sm btn-primary" onclick="alert('Función por implementar')">
                                    <i class="fas fa-plus"></i> Agregar Experiencia
                                </button>
                            </div>
                            
                            @if($user->workExperiences && $user->workExperiences->count() > 0)
                                <div class="timeline">
                                    @foreach($user->workExperiences as $experience)
                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-8">
                                                        <h6 class="card-title">
                                                            {{ $experience->position }}
                                                            @if($experience->is_current)
                                                                <span class="badge badge-success">Actual</span>
                                                            @endif
                                                        </h6>
                                                        <p class="text-muted mb-2">{{ $experience->company }}</p>
                                                        <p class="text-muted small">
                                                            <i class="fas fa-calendar"></i>
                                                            {{ $experience->start_date->format('M Y') }} - 
                                                            {{ $experience->is_current ? 'Presente' : $experience->end_date->format('M Y') }}
                                                            ({{ $experience->duration }} meses)
                                                        </p>
                                                        @if($experience->description)
                                                            <p class="card-text">{{ $experience->description }}</p>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4">
                                                        @if($experience->reference_name)
                                                            <small class="text-muted">
                                                                <strong>Referencia:</strong><br>
                                                                {{ $experience->reference_name }}<br>
                                                                @if($experience->reference_phone)
                                                                    {{ $experience->reference_phone }}<br>
                                                                @endif
                                                                @if($experience->reference_email)
                                                                    {{ $experience->reference_email }}
                                                                @endif
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No hay experiencia laboral registrada.
                                </div>
                            @endif
                        </div>

                        <!-- Tab 5: Aplicaciones -->
                        <div class="tab-pane fade" id="applications" role="tabpanel">
                            <h5 class="mb-3">Solicitudes de Programas</h5>
                            
                            <!-- Estadísticas -->
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $applicationStats['pending'] ?? 0 }}</h3>
                                            <small>Pendientes</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $applicationStats['approved'] ?? 0 }}</h3>
                                            <small>Aprobadas</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $applicationStats['rejected'] ?? 0 }}</h3>
                                            <small>Rechazadas</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h3>{{ $applicationStats['total'] ?? 0 }}</h3>
                                            <small>Total</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($user->applications && $user->applications->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Programa</th>
                                                <th>Estado</th>
                                                <th>Progreso</th>
                                                <th>Fecha</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($user->applications as $application)
                                                <tr>
                                                    <td>#{{ $application->id }}</td>
                                                    <td>{{ $application->program->name ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($application->status == 'pending')
                                                            <span class="badge badge-warning">Pendiente</span>
                                                        @elseif($application->status == 'approved')
                                                            <span class="badge badge-success">Aprobada</span>
                                                        @else
                                                            <span class="badge badge-danger">Rechazada</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar" role="progressbar" 
                                                                 style="width: {{ $application->progress ?? 0 }}%" 
                                                                 aria-valuenow="{{ $application->progress ?? 0 }}" 
                                                                 aria-valuemin="0" aria-valuemax="100">
                                                                {{ $application->progress ?? 0 }}%
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $application->created_at->format('d/m/Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No hay solicitudes registradas.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
