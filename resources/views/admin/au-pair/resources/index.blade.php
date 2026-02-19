@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Recursos del Programa Au Pair</h2>
        <p class="text-muted mb-0">Documentos descargables para participantes</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal">
        <i class="fas fa-plus me-1"></i> Agregar Recurso
    </button>
</div>

@if(session('success'))
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-1"></i> {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Recurso</th>
                    <th>Tipo</th>
                    <th class="text-center">Estado</th>
                    <th>Archivo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($resources as $resource)
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 rounded p-2 me-3 text-center" style="width: 40px; height: 40px;">
                                <i class="fas {{ $resource->icon }} text-primary"></i>
                            </div>
                            <div>
                                <div class="fw-semibold">{{ $resource->title }}</div>
                                <small class="text-muted">{{ $resource->description }}</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge bg-{{ $resource->file_type_badge_color }}">{{ $resource->file_type }}</span></td>
                    <td class="text-center"><span class="badge bg-{{ $resource->status_color }}">{{ $resource->status_label }}</span></td>
                    <td>
                        @if($resource->hasFile())
                            <small class="text-muted">{{ $resource->original_filename }}<br>({{ $resource->file_size_formatted }})</small>
                        @elseif($resource->external_url)
                            <a href="{{ $resource->external_url }}" target="_blank" class="small"><i class="fas fa-external-link-alt me-1"></i>Enlace</a>
                        @else
                            <small class="text-muted">—</small>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            {{-- Upload / Replace file --}}
                            <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadFileModal-{{ $resource->id }}" title="Subir archivo">
                                <i class="fas fa-upload"></i> {{ $resource->hasFile() ? 'Reemplazar' : 'Subir' }}
                            </button>
                            {{-- Download --}}
                            @if($resource->hasFile())
                            <a href="{{ route('admin.aupair.resources.download', $resource->id) }}" class="btn btn-outline-success" title="Descargar">
                                <i class="fas fa-download"></i>
                            </a>
                            @endif
                            {{-- Edit --}}
                            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editResourceModal-{{ $resource->id }}" title="Editar">
                                <i class="fas fa-edit"></i>
                            </button>
                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.aupair.resources.delete', $resource->id) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este recurso?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                        No hay recursos registrados. Usa el botón <strong>+ Agregar Recurso</strong> para crear el primero.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Add Resource Modal --}}
<div class="modal fade" id="addResourceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.resources.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-plus me-1"></i> Agregar Recurso</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" required placeholder="Nombre del recurso">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Descripción</label>
                            <input type="text" name="description" class="form-control form-control-sm" placeholder="Breve descripción">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Tipo <span class="text-danger">*</span></label>
                            <select name="file_type" class="form-select form-select-sm" required>
                                <option value="PDF">PDF</option>
                                <option value="DOC">DOC</option>
                                <option value="VIDEO">VIDEO</option>
                                <option value="LINK">LINK</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Icono</label>
                            <input type="text" name="icon" class="form-control form-control-sm" placeholder="fa-file-pdf" value="fa-file-pdf">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Orden</label>
                            <input type="number" name="sort_order" class="form-control form-control-sm" min="0" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Archivo (opcional)</label>
                            <input type="file" name="file" class="form-control form-control-sm">
                            <small class="text-muted">Máx 50 MB. También puede subir después.</small>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">URL externa (para tipo LINK)</label>
                            <input type="url" name="external_url" class="form-control form-control-sm" placeholder="https://...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Per-resource modals --}}
@foreach($resources as $resource)
{{-- Upload File Modal --}}
<div class="modal fade" id="uploadFileModal-{{ $resource->id }}" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.resources.upload', $resource->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-upload me-1"></i> Subir Archivo</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small mb-2"><strong>{{ $resource->title }}</strong></p>
                    @if($resource->hasFile())
                    <div class="alert alert-warning py-1 px-2 small mb-2"><i class="fas fa-exclamation-triangle me-1"></i> Se reemplazará el archivo actual: <em>{{ $resource->original_filename }}</em></div>
                    @endif
                    <input type="file" name="file" class="form-control form-control-sm" required>
                    <small class="text-muted">Máx 50 MB</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i> Subir</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Resource Modal --}}
<div class="modal fade" id="editResourceModal-{{ $resource->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.resources.update', $resource->id) }}">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-edit me-1"></i> Editar Recurso</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" required value="{{ $resource->title }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Descripción</label>
                            <input type="text" name="description" class="form-control form-control-sm" value="{{ $resource->description }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Tipo <span class="text-danger">*</span></label>
                            <select name="file_type" class="form-select form-select-sm" required>
                                @foreach(['PDF', 'DOC', 'VIDEO', 'LINK'] as $ft)
                                <option value="{{ $ft }}" {{ $resource->file_type === $ft ? 'selected' : '' }}>{{ $ft }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Icono</label>
                            <input type="text" name="icon" class="form-control form-control-sm" value="{{ $resource->icon }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Orden</label>
                            <input type="number" name="sort_order" class="form-control form-control-sm" min="0" value="{{ $resource->sort_order }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">URL externa</label>
                            <input type="url" name="external_url" class="form-control form-control-sm" value="{{ $resource->external_url }}">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input class="form-check-input" type="checkbox" name="is_active" value="1" id="active-{{ $resource->id }}" {{ $resource->is_active ? 'checked' : '' }}>
                                <label class="form-check-label small" for="active-{{ $resource->id }}">Activo</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection
