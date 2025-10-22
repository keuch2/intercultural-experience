@extends('layouts.admin')

@section('title', 'Documentos Vencidos')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-exclamation-triangle"></i> Documentos Vencidos
        </h1>
        <div>
            <a href="{{ route('admin.documents.pending') }}" class="btn btn-sm btn-warning">
                <i class="fas fa-clock"></i> Ver Pendientes
            </a>
            <a href="{{ route('admin.documents.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-list"></i> Todos
            </a>
        </div>
    </div>

    <!-- Alertas Próximos a Vencer -->
    @if($expiringSoon->count() > 0)
        <div class="card shadow mb-4 border-left-warning">
            <div class="card-header py-3 bg-warning">
                <h6 class="m-0 font-weight-bold text-white">
                    <i class="fas fa-bell"></i> Documentos Próximos a Vencer (30 días)
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Participante</th>
                                <th>Tipo</th>
                                <th>Programa</th>
                                <th>Vence en</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiringSoon as $doc)
                                @php
                                    $daysUntilExpiry = now()->diffInDays($doc->expiry_date);
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $doc->application->user->name }}</strong><br>
                                        <small class="text-muted">{{ $doc->application->user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}
                                        </span>
                                    </td>
                                    <td>{{ $doc->application->program->name }}</td>
                                    <td>
                                        <span class="badge badge-{{ $daysUntilExpiry <= 7 ? 'danger' : 'warning' }}">
                                            {{ $daysUntilExpiry }} días
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.participants.show', $doc->application->user->id) }}" 
                                           class="btn btn-sm btn-primary" title="Notificar">
                                            <i class="fas fa-bell"></i>
                                        </a>
                                        <a href="{{ route('admin.documents.show', $doc->id) }}" 
                                           class="btn btn-sm btn-info" title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.documents.expired') }}" method="GET" class="row">
                <div class="col-md-10 mb-3">
                    <label>Tipo de Documento</label>
                    <select name="document_type" class="form-control">
                        <option value="">Todos</option>
                        @foreach(App\Models\ApplicationDocument::where('status', 'verified')->whereNotNull('expiry_date')->distinct('document_type')->pluck('document_type')->filter()->sort() as $type)
                            <option value="{{ $type }}" {{ request('document_type') == $type ? 'selected' : '' }}>
                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
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

    <!-- Lista de Documentos Vencidos -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Documentos Vencidos ({{ $documents->total() }})
            </h6>
        </div>
        <div class="card-body">
            @if($documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Participante</th>
                                <th>Tipo de Documento</th>
                                <th>Programa</th>
                                <th>Fecha de Vencimiento</th>
                                <th>Vencido Hace</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $doc)
                                @php
                                    $daysExpired = $doc->expiry_date->diffInDays(now());
                                    $urgency = $daysExpired > 60 ? 'danger' : ($daysExpired > 30 ? 'warning' : 'secondary');
                                @endphp
                                <tr class="table-{{ $urgency }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img class="img-profile rounded-circle mr-2" 
                                                 src="https://ui-avatars.com/api/?name={{ urlencode($doc->application->user->name) }}&background=4e73df&color=ffffff&size=40" 
                                                 width="40" height="40">
                                            <div>
                                                <strong>{{ $doc->application->user->name }}</strong><br>
                                                <small class="text-muted">{{ $doc->application->user->email }}</small>
                                            </div>
                                        </div>
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
                                    <td>{{ $doc->expiry_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $urgency }}">
                                            {{ $daysExpired }} días
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.participants.show', $doc->application->user->id) }}" 
                                           class="btn btn-sm btn-warning" title="Notificar al Participante">
                                            <i class="fas fa-bell"></i>
                                        </a>
                                        <a href="{{ route('admin.documents.show', $doc->id) }}" 
                                           class="btn btn-sm btn-info" title="Ver Documento">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="showInvalidateModal({{ $doc->id }})" 
                                                title="Invalidar Documento">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $documents->links() }}
                </div>
            @else
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    ¡Excelente! No hay documentos vencidos en este momento.
                </div>
            @endif
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row">
        <div class="col-md-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Total Vencidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $documents->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Próximos a Vencer (30 días)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $expiringSoon->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Requieren Acción
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $documents->total() + $expiringSoon->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-tasks fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para invalidar documento -->
<div class="modal fade" id="invalidateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="invalidateForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Invalidar Documento Vencido</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Esta acción marcará el documento como rechazado y notificará al participante que debe subir uno nuevo.
                    </div>
                    
                    <div class="form-group">
                        <label>Motivo (Opcional)</label>
                        <textarea name="rejection_reason" class="form-control" rows="3">Documento vencido. Por favor sube una versión actualizada.</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban"></i> Invalidar Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showInvalidateModal(id) {
    $('#invalidateForm').attr('action', `/admin/documents/${id}/reject`);
    $('#invalidateModal').modal('show');
}
</script>
@endsection
