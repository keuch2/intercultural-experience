@extends('layouts.admin')
@section('title', 'Informes — ' . $program->name)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div><h2 class="mb-1">Informes y planillas</h2><p class="text-muted mb-0">{{ $program->name }} — filtrá y exportá la planilla de participantes</p></div>
    <a href="{{ route('admin.program.reports.export', array_merge(['program' => $program->slug], request()->query())) }}" class="btn btn-success"><i class="fas fa-file-csv me-1"></i> Exportar CSV ({{ $total }})</a>
</div>

<div class="row g-3 mb-4">
    @foreach([['Participantes', $summary['total'], 'fa-users', 'primary'], ['Docs completa', $summary['doc_complete'], 'fa-folder-open', 'success'], ['Inglés OK', $summary['english_ok'], 'fa-language', 'info'], ['Con oferta', $summary['with_offer'], 'fa-briefcase', 'warning'], ['Visa aprobada', $summary['visa_approved'], 'fa-passport', 'success'], ['Con viaje', $summary['traveling'], 'fa-plane-departure', 'dark']] as [$label, $value, $icon, $color])
    <div class="col"><div class="card card-dashboard card-{{ $color }} shadow-sm"><div class="card-body py-2 d-flex justify-content-between align-items-center"><div><div class="text-muted small text-uppercase">{{ $label }}</div><div class="h4 mb-0 fw-bold">{{ $value }}</div></div><i class="fas {{ $icon }} fa-lg text-{{ $color }} opacity-50"></i></div></div></div>
    @endforeach
</div>

