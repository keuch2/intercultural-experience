@extends('layouts.admin')

@section('title', 'Participantes — ' . $program->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">{{ $program->name }} — Participantes</h2>
        <p class="text-muted mb-0">Gestión del proceso de los participantes del programa</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('admin.program-config.show', $program->id) }}" class="btn btn-outline-warning"><i class="fas fa-cogs"></i> Configurar motor</a>
    </div>
</div>

<div class="row mb-4 g-3">
    <div class="col-md-3">
        <div class="card card-dashboard card-primary shadow-sm"><div class="card-body py-3 d-flex justify-content-between align-items-center">
            <div><div class="text-muted small text-uppercase">Total</div><div class="h3 mb-0 fw-bold">{{ $stats['total'] }}</div></div>
            <div class="text-primary opacity-50"><i class="fas fa-users fa-2x"></i></div>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card card-dashboard card-warning shadow-sm"><div class="card-body py-3 d-flex justify-content-between align-items-center">
            <div><div class="text-muted small text-uppercase">Aprobación pendiente</div><div class="h3 mb-0 fw-bold">{{ $stats['pending_approval'] }}</div></div>
            <div class="text-warning opacity-50"><i class="fas fa-user-clock fa-2x"></i></div>
        </div></div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm"><div class="card-body py-3">
            <div class="text-muted small text-uppercase mb-2">Por etapa</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($definition->stages() as $stage)
                <a href="{{ route('admin.program.participants.index', ['program' => $program->slug, 'stage' => $stage->key]) }}" class="badge text-decoration-none {{ $stage->is_terminal ? 'bg-dark' : 'bg-info text-dark' }}">{{ $stage->label }}: {{ $stats['stages'][$stage->key] ?? 0 }}</a>
                @endforeach
            </div>
        </div></div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.program.participants.index', $program->slug) }}" class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label small text-muted mb-1">Buscar</label><input type="text" name="search" class="form-control form-control-sm" placeholder="Nombre, email o CI..." value="{{ request('search') }}"></div>
            <div class="col-md-2"><label class="form-label small text-muted mb-1">Etapa</label>
                <select name="stage" class="form-select form-select-sm"><option value="">Todas</option>@foreach($definition->stages() as $stage)<option value="{{ $stage->key }}" {{ request('stage') === $stage->key ? 'selected' : '' }}>{{ $stage->label }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small text-muted mb-1">Estado</label>
                <select name="status" class="form-select form-select-sm"><option value="">Todos</option>@foreach(['active' => 'Activo', 'completed' => 'Completado', 'cancelled' => 'Cancelado'] as $k => $l)<option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
            <div class="col-md-1"><label class="form-label small text-muted mb-1">Temporada</label>
                <select name="season" class="form-select form-select-sm"><option value="">Todas</option>@foreach($seasons as $s)<option value="{{ $s }}" {{ request('season') === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
            <div class="col-md-1"><label class="form-label small text-muted mb-1">Inglés</label>
                <select name="english_level" class="form-select form-select-sm"><option value="">Todos</option>@foreach(['A1','A2','B1','B2','C1','C2'] as $l)<option value="{{ $l }}" {{ request('english_level') === $l ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label small text-muted mb-1">Pago verificado</label>
                <select name="gate" class="form-select form-select-sm"><option value="">—</option>@foreach($definition->gates() as $gate)<option value="{{ $gate->key }}" {{ request('gate') === $gate->key ? 'selected' : '' }}>{{ $gate->label }}</option>@endforeach</select></div>
            <div class="col-md-1"><button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-search"></i></button></div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light"><tr><th>Participante</th><th>Inscripción</th><th>Etapa</th><th class="text-center">Aprobado</th><th class="text-center">Docs</th><th class="text-center">Pagos</th><th class="text-center">Inglés</th><th>Actualizado</th><th></th></tr></thead>
            <tbody>
            @forelse($processes as $proc)
                @php
                    $stage = $definition->stage($proc->current_stage_key);
                    $docs = $proc->documents;
                    $best = $proc->englishTests->sortByDesc(fn ($t) => array_search($t->cefr_level, \App\Models\ProgramEnglishTest::CEFR_ORDER, true))->first()?->cefr_level;
                    $gatesTotal = $definition->gates()->count();
                    $gatesOk = $proc->gates->where('is_verified', true)->count();
                    $minLevel = $definition->minEnglishLevel();
                @endphp
                <tr>
                    <td><div class="d-flex align-items-center"><img src="{{ $proc->user?->avatar_url }}" alt="" class="rounded-circle me-2" width="36" height="36"><div><div class="fw-semibold">{{ $proc->user?->name }}</div><small class="text-muted">{{ $proc->user?->email }}</small></div></div></td>
                    <td><small>{{ $proc->enrollment_date?->format('d/m/Y') ?? '-' }}</small>@if($proc->season)<br><small class="text-muted">{{ $proc->season }}</small>@endif</td>
                    <td>
                        @if($proc->status === 'cancelled')<span class="badge bg-danger">Cancelado</span>
                        @elseif($proc->status === 'completed')<span class="badge bg-dark">Completado</span>
                        @else<span class="badge bg-info text-dark">{{ $stage?->label ?? $proc->current_stage_key }}</span>@endif
                    </td>
                    <td class="text-center">{!! optional($proc->application)->status === 'approved' ? '<i class="fas fa-check-circle text-success"></i>' : '<i class="fas fa-clock text-warning" title="Aprobación pendiente"></i>' !!}</td>
                    <td class="text-center">@if($docs->count())<span class="badge {{ $docs->where('status', 'pending')->count() ? 'bg-warning text-dark' : 'bg-success' }}">{{ $docs->where('status', 'approved')->count() }}/{{ $docs->count() }}</span>@else<span class="text-muted">-</span>@endif</td>
                    <td class="text-center">@if($gatesTotal)<span class="badge {{ $gatesOk >= $gatesTotal ? 'bg-success' : ($gatesOk ? 'bg-warning text-dark' : 'bg-danger') }}">{{ $gatesOk }}/{{ $gatesTotal }}</span>@else<span class="text-muted">-</span>@endif</td>
                    <td class="text-center">@if($best)<span class="badge {{ \App\Models\ProgramEnglishTest::levelMeets($best, $minLevel) ? 'bg-success' : 'bg-warning text-dark' }}">{{ $best }}</span>@else<span class="text-muted">-</span>@endif</td>
                    <td><small class="text-muted">{{ $proc->updated_at?->diffForHumans() }}</small></td>
                    <td><a href="{{ route('admin.program.participants.show', [$program->slug, $proc->id]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-right"></i></a></td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center py-5 text-muted"><i class="fas fa-search fa-3x mb-3 d-block opacity-25"></i>No se encontraron participantes.
                    @if(request()->hasAny(['search', 'stage', 'status', 'season', 'english_level', 'gate']))<br><a href="{{ route('admin.program.participants.index', $program->slug) }}">Limpiar filtros</a>@endif</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @if($processes->hasPages())<div class="card-footer">{{ $processes->links() }}</div>@endif
</div>
@endsection
