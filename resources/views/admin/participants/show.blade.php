@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            Participante: {{ $participant->full_name }}
            @if($participant->applications && method_exists($participant->applications(), 'ieCue') && $participant->applications()->ieCue()->count() > 0)
                <span class="badge bg-warning text-dark ms-2">
                    <i class="bi bi-star-fill me-1"></i>
                    IE Cue Alumni
                </span>
            @endif
        </h1>
        <div>
            <a href="{{ route('admin.participants.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            <a href="{{ route('admin.participants.program-history', $participant->id) }}" class="btn btn-info">
                <i class="bi bi-clock-history"></i> Historial
            </a>
            <a href="{{ route('admin.participants.edit', $participant->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Editar
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar con info básica -->
        <div class="col-md-3">
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <img class="img-profile rounded-circle mb-3" 
                         src="{{ $participant->profile_photo ? asset('storage/' . $participant->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($participant->full_name) . '&background=4e73df&color=ffffff&size=200' }}" 
                         width="150" height="150" style="object-fit: cover;">
                    <h4>{{ $participant->full_name }}</h4>
                    <p class="text-muted">{{ $participant->email ?? 'Sin email' }}</p>
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'in_review' => 'info',
                            'approved' => 'success',
                            'rejected' => 'danger',
                        ];
                        $statusLabels = [
                            'pending' => 'Pendiente',
                            'in_review' => 'En Revisión',
                            'approved' => 'Aprobado',
                            'rejected' => 'Rechazado',
                        ];
                        $firstApp = $participant->applications->first();
                        $pStatus = $firstApp->status ?? null;
                        $color = $statusColors[$pStatus] ?? 'secondary';
                        $label = $statusLabels[$pStatus] ?? ($pStatus ? ucfirst($pStatus) : 'Sin aplicación');
                    @endphp
                    <span class="badge bg-{{ $color }} text-white">
                        {{ $label }}
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
                        <small class="text-muted">Programa</small>
                        @php $firstApp = $firstApp ?? $participant->applications->first(); @endphp
                        <h6>{{ optional($firstApp)->program->name ?? 'Sin programa' }}</h6>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Progreso</small>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ optional($firstApp)->progress_percentage ?? 0 }}%" 
                                 aria-valuenow="{{ optional($firstApp)->progress_percentage ?? 0 }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ optional($firstApp)->progress_percentage ?? 0 }}%
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Etapa Actual</small>
                        <p class="mb-0"><strong>{{ optional($firstApp)->current_stage ?? 'Sin definir' }}</strong></p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Fecha de Inscripción</small>
                        <small class="d-block">{{ optional($firstApp)->created_at ? optional($firstApp)->created_at->format('d/m/Y') : 'N/A' }}</small>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Última Actualización</small>
                        <small class="d-block">
                            @php
                                $lastUpdate = collect([
                                    optional($participant)->updated_at,
                                    optional($firstApp)->updated_at,
                                ])->filter()->max();
                            @endphp
                            {{ $lastUpdate ? $lastUpdate->format('d/m/Y H:i') : 'N/A' }}
                        </small>
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
                            <a class="nav-link active" id="general-tab" data-bs-toggle="tab" href="#general" role="tab">
                                <i class="fas fa-user"></i> General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="health-tab" data-bs-toggle="tab" href="#health" role="tab">
                                <i class="fas fa-heartbeat"></i> Salud
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="emergency-tab" data-bs-toggle="tab" href="#emergency" role="tab">
                                <i class="fas fa-phone"></i> Emergencia
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="work-tab" data-bs-toggle="tab" href="#work" role="tab">
                                <i class="fas fa-briefcase"></i> Laboral
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="applications-tab" data-bs-toggle="tab" href="#applications" role="tab">
                                <i class="fas fa-file-alt"></i> Aplicaciones
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="payments-tab" data-bs-toggle="tab" href="#payments" role="tab">
                                <i class="fas fa-dollar-sign"></i> Pagos
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="english-tab" data-bs-toggle="tab" href="#english" role="tab">
                                <i class="fas fa-language"></i> Inglés
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="jobs-tab" data-bs-toggle="tab" href="#jobs" role="tab">
                                <i class="fas fa-building"></i> Job Offers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="visa-tab" data-bs-toggle="tab" href="#visa" role="tab">
                                <i class="fas fa-passport"></i> Visa
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="communications-tab" data-bs-toggle="tab" href="#communications" role="tab">
                                <i class="fas fa-comments"></i> Mensajes
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
                                    {{ $participant->full_name }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Email:</strong><br>
                                    {{ $participant->email ?? 'No especificado' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Teléfono:</strong><br>
                                    {{ $participant->phone ?? 'No especificado' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Fecha de Nacimiento:</strong><br>
                                    {{ $participant->birth_date ? $participant->birth_date->format('d/m/Y') : 'No especificada' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Cédula:</strong><br>
                                    {{ $participant->cedula ?? 'No especificada' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Pasaporte:</strong><br>
                                    {{ $participant->passport_number ?? 'No especificado' }}
                                    @if($participant->passport_expiry)
                                        <br><small class="text-muted">Vence: {{ $participant->passport_expiry->format('d/m/Y') }}</small>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Ciudad:</strong><br>
                                    {{ $participant->city ?? 'No especificada' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>País:</strong><br>
                                    {{ $participant->country ?? 'No especificado' }}
                                </div>
                                <div class="col-md-12 mb-3">
                                    <strong>Dirección:</strong><br>
                                    {{ $participant->address ?? 'No especificada' }}
                                </div>
                            </div>
                            
                            <hr>
                            
                            <h5 class="mb-3">Información del Programa</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Programa:</strong><br>
                                    {{ optional($firstApp)->program->name ?? 'No asignado' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Estado:</strong><br>
                                    <span class="badge bg-{{ $color }} text-white">{{ $label }}</span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Etapa Actual:</strong><br>
                                    {{ optional($firstApp)->current_stage ?? 'Sin definir' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Progreso:</strong><br>
                                    {{ optional($firstApp)->progress_percentage ?? 0 }}%
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Costo Total:</strong><br>
                                    ${{ number_format(optional($firstApp)->total_cost ?? 0, 2) }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Monto Pagado:</strong><br>
                                    ${{ number_format(optional($firstApp)->amount_paid ?? 0, 2) }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Fecha de Aplicación:</strong><br>
                                    {{ optional($firstApp)->applied_at ? optional($firstApp)->applied_at->format('d/m/Y H:i') : 'No especificada' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Fecha de Inicio:</strong><br>
                                    {{ optional($firstApp)->started_at ? optional($firstApp)->started_at->format('d/m/Y') : 'No iniciado' }}
                                </div>
                                @if($participant->bio)
                                <div class="col-md-12 mb-3">
                                    <strong>Biografía:</strong><br>
                                    {{ $participant->bio }}
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
                                    @if($participant->blood_type)
                                        <span class="badge badge-danger">{{ $participant->blood_type }}</span>
                                    @else
                                        <span class="text-muted">No especificado</span>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Seguro Médico:</strong><br>
                                    {{ $participant->health_insurance ?? 'No especificado' }}
                                    @if($participant->health_insurance_number)
                                        <br><small class="text-muted">Nº {{ $participant->health_insurance_number }}</small>
                                    @endif
                                </div>
                                <div class="col-md-12 mb-3">
                                    <strong>Condiciones Médicas:</strong><br>
                                    {{ $participant->medical_conditions ?? 'Ninguna reportada' }}
                                </div>
                                <div class="col-md-12 mb-3">
                                    <strong>Alergias:</strong><br>
                                    {{ $participant->allergies ?? 'Ninguna reportada' }}
                                </div>
                                <div class="col-md-12 mb-3">
                                    <strong>Medicamentos Actuales:</strong><br>
                                    {{ $participant->medications ?? 'Ninguno' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Contacto Médico de Emergencia:</strong><br>
                                    {{ $participant->emergency_medical_contact ?? 'No especificado' }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Teléfono Médico:</strong><br>
                                    {{ $participant->emergency_medical_phone ?? 'No especificado' }}
                                </div>
                            </div>
                        </div>

                        <!-- Tab 3: Contactos de Emergencia -->
                        <div class="tab-pane fade" id="emergency" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Contactos de Emergencia</h5>
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addEmergencyContactModal">
                                    <i class="fas fa-plus"></i> Agregar Contacto
                                </button>
                            </div>
                            
                            @if($participant->emergencyContacts && $participant->emergencyContacts->count() > 0)
                                <div class="row">
                                    @foreach($participant->emergencyContacts as $contact)
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
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addWorkExperienceModal">
                                    <i class="fas fa-plus"></i> Agregar Experiencia
                                </button>
                            </div>
                            
                            @if($participant->workExperiences && $participant->workExperiences->count() > 0)
                                <div class="timeline">
                                    @foreach($participant->workExperiences as $experience)
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
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="bi bi-file-earmark-text"></i> Solicitudes de Programas
                                    </h5>
                                    <small class="text-muted">Total: {{ $allApplications->count() }} solicitud(es)</small>
                                </div>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newApplicationModal">
                                    <i class="bi bi-plus-circle"></i> Nueva Solicitud
                                </button>
                            </div>

                            @if($allApplications->isEmpty())
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No hay solicitudes registradas para este participante.
                                </div>
                            @else
                                {{-- Lista de Solicitudes --}}
                                <div class="row">
                                    @foreach($allApplications as $application)
                                        <div class="col-md-12 mb-3">
                                            <div class="card {{ $application->id == $participant->id ? 'border-primary' : '' }}">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-0">
                                                            <i class="bi bi-file-earmark"></i>
                                                            Solicitud #{{ $application->id }} - {{ optional($application->program)->name ?? 'Sin programa' }}
                                                        </h6>
                                                        @if($application->is_current_program)
                                                            <span class="badge bg-success text-white mt-1">
                                                                <i class="bi bi-star-fill"></i> Principal
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.participants.show', $application->id) }}" 
                                                           class="btn btn-sm btn-outline-primary" 
                                                           title="Ver detalles">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="{{ route('admin.participants.edit', $application->id) }}" 
                                                           class="btn btn-sm btn-outline-success" 
                                                           title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmDelete({{ $application->id }})"
                                                                title="Eliminar">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <small class="text-muted">Estado</small><br>
                                                            @php
                                                                $statusColors = [
                                                                    'pending' => 'warning',
                                                                    'in_review' => 'info',
                                                                    'approved' => 'success',
                                                                    'rejected' => 'danger',
                                                                    'completed' => 'secondary',
                                                                ];
                                                                $statusLabels = [
                                                                    'pending' => 'Pendiente',
                                                                    'in_review' => 'En Revisión',
                                                                    'approved' => 'Aprobado',
                                                                    'rejected' => 'Rechazado',
                                                                    'completed' => 'Completado',
                                                                ];
                                                                $color = $statusColors[$application->status] ?? 'secondary';
                                                                $label = $statusLabels[$application->status] ?? ucfirst($application->status);
                                                            @endphp
                                                            <span class="badge bg-{{ $color }}">{{ $label }}</span>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <small class="text-muted">Etapa Actual</small><br>
                                                            <strong>{{ $application->current_stage ?? 'registration' }}</strong>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <small class="text-muted">Progreso</small><br>
                                                            <div class="progress" style="height: 20px;">
                                                                <div class="progress-bar" role="progressbar" 
                                                                     style="width: {{ $application->progress_percentage }}%">
                                                                    {{ $application->progress_percentage }}%
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <small class="text-muted">Fecha de Aplicación</small><br>
                                                            <strong>{{ optional($application->applied_at)->format('d/m/Y') ?? 'N/A' }}</strong>
                                                        </div>
                                                    </div>
                                                    
                                                    <hr class="my-2">
                                                    
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <small class="text-muted">Categoría</small><br>
                                                            <span class="badge bg-primary">{{ optional($application->program)->main_category ?? 'N/A' }}</span>
                                                            <span class="badge bg-secondary">{{ optional($application->program)->subcategory ?? 'N/A' }}</span>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <small class="text-muted">Costo Total</small><br>
                                                            <strong class="text-primary">${{ number_format($application->total_cost ?? 0, 2) }}</strong>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <small class="text-muted">Saldo Pendiente</small><br>
                                                            @php
                                                                $balance = ($application->total_cost ?? 0) - ($application->amount_paid ?? 0);
                                                            @endphp
                                                            <strong class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                                                ${{ number_format($balance, 2) }}
                                                            </strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Tab 6: Pagos -->
                        <div class="tab-pane fade" id="payments" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="bi bi-cash-coin"></i> Registro de Pagos
                                    </h5>
                                    <small class="text-muted">Total: {{ $participant->payments->count() }} pago(s)</small>
                                </div>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newPaymentModal">
                                    <i class="bi bi-plus-circle"></i> Registrar Pago
                                </button>
                            </div>

                            <!-- Resumen de Pagos -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body">
                                            <h6 class="mb-0">Total Pagado</h6>
                                            <h3 class="mb-0">
                                                ${{ number_format($participant->payments()->verified()->sum('amount'), 2) }}
                                            </h3>
                                            <small>{{ $participant->payments()->verified()->count() }} pagos</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-warning text-white">
                                        <div class="card-body">
                                            <h6 class="mb-0">Pendientes</h6>
                                            <h3 class="mb-0">
                                                ${{ number_format($participant->payments()->pending()->sum('amount'), 2) }}
                                            </h3>
                                            <small>{{ $participant->payments()->pending()->count() }} pagos</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-danger text-white">
                                        <div class="card-body">
                                            <h6 class="mb-0">Rechazados</h6>
                                            <h3 class="mb-0">
                                                ${{ number_format($participant->payments()->rejected()->sum('amount'), 2) }}
                                            </h3>
                                            <small>{{ $participant->payments()->rejected()->count() }} pagos</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body">
                                            <h6 class="mb-0">Saldo Pendiente</h6>
                                            <h3 class="mb-0">
                                                @php
                                                    $totalPaid = $participant->payments()->verified()->sum('amount');
                                                    $totalCost = optional($firstApp)->total_cost ?? 0;
                                                    $balance = $totalCost - $totalPaid;
                                                @endphp
                                                ${{ number_format($balance, 2) }}
                                            </h3>
                                            <small>de ${{ number_format($totalCost, 2) }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($participant->payments->isEmpty())
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No hay pagos registrados para este participante.
                                </div>
                            @else
                                <!-- Listado de Pagos -->
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ID</th>
                                                <th>Fecha</th>
                                                <th>Concepto</th>
                                                <th>Monto</th>
                                                <th>Método</th>
                                                <th>Referencia</th>
                                                <th>Estado</th>
                                                <th>Verificado Por</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($participant->payments as $payment)
                                                <tr>
                                                    <td><strong>#{{ $payment->id }}</strong></td>
                                                    <td>{{ $payment->payment_date->format('d/m/Y') }}</td>
                                                    <td>{{ $payment->concept }}</td>
                                                    <td>
                                                        <strong class="text-primary">
                                                            {{ optional($payment->currency)->code ?? 'USD' }} 
                                                            {{ number_format($payment->amount, 2) }}
                                                        </strong>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-secondary">
                                                            {{ ucfirst($payment->payment_method ?? 'N/A') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small class="text-muted">
                                                            {{ $payment->reference_number ?? '-' }}
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-{{ $payment->status_color }}">
                                                            {{ $payment->status_label }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($payment->verifiedBy)
                                                            <small>{{ optional($payment->verifiedBy)->name }}</small><br>
                                                            <small class="text-muted">{{ optional($payment->verified_at)->format('d/m/Y H:i') }}</small>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            @if($payment->receipt_path)
                                                                <a href="{{ Storage::url($payment->receipt_path) }}" 
                                                                   target="_blank"
                                                                   class="btn btn-outline-info" 
                                                                   title="Ver comprobante">
                                                                    <i class="bi bi-file-earmark-pdf"></i>
                                                                </a>
                                                            @endif
                                                            
                                                            @if($payment->status === 'pending')
                                                                <button type="button" 
                                                                        class="btn btn-outline-success" 
                                                                        onclick="verifyPayment({{ $payment->id }})"
                                                                        title="Verificar">
                                                                    <i class="bi bi-check-circle"></i>
                                                                </button>
                                                                <button type="button" 
                                                                        class="btn btn-outline-danger" 
                                                                        onclick="rejectPayment({{ $payment->id }})"
                                                                        title="Rechazar">
                                                                    <i class="bi bi-x-circle"></i>
                                                                </button>
                                                            @endif
                                                            
                                                            <button type="button" 
                                                                    class="btn btn-outline-primary" 
                                                                    onclick="editPayment({{ $payment->id }})"
                                                                    title="Editar">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger" 
                                                                    onclick="deletePayment({{ $payment->id }})"
                                                                    title="Eliminar">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <th colspan="3" class="text-end">TOTAL VERIFICADO:</th>
                                                <th colspan="6">
                                                    <strong class="text-success">
                                                        ${{ number_format($participant->payments()->verified()->sum('amount'), 2) }}
                                                    </strong>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @endif
                        </div>

                        <!-- Tab 7: Evaluación de Inglés -->
                        <div class="tab-pane fade" id="english" role="tabpanel">
                            <h5 class="mb-3">Evaluación de Inglés</h5>
                            @if($participant->englishEvaluations && $participant->englishEvaluations->count() > 0)
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        @php
                                            $bestEvaluation = $participant->englishEvaluations->sortByDesc('score')->first();
                                        @endphp
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="text-primary">Mejor Evaluación</h6>
                                                <div class="row">
                                                    <div class="col-md-3 text-center">
                                                        <h2 class="text-success">{{ $bestEvaluation->score }}/100</h2>
                                                        <small class="text-muted">Puntaje</small>
                                                    </div>
                                                    <div class="col-md-3 text-center">
                                                        <h2 class="text-info">{{ $bestEvaluation->cefr_level }}</h2>
                                                        <small class="text-muted">Nivel CEFR</small>
                                                    </div>
                                                    <div class="col-md-3 text-center">
                                                        <span class="badge badge-lg badge-{{ $bestEvaluation->classification == 'EXCELLENT' ? 'success' : ($bestEvaluation->classification == 'GREAT' ? 'primary' : ($bestEvaluation->classification == 'GOOD' ? 'info' : 'warning')) }}">
                                                            {{ $bestEvaluation->classification }}
                                                        </span>
                                                        <br><small class="text-muted">Clasificación</small>
                                                    </div>
                                                    <div class="col-md-3 text-center">
                                                        <h6>{{ $bestEvaluation->evaluated_at->format('d/m/Y') }}</h6>
                                                        <small class="text-muted">Fecha</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mb-3">Historial de Evaluaciones ({{ $participant->englishEvaluations->count() }}/3 intentos)</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Intento</th>
                                                <th>Fecha</th>
                                                <th>Puntaje</th>
                                                <th>Nivel CEFR</th>
                                                <th>Clasificación</th>
                                                <th>Evaluador</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($participant->englishEvaluations->sortBy('attempt_number') as $eval)
                                                <tr>
                                                    <td>{{ $eval->attempt_number }}</td>
                                                    <td>{{ $eval->evaluated_at->format('d/m/Y') }}</td>
                                                    <td><strong>{{ $eval->score }}/100</strong></td>
                                                    <td><span class="badge badge-info">{{ $eval->cefr_level }}</span></td>
                                                    <td>
                                                        <span class="badge badge-{{ $eval->classification == 'EXCELLENT' ? 'success' : ($eval->classification == 'GREAT' ? 'primary' : ($eval->classification == 'GOOD' ? 'info' : 'warning')) }}">
                                                            {{ $eval->classification }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $eval->evaluated_by ?? 'Sistema' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if($participant->englishEvaluations->count() < 3)
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        El participante puede realizar {{ 3 - $participant->englishEvaluations->count() }} evaluación(es) más.
                                    </div>
                                @else
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> 
                                        El participante ha completado los 3 intentos permitidos.
                                    </div>
                                @endif
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i> No se han registrado evaluaciones de inglés.
                                    <br><small>El participante puede realizar hasta 3 evaluaciones.</small>
                                </div>
                            @endif
                        </div>

                        <!-- Tab 7: Job Offers -->
                        <div class="tab-pane fade" id="jobs" role="tabpanel">
                            <h5 class="mb-3">Job Offers y Reservas</h5>
                            @if($participant->jobOfferReservations && $participant->jobOfferReservations->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Oferta</th>
                                                <th>Empresa</th>
                                                <th>Ubicación</th>
                                                <th>Posición</th>
                                                <th>Estado</th>
                                                <th>Fecha Reserva</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($participant->jobOfferReservations as $reservation)
                                                <tr>
                                                    <td>#{{ $reservation->job_offer_id }}</td>
                                                    <td>{{ $reservation->jobOffer->host_company->name ?? 'N/A' }}</td>
                                                    <td>{{ $reservation->jobOffer->location ?? 'N/A' }}</td>
                                                    <td>{{ $reservation->jobOffer->position ?? 'N/A' }}</td>
                                                    <td>
                                                        @if($reservation->status == 'confirmed')
                                                            <span class="badge badge-success">Confirmada</span>
                                                        @elseif($reservation->status == 'pending')
                                                            <span class="badge badge-warning">Pendiente</span>
                                                        @else
                                                            <span class="badge badge-danger">Cancelada</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $reservation->created_at->format('d/m/Y') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No hay reservas de job offers registradas.
                                </div>
                            @endif
                        </div>

                        <!-- Tab 8: Proceso de Visa -->
                        <div class="tab-pane fade" id="visa" role="tabpanel">
                            <h5 class="mb-3">Proceso de Visa</h5>
                            @if($participant->visaProcess)
                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-3">
                                                    <h6 class="text-primary mb-0">Estado del Proceso</h6>
                                                    <a href="{{ route('admin.visa.timeline', $participant->id) }}" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-timeline"></i> Ver Timeline Completo
                                                    </a>
                                                </div>
                                                <div class="progress mb-3" style="height: 25px;">
                                                    <div class="progress-bar bg-success" role="progressbar" 
                                                         style="width: {{ $participant->visaProcess->progress_percentage }}%">
                                                        {{ $participant->visaProcess->progress_percentage }}%
                                                    </div>
                                                </div>
                                                <div class="row text-center">
                                                    <div class="col-md-4">
                                                        <h6 class="text-muted">Etapa Actual</h6>
                                                        <span class="badge badge-info badge-lg">
                                                            {{ ucfirst(str_replace('_', ' ', $participant->visaProcess->current_step)) }}
                                                        </span>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="text-muted">Resultado</h6>
                                                        @if($participant->visaProcess->visa_result && $participant->visaProcess->visa_result != 'pending')
                                                            <span class="badge badge-{{ $participant->visaProcess->visa_result == 'approved' ? 'success' : ($participant->visaProcess->visa_result == 'rejected' ? 'danger' : 'warning') }} badge-lg">
                                                                {{ strtoupper($participant->visaProcess->visa_result) }}
                                                            </span>
                                                        @else
                                                            <span class="badge badge-secondary badge-lg">EN PROCESO</span>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-4">
                                                        <h6 class="text-muted">Cita Consular</h6>
                                                        @if($participant->visaProcess->consular_appointment_scheduled)
                                                            <i class="fas fa-check-circle text-success"></i>
                                                            {{ $participant->visaProcess->consular_appointment_date->format('d/m/Y') }}
                                                        @else
                                                            <span class="text-muted">Sin programar</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="mb-3">Pasos Completados</h6>
                                <ul class="list-group">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Documentación Completa
                                        @if($participant->visaProcess->documentation_complete)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Completado</span>
                                        @else
                                            <span class="badge badge-secondary">Pendiente</span>
                                        @endif
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Sponsor Interview
                                        @if($participant->visaProcess->sponsor_interview_status == 'approved')
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Aprobado</span>
                                        @else
                                            <span class="badge badge-warning">{{ ucfirst($participant->visaProcess->sponsor_interview_status) }}</span>
                                        @endif
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Job Interview
                                        @if($participant->visaProcess->job_interview_status == 'approved')
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Aprobado</span>
                                        @else
                                            <span class="badge badge-warning">{{ ucfirst($participant->visaProcess->job_interview_status) }}</span>
                                        @endif
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        DS-160 Completado
                                        @if($participant->visaProcess->ds160_completed)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Completado</span>
                                        @else
                                            <span class="badge badge-secondary">Pendiente</span>
                                        @endif
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        DS-2019 Recibido
                                        @if($participant->visaProcess->ds2019_received)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Recibido</span>
                                        @else
                                            <span class="badge badge-secondary">Pendiente</span>
                                        @endif
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        SEVIS Pagado
                                        @if($participant->visaProcess->sevis_paid)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Pagado</span>
                                        @else
                                            <span class="badge badge-secondary">Pendiente</span>
                                        @endif
                                    </li>
                                </ul>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No se ha iniciado el proceso de visa para este participante.
                                    <br><br>
                                    <a href="{{ route('admin.visa.timeline', $participant->id) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> Iniciar Proceso
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Tab 9: Comunicaciones -->
                        <div class="tab-pane fade" id="communications" role="tabpanel">
                            <h5 class="mb-3">Mensajes y Comunicaciones</h5>
                            
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-plus"></i> Enviar Nuevo Mensaje
                                </div>
                                <div class="card-body">
                                    <form action="#" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <label for="subject">Asunto</label>
                                            <input type="text" class="form-control" id="subject" name="subject" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="message">Mensaje</label>
                                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                                        </div>
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="send_email" name="send_email" checked>
                                                <label class="form-check-label" for="send_email">
                                                    Enviar también por correo electrónico
                                                </label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane"></i> Enviar Mensaje
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <h6 class="mb-3">Historial de Mensajes</h6>
                            @if($participant->notifications && $participant->notifications->count() > 0)
                                <div class="list-group">
                                    @foreach($participant->notifications->sortByDesc('created_at')->take(10) as $notification)
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">{{ $notification->title ?? 'Notificación' }}</h6>
                                                <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-1">{{ $notification->message ?? 'Sin mensaje' }}</p>
                                            <small class="text-muted">
                                                @if($notification->read_at)
                                                    <i class="fas fa-check-double text-success"></i> Leído
                                                @else
                                                    <i class="fas fa-check text-muted"></i> No leído
                                                @endif
                                            </small>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> No hay mensajes registrados.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Nuevo Pago --}}
<div class="modal fade" id="newPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cash-coin"></i> Registrar Nuevo Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="paymentForm" action="{{ route('admin.payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="application_id" value="{{ optional($firstApp)->id }}">
                <input type="hidden" name="user_id" value="{{ $participant->id }}">
                <input type="hidden" name="program_id" value="{{ optional($firstApp)->program_id }}">
                <input type="hidden" name="created_by" value="{{ auth()->id() }}">
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="concept" class="form-label">
                                Concepto de Pago <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="concept" name="concept" onchange="toggleOtherConcept()" required>
                                <option value="">Seleccionar concepto</option>
                                <option value="Inscripción">Inscripción</option>
                                <option value="Primera Cuota">Primera Cuota</option>
                                <option value="Segunda Cuota">Segunda Cuota</option>
                                <option value="Tercera Cuota">Tercera Cuota</option>
                                <option value="Cuota Mensual">Cuota Mensual</option>
                                <option value="Pago Final">Pago Final</option>
                                <option value="Depósito de Garantía">Depósito de Garantía</option>
                                <option value="Visa J1">Visa J1</option>
                                <option value="SEVIS">SEVIS</option>
                                <option value="Seguro Médico">Seguro Médico</option>
                                <option value="Vuelos">Vuelos</option>
                                <option value="Documentación">Documentación</option>
                                <option value="Otros Servicios">Otros Servicios</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">
                                Monto <span class="text-danger">*</span>
                            </label>
                            <input type="number" 
                                   class="form-control" 
                                   id="amount" 
                                   name="amount" 
                                   step="0.01" 
                                   min="0" 
                                   placeholder="0.00"
                                   required>
                        </div>
                    </div>

                    <div class="mb-3" id="otherConceptField" style="display: none;">
                        <label for="other_concept" class="form-label">
                            Especificar Concepto <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control" 
                               id="other_concept" 
                               name="other_concept" 
                               placeholder="Escriba el concepto del pago...">
                        <small class="text-muted">Este concepto se usará en lugar de "Otro"</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">
                                Método de Pago <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Seleccionar método</option>
                                <option value="Transferencia bancaria">Transferencia bancaria</option>
                                <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                                <option value="PayPal">PayPal</option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="reference_number" class="form-label">
                                Referencia <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="reference_number" 
                                   name="reference_number" 
                                   placeholder="Número de transferencia, recibo, etc."
                                   required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="currency_id" class="form-label">
                                Moneda <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="currency_id" name="currency_id" required>
                                <option value="">Seleccionar moneda</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}" {{ $currency->code === 'USD' ? 'selected' : '' }}>
                                        {{ $currency->code }} - {{ $currency->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_status" class="form-label">
                                Estado del Pago <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="payment_status" name="status" required>
                                <option value="verified">Realizado</option>
                                <option value="pending" selected>Pendiente</option>
                            </select>
                            <small class="text-muted">
                                <strong>Realizado:</strong> Confirmado, se suma al total.<br>
                                <strong>Pendiente:</strong> En proceso de verificación.
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Observaciones adicionales..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Registrar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Nueva Solicitud --}}
<div class="modal fade" id="newApplicationModal" tabindex="-1" aria-labelledby="newApplicationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="newApplicationModalLabel">
                    <i class="bi bi-plus-circle"></i> Crear Nueva Solicitud
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.participants.store') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $participant->id }}">
                <input type="hidden" name="copy_from_application" value="{{ optional($firstApp)->id }}">
                
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Nueva solicitud:</strong> Se creará una solicitud independiente para este participante.
                    </div>

                    <div class="mb-3">
                        <label for="new_program_id" class="form-label">
                            Seleccionar Programa <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="new_program_id" name="program_id" required>
                            <option value="">-- Seleccionar programa --</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" data-cost="{{ $program->cost }}">
                                    [{{ $program->main_category }}] {{ $program->name }} - {{ $program->country }} ({{ number_format($program->cost, 2) }} USD)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="copy_basic_data" name="copy_basic_data" checked>
                                <label class="form-check-label" for="copy_basic_data">
                                    <strong>Copiar datos básicos</strong><br>
                                    <small class="text-muted">(Nombre, cédula, pasaporte, contacto, etc.)</small>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="set_as_current" name="set_as_current">
                                <label class="form-check-label" for="set_as_current">
                                    <strong>Marcar como programa principal</strong><br>
                                    <small class="text-muted">Desmarcará los demás programas</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div id="program-info" class="alert alert-light d-none">
                        <h6 class="text-success">Información del Programa:</h6>
                        <div id="program-details"></div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Crear Solicitud
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Confirmar Eliminación --}}
<div class="modal fade" id="deleteApplicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>¿Estás seguro de eliminar esta solicitud?</strong></p>
                <p class="text-muted">Esta acción no se puede deshacer. Se eliminarán todos los datos asociados a esta solicitud.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Eliminar Solicitud
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@section('styles')
<style>
.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}
.activity-content {
    padding-left: 10px;
}
.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}
</style>
@endsection

@push('scripts')
<script>
// Mostrar info del programa seleccionado en el modal
document.getElementById('new_program_id')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const programInfo = document.getElementById('program-info');
    const programDetails = document.getElementById('program-details');
    
    if (this.value) {
        const programText = selectedOption.textContent;
        const cost = selectedOption.dataset.cost;
        
        programDetails.innerHTML = `
            <p class="mb-1"><strong>Programa:</strong> ${programText}</p>
            <p class="mb-0"><strong>Costo:</strong> $${parseFloat(cost).toFixed(2)} USD</p>
        `;
        programInfo.classList.remove('d-none');
    } else {
        programInfo.classList.add('d-none');
    }
});

// Función para confirmar eliminación de solicitud
function confirmDelete(applicationId) {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteApplicationModal'));
    const deleteForm = document.getElementById('deleteForm');
    const baseUrl = "{{ url('/admin/participants') }}";
    deleteForm.action = `${baseUrl}/${applicationId}`;
    deleteModal.show();
}

// Confirmación adicional para eliminar
document.getElementById('deleteForm')?.addEventListener('submit', function(e) {
    const confirmText = confirm('Esta acción eliminará TODOS los datos asociados a esta solicitud. ¿Continuar?');
    if (!confirmText) {
        e.preventDefault();
    }
});

// ===== FUNCIONES DE PAGOS =====

// Verificar pago
function verifyPayment(paymentId) {
    if (confirm('¿Estás seguro de verificar este pago?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/payments/${paymentId}/verify`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Rechazar pago
function rejectPayment(paymentId) {
    const notes = prompt('¿Por qué se rechaza este pago? (opcional)');
    if (notes !== null) { // null = canceló, '' = aceptó sin escribir
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/payments/${paymentId}/reject`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        if (notes) {
            const notesInput = document.createElement('input');
            notesInput.type = 'hidden';
            notesInput.name = 'notes';
            notesInput.value = notes;
            form.appendChild(notesInput);
        }
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Editar pago (por implementar - podría abrir modal con datos)
function editPayment(paymentId) {
    alert('Función de editar pago - ID: ' + paymentId + '\nPor implementar: Modal de edición');
    // TODO: Cargar datos del pago en un modal y permitir edición
}

// Eliminar pago
function deletePayment(paymentId) {
    if (confirm('¿Estás seguro de eliminar este pago? Esta acción no se puede deshacer.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/payments/${paymentId}`;
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Mostrar/ocultar campo de texto para concepto "Otro"
function toggleOtherConcept() {
    const conceptSelect = document.getElementById('concept');
    const otherConceptField = document.getElementById('otherConceptField');
    const otherConceptInput = document.getElementById('other_concept');
    
    if (conceptSelect.value === 'Otro') {
        otherConceptField.style.display = 'block';
        otherConceptInput.required = true;
    } else {
        otherConceptField.style.display = 'none';
        otherConceptInput.required = false;
        otherConceptInput.value = '';
    }
}
</script>
@endpush

{{-- Modal: Agregar Contacto de Emergencia --}}
<div class="modal fade" id="addEmergencyContactModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.participants.emergency-contacts.store', $participant->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-phone-alt"></i> Agregar Contacto de Emergencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nombre Completo *</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Relación *</label>
                        <input type="text" class="form-control" name="relationship" required placeholder="Ej: Madre, Padre, Hermano...">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Teléfono *</label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tel. Alternativo</label>
                            <input type="text" class="form-control" name="alternative_phone">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Dirección</label>
                        <input type="text" class="form-control" name="address">
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="is_primary_contact">
                        <label class="form-check-label" for="is_primary_contact">Contacto principal</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal: Agregar Experiencia Laboral --}}
<div class="modal fade" id="addWorkExperienceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.participants.work-experiences.store', $participant->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-briefcase"></i> Agregar Experiencia Laboral</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Empresa *</label>
                            <input type="text" class="form-control" name="company" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cargo/Posición *</label>
                            <input type="text" class="form-control" name="position" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Fecha Inicio *</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" name="end_date" id="work_end_date">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_current" value="1" id="is_current_job"
                                       onchange="document.getElementById('work_end_date').disabled = this.checked;">
                                <label class="form-check-label" for="is_current_job">Trabajo actual</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Describe las responsabilidades y logros..."></textarea>
                    </div>
                    <hr>
                    <h6 class="text-muted">Referencia Laboral (opcional)</h6>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" name="reference_name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="reference_phone">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="reference_email">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
