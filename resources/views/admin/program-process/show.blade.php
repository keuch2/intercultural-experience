@extends('layouts.admin')

@section('title', $user->name . ' — ' . $program->name)

@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3" role="alert"><i class="fas fa-check-circle me-1"></i> {{ session('success') }}<button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3" role="alert"><i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}<button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button></div>@endif
@if($errors->any())<div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3" role="alert"><i class="fas fa-exclamation-triangle me-1"></i><ul class="mb-0 ps-3">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul><button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button></div>@endif

@php
    $currentStage = $definition->stage($process->current_stage_key);
    $isApproved = $envelope['application_approved'];
    $statusColor = match($process->status) { 'completed' => 'dark', 'cancelled' => 'danger', default => 'primary' };
    $paymentPct = 0;
    if ($application && $application->total_cost > 0) {
        $paid = $application->payments->where('status', 'verified')->sum(fn ($p) => $p->converted_amount ?? $p->amount);
        $paymentPct = min(100, (int) round(($paid / $application->total_cost) * 100));
    } elseif (count($envelope['gates'])) {
        $paymentPct = (int) round(collect($envelope['gates'])->where('verified', true)->count() / count($envelope['gates']) * 100);
    }
    $english = $envelope['modules']['english_test'] ?? null;
@endphp

