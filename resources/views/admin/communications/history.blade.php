@extends('layouts.admin')

@section('title', 'Historial de Comunicaciones')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-history"></i> Historial de Comunicaciones
        </h1>
        <div>
            <button type="button" class="btn btn-sm btn-success" onclick="exportHistory()">
                <i class="fas fa-file-excel"></i> Exportar
            </button>
            <a href="{{ route('admin.communications.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.communications.history') }}" method="GET" class="row">
                <div class="col-md-5 mb-3">
                    <label>Buscar</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Destinatario, email o asunto..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Estado</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Enviados</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Fallidos</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendientes</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Por Página</label>
                    <select name="per_page" class="form-control">
                        <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                        <option value="200" {{ request('per_page') == '200' ? 'selected' : '' }}>200</option>
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

    <!-- Stats Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Enviados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $logs->where('status', 'sent')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Fallidos
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $logs->where('status', 'failed')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
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
                                Pendientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $logs->where('status', 'pending')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Registros ({{ $logs->total() }})
            </h6>
        </div>
        <div class="card-body">
            @if($logs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th style="width: 10%;">Fecha/Hora</th>
                                <th style="width: 20%;">Destinatario</th>
                                <th style="width: 25%;">Asunto</th>
                                <th style="width: 15%;">Enviado Por</th>
                                <th style="width: 10%;">Estado</th>
                                <th style="width: 10%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($logs as $log)
                                <tr>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($log->created_at)->format('d/m/Y') }}</small><br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $log->recipient_name }}</strong><br>
                                        <small class="text-muted">{{ $log->recipient_email }}</small>
                                    </td>
                                    <td>
                                        <span title="{{ $log->subject }}">
                                            {{ Str::limit($log->subject, 50) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($log->sent_by)
                                            {{ \App\Models\User::find($log->sent_by)->name ?? 'N/A' }}
                                        @else
                                            <span class="text-muted">Sistema</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->status == 'sent')
                                            <span class="badge badge-success">
                                                <i class="fas fa-check"></i> Enviado
                                            </span>
                                        @elseif($log->status == 'failed')
                                            <span class="badge badge-danger">
                                                <i class="fas fa-times"></i> Fallido
                                            </span>
                                        @else
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> Pendiente
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" 
                                                onclick="showFullDetails({{ json_encode($log) }})"
                                                title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if($log->status == 'failed')
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    onclick="retryEmail({{ $log->id }})"
                                                    title="Reintentar">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $logs->appends(request()->query())->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No se encontraron registros con los filtros aplicados.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Full Details -->
<div class="modal fade" id="fullDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-envelope"></i> Detalles Completos
                </h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Destinatario:</strong><br>
                        <span id="full-recipient"></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Email:</strong><br>
                        <span id="full-email"></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha:</strong><br>
                        <span id="full-date"></span>
                    </div>
                    <div class="col-md-3">
                        <strong>Estado:</strong><br>
                        <span id="full-status"></span>
                    </div>
                </div>

                <hr>

                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Asunto:</strong><br>
                        <div class="alert alert-secondary" id="full-subject"></div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <strong>Mensaje Completo:</strong>
                        <div class="border p-3 bg-light" style="white-space: pre-wrap; max-height: 400px; overflow-y: auto;" id="full-message"></div>
                    </div>
                </div>

                <div id="full-error" style="display: none;">
                    <div class="alert alert-danger">
                        <strong><i class="fas fa-exclamation-triangle"></i> Error:</strong><br>
                        <span id="full-error-message"></span>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <strong>Enviado por:</strong> <span id="full-sent-by"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showFullDetails(log) {
    $('#full-recipient').text(log.recipient_name);
    $('#full-email').text(log.recipient_email);
    $('#full-date').text(new Date(log.created_at).toLocaleString('es-ES'));
    $('#full-subject').text(log.subject);
    $('#full-message').text(log.message);
    
    let statusBadge = '';
    if (log.status === 'sent') {
        statusBadge = '<span class="badge badge-success"><i class="fas fa-check"></i> ENVIADO</span>';
    } else if (log.status === 'failed') {
        statusBadge = '<span class="badge badge-danger"><i class="fas fa-times"></i> FALLIDO</span>';
    } else {
        statusBadge = '<span class="badge badge-warning"><i class="fas fa-clock"></i> PENDIENTE</span>';
    }
    $('#full-status').html(statusBadge);
    
    if (log.error_message) {
        $('#full-error').show();
        $('#full-error-message').text(log.error_message);
    } else {
        $('#full-error').hide();
    }
    
    if (log.sent_by) {
        $('#full-sent-by').text('Admin');
    } else {
        $('#full-sent-by').text('Sistema Automático');
    }
    
    $('#fullDetailsModal').modal('show');
}

function retryEmail(id) {
    if (confirm('¿Reintentar enviar este email?')) {
        alert('Funcionalidad de reintento pendiente de implementar');
        // TODO: Implementar reenvío
    }
}

function exportHistory() {
    const params = new URLSearchParams(window.location.search);
    params.append('export', 'excel');
    window.location.href = '{{ route("admin.communications.history") }}?' + params.toString();
}
</script>
@endsection

@section('styles')
<style>
.table td {
    vertical-align: middle;
}
</style>
@endsection
