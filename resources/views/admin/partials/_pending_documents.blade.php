{{-- Documentos pendientes de aprobación (todos los programas). Requiere $pendingDocuments y $pendingDocs. --}}
<div class="card shadow mb-4 {{ $pendingDocs ? 'border-info' : '' }}">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-upload me-1"></i> Documentos pendientes de aprobación
            @if($pendingDocs)<span class="badge bg-danger ms-1">{{ $pendingDocs }}</span>@endif</h6>
        <small class="text-muted">Más antiguos primero · todos los programas</small>
    </div>
    <div class="card-body p-0">
        @forelse($pendingDocuments as $d)
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-2 border-bottom">
            <div class="flex-grow-1" style="min-width: 240px">
                <div>
                    <a href="{{ $d['url'] }}" class="fw-semibold text-decoration-none">{{ $d['participant'] ?? 'Participante' }}</a>
                    <span class="badge bg-light text-dark border ms-1">{{ $d['program'] }}</span>
                    @unless($d['from_participant'])<span class="badge bg-secondary ms-1">Cargado por IE</span>@endunless
                </div>
                <div class="small">{{ $d['label'] }} <span class="text-muted">· cargado {{ $d['created_at']->diffForHumans() }}</span></div>
            </div>
            <a href="{{ $d['url'] }}" class="btn btn-sm btn-outline-primary py-0"><i class="fas fa-search me-1"></i> Revisar</a>
        </div>
        @empty
        <p class="text-muted small m-3"><i class="fas fa-check-circle text-success me-1"></i> No hay documentos pendientes de aprobación.</p>
        @endforelse
        @if($pendingDocs > $pendingDocuments->count())
        <p class="small text-muted m-2 ms-3">Mostrando {{ $pendingDocuments->count() }} de {{ $pendingDocs }}.</p>
        @endif
    </div>
</div>
