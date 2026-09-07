<div class="row g-3">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header"><strong>Recursos descargables</strong> <span class="text-muted small ms-2">Visibles en la app en "Recursos del programa".</span></div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>#</th><th>Título</th><th>Tipo</th><th>Archivo / URL</th><th></th></tr></thead>
                    <tbody>
                    @forelse($resources as $res)
                        <tr class="{{ $res->is_active ? '' : 'table-secondary text-muted' }}">
                            <td>{{ $res->sort_order }}</td>
                            <td><i class="fas {{ $res->icon }} me-1 text-muted"></i>{{ $res->title }}<br><small class="text-muted">{{ $res->description }}</small></td>
                            <td><span class="badge bg-light text-dark">{{ $res->file_type }}</span></td>
                            <td class="small">
                                @if($res->hasFile())<a href="{{ route('admin.program-config.resources.download', [$program->id, $res->id]) }}"><i class="fas fa-download"></i> {{ $res->original_filename }}</a> ({{ $res->file_size_formatted }})
                                @elseif($res->external_url)<a href="{{ $res->external_url }}" target="_blank" rel="noopener">{{ Str::limit($res->external_url, 40) }}</a>
                                @else <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> sin archivo</span>@endif
                            </td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#res-edit-{{ $res->id }}"><i class="fas fa-edit"></i></button>
                                <form action="{{ route('admin.program-config.resources.destroy', [$program->id, $res->id]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar el recurso?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                        <tr class="collapse" id="res-edit-{{ $res->id }}"><td colspan="5" class="bg-light">
                            <form action="{{ route('admin.program-config.resources.update', [$program->id, $res->id]) }}" method="POST" enctype="multipart/form-data">@csrf @method('PUT')
                                @include('admin.program-config.tabs._form_resource', ['x' => $res])
                                <button class="btn btn-sm btn-primary mt-2"><i class="fas fa-save"></i> Guardar</button>
                            </form>
                        </td></tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Sin recursos.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm"><div class="card-header"><strong><i class="fas fa-plus me-1"></i> Nuevo recurso</strong></div>
            <div class="card-body">
                <form action="{{ route('admin.program-config.resources.store', $program->id) }}" method="POST" enctype="multipart/form-data">@csrf
                    @include('admin.program-config.tabs._form_resource', ['x' => null])
                    <button class="btn btn-primary mt-2 w-100"><i class="fas fa-plus"></i> Crear</button>
                </form>
            </div>
        </div>
    </div>
</div>
