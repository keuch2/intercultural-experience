<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center"><h5 class="card-title mb-0"><i class="fas fa-folder-open text-primary me-2"></i> Recursos del programa</h5><a href="{{ route('admin.program-config.show', ['program' => $program->id, 'tab' => 'resources']) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-cog me-1"></i> Administrar recursos</a></div>
    <div class="card-body">
        @forelse($tabData['resources'] as $res)
        <div class="border rounded p-2 mb-2 d-flex align-items-center justify-content-between {{ $res->is_active ? '' : 'opacity-50' }}">
            <div><i class="fas {{ $res->icon }} me-2 text-muted"></i><strong>{{ $res->title }}</strong>@if($res->description)<br><small class="text-muted">{{ $res->description }}</small>@endif</div>
            <div>@if($res->hasFile())<a href="{{ route('admin.program-config.resources.download', [$program->id, $res->id]) }}" class="btn btn-sm btn-outline-primary py-0"><i class="fas fa-download"></i> {{ $res->file_size_formatted }}</a>@elseif($res->external_url)<a href="{{ $res->external_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-link"></i></a>@else<span class="badge bg-warning text-dark">sin archivo</span>@endif</div>
        </div>
        @empty
        <p class="text-muted small mb-0">Sin recursos configurados.</p>
        @endforelse
        <p class="text-muted small mt-3 mb-0"><i class="fas fa-info-circle me-1"></i> Los recursos son comunes a todos los participantes del programa y se muestran en la app.</p>
    </div>
</div>
