@extends('layouts.admin')
@section('title', $offer->display_name . ' — Pool de Ofertas')
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3"><i class="fas fa-check-circle me-1"></i> {{ session('success') }}<button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3"><i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}<button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button></div>@endif
@php $active = $offer->assignments->where('status', 'active'); $past = $offer->assignments->where('status', '!=', 'active'); $r = fn ($n, $x = []) => route("admin.program.job-pool.$n", array_merge([$program->slug, $offer->id], $x)); @endphp

<div class="card shadow-sm mb-4"><div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
        <div>
            <h3 class="mb-1"><i class="fas fa-briefcase text-primary me-2"></i>{{ $offer->display_name }} <span class="badge bg-{{ $offer->status_color }} ms-2">{{ $offer->status_label }}</span></h3>
            @if($offer->job_title)<div class="fw-semibold"><i class="fas fa-building me-1 text-muted"></i>{{ $offer->employer_name }}</div>@endif
            <div class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $offer->city }}, {{ $offer->state }} &middot; Publicada {{ $offer->published_at?->format('d/m/Y H:i') }} @if($offer->creator)por {{ $offer->creator->name }}@endif</div>
            <div class="mt-2 d-flex gap-3">
                <div><small class="text-muted d-block">Posiciones</small><span class="h4 mb-0">{{ $offer->positions_available }}</span> <small class="text-muted">disponibles de {{ $offer->positions_total }}</small></div>
                <div><small class="text-muted d-block">Ocupadas</small><span class="h4 mb-0">{{ $offer->positions_taken }}</span></div>
                <div><small class="text-muted d-block">Fecha límite</small><span class="h5 mb-0 {{ $offer->isDeadlinePassed() ? 'text-danger' : '' }}">{{ $offer->application_deadline?->format('d/m/Y') ?? '—' }}</span>@if($offer->isDeadlinePassed())<small class="d-block text-danger">vencida</small>@endif</div>
                <div><small class="text-muted d-block">PDF</small>@if($offer->hasPdf())<a href="{{ $r('pdf') }}" class="btn btn-sm btn-outline-danger py-0"><i class="fas fa-file-pdf me-1"></i>{{ Str::limit($offer->pdf_original_filename, 30) }}</a>@else<span class="badge bg-warning text-dark">sin PDF</span>@endif</div>
            </div>
            @if($offer->notes)<div class="mt-2 small text-muted"><i class="fas fa-sticky-note me-1"></i>{{ $offer->notes }}</div>@endif
        </div>
        <div class="d-flex flex-wrap gap-1">
            <a href="{{ $r('edit') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit me-1"></i> Editar / reemplazar PDF</a>
            @if($offer->status === 'active')<form method="POST" action="{{ $r('pause') }}">@csrf<button class="btn btn-sm btn-outline-warning"><i class="fas fa-pause me-1"></i> Pausar</button></form>@endif
            @if($offer->status === 'paused' || $offer->status === 'closed')<form method="POST" action="{{ $r('reactivate') }}">@csrf<button class="btn btn-sm btn-outline-success"><i class="fas fa-play me-1"></i> Reactivar</button></form>@endif
            @if($offer->status !== 'closed')<form method="POST" action="{{ $r('close') }}" onsubmit="return confirm('¿Cerrar la oferta? Dejará de mostrarse en la app.')">@csrf<button class="btn btn-sm btn-outline-dark"><i class="fas fa-lock me-1"></i> Cerrar</button></form>@endif
            <form method="POST" action="{{ $r('destroy') }}" onsubmit="return confirm('¿Eliminar la oferta? Solo es posible sin participantes asignados.')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i> Eliminar</button></form>
            <a href="{{ route('admin.program.job-pool.index', $program->slug) }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i></a>
        </div>
    </div>
