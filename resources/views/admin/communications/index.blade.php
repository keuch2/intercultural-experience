@extends('layouts.admin')

@section('title', 'Comunicaciones')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-envelope"></i> Comunicaciones
        </h1>
        <div>
            <a href="{{ route('admin.communications.history') }}" class="btn btn-sm btn-info">
                <i class="fas fa-history"></i> Ver Historial
            </a>
            <a href="{{ route('admin.communications.templates') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-file-alt"></i> Templates
            </a>
            <a href="{{ route('admin.communications.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-paper-plane"></i> Nueva Comunicación
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
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Enviados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Hoy
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['today'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Esta Semana
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['this_week'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Este Mes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['this_month'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
            <form action="{{ route('admin.communications.index') }}" method="GET" class="row">
                <div class="col-md-4 mb-3">
                    <label>Buscar</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Asunto o destinatario..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Desde</label>
                    <input type="date" name="date_from" class="form-control" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label>Hasta</label>
                    <input type="date" name="date_to" class="form-control" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Recent Communications -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Comunicaciones Recientes</h6>
        </div>
        <div class="card-body">
            @if($communications->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Asunto</th>
                                <th>Enviado Por</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($communications as $comm)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($comm->created_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <strong>{{ $comm->subject }}</strong><br>
                                        <small class="text-muted">
                                            Para: {{ $comm->recipient_name }} ({{ $comm->recipient_email }})
                                        </small>
                                    </td>
                                    <td>
                                        @if($comm->sent_by)
                                            {{ \App\Models\User::find($comm->sent_by)->name ?? 'N/A' }}
                                        @else
                                            <span class="text-muted">Sistema</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $comm->status == 'sent' ? 'success' : ($comm->status == 'failed' ? 'danger' : 'warning') }}">
                                            {{ strtoupper($comm->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" 
                                                onclick="showDetails({{ json_encode($comm) }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $communications->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No hay comunicaciones registradas.
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4 border-left-primary">
                <div class="card-body">
                    <h5><i class="fas fa-paper-plane text-primary"></i> Envío Masivo</h5>
                    <p class="text-muted">Envía emails a múltiples participantes con segmentación avanzada</p>
                    <a href="{{ route('admin.communications.create') }}" class="btn btn-primary btn-sm">
                        Comenzar <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4 border-left-info">
                <div class="card-body">
                    <h5><i class="fas fa-file-alt text-info"></i> Templates</h5>
                    <p class="text-muted">Usa o crea templates predefinidos para comunicaciones frecuentes</p>
                    <a href="{{ route('admin.communications.templates') }}" class="btn btn-info btn-sm">
                        Ver Templates <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4 border-left-success">
                <div class="card-body">
                    <h5><i class="fas fa-history text-success"></i> Historial</h5>
                    <p class="text-muted">Revisa el historial completo de todas las comunicaciones enviadas</p>
                    <a href="{{ route('admin.communications.history') }}" class="btn btn-success btn-sm">
                        Ver Historial <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Details -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalles de la Comunicación</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Destinatario:</strong><br>
                        <span id="detail-recipient"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Email:</strong><br>
                        <span id="detail-email"></span>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Fecha:</strong><br>
                        <span id="detail-date"></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Estado:</strong><br>
                        <span id="detail-status"></span>
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <strong>Asunto:</strong><br>
                    <span id="detail-subject"></span>
                </div>
                <div class="mb-3">
                    <strong>Mensaje:</strong>
                    <div class="border p-3" style="white-space: pre-wrap;" id="detail-message"></div>
                </div>
                <div id="detail-error" style="display: none;">
                    <strong class="text-danger">Error:</strong>
                    <div class="alert alert-danger" id="detail-error-message"></div>
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
function showDetails(comm) {
    $('#detail-recipient').text(comm.recipient_name);
    $('#detail-email').text(comm.recipient_email);
    $('#detail-date').text(new Date(comm.created_at).toLocaleString('es-ES'));
    $('#detail-subject').text(comm.subject);
    $('#detail-message').text(comm.message);
    
    const statusBadge = comm.status === 'sent' ? 
        '<span class="badge badge-success">ENVIADO</span>' : 
        (comm.status === 'failed' ? '<span class="badge badge-danger">FALLIDO</span>' : 
        '<span class="badge badge-warning">PENDIENTE</span>');
    $('#detail-status').html(statusBadge);
    
    if (comm.error_message) {
        $('#detail-error').show();
        $('#detail-error-message').text(comm.error_message);
    } else {
        $('#detail-error').hide();
    }
    
    $('#detailsModal').modal('show');
}
</script>
@endsection
