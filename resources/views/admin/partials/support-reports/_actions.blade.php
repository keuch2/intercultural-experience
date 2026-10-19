{{-- Estado + acciones de un reporte del participante. Requiere $log y $kind ('program' | 'au-pair'). --}}
@if($log->source === 'participant')
@php $reviewStatus = $log->reviewStatus(); @endphp
<div class="d-flex flex-wrap align-items-center gap-2 mt-2">
    @if($reviewStatus === 'resolved')
        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Solucionado</span>
        <small class="text-muted">{{ $log->resolved_at->format('d/m/Y H:i') }}@if($log->resolvedBy) · {{ $log->resolvedBy->name }}@endif</small>
    @elseif($reviewStatus === 'read')
        <span class="badge bg-secondary"><i class="fas fa-eye me-1"></i>Leído</span>
        <small class="text-muted">{{ $log->read_at->format('d/m/Y H:i') }}@if($log->readBy) · {{ $log->readBy->name }}@endif</small>
    @else
        <span class="badge bg-danger"><i class="fas fa-bell me-1"></i>Pendiente</span>
        <form method="POST" action="{{ route('admin.support-reports.read', [$kind, $log->id]) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-eye me-1"></i> Marcar como leído</button></form>
    @endif
    @if($reviewStatus !== 'resolved')
        <button type="button" class="btn btn-sm btn-outline-success py-0" data-bs-toggle="modal" data-bs-target="#resolveSupportReportModal" data-resolve-report data-action="{{ route('admin.support-reports.resolve', [$kind, $log->id]) }}" data-title="{{ $log->title }}"><i class="fas fa-check me-1"></i> Marcar como solucionado</button>
    @endif
</div>
@endif
