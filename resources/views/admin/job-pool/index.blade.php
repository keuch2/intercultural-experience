@extends('layouts.admin')
@section('title', 'Pool de Ofertas — ' . $program->name)
@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible fade show py-2 px-3 mb-3"><i class="fas fa-check-circle me-1"></i> {{ session('success') }}<button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button></div>@endif
@if(session('error'))<div class="alert alert-danger alert-dismissible fade show py-2 px-3 mb-3"><i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}<button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button></div>@endif
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="mb-1">Pool de Ofertas Laborales</h2><p class="text-muted mb-0">{{ $program->name }} — ofertas visibles para los participantes habilitados</p></div>
    <a href="{{ route('admin.program.job-pool.create', $program->slug) }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nueva oferta</a>
</div>
<div class="row g-3 mb-4">
    @foreach([['Ofertas', $stats['total'], 'fa-briefcase', 'primary'], ['Activas con cupo', $stats['active'], 'fa-check-circle', 'success'], ['Sin cupo', $stats['exhausted'], 'fa-ban', 'secondary'], ['Posiciones disponibles', $stats['positions_available'], 'fa-users', 'info'], ['Participantes asignados', $stats['assigned'], 'fa-user-check', 'warning']] as [$label, $value, $icon, $color])
    <div class="col"><div class="card card-dashboard card-{{ $color }} shadow-sm"><div class="card-body py-3 d-flex justify-content-between align-items-center"><div><div class="text-muted small text-uppercase">{{ $label }}</div><div class="h3 mb-0 fw-bold">{{ $value }}</div></div><div class="text-{{ $color }} opacity-50"><i class="fas {{ $icon }} fa-2x"></i></div></div></div></div>
    @endforeach
</div>
<div class="card shadow-sm mb-4"><div class="card-body py-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4"><label class="form-label small text-muted mb-1">Buscar</label><input type="text" name="search" class="form-control form-control-sm" placeholder="Puesto, empleador, ciudad o estado" value="{{ request('search') }}"></div>
        <div class="col-md-3"><label class="form-label small text-muted mb-1">Estado</label><select name="status" class="form-select form-select-sm"><option value="">Todas</option>@foreach(['active' => 'Activas', 'exhausted' => 'Sin cupo', 'paused' => 'Pausadas', 'closed' => 'Cerradas'] as $k => $l)<option value="{{ $k }}" {{ request('status') === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="col-md-1"><button class="btn btn-sm btn-primary w-100"><i class="fas fa-search"></i></button></div>
    </form>
</div></div>
<div class="card shadow-sm"><div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>Puesto / Empleador</th><th>Ubicación</th><th class="text-center">Posiciones</th><th class="text-center">Asignados</th><th>Fecha límite</th><th>Estado</th><th>PDF</th><th>Publicada</th><th></th></tr></thead>
        <tbody>
        @forelse($offers as $offer)
        <tr>
            <td><a href="{{ route('admin.program.job-pool.show', [$program->slug, $offer->id]) }}" class="fw-semibold text-decoration-none">{{ $offer->display_name }}</a>@if($offer->job_title)<div class="small text-muted">{{ $offer->employer_name }}</div>@endif</td>
            <td>{{ $offer->city }}, {{ $offer->state }}</td>
            <td class="text-center"><span class="badge {{ $offer->positions_available > 0 ? 'bg-success' : 'bg-secondary' }}">{{ $offer->positions_available }}</span> <small class="text-muted">/ {{ $offer->positions_total }}</small></td>
            <td class="text-center">{{ $offer->active_assignments_count }}</td>
            <td>@if($offer->application_deadline)<small class="{{ $offer->isDeadlinePassed() ? 'text-danger fw-semibold' : 'text-muted' }}">{{ $offer->application_deadline->format('d/m/Y') }}</small>@else<small class="text-muted">—</small>@endif</td>
            <td><span class="badge bg-{{ $offer->status_color }}">{{ $offer->status_label }}</span></td>
            <td>@if($offer->hasPdf())<a href="{{ route('admin.program.job-pool.pdf', [$program->slug, $offer->id]) }}" class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-file-pdf text-danger"></i></a>@else<span class="text-warning" title="Sin PDF"><i class="fas fa-exclamation-triangle"></i></span>@endif</td>
            <td><small class="text-muted">{{ $offer->published_at?->format('d/m/Y') }}</small></td>
            <td class="text-end"><a href="{{ route('admin.program.job-pool.show', [$program->slug, $offer->id]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-arrow-right"></i></a></td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-briefcase fa-3x mb-3 d-block opacity-25"></i>No hay ofertas cargadas todavía.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>@if($offers->hasPages())<div class="card-footer">{{ $offers->links() }}</div>@endif</div>
@endsection
