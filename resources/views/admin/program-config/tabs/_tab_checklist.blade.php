<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header"><strong>Checklist de gestión (lo completa el equipo IE)</strong></div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Etapa</th><th>Clave</th><th>Etiqueta</th><th>Tipo</th><th>Bloquea avance</th><th></th></tr></thead>
                    <tbody>
                    @forelse($checklist as $item)
                        <tr class="{{ $item->is_active ? '' : 'table-secondary text-muted' }}">
                            <td>{{ $item->sort_order }}</td>
                            <td><small><code>{{ $item->stage_key ?? '—' }}</code></small></td>
                            <td><code>{{ $item->key }}</code></td>
                            <td>{{ $item->label }}</td>
                            <td><span class="badge bg-light text-dark">{{ $item->item_type === 'file' ? 'con archivo' : 'sí/no' }}</span></td>
                            <td>{!! $item->required_for_advance ? '<i class="fas fa-check text-success"></i>' : '—' !!}</td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#chk-edit-{{ $item->id }}"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('admin.program-config.checklist.destroy', [$program->id, $item->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar {{ $item->key }}?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                        <tr class="collapse" id="chk-edit-{{ $item->id }}"><td colspan="7" class="bg-light">
                            <form action="{{ route('admin.program-config.checklist.update', [$program->id, $item->id]) }}" method="POST">@csrf @method('PUT')
                                @include('admin.program-config.tabs._form_checklist', ['c' => $item])
                                <button class="btn btn-sm btn-primary mt-2"><i class="fas fa-save"></i> Guardar</button>
                            </form>
                        </td></tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Sin ítems de checklist.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm"><div class="card-header"><strong><i class="fas fa-plus me-1"></i> Nuevo ítem</strong></div>
            <div class="card-body">
                <form action="{{ route('admin.program-config.checklist.store', $program->id) }}" method="POST">@csrf
                    @include('admin.program-config.tabs._form_checklist', ['c' => null])
                    <button class="btn btn-primary mt-2 w-100"><i class="fas fa-plus"></i> Crear</button>
                </form>
            </div>
        </div>
    </div>
</div>
