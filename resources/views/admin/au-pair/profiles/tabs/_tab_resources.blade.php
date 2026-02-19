{{-- Tab: Recursos del Programa (E) --}}
@php
    $recursos = \App\Models\AuPairResource::where('is_active', true)->orderBy('sort_order')->get();
@endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-folder-open text-primary me-2"></i> E. Recursos del Programa
        </h5>
        <a href="{{ route('admin.aupair.resources.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-cog me-1"></i> Gestionar Recursos
        </a>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-3">Documentos descargables para el participante.</p>

        @if($recursos->count() > 0)
        <div class="list-group">
            @foreach($recursos as $recurso)
            <div class="list-group-item d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 rounded p-2 me-3 text-center" style="width: 40px; height: 40px;">
                        <i class="fas {{ $recurso->icon ?? 'fa-file-pdf' }} text-primary"></i>
                    </div>
                    <div>
                        <div class="fw-semibold small">{{ $recurso->title }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">{{ $recurso->description }}</div>
                    </div>
                </div>
                <div>
                    @if($recurso->hasFile())
                        <a href="{{ route('admin.aupair.resources.download', $recurso->id) }}" class="btn btn-sm btn-outline-primary" title="Descargar">
                            <i class="fas fa-download"></i>
                        </a>
                    @elseif($recurso->external_url)
                        <a href="{{ $recurso->external_url }}" target="_blank" class="btn btn-sm btn-outline-info" title="Abrir enlace">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    @else
                        <span class="badge bg-warning text-dark">Pendiente de carga</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-muted small mb-0"><i class="fas fa-info-circle"></i> No hay recursos disponibles. <a href="{{ route('admin.aupair.resources.index') }}">Agregar recursos</a>.</p>
        @endif
    </div>
</div>
