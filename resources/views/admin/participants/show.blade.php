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
                    {{-- Módulo 6 fix: Profile photo with edit capability --}}
                    <div class="position-relative d-inline-block mb-3">
                        <img class="img-profile rounded-circle"
                             src="{{ $participant->profile_photo ? asset('storage/' . $participant->profile_photo) : 'https://ui-avatars.com/api/?name=' . urlencode($participant->full_name) . '&background=4e73df&color=ffffff&size=200' }}"
                             width="150" height="150" style="object-fit: cover;">
                        <button type="button" class="btn btn-sm btn-primary position-absolute bottom-0 end-0 rounded-circle"
                                data-bs-toggle="modal" data-bs-target="#editPhotoModal"
                                title="Cambiar foto de perfil">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
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

            {{-- Módulo C9: Notas del participante (generalizado del widget Au Pair) --}}
            @include('admin.partials._participant_notes_widget', ['user' => $participant, 'notes' => $notes])
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
                        {{-- Rediseño Fase 1: pestañas Pagos / Inglés / Visa / Mensajes eliminadas.
                             Toda esa información ahora vive dentro de cada tarjeta de Aplicación. --}}
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
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Información del Programa</h5>
                                @php
                                    $programSubcategory = optional(optional($firstApp)->program)->subcategory;
                                @endphp
                                @if($programSubcategory === 'Au Pair' && $participant->user_id)
                                    <a href="{{ route('admin.aupair.profiles.show', $participant->user_id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt"></i> Ver Perfil Au Pair
                                    </a>
                                @endif
                            </div>
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
                                    @php $cur = optional($firstApp)->cost_currency ?? 'USD'; @endphp
                                    {{ $cur }} {{ number_format(optional($firstApp)->total_cost ?? 0, 2) }}
                                </div>
                                <div class="col-md-6 mb-3">
                                    <strong>Monto Pagado:</strong><br>
                                    {{ $cur }} {{ number_format(optional($firstApp)->amount_paid ?? 0, 2) }}
                                </div>
                                @if(optional($firstApp)->exchange_rate)
                                <div class="col-md-6 mb-3">
                                    <strong>Tipo de Cambio:</strong><br>
                                    1 USD = {{ number_format(optional($firstApp)->exchange_rate, 0) }} PYG
                                </div>
                                @endif
                                @if(optional($firstApp)->payment_deadline)
                                <div class="col-md-6 mb-3">
                                    <strong>Fecha Límite de Pago:</strong><br>
                                    @php $deadline = optional($firstApp)->payment_deadline; @endphp
                                    <span class="{{ $deadline->isPast() ? 'text-danger fw-bold' : '' }}">
                                        {{ $deadline->format('d/m/Y') }}
                                        @if($deadline->isPast()) (Vencido) @endif
                                    </span>
                                </div>
                                @endif
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
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Información de Salud</h5>
                                {{-- Módulo 2 fix: Enable health section editing --}}
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editHealthModal">
                                    <i class="fas fa-edit"></i> Editar Salud
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <strong>Tipo de Sangre:</strong><br>
                                    @if($participant->blood_type)
                                        {{-- Módulo 2 fix: Use Bootstrap 5 class --}}
                                        <span class="badge bg-danger">{{ $participant->blood_type }}</span>
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

                        <!-- Tab 5: Aplicaciones (Rediseño Fase 1 — núcleo del perfil) -->
                        <div class="tab-pane fade" id="applications" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <h5 class="mb-0">
                                        <i class="bi bi-briefcase"></i> Aplicaciones
                                    </h5>
                                    <small class="text-muted">
                                        Cada tarjeta representa una aplicación independiente a un programa.
                                        Total: {{ $allApplications->count() }} aplicación(es).
                                    </small>
                                </div>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newApplicationModal">
                                    <i class="bi bi-plus-circle me-1"></i> Nueva Aplicación
                                </button>
                            </div>

                            @if($allApplications->isEmpty())
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No hay aplicaciones registradas para este participante.
                                </div>
                            @else
                                @php
                                    $stageColors = [
                                        'registration' => ['warning', 'Admisión'],
                                        'admission' => ['warning', 'Admisión'],
                                        'application' => ['info', 'Aplicación'],
                                        'application_payment1' => ['info', 'Aplicación'],
                                        'application_payment2' => ['info', 'Aplicación'],
                                        'match_visa' => ['primary', 'Match / Visa'],
                                        'match' => ['primary', 'Match / Visa'],
                                        'visa' => ['primary', 'Visa'],
                                        'support' => ['success', 'Participante (en programa)'],
                                        'in_program' => ['success', 'Participante (en programa)'],
                                        'completed' => ['secondary', 'Completado'],
                                        'cancelled' => ['secondary', 'Cancelado'],
                                        'rejected' => ['danger', 'Rechazado'],
                                    ];
                                @endphp

                                @foreach($allApplications as $application)
                                    @php
                                        $stageKey = $application->current_stage ?? 'registration';
                                        [$stageColor, $stageLabel] = $stageColors[$stageKey] ?? ['secondary', ucfirst(str_replace('_', ' ', $stageKey))];
                                        $progress = (int) ($application->progress_percentage ?? 0);
                                        $progressColor = $progress >= 90 ? 'success' : ($progress >= 50 ? 'info' : 'warning');

                                        $eng = $application->latestEnglishEvaluation;
                                        $engCount = $application->englishEvaluations->count();

                                        $visa = $application->visaProcess;
                                        $requiresVisa = optional($application->program)->requires_visa ?? true;

                                        $verifiedPaid = $application->payments->where('status', 'verified')->sum('amount');
                                        $totalCost = (float) ($application->total_cost ?? 0);
                                        $balance = $totalCost - $verifiedPaid;
                                        $costCurrency = $application->cost_currency ?? 'USD';

                                        $subcat = optional($application->program)->subcategory;
                                        $programIcon = match($subcat) {
                                            'Au Pair' => 'bi-people-fill',
                                            'Work and Travel' => 'bi-suitcase-fill',
                                            "Teacher's Program" => 'bi-mortarboard-fill',
                                            'Intern/Trainee' => 'bi-briefcase-fill',
                                            'Higher Education' => 'bi-book-half',
                                            'Language Program' => 'bi-translate',
                                            'Work and Study' => 'bi-easel2-fill',
                                            default => 'bi-globe2',
                                        };
                                    @endphp

                                    <div class="card shadow-sm mb-3">
                                        <div class="card-body">
                                            {{-- Header: programa + acciones --}}
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3"
                                                         style="width:48px; height:48px;">
                                                        <i class="bi {{ $programIcon }} fs-4 text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold">{{ optional($application->program)->name ?? 'Sin programa' }}</h6>
                                                        <small class="text-muted">Solicitud #{{ $application->id }}</small>
                                                    </div>
                                                </div>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light" type="button" data-bs-toggle="dropdown">
                                                        <i class="bi bi-three-dots"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        @if($subcat === 'Au Pair')
                                                            <li>
                                                                <a class="dropdown-item" href="{{ route('admin.aupair.profiles.show', ['id' => $participant->id]) }}">
                                                                    <i class="bi bi-eye me-1"></i> Ver perfil Au Pair
                                                                </a>
                                                            </li>
                                                        @endif
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.payment-management.show', $application->id) }}">
                                                                <i class="bi bi-cash-coin me-1"></i> Perfil financiero
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('admin.participants.edit', $application->id) }}">
                                                                <i class="bi bi-pencil me-1"></i> Editar
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li>
                                                            <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $application->id }})">
                                                                <i class="bi bi-trash me-1"></i> Eliminar
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>

                                            {{-- Grid principal --}}
                                            <div class="row g-3">
                                                {{-- Estado de Etapa --}}
                                                <div class="col-md-2">
                                                    <small class="text-muted d-block">Estado de la Etapa</small>
                                                    <span class="badge bg-{{ $stageColor }} bg-opacity-25 text-{{ $stageColor }} border border-{{ $stageColor }} px-2 py-1">
                                                        {{ $stageLabel }}
                                                    </span>
                                                    @if($stageKey === 'in_program' || $stageKey === 'support')
                                                        <small class="text-muted d-block mt-1">Actualmente en programa.</small>
                                                    @elseif($stageKey === 'visa' || $stageKey === 'match_visa')
                                                        <small class="text-muted d-block mt-1">En proceso de visa.</small>
                                                    @elseif(in_array($stageKey, ['registration', 'admission']))
                                                        <small class="text-muted d-block mt-1">En revisión de documentos.</small>
                                                    @endif
                                                </div>

                                                {{-- Progreso --}}
                                                <div class="col-md-3">
                                                    <small class="text-muted d-block">Progreso General</small>
                                                    <div class="progress mt-1" style="height: 10px;">
                                                        <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" style="width: {{ $progress }}%"></div>
                                                    </div>
                                                    <small class="text-muted">{{ $progress }}%</small>
                                                    <div class="mt-2">
                                                        <small class="text-muted d-block">Fecha de Aplicación</small>
                                                        <strong>{{ optional($application->applied_at ?? $application->created_at)->format('d/m/Y') ?? 'N/A' }}</strong>
                                                    </div>
                                                </div>

                                                {{-- Inglés --}}
                                                <div class="col-md-2">
                                                    <small class="text-muted d-block">Inglés (Último test)</small>
                                                    @if($eng)
                                                        <span class="badge bg-success">{{ $eng->cefr_level }}</span>
                                                        <small class="text-muted d-block mt-1">
                                                            Último test: {{ $eng->evaluated_at->format('d/m/Y') }}
                                                        </small>
                                                        <small class="text-muted d-block">
                                                            Intentos: {{ $engCount }} de 3
                                                        </small>
                                                    @else
                                                        <span class="badge bg-light text-dark">Sin evaluar</span>
                                                        <small class="text-muted d-block mt-1">0 de 3 intentos</small>
                                                    @endif
                                                </div>

                                                {{-- Visa --}}
                                                <div class="col-md-2">
                                                    <small class="text-muted d-block">Visa</small>
                                                    @if(!$requiresVisa)
                                                        <span class="badge bg-light text-dark">No requiere</span>
                                                    @elseif($visa)
                                                        @php
                                                            $vResult = $visa->visa_result;
                                                            $vBadge = match($vResult) {
                                                                'approved' => ['success', 'Aprobada'],
                                                                'rejected' => ['danger', 'Rechazada'],
                                                                default => ['info', 'En proceso'],
                                                            };
                                                        @endphp
                                                        <span class="badge bg-{{ $vBadge[0] }}">{{ $vBadge[1] }}</span>
                                                        @if(optional($application->program)->subcategory === 'Au Pair' || optional($application->program)->subcategory === 'Work and Travel')
                                                            <small class="text-muted d-block mt-1">Tipo: J1</small>
                                                        @endif
                                                        @if($visa->consular_appointment_date)
                                                            <small class="text-muted d-block">Cita: {{ $visa->consular_appointment_date->format('d/m/Y') }}</small>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-secondary">No iniciada</span>
                                                        <small class="text-muted d-block mt-1">Sin iniciar proceso</small>
                                                    @endif
                                                </div>

                                                {{-- Financiero --}}
                                                <div class="col-md-3">
                                                    <small class="text-muted d-block">Costo Total</small>
                                                    <strong class="text-primary">
                                                        {{ $costCurrency }} {{ number_format($totalCost, 2) }}
                                                    </strong>
                                                    <div class="mt-2">
                                                        <small class="text-muted d-block">Saldo Pendiente</small>
                                                        <strong class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                                            {{ $costCurrency }} {{ number_format(max($balance, 0), 2) }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Leyenda de etapas --}}
                                <div class="card bg-light mt-4 border-0">
                                    <div class="card-body py-3">
                                        <div class="row align-items-center">
                                            <div class="col-md-5">
                                                <small class="text-primary fw-bold">
                                                    <i class="bi bi-info-circle me-1"></i> Información importante
                                                </small>
                                                <small class="d-block text-muted">
                                                    Cada tarjeta representa una aplicación independiente a un programa.
                                                    Un participante puede tener múltiples aplicaciones activas o completadas a lo largo del tiempo.
                                                </small>
                                            </div>
                                            <div class="col-md-7">
                                                <small class="fw-bold d-block mb-1">Etapas del Proceso</small>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <small><span class="badge bg-warning">●</span> Admisión</small>
                                                    <small><span class="badge bg-info">●</span> Aplicación</small>
                                                    <small><span class="badge bg-primary">●</span> Visa</small>
                                                    <small><span class="badge bg-success">●</span> Participante (en programa)</small>
                                                    <small><span class="badge bg-secondary">●</span> Completado</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Rediseño Fase 1: Tab Pagos eliminado. Costo/saldo ahora se ven dentro de cada tarjeta de Aplicación; los pagos completos en Gestión de Pagos. --}}
                        {{-- Rediseño Fase 1: Tab Inglés eliminado. Las evaluaciones ahora se ven dentro de cada tarjeta de Aplicación. --}}
                        {{-- Rediseño Fase 1: Tab Visa eliminado. El estado de visa ahora se muestra en cada tarjeta de Aplicación. --}}
                        {{-- Rediseño Fase 1: Tab Comunicaciones eliminado. Los mensajes pasan a vivir dentro de cada Aplicación (Fase 2). --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Rediseño Fase 1: Modal "Registrar Nuevo Pago" eliminado del perfil general. Se registra desde el Perfil Financiero de cada Aplicación. --}}

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

