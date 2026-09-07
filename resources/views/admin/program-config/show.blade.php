@extends('layouts.admin')

@section('title', 'Configurar motor — ' . $program->name)

@section('content')
<div class="container-fluid">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3" role="alert">
        <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3" role="alert">
        <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3" role="alert">
        <i class="fas fa-exclamation-triangle me-1"></i>
        <ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="card shadow-sm mb-3">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h3 class="mb-1"><i class="fas fa-cogs text-warning me-2"></i>Motor de programa: {{ $program->name }}</h3>
                <div class="text-muted small">
                    Slug: <code>{{ $program->slug ?? '—' }}</code>
                    &middot; Motor: <span class="badge {{ $program->engine_enabled ? 'bg-success' : 'bg-secondary' }}">{{ $program->engine_enabled ? 'habilitado' : 'deshabilitado' }}</span>
                    &middot; App: <span class="badge {{ $program->is_available_in_app ? 'bg-success' : 'bg-secondary' }}">{{ $program->is_available_in_app ? 'disponible' : 'no disponible' }}</span>
                    &middot; Participantes en el motor: <strong>{{ $processesCount }}</strong>
                </div>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.ie-programs.edit', $program) }}" class="btn btn-outline-secondary"><i class="fas fa-edit"></i> Datos del programa</a>
                <a href="{{ route('admin.programs.forms.index', $program) }}" class="btn btn-outline-secondary"><i class="fas fa-wpforms"></i> Formulario dinámico</a>
                <a href="{{ route('admin.ie-programs.show', $program) }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
            </div>
        </div>
    </div>

    @if(!$definition->hasStages())
    <div class="alert alert-warning"><i class="fas fa-info-circle me-1"></i> Este programa aún no tiene etapas. Empezá creando las etapas del proceso (por ejemplo: <code>admission</code>, <code>application</code>, …) y luego asociá documentos, checklist y gates de pago.</div>
    @endif

    <ul class="nav nav-tabs mb-3">
        @foreach($tabs as $key => $label)
        <li class="nav-item">
            <a class="nav-link {{ $activeTab === $key ? 'active' : '' }}" href="{{ route('admin.program-config.show', ['program' => $program->id, 'tab' => $key]) }}">{{ $label }}</a>
        </li>
        @endforeach
    </ul>

    @include('admin.program-config.tabs._tab_' . $activeTab)
</div>
@endsection
