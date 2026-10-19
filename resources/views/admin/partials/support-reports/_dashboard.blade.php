{{-- Reportes del participante pendientes (todos los programas). Requiere $supportReports y $supportReportsCount. --}}
<div class="card shadow mb-4 {{ $supportReportsCount ? 'border-warning' : '' }}">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-headset me-1"></i> Reportes de participantes pendientes
            @if($supportReportsCount)<span class="badge bg-danger ms-1">{{ $supportReportsCount }}</span>@endif</h6>
        <small class="text-muted">Desde la app · todos los programas</small>
    </div>
    <div class="card-body p-0">
        @forelse($supportReports as $r)
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-2 border-bottom">
            <div class="flex-grow-1" style="min-width: 240px">
                <div>
                    @if($r['urgent'])<span class="badge bg-danger me-1">Urgente</span>@endif
                    <a href="{{ $r['url'] }}" class="fw-semibold text-decoration-none">{{ $r['participant'] ?? 'Participante' }}</a>
                    <span class="badge bg-light text-dark border ms-1">{{ $r['program'] }}</span>
                    <span class="badge bg-warning text-dark ms-1">{{ $r['type_label'] }}</span>
                </div>
                <div class="small">{{ $r['title'] }} <span class="text-muted">· {{ $r['created_at']->diffForHumans() }}</span></div>
            </div>
            <div class="d-flex gap-1">
                <a href="{{ $r['url'] }}" class="btn btn-sm btn-outline-primary py-0"><i class="fas fa-arrow-right me-1"></i> Ver Support</a>
                <form method="POST" action="{{ route('admin.support-reports.read', [$r['kind'], $r['id']]) }}">@csrf<button class="btn btn-sm btn-outline-secondary py-0" title="Marcar como leído"><i class="fas fa-eye"></i> Leído</button></form>
                <button type="button" class="btn btn-sm btn-outline-success py-0" data-bs-toggle="modal" data-bs-target="#resolveSupportReportModal" data-resolve-report data-action="{{ route('admin.support-reports.resolve', [$r['kind'], $r['id']]) }}" data-title="{{ $r['participant'] }} — {{ $r['title'] }}"><i class="fas fa-check"></i> Solucionado</button>
            </div>
        </div>
        @empty
        <p class="text-muted small m-3"><i class="fas fa-check-circle text-success me-1"></i> No hay reportes pendientes.</p>
        @endforelse
        @if($supportReportsCount > $supportReports->count())
        <p class="small text-muted m-2 ms-3">Mostrando {{ $supportReports->count() }} de {{ $supportReportsCount }}.</p>
        @endif
    </div>
</div>
@include('admin.partials.support-reports._resolve_modal')
