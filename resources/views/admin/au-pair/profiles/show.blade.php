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
                    @php
                        $userApp = \App\Models\Application::where('user_id', $user->id)->latest()->first();
                    @endphp
                    @if($userApp)
                    {{-- Módulo 7 fix: Use $user->id instead of $userApp->id — route expects User ID, not Application ID --}}
                    <a href="{{ route('admin.participants.show', $user->id) }}" class="btn btn-sm btn-outline-primary me-1">
                        <i class="fas fa-user"></i> Perfil General
                    </a>
                    @endif
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
                @php
                    $paymentPercentage = 0;
                    if ($application && $application->total_cost > 0) {
                        $totalPaid = $application->payments()->where('status', 'verified')->sum('amount');
                        $paymentPercentage = min(100, round(($totalPaid / $application->total_cost) * 100));
                    } elseif ($stages['_meta']['payment1_verified'] && $stages['_meta']['payment2_verified']) {
                        $paymentPercentage = 100;
                    } elseif ($stages['_meta']['payment1_verified'] || $stages['_meta']['payment2_verified']) {
                        $paymentPercentage = 50;
                    }
                @endphp
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
                    {{-- Módulo 16: Payment percentage badge instead of X/2 --}}
                    @if($tabKey === 'payments')
                        <span class="ms-auto badge {{ $paymentPercentage >= 100 ? 'bg-success' : ($paymentPercentage > 0 ? 'bg-warning text-dark' : 'bg-danger') }}" style="font-size: 0.65rem;">
                            {{ $paymentPercentage }}%
                        </span>
                    @endif
                </a>
                @endforeach
            </div>
        </div>

        {{-- Módulo 15: Restructured summary widget --}}
        @php
            // Módulo 10 fix: Calculate last update from all relevant sources
            $lastUpdate = collect([
                $user->updated_at,
                $process ? $process->updated_at : null,
                $application ? $application->updated_at : null,
            ])->filter()->max();

            $stageLabels = [
                'admission' => 'Admisión',
                'application' => 'Aplicación',
                'match_visa' => 'Match / Visa J1',
                'support' => 'Support',
                'completed' => 'Completado',
            ];
        @endphp
        <div class="card shadow-sm mt-3">
            <div class="card-body py-3">
                <h6 class="card-title small text-muted text-uppercase mb-2">Resumen</h6>
                <div class="small">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Inglés:</span>
                        <span class="fw-semibold">{{ $stages['_meta']['english_level'] ?? 'Sin evaluar' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Pagos:</span>
                        <span class="fw-semibold {{ $paymentPercentage >= 100 ? 'text-success' : ($paymentPercentage > 0 ? 'text-warning' : 'text-danger') }}">{{ $paymentPercentage }}%</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Etapa:</span>
                        <span class="fw-semibold">{{ $stageLabels[$stages['_meta']['current_stage']] ?? ucfirst($stages['_meta']['current_stage']) }}</span>
                    </div>
                </div>
                {{-- Módulo 10 fix: Show last update timestamp --}}
                @if($lastUpdate)
                <hr class="my-2">
                <div class="text-muted" style="font-size: 0.75rem;">
                    <i class="fas fa-clock me-1"></i> Última actualización: {{ $lastUpdate->format('d/m/Y H:i') }}
                </div>
                @endif
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

{{-- Módulo 14: Notas del proceso — sección independiente visible en todas las pestañas --}}
@if($process)
<div class="row mt-2">
    <div class="col-md-9 col-lg-10 offset-md-3 offset-lg-2">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-2" role="button" data-bs-toggle="collapse" data-bs-target="#notasProcesoCollapse" aria-expanded="{{ ($process->notes) ? 'true' : 'false' }}">
                <h6 class="mb-0">
                    <i class="fas fa-sticky-note text-primary me-1"></i> Notas del Proceso
                    @if($process->notes)
                        <span class="badge bg-info ms-1" style="font-size: 0.65rem;">Con notas</span>
                    @endif
                </h6>
                <i class="fas fa-chevron-down text-muted small"></i>
            </div>
            <div class="collapse {{ ($process->notes) ? 'show' : '' }}" id="notasProcesoCollapse">
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.aupair.profiles.update-payment-notes', $user->id) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="redirect_tab" value="{{ $activeTab }}">
                        <textarea name="payment_notes" class="form-control form-control-sm" rows="3" placeholder="Notas internas del proceso: observaciones, acuerdos, seguimiento...">{{ $process->notes ?? '' }}</textarea>
                        <button type="submit" class="btn btn-sm btn-primary mt-2"><i class="fas fa-save me-1"></i> Guardar Notas</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection
