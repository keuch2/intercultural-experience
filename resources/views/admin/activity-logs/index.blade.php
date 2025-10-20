@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-history me-2"></i>Registro de Auditoría
    </h1>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow">
            <div class="card-body">
                <div class="text-white-50 small">Total Registros</div>
                <div class="h5 mb-0">{{ number_format($stats['total_logs']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white shadow">
            <div class="card-body">
                <div class="text-white-50 small">Hoy</div>
                <div class="h5 mb-0">{{ number_format($stats['today_logs']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow">
            <div class="card-body">
                <div class="text-white-50 small">Esta Semana</div>
                <div class="h5 mb-0">{{ number_format($stats['week_logs']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white shadow">
            <div class="card-body">
                <div class="text-white-50 small">Usuarios Activos</div>
                <div class="h5 mb-0">{{ number_format($stats['unique_users']) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="row g-3">
            <div class="col-md-2">
                <label for="log_name" class="form-label">Tipo de Log</label>
                <select class="form-select form-select-sm" id="log_name" name="log_name">
                    <option value="">Todos</option>
                    @foreach($logNames as $logName)
                        <option value="{{ $logName }}" {{ request('log_name') == $logName ? 'selected' : '' }}>
                            {{ ucfirst($logName) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="action" class="form-label">Acción</label>
                <select class="form-select form-select-sm" id="action" name="action">
                    <option value="">Todas</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                            {{ ucfirst($action) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="causer_id" class="form-label">Usuario</label>
                <select class="form-select form-select-sm" id="causer_id" name="causer_id">
                    <option value="">Todos</option>
                    @foreach($causers as $causer)
                        <option value="{{ $causer->id }}" {{ request('causer_id') == $causer->id ? 'selected' : '' }}>
                            {{ $causer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">Desde</label>
                <input type="date" class="form-control form-control-sm" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">Hasta</label>
                <input type="date" class="form-control form-control-sm" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label d-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Logs -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>Registros de Actividad ({{ $logs->total() }})
        </h6>
    </div>
    <div class="card-body">
        @if($logs->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead>
                        <tr>
                            <th style="width:140px;">Fecha/Hora</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Descripción</th>
                            <th>IP</th>
                            <th style="width:80px;">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                            <tr>
                                <td>
                                    <small>{{ $log->created_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    @if($log->causer)
                                        <i class="fas fa-user me-1 text-muted"></i>{{ $log->causer->name }}
                                    @else
                                        <span class="text-muted"><i>Sistema</i></span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->action)
                                        @if(in_array($log->action, ['created', 'create']))
                                            <span class="badge bg-success">{{ ucfirst($log->action) }}</span>
                                        @elseif(in_array($log->action, ['updated', 'update']))
                                            <span class="badge bg-info">{{ ucfirst($log->action) }}</span>
                                        @elseif(in_array($log->action, ['deleted', 'delete']))
                                            <span class="badge bg-danger">{{ ucfirst($log->action) }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($log->action) }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <small>{{ Str::limit($log->description, 60) }}</small>
                                    @if($log->log_name)
                                        <br><span class="badge bg-light text-dark">{{ $log->log_name }}</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                                <td>
                                    <a href="{{ route('admin.activity-logs.show', $log->id) }}" 
                                       class="btn btn-sm btn-outline-primary"
                                       title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Mostrando {{ $logs->firstItem() }} a {{ $logs->lastItem() }} 
                    de {{ $logs->total() }} registros
                </div>
                <div>
                    {{ $logs->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay registros de actividad</h5>
            </div>
        @endif
    </div>
</div>
@endsection
