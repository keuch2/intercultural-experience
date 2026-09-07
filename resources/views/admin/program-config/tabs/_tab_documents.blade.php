<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Documentos requeridos</strong>
                <span class="text-muted small">Agrupados por etapa → grupo (tab en la app). "Desbloqueo" = gate de pago que habilita la carga.</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Etapa / Grupo</th><th>Clave</th><th>Etiqueta</th><th>Req.</th><th>Min</th><th>Sube</th><th>Desbloqueo</th><th></th></tr></thead>
                    <tbody>
                    @forelse($requirements->sortBy(fn($r) => sprintf('%03d-%s-%05d', $definition->stageIndex($r->stage_key), $r->effective_group, $r->sort_order)) as $req)
                        <tr class="{{ $req->is_active ? '' : 'table-secondary text-muted' }}">
                            <td><small><code>{{ $req->stage_key }}</code>@if($req->group_key && $req->group_key !== $req->stage_key) → <code>{{ $req->group_key }}</code>@endif</small></td>
                            <td><code>{{ $req->key }}</code></td>
                            <td>{{ $req->label }} @if(!$req->is_active)<span class="badge bg-secondary">inactivo</span>@endif @if($req->section)<span class="badge bg-light text-dark">{{ $req->section }}</span>@endif</td>
                            <td>{!! $req->is_required ? '<i class="fas fa-check text-success"></i>' : '<span class="text-muted">opc.</span>' !!}</td>
                            <td>{{ $req->min_count }}{{ $req->allow_multiple ? '+' : '' }}</td>
                            <td><span class="badge {{ $req->uploaded_by === 'staff' ? 'bg-dark' : 'bg-info text-dark' }}">{{ $req->uploaded_by === 'staff' ? 'IE' : 'participante' }}</span></td>
                            <td><small>{{ $req->unlock_gate_key ?? '—' }}</small></td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#req-edit-{{ $req->id }}"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('admin.program-config.documents.destroy', [$program->id, $req->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar el requisito {{ $req->key }}?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                        <tr class="collapse" id="req-edit-{{ $req->id }}"><td colspan="8" class="bg-light">
                            <form action="{{ route('admin.program-config.documents.update', [$program->id, $req->id]) }}" method="POST">@csrf @method('PUT')
                                @include('admin.program-config.tabs._form_requirement', ['r' => $req])
                                <button class="btn btn-sm btn-primary mt-2"><i class="fas fa-save"></i> Guardar</button>
                            </form>
                        </td></tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">Sin documentos configurados.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header"><strong><i class="fas fa-plus me-1"></i> Nuevo documento requerido</strong></div>
            <div class="card-body">
                @if($stages->isEmpty())
                    <div class="text-muted small">Primero creá al menos una etapa.</div>
                @else
                <form action="{{ route('admin.program-config.documents.store', $program->id) }}" method="POST">@csrf
                    @include('admin.program-config.tabs._form_requirement', ['r' => null])
                    <button class="btn btn-primary mt-2 w-100"><i class="fas fa-plus"></i> Crear</button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
