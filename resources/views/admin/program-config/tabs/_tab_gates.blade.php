<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header"><strong>Gates de pago</strong> <span class="text-muted small ms-2">Hitos de pago que habilitan documentos o el avance de etapa (reemplazan a "Pago 1 / Pago 2").</span></div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Clave</th><th>Etiqueta</th><th>Monto</th><th>Concepto (auto)</th><th></th></tr></thead>
                    <tbody>
                    @forelse($gates as $gate)
                        <tr class="{{ $gate->is_active ? '' : 'table-secondary text-muted' }}">
                            <td>{{ $gate->sort_order }}</td>
                            <td><code>{{ $gate->key }}</code></td>
                            <td>{{ $gate->label }}</td>
                            <td>{{ $gate->amount !== null ? number_format((float) $gate->amount, 2) . ' ' . ($gate->currency?->code ?? '') : '—' }}</td>
                            <td><small>{{ $gate->concept_match ?? '—' }} {!! $gate->auto_verify_from_payments ? '<span class="badge bg-success">auto</span>' : '' !!}</small></td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#gate-edit-{{ $gate->id }}"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('admin.program-config.gates.destroy', [$program->id, $gate->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar el gate {{ $gate->key }}?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                        <tr class="collapse" id="gate-edit-{{ $gate->id }}"><td colspan="6" class="bg-light">
                            <form action="{{ route('admin.program-config.gates.update', [$program->id, $gate->id]) }}" method="POST">@csrf @method('PUT')
                                @include('admin.program-config.tabs._form_gate', ['g' => $gate])
                                <button class="btn btn-sm btn-primary mt-2"><i class="fas fa-save"></i> Guardar</button>
                            </form>
                        </td></tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Sin gates de pago.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm"><div class="card-header"><strong><i class="fas fa-plus me-1"></i> Nuevo gate</strong></div>
            <div class="card-body">
                <form action="{{ route('admin.program-config.gates.store', $program->id) }}" method="POST">@csrf
                    @include('admin.program-config.tabs._form_gate', ['g' => null])
                    <button class="btn btn-primary mt-2 w-100"><i class="fas fa-plus"></i> Crear</button>
                </form>
            </div>
        </div>
    </div>
</div>
