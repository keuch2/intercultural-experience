@extends('layouts.admin')

@section('content')
{{-- Flash messages --}}
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
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
    <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Header del perfil --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto">
                <img src="{{ $user->avatar_url }}" alt="" class="rounded-circle" width="64" height="64">
            </div>
            <div class="col">
                <h3 class="mb-1">{{ $user->name }}</h3>
                <div class="text-muted">
                    {{ $user->email }}
                    @if($user->phone) &middot; {{ $user->phone }} @endif
                    @if($user->city) &middot; {{ $user->city }}, {{ $user->country ?? '' }} @endif
                </div>
                @if($user->age)
                <small class="text-muted">{{ $user->age }} años</small>
                @endif
            </div>
            <div class="col-auto text-end">
                {{-- Estado global --}}
                @php
                    $globalStatus = 'Admisión';
                    $globalColor = 'warning';
                    if ($stages['admission']['status'] === 'complete' && $stages['application']['status'] !== 'locked') {
                        $globalStatus = 'Aplicación';
                        $globalColor = 'info';
                    }
                    if ($stages['match_visa']['status'] !== 'locked') {
                        $globalStatus = 'Match / Visa';
                        $globalColor = 'primary';
                    }
                    if ($profile && $profile->profile_status === 'matched') {
                        $globalStatus = 'Matched';
                        $globalColor = 'success';
                    }
                @endphp
                <span class="badge bg-{{ $globalColor }} fs-6 px-3 py-2">
                    {{ $globalStatus }}
                </span>
                <div class="mt-2">
                    <a href="{{ route('admin.aupair.profiles.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al listado
                    </a>
                </div>
            </div>
        </div>

        {{-- Alertas --}}
        <div class="mt-3">
            @if(!$stages['_meta']['payment1_verified'])
            <div class="alert alert-warning alert-sm py-2 px-3 mb-2 d-flex align-items-center" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <small><strong>Pago pendiente:</strong> El participante no ha realizado el 1er pago de inscripción. Documentos de aplicación bloqueados.</small>
            </div>
            @endif
            @if($stages['_meta']['payment1_verified'] && !$stages['_meta']['payment2_verified'])
            <div class="alert alert-info alert-sm py-2 px-3 mb-2 d-flex align-items-center" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <small><strong>2do pago pendiente:</strong> Documentos del segundo pago del programa aún bloqueados.</small>
            </div>
            @endif
            @if($stages['_meta']['english_level'] && !in_array($stages['_meta']['english_level'], ['B1','B2','C1','C2']))
            <div class="alert alert-danger alert-sm py-2 px-3 mb-2 d-flex align-items-center" role="alert">
                <i class="fas fa-language me-2"></i>
                <small><strong>Nivel de inglés insuficiente:</strong> Nivel actual: {{ $stages['_meta']['english_level'] }}. Mínimo requerido: B1.</small>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="row">
    {{-- Sidebar de etapas --}}
    <div class="col-md-3 col-lg-2 mb-4">
        <div class="card shadow-sm">
            <div class="list-group list-group-flush">
                @foreach(['admission' => 'Admisión', 'application' => 'Aplicación', 'match_visa' => 'Match / Visa J1', 'support' => 'Support', 'resources' => 'Recursos', 'reports' => 'Informes', 'payments' => 'Pagos'] as $tabKey => $tabLabel)
                @php
                    $stage = $stages[$tabKey] ?? ['status' => 'available'];
                    $status = $stage['status'];
                    $isActive = $activeTab === $tabKey;
                @endphp
                <a href="{{ route('admin.aupair.profiles.show', ['id' => $user->id, 'tab' => $tabKey]) }}"
                   class="list-group-item list-group-item-action d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                    <span class="me-2">
                        @switch($status)
                            @case('pending')
                                <i class="fas fa-circle text-warning" style="font-size: 0.6rem;"></i>
                                @break
                            @case('in_progress')
                                <i class="fas fa-spinner text-info"></i>
                                @break
                            @case('complete')
                                <i class="fas fa-check-circle text-success"></i>
                                @break
                            @default
                                <i class="fas fa-{{ $stage['icon'] ?? 'circle' }} {{ $isActive ? '' : 'text-muted' }}" style="font-size: 0.8rem;"></i>
                        @endswitch
                    </span>
                    <span class="small fw-semibold">{{ $tabLabel }}</span>
                    @if($tabKey === 'payments')
                        <span class="ms-auto badge {{ $stages['_meta']['payment1_verified'] && $stages['_meta']['payment2_verified'] ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size: 0.65rem;">
                            {{ ($stages['_meta']['payment1_verified'] ? 1 : 0) + ($stages['_meta']['payment2_verified'] ? 1 : 0) }}/2
                        </span>
                    @endif
                </a>
                @endforeach
            </div>
        </div>

        {{-- Mini resumen --}}
        <div class="card shadow-sm mt-3">
            <div class="card-body py-3">
                <h6 class="card-title small text-muted text-uppercase mb-2">Resumen</h6>
                <div class="small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Inglés:</span>
                        <span class="fw-semibold">{{ $stages['_meta']['english_level'] ?? 'Sin evaluar' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Pago 1:</span>
                        @if($stages['_meta']['payment1_verified'])
                            <span class="text-success"><i class="fas fa-check"></i> Verificado</span>
                        @else
                            <span class="text-danger"><i class="fas fa-times"></i> Pendiente</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Pago 2:</span>
                        @if($stages['_meta']['payment2_verified'])
                            <span class="text-success"><i class="fas fa-check"></i> Verificado</span>
                        @else
                            <span class="text-danger"><i class="fas fa-times"></i> Pendiente</span>
                        @endif
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Docs Admisión:</span>
                        @if($stages['_meta']['admission_docs_approved'])
                            <span class="text-success"><i class="fas fa-check"></i></span>
                        @else
                            <span class="text-warning"><i class="fas fa-clock"></i></span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Contenido del tab activo --}}
    <div class="col-md-9 col-lg-10">
        @include('admin.au-pair.profiles.tabs._tab_' . $activeTab, [
            'user' => $user,
            'application' => $application,
            'profile' => $profile,
            'stages' => $stages,
            'tabData' => $tabData,
        ])
    </div>
</div>
@endsection
