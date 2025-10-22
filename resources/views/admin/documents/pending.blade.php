@extends('layouts.admin')

@section('title', 'Documentos Pendientes de Revisión')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-check"></i> Documentos Pendientes
        </h1>
        <div>
            <a href="{{ route('admin.documents.expired') }}" class="btn btn-sm btn-warning">
                <i class="fas fa-exclamation-triangle"></i> Ver Vencidos
            </a>
            <a href="{{ route('admin.documents.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-list"></i> Todos
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Urgentes (>3 días)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['urgent'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.documents.pending') }}" method="GET" class="row">
                <div class="col-md-5 mb-3">
                    <label>Tipo de Documento</label>
                    <select name="document_type" class="form-control">
                        <option value="">Todos</option>
                        @foreach($documentTypes as $type)
                            <option value="{{ $type }}" {{ request('document_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 mb-3">
                    <label>Programa</label>
                    <select name="program_type" class="form-control">
                        <option value="">Todos</option>
                        <option value="IE" {{ request('program_type') == 'IE' ? 'selected' : '' }}>IE</option>
                        <option value="YFU" {{ request('program_type') == 'YFU' ? 'selected' : '' }}>YFU</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Lista de Documentos -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Documentos para Revisión ({{ $documents->total() }})
            </h6>
            <div>
                <button type="button" class="btn btn-sm btn-success" onclick="bulkApprove()">
                    <i class="fas fa-check"></i> Aprobar Seleccionados
                </button>
                <button type="button" class="btn btn-sm btn-danger" onclick="showBulkRejectModal()">
                    <i class="fas fa-times"></i> Rechazar Seleccionados
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" onclick="toggleAll(this)">
                                </th>
                                <th>Participante</th>
                                <th>Tipo</th>
                                <th>Programa</th>
                                <th>Fecha Subida</th>
                                <th>Antigüedad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                @php
                                    $daysOld = $doc->created_at->diffInDays(now());
                                    $urgentClass = $daysOld > 3 ? 'table-danger' : ($daysOld > 1 ? 'table-warning' : '');
                                @endphp
                                <tr class="{{ $urgentClass }}">
                                    <td>
                                        <input type="checkbox" class="doc-checkbox" value="{{ $doc->id }}">
                                    </td>
                                    <td>
                                        <strong>{{ $doc->application->user->name }}</strong><br>
                                        <small class="text-muted">{{ $doc->application->user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $doc->application->program->name }}<br>
                                        <small class="text-muted">{{ $doc->application->program->main_category }}</small>
                                    </td>
                                    <td>{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($daysOld > 3)
                                            <span class="badge badge-danger">{{ $daysOld }} días</span>
                                        @elseif($daysOld > 1)
                                            <span class="badge badge-warning">{{ $daysOld }} días</span>
                                        @else
                                            <span class="badge badge-success">{{ $daysOld }} días</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.documents.review', $doc->id) }}" 
                                           class="btn btn-sm btn-primary" title="Revisar">
                                            <i class="fas fa-search"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-success" 
                                                onclick="quickApprove({{ $doc->id }})" title="Aprobar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="showRejectModal({{ $doc->id }})" title="Rechazar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $documents->links() }}
                </div>
            @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    ¡Excelente! No hay documentos pendientes de revisión.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Rechazar Individual -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Rechazar Documento</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Motivo del Rechazo *</label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required
                                  placeholder="Explica por qué se rechaza este documento..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rechazar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Rechazar Masivo -->
<div class="modal fade" id="bulkRejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.documents.bulk-reject') }}" method="POST">
                @csrf
                <input type="hidden" name="document_ids" id="bulk_reject_ids">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Rechazar Documentos Seleccionados</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong id="bulk_reject_count"></strong> documento(s) serán rechazados.
                    </div>
                    <div class="form-group">
                        <label>Motivo del Rechazo *</label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required
                                  placeholder="Este motivo se aplicará a todos los documentos seleccionados..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rechazar Todos</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleAll(source) {
    $('.doc-checkbox').prop('checked', source.checked);
}

function getSelectedDocs() {
    return $('.doc-checkbox:checked').map(function() {
        return $(this).val();
    }).get();
}

function quickApprove(id) {
    if(confirm('¿Aprobar este documento?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/documents/${id}/verify`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }
}

function showRejectModal(id) {
    $('#rejectForm').attr('action', `/admin/documents/${id}/reject`);
    $('#rejectModal').modal('show');
}

function bulkApprove() {
    const selected = getSelectedDocs();
    if(selected.length === 0) {
        alert('Selecciona al menos un documento');
        return;
    }
    
    if(confirm(`¿Aprobar ${selected.length} documento(s)?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.documents.bulk-approve") }}';
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = '{{ csrf_token() }}';
        form.appendChild(csrf);
        
        selected.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'document_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        document.body.appendChild(form);
        form.submit();
    }
}

function showBulkRejectModal() {
    const selected = getSelectedDocs();
    if(selected.length === 0) {
        alert('Selecciona al menos un documento');
        return;
    }
    
    $('#bulk_reject_ids').val(JSON.stringify(selected));
    $('#bulk_reject_count').text(selected.length);
    $('#bulkRejectModal').modal('show');
}
</script>
@endsection
