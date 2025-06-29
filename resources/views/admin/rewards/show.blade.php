@extends('layouts.admin')

@section('title', 'Detalles de la Recompensa')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Detalles de la Recompensa</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.rewards.index') }}">Recompensas</a></li>
        <li class="breadcrumb-item active">{{ $reward->name }}</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    <div class="row">
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Información de la Recompensa
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        @if($reward->image)
                            <img src="{{ asset('storage/' . $reward->image) }}" alt="{{ $reward->name }}" class="img-fluid rounded mb-3" style="max-height: 200px;">
                        @else
                            <div class="bg-light p-5 rounded mb-3 d-flex align-items-center justify-content-center">
                                <i class="fas fa-gift fa-3x text-secondary"></i>
                            </div>
                        @endif
                    </div>
                    
                    <h5 class="card-title">{{ $reward->name }}</h5>
                    <p class="card-text">
                        <span class="badge bg-{{ $reward->is_active ? 'success' : 'danger' }}">
                            {{ $reward->is_active ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                    
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item">
                            <strong>Costo:</strong> {{ $reward->cost }} puntos
                        </li>
                        <li class="list-group-item">
                            <strong>Stock:</strong> 
                            @if($reward->stock === null)
                                Ilimitado
                            @else
                                {{ $reward->stock }} unidades
                            @endif
                        </li>
                        <li class="list-group-item">
                            <strong>Canjes realizados:</strong> {{ $redemptionStats['total'] }}
                        </li>
                        <li class="list-group-item">
                            <strong>Fecha de creación:</strong> {{ $reward->created_at->format('d/m/Y H:i') }}
                        </li>
                    </ul>
                    
                    <div class="d-flex justify-content-between mt-3">
                        <a href="{{ route('admin.rewards.edit', $reward->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-alt me-1"></i>
                    Descripción
                </div>
                <div class="card-body">
                    {!! $reward->description !!}
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Estadísticas de Canjes
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-primary text-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h2 class="mb-0">{{ $redemptionStats['total'] }}</h2>
                                            <div>Total</div>
                                        </div>
                                        <i class="fas fa-exchange-alt fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-warning text-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h2 class="mb-0">{{ $redemptionStats['pending'] }}</h2>
                                            <div>Pendientes</div>
                                        </div>
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-success text-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h2 class="mb-0">{{ $redemptionStats['approved'] }}</h2>
                                            <div>Aprobados</div>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-12">
                            <canvas id="redemptionStatusChart" width="100%" height="40"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-exchange-alt me-1"></i>
                        Canjes Recientes
                    </div>
                    <a href="{{ route('admin.redemptions.index', ['reward_id' => $reward->id]) }}" class="btn btn-sm btn-primary">
                        Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                    <th>Fecha de Canje</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($redemptions as $redemption)
                                    <tr>
                                        <td>{{ $redemption->id }}</td>
                                        <td>{{ $redemption->user->name }}</td>
                                        <td>
                                            @if($redemption->status == 'pending')
                                                <span class="badge bg-warning">Pendiente</span>
                                            @elseif($redemption->status == 'approved')
                                                <span class="badge bg-success">Aprobado</span>
                                            @elseif($redemption->status == 'rejected')
                                                <span class="badge bg-danger">Rechazado</span>
                                            @elseif($redemption->status == 'delivered')
                                                <span class="badge bg-info">Entregado</span>
                                            @endif
                                        </td>
                                        <td>{{ $redemption->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.redemptions.show', $redemption->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No hay canjes para esta recompensa.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-3">
                        {{ $redemptions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar la recompensa <strong>{{ $reward->name }}</strong>?</p>
                <p class="text-danger">Esta acción no se puede deshacer.</p>
                
                @if($redemptionStats['total'] > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> Esta recompensa tiene {{ $redemptionStats['total'] }} canjes asociados. No se puede eliminar.
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                
                @if($redemptionStats['total'] == 0)
                    <form action="{{ route('admin.rewards.destroy', $reward->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
                @else
                    <button type="button" class="btn btn-danger" disabled>Eliminar</button>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de estado de canjes
        var statusCtx = document.getElementById('redemptionStatusChart');
        var statusLabels = ['Pendientes', 'Aprobados', 'Rechazados'];
        var statusData = [
            {{ $redemptionStats['pending'] }},
            {{ $redemptionStats['approved'] }},
            {{ $redemptionStats['rejected'] }}
        ];
        var statusColors = ['#ffc107', '#198754', '#dc3545'];
        
        new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: statusColors,
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
                        text: 'Distribución de Canjes por Estado'
                    }
                }
            }
        });
    });
</script>
@endsection
