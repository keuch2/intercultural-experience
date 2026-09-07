<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h5 class="card-title mb-0"><i class="fas fa-chart-bar text-primary me-2"></i> Historial de actividad</h5></div>
    <div class="card-body">
        @forelse($tabData['logs'] as $log)
        <div class="d-flex gap-3 border-bottom py-2">
            <small class="text-muted text-nowrap" style="min-width:120px">{{ $log->created_at->format('d/m/Y H:i') }}</small>
            <div><span class="badge bg-light text-dark me-1">{{ $log->action ?? $log->event ?? '-' }}</span> {{ $log->description }} @if($log->causer)<small class="text-muted">— {{ $log->causer->name }}</small>@endif</div>
        </div>
        @empty
        <p class="text-muted small mb-0"><i class="fas fa-info-circle"></i> Sin actividad registrada para este participante.</p>
        @endforelse
    </div>
</div>
