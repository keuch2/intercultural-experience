{{-- Tabla de documentos de un grupo. Variables: $group (array), $groupEntries (collection de DocumentService::describe) --}}
@php
    $gate = $definition->gate($group['unlock_gate_key'] ?? null);
    $gateOk = $gate ? $process->isGateVerified($gate->key) : true;
    $groupEntries = collect($groupEntries);
    $showTitle = $showTitle ?? true;
@endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-folder-open text-primary me-2"></i> Documentos: {{ $group['label'] }}
            @if($gate)<span class="badge {{ $gateOk ? 'bg-success' : 'bg-warning text-dark' }} ms-2" title="Se habilitan en la app al verificar este pago"><i class="fas fa-{{ $gateOk ? 'unlock' : 'lock' }} me-1"></i>{{ $gate->label }}</span>@endif
        </h5>
        @php $pendingKeys = $groupEntries->filter(fn ($e) => collect($e['files'])->where('status', 'pending')->isNotEmpty())->pluck('document_type'); @endphp
        @if($pendingKeys->isNotEmpty())
        <form method="POST" action="{{ route('admin.program.documents.bulk-approve', [$program->slug, $process->id, $pendingKeys->first()]) }}" class="d-inline">@csrf<button class="btn btn-sm btn-outline-success" title="Aprobar pendientes de {{ $pendingKeys->first() }}"><i class="fas fa-check-double"></i></button></form>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light"><tr><th>Documento</th><th class="text-center" style="width:90px">Req.</th><th class="text-center" style="width:80px">Sube</th><th class="text-center" style="width:130px">Estado</th><th style="width:340px">Archivos / acciones</th></tr></thead>
                <tbody>
                @forelse($groupEntries as $entry)
                    @php $files = collect($entry['files']); $key = $entry['document_type']; @endphp
                    <tr>
                        <td><i class="fas fa-file me-1 text-muted"></i> {{ $entry['label'] }}
                            @if($entry['min_count'])<span class="badge bg-light text-dark ms-1">mín. {{ $entry['min_count'] }}</span>@endif
                            @if($entry['description'])<br><small class="text-muted">{{ $entry['description'] }}</small>@endif
                        </td>
                        <td class="text-center"><span class="badge bg-{{ $entry['required'] ? 'danger' : 'secondary' }}">{{ $entry['required'] ? 'Sí' : 'No' }}</span></td>
                        <td class="text-center"><span class="badge {{ $entry['uploaded_by'] === 'staff' ? 'bg-dark' : 'bg-info text-dark' }}">{{ $entry['uploaded_by'] === 'staff' ? 'IE' : 'Part.' }}</span></td>
                        <td class="text-center">
                            @switch($entry['status'])
                                @case('approved')<span class="badge bg-success"><i class="fas fa-check-circle"></i> Aprobado</span>@break
                                @case('pending')<span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> En revisión</span>@break
                                @case('rejected')<span class="badge bg-danger"><i class="fas fa-times-circle"></i> Rechazado</span>@break
                                @default<span class="badge bg-light text-dark"><i class="fas fa-minus-circle"></i> Sin subir</span>
                            @endswitch
                            @if($files->count() > 1)<br><small class="text-muted">{{ $files->where('status', 'approved')->count() }}/{{ $files->count() }} aprobados</small>@endif
                        </td>
                        <td>
                            @foreach($files as $file)
                            <div class="d-flex gap-1 flex-wrap align-items-center mb-1">
                                <span class="badge bg-{{ ['approved' => 'success', 'pending' => 'warning text-dark', 'rejected' => 'danger'][$file['status']] ?? 'secondary' }}">{{ $file['status_label'] }}</span>
                                <small class="text-muted"><i class="fas fa-paperclip"></i> {{ Str::limit($file['original_filename'], 28) }} ({{ $file['file_size_formatted'] }})</small>
                                @if($file['rejection_reason'])<small class="text-danger"><i class="fas fa-comment-alt"></i> {{ $file['rejection_reason'] }}</small>@endif
                                <a href="{{ route('admin.program.documents.download', [$program->slug, $process->id, $file['id']]) }}" class="btn btn-sm btn-outline-primary py-0" title="Descargar"><i class="fas fa-download"></i></a>
                                @if($file['status'] !== 'approved')
                                    <form method="POST" action="{{ route('admin.program.documents.review', [$program->slug, $process->id, $file['id']]) }}" class="d-inline">@csrf @method('PUT')<input type="hidden" name="action" value="approve"><button class="btn btn-sm btn-outline-success py-0" title="Aprobar"><i class="fas fa-check"></i></button></form>
                                    <button class="btn btn-sm btn-outline-danger py-0" title="Rechazar" data-bs-toggle="modal" data-bs-target="#rejectDoc{{ $file['id'] }}"><i class="fas fa-times"></i></button>
                                    <form method="POST" action="{{ route('admin.program.documents.delete', [$program->slug, $process->id, $file['id']]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este documento?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-secondary py-0" title="Eliminar"><i class="fas fa-trash"></i></button></form>
                                @else
                                    <button class="btn btn-sm btn-outline-danger py-0" title="Eliminar (requiere motivo)" data-bs-toggle="modal" data-bs-target="#deleteDoc{{ $file['id'] }}"><i class="fas fa-trash"></i></button>
                                @endif
                            </div>
                            @endforeach
                            @if($files->count() > 1)
                                <a href="{{ route('admin.program.documents.download-all', [$program->slug, $process->id, $key]) }}" class="btn btn-sm btn-outline-secondary py-0 mb-1"><i class="fas fa-file-archive me-1"></i> Descargar todo</a>
                            @endif
                            <button class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal" data-bs-target="#uploadDoc{{ $key }}"><i class="fas fa-upload me-1"></i>{{ $files->isEmpty() ? 'Subir' : 'Agregar' }}</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">Sin documentos configurados para este grupo.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modales --}}
@foreach($groupEntries as $entry)
<div class="modal fade" id="uploadDoc{{ $entry['document_type'] }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('admin.program.documents.upload', [$program->slug, $process->id]) }}" enctype="multipart/form-data">@csrf
        <input type="hidden" name="requirement_key" value="{{ $entry['document_type'] }}">
        <div class="modal-header"><h6 class="modal-title">Subir: {{ $entry['label'] }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="mb-3"><label class="form-label small">Archivo(s) <span class="text-danger">*</span></label><input type="file" name="files[]" class="form-control form-control-sm" required {{ $entry['allow_multiple'] ? 'multiple' : '' }} accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.mp4,.mov"><small class="text-muted">PDF, imagen, Word o video. Lo que sube IE queda aprobado.</small></div>
            <div class="mb-0"><label class="form-label small">Notas</label><input type="text" name="notes" class="form-control form-control-sm"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i> Subir</button></div>
    </form>
</div></div></div>
    @foreach(collect($entry['files']) as $file)
        @if($file['status'] !== 'approved')
        <div class="modal fade" id="rejectDoc{{ $file['id'] }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="{{ route('admin.program.documents.review', [$program->slug, $process->id, $file['id']]) }}">@csrf @method('PUT')<input type="hidden" name="action" value="reject">
                <div class="modal-header"><h6 class="modal-title">Rechazar documento</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><p class="small text-muted">{{ $entry['label'] }} — {{ $file['original_filename'] }}</p><label class="form-label small">Motivo del rechazo <span class="text-danger">*</span></label><textarea name="rejection_reason" class="form-control form-control-sm" rows="3" required></textarea></div>
                <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times me-1"></i> Rechazar</button></div>
            </form>
        </div></div></div>
        @else
        <div class="modal fade" id="deleteDoc{{ $file['id'] }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="{{ route('admin.program.documents.delete', [$program->slug, $process->id, $file['id']]) }}">@csrf @method('DELETE')
                <div class="modal-header bg-danger text-white"><h6 class="modal-title"><i class="fas fa-exclamation-triangle me-1"></i> Eliminar documento aprobado</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><p class="small text-muted">{{ $entry['label'] }} — {{ $file['original_filename'] }}</p><label class="form-label small">Motivo <span class="text-danger">*</span></label><textarea name="deletion_reason" class="form-control form-control-sm" rows="3" required></textarea></div>
                <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i> Eliminar</button></div>
            </form>
        </div></div></div>
        @endif
    @endforeach
@endforeach