{{-- Módulo 6: Modal para cambiar foto de perfil --}}
<div class="modal fade" id="editPhotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.participants.update-photo', $participant->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-camera"></i> Cambiar Foto de Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nueva Foto *</label>
                        <input type="file" class="form-control" name="profile_photo" accept="image/*" required>
                        <div class="form-text">Formatos: JPG, PNG, GIF. Máximo 2MB</div>
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

{{-- Módulo 2 fix: Modal para editar información de Salud --}}
<div class="modal fade" id="editHealthModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.participants.update-health', $participant->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-heartbeat"></i> Editar Información de Salud</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Sangre</label>
                            <select class="form-control" name="blood_type">
                                <option value="">Seleccionar...</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                                    <option value="{{ $bt }}" {{ $participant->blood_type == $bt ? 'selected' : '' }}>{{ $bt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Seguro Médico</label>
                            <input type="text" class="form-control" name="health_insurance" value="{{ $participant->health_insurance }}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Nº de Seguro</label>
                            <input type="text" class="form-control" name="health_insurance_number" value="{{ $participant->health_insurance_number }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Condiciones Médicas</label>
                        <textarea class="form-control" name="medical_conditions" rows="2">{{ $participant->medical_conditions }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alergias</label>
                        <textarea class="form-control" name="allergies" rows="2">{{ $participant->allergies }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Medicamentos Actuales</label>
                        <textarea class="form-control" name="medications" rows="2">{{ $participant->medications }}</textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Contacto Médico de Emergencia</label>
                            <input type="text" class="form-control" name="emergency_medical_contact" value="{{ $participant->emergency_medical_contact }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono Médico</label>
                            <input type="text" class="form-control" name="emergency_medical_phone" value="{{ $participant->emergency_medical_phone }}">
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

{{-- Rediseño Fase 1: Modales de Evaluación de Inglés (registrar/editar) eliminados del perfil general.
     Se gestionan ahora desde el perfil específico de cada programa (ej. tab "Aplicación → Test de Inglés" en Au Pair). --}}

@endsection