</div></div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center"><h5 class="card-title mb-0"><i class="fas fa-user-check text-success me-2"></i> Participantes asignados ({{ $active->count() }})</h5>
                @if($offer->isSelectable() && $eligible->isNotEmpty())<button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#assignForm"><i class="fas fa-user-plus me-1"></i> Asignar manualmente</button>@endif</div>
            <div class="card-body">
                <div class="collapse mb-3" id="assignForm"><form method="POST" action="{{ $r('assign') }}" class="d-flex gap-2">@csrf
                    <select name="process_id" class="form-select form-select-sm" required><option value="">Participante habilitado sin oferta…</option>@foreach($eligible as $p)<option value="{{ $p->id }}">{{ $p->user?->name }} ({{ $p->user?->email }})</option>@endforeach</select>
                    <button class="btn btn-sm btn-primary text-nowrap"><i class="fas fa-check me-1"></i> Asignar</button>
                </form></div>
                @forelse($active as $a)
                <div class="border rounded p-3 mb-2">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <a href="{{ route('admin.program.participants.show', [$program->slug, $a->program_process_id]) }}" class="fw-semibold text-decoration-none">{{ $a->process?->user?->name }}</a><br>
                            <small class="text-muted">Seleccionada {{ $a->selected_at->format('d/m/Y H:i') }} · {{ $a->assignedBy ? 'asignada por '.$a->assignedBy->name : 'auto-selección desde la app' }}</small>
                        </div>
                        <span class="badge bg-success">{{ $a->status_label }}</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <form method="POST" action="{{ $r('release', [$a->id]) }}" class="d-flex gap-1" onsubmit="return confirm('¿Liberar la asignación? El cupo vuelve a estar disponible y el participante es notificado.')">@csrf
                            <input type="text" name="reason" class="form-control form-control-sm" placeholder="Motivo (opcional)" style="max-width:220px"><button class="btn btn-sm btn-outline-warning text-nowrap"><i class="fas fa-unlink me-1"></i> Liberar</button></form>
                        @if($eligible->isNotEmpty())
                        <form method="POST" action="{{ $r('reassign', [$a->id]) }}" class="d-flex gap-1" onsubmit="return confirm('¿Reasignar esta oferta a otro participante?')">@csrf
                            <select name="to_process_id" class="form-select form-select-sm" required style="max-width:260px"><option value="">Reasignar a…</option>@foreach($eligible as $p)<option value="{{ $p->id }}">{{ $p->user?->name }}</option>@endforeach</select>
                            <button class="btn btn-sm btn-outline-primary text-nowrap"><i class="fas fa-exchange-alt me-1"></i> Reasignar</button></form>
                        @endif
                    </div>
                </div>
                @empty
                <p class="text-muted small mb-0"><i class="fas fa-info-circle"></i> Ningún participante seleccionó esta oferta todavía.</p>
                @endforelse

                @if($past->isNotEmpty())
                <h6 class="small text-muted text-uppercase mt-4 mb-2">Asignaciones anteriores</h6>
                <table class="table table-sm mb-0"><tbody>
                    @foreach($past as $a)<tr><td>{{ $a->process?->user?->name }}</td><td><small>{{ $a->selected_at->format('d/m/Y') }} → {{ $a->released_at?->format('d/m/Y') }}</small></td><td><span class="badge bg-secondary">{{ $a->status_label }}</span></td><td><small class="text-muted">{{ $a->release_reason }} @if($a->releasedBy)· {{ $a->releasedBy->name }}@endif</small></td></tr>@endforeach
                </tbody></table>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm"><div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-history text-muted me-1"></i> Historial de la oferta</h6></div>
            <div class="card-body" style="max-height:520px;overflow:auto">
                @forelse($offer->events as $ev)
                <div class="border-bottom py-2 small"><div class="d-flex justify-content-between"><strong>{{ $ev->label }}</strong><span class="text-muted">{{ $ev->created_at->format('d/m/Y H:i') }}</span></div>
                    <div class="text-muted">{{ $ev->actor?->name ?? ucfirst($ev->actor_type) }}@if(!empty($ev->payload['reason'])) · {{ $ev->payload['reason'] }}@endif @if(isset($ev->payload['positions_available'])) · cupos: {{ $ev->payload['positions_available'] }}@endif</div></div>
                @empty<p class="text-muted small mb-0">Sin eventos.</p>@endforelse
            </div>
        </div>
    </div>
</div>
@endsection
