@extends('layouts.admin')

@section('title', 'Historial de Programas - ' . $participant->full_name)

@section('content')
<div class="container-fluid">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2">
                <i class="bi bi-clock-history me-2"></i>
                Historial de Programas
            </h1>
            <p class="text-muted mb-0">
                {{ $participant->full_name }}
                @if($participant->applications()->ieCue()->count() > 0)
                    <span class="badge bg-warning text-dark ms-2">
                        <i class="bi bi-star-fill me-1"></i>
                        IE Cue Alumni
                    </span>
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('admin.participants.show', $participant->id) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Volver al Perfil
            </a>
        </div>
    </div>

    {{-- Estadísticas --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h3 class="text-primary mb-1">
                        {{ $participant->applications()->count() }}
                    </h3>
                    <p class="text-muted mb-0 small">Aplicaciones Totales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success mb-1">
                        {{ $participant->applications()->ieCue()->count() }}
                    </h3>
                    <p class="text-muted mb-0 small">Programas Completados</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning mb-1">
                        {{ $participant->applications()->where('is_current_program', true)->count() }}
                    </h3>
                    <p class="text-muted mb-0 small">Programa Actual</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info mb-1">
                        {{ $participant->applications()->whereIn('status', ['pending', 'in_review'])->count() }}
                    </h3>
                    <p class="text-muted mb-0 small">En Proceso</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Programa Actual --}}
    @php
        $currentApplication = $participant->applications()->where('is_current_program', true)->first();
    @endphp
    
    @if($currentApplication)
        <div class="card border-warning mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">
                    <i class="bi bi-star-fill me-2"></i>
                    Programa Actual
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6 class="text-muted mb-1">Programa</h6>
                        <p class="mb-0">
                            <span class="badge bg-{{ $currentApplication->program->main_category === 'IE' ? 'primary' : 'success' }}">
                                {{ $currentApplication->program->main_category }}
                            </span>
                            <strong class="ms-2">{{ $currentApplication->program->name }}</strong>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Estado</h6>
                        <p class="mb-0">
                            @php
                                $statusColors = [
                                    'pending' => 'secondary',
                                    'in_review' => 'info',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'completed' => 'primary'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$currentApplication->status] ?? 'secondary' }}">
                                {{ ucfirst($currentApplication->status) }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-3">
                        <h6 class="text-muted mb-1">Etapa</h6>
                        <p class="mb-0">{{ ucfirst(str_replace('_', ' ', $currentApplication->current_stage ?? 'N/A')) }}</p>
                    </div>
                    <div class="col-md-2 text-end">
                        <a href="{{ route('admin.participants.edit', $currentApplication->id) }}" class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil me-1"></i>
                            Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Timeline de Programas --}}
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">
                <i class="bi bi-timeline me-2"></i>
                Timeline de Programas
            </h5>
        </div>
        <div class="card-body">
            @if($participant->applications()->count() === 0)
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    Este participante aún no tiene aplicaciones registradas.
                </div>
            @else
                <div class="timeline">
                    @foreach($participant->applications()->orderBy('created_at', 'desc')->get() as $application)
                        <div class="timeline-item mb-4">
                            <div class="row">
                                <div class="col-md-2 text-muted">
                                    <small>
                                        <i class="bi bi-calendar3 me-1"></i>
                                        {{ $application->created_at->format('d M Y') }}
                                    </small>
                                </div>
                                <div class="col-md-10">
                                    <div class="card {{ $application->is_current_program ? 'border-warning' : '' }}">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-2">
                                                        <span class="badge bg-{{ $application->program->main_category === 'IE' ? 'primary' : 'success' }} me-2">
                                                            {{ $application->program->main_category }}
                                                        </span>
                                                        {{ $application->program->name }}
                                                        
                                                        @if($application->is_current_program)
                                                            <span class="badge bg-warning text-dark ms-2">
                                                                <i class="bi bi-star-fill me-1"></i>
                                                                Actual
                                                            </span>
                                                        @endif
                                                        
                                                        @if($application->is_ie_cue)
                                                            <span class="badge bg-success ms-2">
                                                                <i class="bi bi-check-circle-fill me-1"></i>
                                                                Completado
                                                            </span>
                                                        @endif
                                                    </h6>
                                                    
                                                    <div class="row text-sm">
                                                        <div class="col-md-3">
                                                            <i class="bi bi-globe2 me-1 text-muted"></i>
                                                            {{ $application->program->country }}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <i class="bi bi-flag me-1 text-muted"></i>
                                                            {{ ucfirst($application->status) }}
                                                        </div>
                                                        <div class="col-md-4">
                                                            <i class="bi bi-graph-up me-1 text-muted"></i>
                                                            {{ ucfirst(str_replace('_', ' ', $application->current_stage ?? 'N/A')) }}
                                                        </div>
                                                    </div>

                                                    @if($application->completed_at)
                                                        <div class="mt-2">
                                                            <small class="text-success">
                                                                <i class="bi bi-check-circle me-1"></i>
                                                                Completado el {{ \Carbon\Carbon::parse($application->completed_at)->format('d M Y') }}
                                                            </small>
                                                        </div>
                                                    @endif

                                                    {{-- Datos específicos del programa --}}
                                                    @if($application->program->subcategory === 'Work and Travel' && $application->workTravelData)
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <i class="bi bi-briefcase me-1"></i>
                                                                {{ $application->workTravelData->job_position ?? 'Sin posición asignada' }}
                                                                @if($application->workTravelData->sponsor_company)
                                                                    • Sponsor: {{ $application->workTravelData->sponsor_company }}
                                                                @endif
                                                            </small>
                                                        </div>
                                                    @elseif($application->program->subcategory === 'Au Pair' && $application->auPairData)
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <i class="bi bi-people me-1"></i>
                                                                {{ $application->auPairData->childcare_experience_hours ?? 0 }} horas experiencia
                                                                @if($application->auPairData->host_family_name)
                                                                    • Familia: {{ $application->auPairData->host_family_name }}
                                                                @endif
                                                            </small>
                                                        </div>
                                                    @elseif($application->program->subcategory === "Teacher's Program" && $application->teacherData)
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <i class="bi bi-mortarboard me-1"></i>
                                                                {{ $application->teacherData->degree_title ?? 'Sin título' }}
                                                                @if($application->teacherData->specific_school)
                                                                    • Escuela: {{ $application->teacherData->specific_school }}
                                                                @endif
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                                <div>
                                                    <a href="{{ route('admin.participants.show', $application->id) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye me-1"></i>
                                                        Ver Detalle
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Acción: Nueva Aplicación --}}
    <div class="card mt-4 border-success">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-1">
                        <i class="bi bi-plus-circle me-2 text-success"></i>
                        ¿El participante quiere aplicar a otro programa?
                    </h6>
                    <p class="text-muted mb-0 small">
                        @if($participant->applications()->ieCue()->count() > 0)
                            Como alumno IE Cue, recibirá beneficios especiales: descuentos, prioridad y datos pre-cargados.
                        @else
                            Podrá aplicar a cualquier programa disponible.
                        @endif
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.participants.create') }}?user_id={{ $participant->user_id }}" 
                       class="btn btn-success">
                        <i class="bi bi-plus-lg me-1"></i>
                        Nueva Aplicación
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.timeline-item {
    position: relative;
}
.timeline-item:before {
    content: '';
    position: absolute;
    left: 0;
    top: 10px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #6c757d;
    border: 3px solid #fff;
    box-shadow: 0 0 0 2px #6c757d;
}
.timeline-item:not(:last-child):after {
    content: '';
    position: absolute;
    left: 5px;
    top: 22px;
    bottom: -20px;
    width: 2px;
    background: #dee2e6;
}
</style>
@endsection
