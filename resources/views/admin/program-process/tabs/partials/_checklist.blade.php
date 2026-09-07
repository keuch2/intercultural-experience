@php $items = $tabData['checklist']; $gates = $tabData['gates']; @endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h5 class="card-title mb-0"><i class="fas fa-tasks text-info me-2"></i> Gestión de procesos (checklist)</h5></div>
    <div class="card-body">
        @if($gates->isNotEmpty())
        <div class="row g-2 mb-3">
            @foreach($gates as $gate)
            @php $ok = $process->isGateVerified($gate->key); @endphp
            <div class="col-md-6"><div class="border rounded p-2 d-flex align-items-center justify-content-between {{ $ok ? 'border-success bg-success bg-opacity-10' : 'border-warning bg-warning bg-opacity-10' }}">
                <div><strong class="small">{{ $gate->label }}</strong><br><small class="text-muted">Gate de pago requerido en esta etapa</small></div>
                @if($ok)<span class="badge bg-success"><i class="fas fa-check-circle"></i> Verificado</span>
                @else<form method="POST" action="{{ route('admin.program.gates.update', [$program->slug, $process->id, $gate->key]) }}">@csrf @method('PUT')<input type="hidden" name="value" value="1"><input type="hidden" name="redirect_tab" value="{{ $activeTab }}"><button class="btn btn-sm btn-outline-success py-0"><i class="fas fa-check me-1"></i> Marcar verificado</button></form>@endif
            </div></div>
            @endforeach
        </div>
        @endif

        @if($items->isNotEmpty())
        <form method="POST" action="{{ route('admin.program.checklist.update', [$program->slug, $process->id]) }}" enctype="multipart/form-data">@csrf @method('PUT')<input type="hidden" name="stage_key" value="{{ $tabData['stage']->key }}">
            <div class="list-group mb-3">
                @foreach($items as $item)
                @php $row = $process->checklist->firstWhere('item_key', $item->key); $done = (bool) $row?->is_done; @endphp
                <label class="list-group-item d-flex align-items-center {{ $item->item_type === 'file' ? ($done ? 'list-group-item-success' : 'list-group-item-warning') : '' }}">
                    <input type="hidden" name="items[{{ $item->key }}]" value="0">
                    <input class="form-check-input me-3" type="checkbox" name="items[{{ $item->key }}]" value="1" {{ $done ? 'checked' : '' }} {{ $item->item_type === 'file' ? 'disabled' : '' }}>
                    <span>{{ $item->label }} @if($item->required_for_advance)<span class="badge bg-danger ms-1" title="Requerido para avanzar">req.</span>@endif</span>
                    @if($item->item_type === 'file')
                        <span class="ms-auto d-flex align-items-center gap-2">
                            @if($row?->file_path)
                                <a href="{{ route('admin.program.checklist.download', [$program->slug, $process->id, $item->key]) }}" class="btn btn-sm btn-outline-primary py-0"><i class="fas fa-file-pdf me-1"></i>{{ Str::limit($row->original_filename, 30) }}</a>
                                <small class="text-success">{{ $row->done_at?->format('d/m/Y') }}</small>
                                <button type="button" class="btn btn-sm btn-outline-danger py-0" data-bs-toggle="modal" data-bs-target="#deleteChecklistFile{{ $item->key }}"><i class="fas fa-trash"></i></button>
                            @else
                                <input type="file" name="files[{{ $item->key }}]" class="form-control form-control-sm" style="max-width:280px" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="text-muted">Se marca al subir el archivo</small>
                            @endif
                        </span>
                    @elseif($done && $row?->done_at)
                        <small class="ms-auto text-muted">{{ $row->done_at->format('d/m/Y') }} · {{ $row->doneBy?->name }}</small>
                    @endif
                </label>
                @endforeach
            </div>
            <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Guardar checklist</button>
        </form>
        @endif
    </div>
</div>
@foreach($items->where('item_type', 'file') as $item)
<div class="modal fade" id="deleteChecklistFile{{ $item->key }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('admin.program.checklist.delete-file', [$program->slug, $process->id, $item->key]) }}">@csrf @method('DELETE')
        <div class="modal-header bg-danger text-white"><h6 class="modal-title">Eliminar archivo: {{ $item->label }}</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><label class="form-label small">Motivo <span class="text-danger">*</span></label><textarea name="reason" class="form-control form-control-sm" rows="3" required></textarea></div>
        <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash me-1"></i> Eliminar</button></div>
    </form>
</div></div></div>
@endforeach