<div class="card shadow-sm mb-4"><div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-2"><label class="form-label small text-muted mb-1">Buscar</label><input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Nombre, email, CI"></div>
        <div class="col-md-1"><label class="form-label small text-muted mb-1">Temporada</label><select name="season" class="form-select form-select-sm"><option value="">Todas</option>@foreach($options['seasons'] as $s)<option value="{{ $s }}" {{ request('season') === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label small text-muted mb-1">Universidad</label><select name="university" class="form-select form-select-sm"><option value="">Todas</option>@foreach($options['universities'] as $u)<option value="{{ $u }}" {{ request('university') === $u ? 'selected' : '' }}>{{ $u }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label small text-muted mb-1">Carrera</label><select name="career" class="form-select form-select-sm"><option value="">Todas</option>@foreach($options['careers'] as $c)<option value="{{ $c }}" {{ request('career') === $c ? 'selected' : '' }}>{{ $c }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label small text-muted mb-1">Etapa</label><select name="stage" class="form-select form-select-sm"><option value="">Todas</option>@foreach($options['stages'] as $st)<option value="{{ $st->key }}" {{ request('stage') === $st->key ? 'selected' : '' }}>{{ $st->label }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label small text-muted mb-1">Estado documental</label><select name="doc_status" class="form-select form-select-sm"><option value="">Todos</option>@foreach(['complete' => 'Completa', 'pending' => 'En revisión', 'rejected' => 'Con rechazos', 'missing' => 'Incompleta'] as $k => $l)<option value="{{ $k }}" {{ request('doc_status') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="col-md-1"><label class="form-label small text-muted mb-1">Inglés</label><select name="english_level" class="form-select form-select-sm"><option value="">Todos</option>@foreach(['A1','A2','B1','B2','C1','C2'] as $l)<option value="{{ $l }}" {{ request('english_level') === $l ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label small text-muted mb-1">Oferta laboral</label><select name="offer_id" class="form-select form-select-sm"><option value="">Todas</option>@foreach($options['offers'] as $o)<option value="{{ $o->id }}" {{ (string) request('offer_id') === (string) $o->id ? 'selected' : '' }}>{{ $o->headline }} ({{ $o->city }})</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label small text-muted mb-1">Sponsor</label><select name="sponsor_id" class="form-select form-select-sm"><option value="">Todos</option>@foreach($options['sponsors'] as $sp)<option value="{{ $sp->id }}" {{ (string) request('sponsor_id') === (string) $sp->id ? 'selected' : '' }}>{{ $sp->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label small text-muted mb-1">Visa</label><select name="visa_result" class="form-select form-select-sm"><option value="">Todas</option>@foreach(['pending' => 'Pendiente', 'approved' => 'Aprobada', 'denied' => 'Denegada', 'administrative_process' => 'Proceso administrativo'] as $k => $l)<option value="{{ $k }}" {{ request('visa_result') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label small text-muted mb-1">Viaje</label><select name="travel" class="form-select form-select-sm"><option value="">Todos</option><option value="scheduled" {{ request('travel') === 'scheduled' ? 'selected' : '' }}>Con fecha de salida</option><option value="not_scheduled" {{ request('travel') === 'not_scheduled' ? 'selected' : '' }}>Sin fecha de salida</option></select></div>
        <div class="col-md-2"><label class="form-label small text-muted mb-1">Pago</label><div class="input-group input-group-sm"><select name="gate" class="form-select"><option value="">—</option>@foreach($options['gates'] as $g)<option value="{{ $g->key }}" {{ request('gate') === $g->key ? 'selected' : '' }}>{{ $g->label }}</option>@endforeach</select><select name="gate_verified" class="form-select" style="max-width:110px"><option value="1" {{ request('gate_verified', '1') === '1' ? 'selected' : '' }}>Verificado</option><option value="0" {{ request('gate_verified') === '0' ? 'selected' : '' }}>Pendiente</option></select></div></div>
        <div class="col-md-1"><label class="form-label small text-muted mb-1">Estado</label><select name="status" class="form-select form-select-sm"><option value="">Todos</option>@foreach(['active' => 'Activo', 'completed' => 'Completado', 'cancelled' => 'Cancelado'] as $k => $l)<option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="col-md-2 d-flex gap-1"><button class="btn btn-sm btn-primary flex-fill"><i class="fas fa-filter me-1"></i> Filtrar</button><a href="{{ route('admin.program.reports.index', $program->slug) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a></div>
    </form>
</div></div>

@if($truncated)<div class="alert alert-warning py-2 px-3"><small><i class="fas fa-exclamation-triangle me-1"></i>Se muestran las primeras {{ $rows->count() }} filas de {{ $total }}. El CSV exporta todas.</small></div>@endif

<div class="card shadow-sm"><div class="table-responsive">
    <table class="table table-sm table-hover align-middle mb-0" style="font-size:.85rem">
        <thead class="table-light"><tr><th>Participante</th><th>Universidad / Carrera</th><th>Temp.</th><th>Etapa</th><th class="text-center">Docs</th><th class="text-center">Inglés</th><th>Oferta</th><th>Sponsor / Placement</th><th>Visa</th><th>Viaje</th><th class="text-end">Pagos</th><th></th></tr></thead>
        <tbody>
        @forelse($rows as $r)
        <tr>
            <td><div class="fw-semibold">{{ $r['name'] }}</div><small class="text-muted">{{ $r['email'] }}</small></td>
            <td><small>{{ $r['university'] ?? '-' }}@if($r['career'])<br>{{ $r['career'] }}@endif</small></td>
            <td><small>{{ $r['season'] ?? '-' }}</small></td>
            <td><span class="badge {{ $r['status'] === 'Cancelado' ? 'bg-danger' : ($r['status'] === 'Completado' ? 'bg-dark' : 'bg-info text-dark') }}">{{ $r['status'] === 'Activo' ? $r['stage'] : $r['status'] }}</span>@unless($r['approved'])<br><small class="text-warning"><i class="fas fa-clock"></i> sin aprobar</small>@endunless</td>
            <td class="text-center"><span class="badge {{ ['complete' => 'bg-success', 'pending' => 'bg-warning text-dark', 'rejected' => 'bg-danger', 'missing' => 'bg-secondary'][$r['doc_status']] }}" title="{{ $r['doc_status_label'] }}">{{ $r['docs_approved'] }}/{{ $r['docs_required'] }}</span></td>
            <td class="text-center">@if($r['english'])<span class="badge {{ $r['english_ok'] ? 'bg-success' : 'bg-warning text-dark' }}">{{ $r['english'] }}</span>@else<span class="text-muted">-</span>@endif</td>
            <td><small>{{ $r['offer'] ?? '-' }}@if($r['offer_location'])<br><span class="text-muted">{{ $r['offer_location'] }}</span>@endif</small></td>
            <td><small>{{ $r['sponsor'] ?? '-' }}@if($r['placement_status'])<br><span class="text-muted">{{ $r['placement_status'] }}</span>@endif</small></td>
            <td><small>{{ $r['visa_result'] ?? '-' }}@if($r['appointment'])<br><span class="text-muted">{{ $r['appointment'] }}</span>@endif</small></td>
            <td><small>{{ $r['departure'] ?? '-' }}</small></td>
            <td class="text-end"><small>{{ $r['paid'] }} / {{ $r['cost'] }}<br>@foreach($definition->gates() as $i => $g)<i class="fas fa-circle {{ $r['gates'][$i] ? 'text-success' : 'text-secondary opacity-25' }}" title="{{ $g->label }}" style="font-size:.5rem"></i> @endforeach</small></td>
            <td><a href="{{ route('admin.program.participants.show', [$program->slug, $r['process_id']]) }}" class="btn btn-sm btn-outline-primary py-0"><i class="fas fa-arrow-right"></i></a></td>
        </tr>
        @empty
        <tr><td colspan="12" class="text-center text-muted py-5">Sin participantes para los filtros seleccionados.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>
@endsection
