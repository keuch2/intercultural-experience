@extends('layouts.admin')

@section('title', 'Gestión de Tickets de Soporte')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Tickets de Soporte</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Tickets de Soporte</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filtros
        </div>
        <div class="card-body">
            <form action="{{ route('admin.support.index') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="search" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="ID, asunto o usuario">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Todos</option>
                                <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Abierto</option>
                                <option value="answered" {{ request('status') == 'answered' ? 'selected' : '' }}>Respondido</option>
                                <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Cerrado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="priority" class="form-label">Prioridad</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="">Todas</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="date_range" class="form-label">Rango de Fechas</label>
                            <input type="text" class="form-control" id="date_range" name="date_range" value="{{ request('date_range') }}" placeholder="DD/MM/YYYY - DD/MM/YYYY">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                        <a href="{{ route('admin.support.index') }}" class="btn btn-secondary">Limpiar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-headset me-1"></i>
                Tickets de Soporte
            </div>
            <a href="{{ route('admin.support.export') }}" class="btn btn-sm btn-success">
                <i class="fas fa-file-excel me-1"></i> Exportar
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Asunto</th>
                            <th>Estado</th>
                            <th>Prioridad</th>
                            <th>Fecha de Creación</th>
                            <th>Última Actualización</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td>{{ $ticket->id }}</td>
                                <td>{{ $ticket->user->name }}</td>
                                <td>{{ Str::limit($ticket->subject, 30) }}</td>
                                <td>
                                    @if($ticket->status == 'open')
                                        <span class="badge bg-warning">Abierto</span>
                                    @elseif($ticket->status == 'answered')
                                        <span class="badge bg-info">Respondido</span>
                                    @elseif($ticket->status == 'closed')
                                        <span class="badge bg-secondary">Cerrado</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->priority == 'low')
                                        <span class="badge bg-success">Baja</span>
                                    @elseif($ticket->priority == 'medium')
                                        <span class="badge bg-warning">Media</span>
                                    @elseif($ticket->priority == 'high')
                                        <span class="badge bg-danger">Alta</span>
                                    @endif
                                </td>
                                <td>{{ $ticket->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $ticket->updated_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ route('admin.support.show', $ticket->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    
                                    @if($ticket->status != 'closed')
                                        <form action="{{ route('admin.support.close', $ticket->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-secondary" onclick="return confirm('¿Está seguro de que desea cerrar este ticket?')">
                                                <i class="fas fa-times-circle"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.support.reopen', $ticket->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('¿Está seguro de que desea reabrir este ticket?')">
                                                <i class="fas fa-redo"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay tickets de soporte disponibles.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-center mt-3">
                {{ $tickets->links() }}
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Tickets por Estado
                </div>
                <div class="card-body">
                    <canvas id="ticketsByStatusChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Tickets por Prioridad
                </div>
                <div class="card-body">
                    <canvas id="ticketsByPriorityChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar selector de fechas
        flatpickr("#date_range", {
            mode: "range",
            dateFormat: "d/m/Y",
            locale: {
                firstDayOfWeek: 1,
                weekdays: {
                    shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
                },
                months: {
                    shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                },
            }
        });
        
        // Gráfico de tickets por estado
        var statusCtx = document.getElementById('ticketsByStatusChart');
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: ['Abiertos', 'Respondidos', 'Cerrados'],
                datasets: [{
                    data: [
                        {{ $tickets->where('status', 'open')->count() }},
                        {{ $tickets->where('status', 'answered')->count() }},
                        {{ $tickets->where('status', 'closed')->count() }}
                    ],
                    backgroundColor: ['#ffc107', '#0dcaf0', '#6c757d'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Distribución de Tickets por Estado'
                    }
                }
            }
        });
        
        // Gráfico de tickets por prioridad
        var priorityCtx = document.getElementById('ticketsByPriorityChart');
        new Chart(priorityCtx, {
            type: 'pie',
            data: {
                labels: ['Baja', 'Media', 'Alta'],
                datasets: [{
                    data: [
                        {{ $tickets->where('priority', 'low')->count() }},
                        {{ $tickets->where('priority', 'medium')->count() }},
                        {{ $tickets->where('priority', 'high')->count() }}
                    ],
                    backgroundColor: ['#198754', '#ffc107', '#dc3545'],
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    title: {
                        display: true,
                        text: 'Distribución de Tickets por Prioridad'
                    }
                }
            }
        });
    });
</script>
@endsection
