@php $gateOptions = $gates; $checkOptions = $checklist; @endphp
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Etapas del proceso</strong>
                <span class="text-muted small">Orden = <code>sort_order</code>. La última suele ser terminal (Completado).</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Clave</th><th>Etiqueta</th><th>Pantalla móvil</th><th>Guards</th><th></th></tr></thead>
                    <tbody>
                    @forelse($stages as $stage)
                        @php $g = $stage->guards ?? []; @endphp
                        <tr>
                            <td>{{ $stage->sort_order }}</td>
                            <td><code>{{ $stage->key }}</code> @if($stage->is_terminal)<span class="badge bg-dark">terminal</span>@endif</td>
                            <td>{{ $stage->label }}</td>
                            <td><small class="text-muted">{{ $stage->mobile_screen ?? '—' }}</small></td>
                            <td class="small">
                                @if(!empty($g['manual_only'])) <span class="badge bg-secondary">manual</span> @else
                                    @if(!empty($g['require_docs_approved'])) <span class="badge bg-info text-dark">docs</span> @endif
                                    @foreach($g['require_gates'] ?? [] as $gk) <span class="badge bg-success">pago:{{ $gk }}</span> @endforeach
                                    @foreach($g['require_checklist'] ?? [] as $ck) <span class="badge bg-primary">check:{{ $ck }}</span> @endforeach
                                    @if(!empty($g['require_english_min_level'])) <span class="badge bg-warning text-dark">inglés</span> @endif
                                    @if(!empty($g['require_job_assignment'])) <span class="badge bg-warning text-dark">oferta</span> @endif
                                    @if(!empty($g['require_placement_complete'])) <span class="badge bg-warning text-dark">placement</span> @endif
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#stage-edit-{{ $stage->id }}"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('admin.program-config.stages.destroy', [$program->id, $stage->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar la etapa {{ $stage->key }}?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                        <tr class="collapse" id="stage-edit-{{ $stage->id }}"><td colspan="6" class="bg-light">
                            <form action="{{ route('admin.program-config.stages.update', [$program->id, $stage->id]) }}" method="POST">@csrf @method('PUT')
                                @include('admin.program-config.tabs._form_stage', ['s' => $stage])
                                <button class="btn btn-sm btn-primary mt-2"><i class="fas fa-save"></i> Guardar</button>
                            </form>
                        </td></tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Sin etapas configuradas.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header"><strong><i class="fas fa-plus me-1"></i> Nueva etapa</strong></div>
            <div class="card-body">
                <form action="{{ route('admin.program-config.stages.store', $program->id) }}" method="POST">@csrf
                    @include('admin.program-config.tabs._form_stage', ['s' => null])
                    <button class="btn btn-primary mt-2 w-100"><i class="fas fa-plus"></i> Crear etapa</button>
                </form>
            </div>
        </div>
    </div>
</div>