{{-- Header --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-auto"><img src="{{ $user->avatar_url }}" alt="" class="rounded-circle" width="64" height="64"></div>
            <div class="col">
                <h3 class="mb-1">{{ $user->name }} <small class="text-muted fs-6">· {{ $program->name }}</small></h3>
                <div class="text-muted">{{ $user->email }} @if($user->phone) &middot; {{ $user->phone }} @endif @if($user->city) &middot; {{ $user->city }}, {{ $user->country ?? '' }} @endif</div>
                <small class="text-muted">@if($process->season)Temporada {{ $process->season }}@endif @if($process->program_start_date) &middot; <i class="fas fa-plane-departure me-1"></i>Inicio del programa: <strong>{{ $process->program_start_date->format('d/m/Y') }}</strong>@if($process->program_end_date) &ndash; Fin: {{ $process->program_end_date->format('d/m/Y') }}@endif @endif</small>
            </div>
            <div class="col-auto text-end">
                <div class="d-inline-flex align-items-center px-3 py-2 rounded border border-{{ $statusColor }} bg-{{ $statusColor }} bg-opacity-10">
                    <i class="fas fa-flag text-{{ $statusColor }} me-2"></i><span class="text-muted small me-1">Etapa actual:</span>
                    <strong class="text-{{ $statusColor }}">{{ $process->status === 'cancelled' ? 'Cancelado' : ($currentStage?->label ?? $process->current_stage_key) }}</strong>
                    <span class="badge bg-{{ $statusColor }} ms-2">{{ $envelope['progress_pct'] }}%</span>
                </div>
                <div class="mt-2">
                    @if($isApproved)
                        <span class="badge bg-success bg-opacity-25 text-success border border-success"><i class="fas fa-check-circle me-1"></i> Postulante aprobado</span>
                        <form method="POST" action="{{ route('admin.program.participants.approve', [$program->slug, $process->id]) }}" class="d-inline ms-1" onsubmit="return confirm('¿Revocar la aprobación? El postulante dejará de poder subir documentos desde la app.')">@csrf<input type="hidden" name="action" value="revoke"><button type="submit" class="btn btn-sm btn-outline-danger py-0">Revocar</button></form>
                    @else
                        <span class="badge bg-warning bg-opacity-25 text-warning border border-warning"><i class="fas fa-clock me-1"></i> Aprobación pendiente</span>
                        <form method="POST" action="{{ route('admin.program.participants.approve', [$program->slug, $process->id]) }}" class="d-inline ms-1">@csrf<button type="submit" class="btn btn-sm btn-success py-0"><i class="fas fa-user-check me-1"></i> Aprobar postulante</button></form>
                    @endif
                </div>
                <div class="mt-2">
                    <a href="{{ route('admin.participants.show', $user->id) }}" class="btn btn-sm btn-outline-primary me-1"><i class="fas fa-user"></i> Perfil general</a>
                    <a href="{{ route('admin.program.participants.index', $program->slug) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
        </div>

        <div class="mt-3">
            @foreach($envelope['gates'] as $gate)
                @unless($gate['verified'])
                <div class="alert alert-warning py-2 px-3 mb-2 d-flex align-items-center"><i class="fas fa-exclamation-triangle me-2"></i><small><strong>Pago pendiente:</strong> {{ $gate['label'] }} no verificado.</small></div>
                @endunless
            @endforeach
            @if($english && $english['best_level'] && ! $english['meets_minimum'])
            <div class="alert alert-danger py-2 px-3 mb-2 d-flex align-items-center"><i class="fas fa-language me-2"></i><small><strong>Nivel de inglés insuficiente:</strong> {{ $english['best_level'] }}. Mínimo requerido: {{ $english['min_level'] }}.</small></div>
            @endif
            @if($process->status === 'active' && $currentStage && !$currentStage->is_terminal && empty($envelope['blocking_reasons']) && $currentStage->hasAutomaticGuards())
            <div class="alert alert-success py-2 px-3 mb-2 d-flex align-items-center justify-content-between">
                <small><i class="fas fa-check-circle me-2"></i><strong>Requisitos de "{{ $currentStage->label }}" completos.</strong> Se puede avanzar a "{{ $definition->nextStage($currentStage->key)?->label }}".</small>
                <form method="POST" action="{{ route('admin.program.stage.advance', [$program->slug, $process->id]) }}">@csrf<button class="btn btn-sm btn-success py-0">Avanzar <i class="fas fa-arrow-right ms-1"></i></button></form>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 col-lg-2 mb-4">
        <div class="card shadow-sm">
            <div class="list-group list-group-flush">
                @foreach($tabs as $tabKey => $tab)
                    @php
                        $isActive = $activeTab === $tabKey;
                        $state = $tab['type'] === 'stage' ? collect($envelope['stages'])->firstWhere('key', $tabKey)['state'] ?? null : null;
                    @endphp
                    <a href="{{ route('admin.program.participants.show', ['program' => $program->slug, 'process' => $process->id, 'tab' => $tabKey]) }}" class="list-group-item list-group-item-action d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                        <span class="me-2">
                            @if($state === 'complete')<i class="fas fa-check-circle text-success"></i>
                            @elseif($state === 'in_progress')<i class="fas fa-spinner text-info"></i>
                            @elseif($state === 'locked')<i class="fas fa-circle text-muted" style="font-size:.6rem"></i>
                            @else<i class="fas {{ $tab['icon'] }} {{ $isActive ? '' : 'text-muted' }}" style="font-size:.8rem"></i>@endif
                        </span>
                        <span class="small fw-semibold">{{ $tab['label'] }}</span>
                        @if($tabKey === 'payments')<span class="ms-auto badge {{ $paymentPct >= 100 ? 'bg-success' : ($paymentPct > 0 ? 'bg-warning text-dark' : 'bg-danger') }}" style="font-size:.65rem">{{ $paymentPct }}%</span>@endif
                    </a>
                @endforeach
            </div>
        </div>

        <div class="card shadow-sm mt-3"><div class="card-body py-3">
            <h6 class="card-title small text-muted text-uppercase mb-2">Resumen</h6>
            <div class="small">
                @if($english)<div class="d-flex justify-content-between mb-1"><span class="text-muted">Inglés:</span><span class="fw-semibold">{{ $english['best_level'] ?? 'Sin evaluar' }}</span></div>@endif
                <div class="mb-2"><div class="d-flex justify-content-between mb-1"><span class="text-muted">Pagos:</span><span class="fw-semibold">{{ $paymentPct }}%</span></div><div class="progress" style="height:5px"><div class="progress-bar {{ $paymentPct >= 100 ? 'bg-success' : 'bg-warning' }}" style="width: {{ $paymentPct }}%"></div></div></div>
                <div class="d-flex justify-content-between mb-1"><span class="text-muted">Progreso:</span><span class="fw-semibold">{{ $envelope['progress_pct'] }}%</span></div>
                <div class="d-flex justify-content-between mb-1"><span class="text-muted">Próxima acción:</span></div>
                <div class="text-muted fst-italic">{{ $envelope['next_action']['label'] }}</div>
            </div>
            @if(!empty($envelope['blocking_reasons']) && $process->status === 'active')
            <hr class="my-2"><div class="small text-warning"><i class="fas fa-lock me-1"></i>Para avanzar:<ul class="ps-3 mb-0">@foreach($envelope['blocking_reasons'] as $r)<li>{{ $r }}</li>@endforeach</ul></div>
            <form method="POST" action="{{ route('admin.program.stage.advance', [$program->slug, $process->id]) }}" class="mt-2" onsubmit="return confirm('Avanzar de etapa ignorando las condiciones pendientes. ¿Continuar?')">@csrf<input type="hidden" name="force" value="1"><button class="btn btn-sm btn-outline-warning w-100"><i class="fas fa-forward me-1"></i> Forzar avance</button></form>
            @endif
            @if($process->status === 'active' && $definition->stageIndex($process->current_stage_key) > 0)
            <form method="POST" action="{{ route('admin.program.stage.revert', [$program->slug, $process->id]) }}" class="mt-2 d-flex gap-1">@csrf
                <select name="to_stage" class="form-select form-select-sm">@foreach($definition->stages() as $i => $st)@if($i < $definition->stageIndex($process->current_stage_key))<option value="{{ $st->key }}">{{ $st->label }}</option>@endif @endforeach</select>
                <button class="btn btn-sm btn-outline-secondary" title="Retroceder etapa" onclick="return confirm('¿Retroceder a la etapa seleccionada?')"><i class="fas fa-undo"></i></button>
            </form>
            @endif
        </div></div>

        @include('admin.partials._participant_notes_widget', ['user' => $user, 'notes' => $notes])
    </div>

    <div class="col-md-9 col-lg-10">
        @php $tabMeta = $tabs[$activeTab]; $view = $tabMeta['type'] === 'stage' ? 'admin.program-process.tabs._tab_stage' : 'admin.program-process.tabs._tab_' . $activeTab; @endphp
        @includeIf($view, ['tab' => $tabMeta])
    </div>
</div>

<div class="row mt-2"><div class="col-md-9 col-lg-10 offset-md-3 offset-lg-2">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-2" role="button" data-bs-toggle="collapse" data-bs-target="#notasProceso" aria-expanded="{{ $process->notes ? 'true' : 'false' }}">
            <h6 class="mb-0"><i class="fas fa-sticky-note text-primary me-1"></i> Notas del proceso @if($process->notes)<span class="badge bg-info ms-1" style="font-size:.65rem">Con notas</span>@endif</h6><i class="fas fa-chevron-down text-muted small"></i>
        </div>
        <div class="collapse {{ $process->notes ? 'show' : '' }}" id="notasProceso"><div class="card-body">
            <form method="POST" action="{{ route('admin.program.notes.update', [$program->slug, $process->id]) }}">@csrf @method('PUT')<input type="hidden" name="redirect_tab" value="{{ $activeTab }}">
                <textarea name="notes" class="form-control form-control-sm" rows="3" placeholder="Notas internas del proceso...">{{ $process->notes }}</textarea>
                <button type="submit" class="btn btn-sm btn-primary mt-2"><i class="fas fa-save me-1"></i> Guardar notas</button>
            </form>
            @if($process->status === 'active')
            <form method="POST" action="{{ route('admin.program.process.cancel', [$program->slug, $process->id]) }}" class="mt-3 d-flex gap-2 align-items-center" onsubmit="return confirm('¿Cancelar el proceso de este participante?')">@csrf
                <input type="text" name="reason" class="form-control form-control-sm" placeholder="Motivo de cancelación (opcional)">
                <button class="btn btn-sm btn-outline-danger text-nowrap"><i class="fas fa-ban me-1"></i> Cancelar proceso</button>
            </form>
            @endif
            @if($process->application)
            <div class="mt-2 d-flex align-items-center gap-2"><small class="text-muted">Para que el participante pueda postular de nuevo:</small>
            @php $ds = $process->application->deletion_summary ?? app(\App\Services\ApplicationDeletionService::class)->summary($process->application); @endphp
<button type="button" class="btn btn-sm btn-outline-danger text-nowrap" data-bs-toggle="modal" data-bs-target="#deleteApplicationModal" data-action="{{ route('admin.participants.applications.destroy', [$process->user_id, $process->application_id]) }}" data-program="{{ optional($process->application->program)->name }}" data-docs="{{ $ds['documents'] }}" data-pay-verified="{{ $ds['payments_verified'] }}" data-pay-pending="{{ $ds['payments_pending'] }}" data-assignment="{{ $ds['has_active_assignment'] ? 1 : 0 }}"><i class="fas fa-trash me-1"></i> Eliminar postulación</button>
            </div>
            @endif
        </div></div>
    </div>
</div></div>
@include('admin.partials._delete_application_modal')
@endsection
