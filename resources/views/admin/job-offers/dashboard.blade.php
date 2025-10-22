@extends('layouts.admin')

@section('title', 'Dashboard - Ofertas Laborales')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-briefcase"></i> Dashboard - Ofertas Laborales
        </h1>
        <div>
            <a href="{{ route('admin.job-offers.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-list"></i> Ver Todas
            </a>
            <a href="{{ route('admin.job-offers.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Nueva Oferta
            </a>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Ofertas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $totalOffers }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-briefcase fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Ofertas Activas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $activeOffers }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Tasa de Ocupación
                            </div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                        {{ $occupancyRate }}%
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: {{ $occupancyRate }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-pie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Cupos Ocupados
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $occupiedSlots }}/{{ $totalSlots }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Top Ofertas por Ocupación -->
        <div class="col-xl-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top 10 Ofertas Más Ocupadas</h6>
                </div>
                <div class="card-body">
                    @if($topOffers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Posición</th>
                                        <th>Sponsor</th>
                                        <th>Empresa</th>
                                        <th>Ubicación</th>
                                        <th>Ocupación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($topOffers as $offer)
                                        @php
                                            $occupied = $offer->total_slots - $offer->available_slots;
                                            $occupancy = $offer->total_slots > 0 ? round(($occupied / $offer->total_slots) * 100, 1) : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $offer->position }}</strong><br>
                                                <small class="text-muted">{{ $offer->job_offer_id }}</small>
                                            </td>
                                            <td>{{ $offer->sponsor->name }}</td>
                                            <td>{{ $offer->hostCompany->name }}</td>
                                            <td>{{ $offer->city }}, {{ $offer->state }}</td>
                                            <td>
                                                <div class="mb-1">
                                                    <strong>{{ $occupied }}/{{ $offer->total_slots }}</strong>
                                                    <span class="badge badge-{{ $occupancy >= 80 ? 'danger' : ($occupancy >= 50 ? 'warning' : 'success') }} ml-2">
                                                        {{ $occupancy }}%
                                                    </span>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-{{ $occupancy >= 80 ? 'danger' : ($occupancy >= 50 ? 'warning' : 'success') }}" 
                                                         style="width: {{ $occupancy }}%"></div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.job-offers.show', $offer->id) }}" 
                                                   class="btn btn-sm btn-info" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.job-offers.matching', $offer->id) }}" 
                                                   class="btn btn-sm btn-success" title="Ver Candidatos">
                                                    <i class="fas fa-user-check"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No hay ofertas activas en este momento.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Ofertas por Estado -->
        <div class="col-xl-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Ofertas por Estado</h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="statesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reservas Recientes -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Reservas Recientes</h6>
                </div>
                <div class="card-body">
                    @if($recentReservations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Participante</th>
                                        <th>Oferta</th>
                                        <th>Empresa</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentReservations as $reservation)
                                        <tr>
                                            <td>
                                                <strong>{{ $reservation->user->name }}</strong><br>
                                                <small class="text-muted">{{ $reservation->user->email }}</small>
                                            </td>
                                            <td>
                                                {{ $reservation->jobOffer->position }}<br>
                                                <small class="text-muted">{{ $reservation->jobOffer->city }}, {{ $reservation->jobOffer->state }}</small>
                                            </td>
                                            <td>{{ $reservation->jobOffer->hostCompany->name }}</td>
                                            <td>
                                                <span class="badge badge-{{ 
                                                    $reservation->status == 'confirmed' ? 'success' : 
                                                    ($reservation->status == 'pending' ? 'warning' : 
                                                    ($reservation->status == 'rejected' ? 'danger' : 'secondary')) 
                                                }}">
                                                    {{ strtoupper($reservation->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($reservation->status == 'pending')
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="updateReservation({{ $reservation->id }}, 'confirmed')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="showRejectModal({{ $reservation->id }})">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No hay reservas recientes.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para rechazar reserva -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="rejectForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Rechazar Reserva</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" value="rejected">
                    <div class="form-group">
                        <label>Motivo del Rechazo *</label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
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
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
$(document).ready(function() {
    // Gráfico de ofertas por estado
    const statesData = @json($offersByState);
    const statesCtx = document.getElementById('statesChart').getContext('2d');
    new Chart(statesCtx, {
        type: 'doughnut',
        data: {
            labels: statesData.map(item => item.state),
            datasets: [{
                data: statesData.map(item => item.count),
                backgroundColor: [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
                    '#858796', '#5a5c69', '#2e59d9', '#17a673', '#2c9faf'
                ]
            }]
        },
        options: {
            responsive: true,
            animation: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});

function updateReservation(id, status) {
    if(confirm('¿Confirmar esta reserva?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/job-offers/reservations/${id}/update-status`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        
        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = status;
        
        form.appendChild(csrfToken);
        form.appendChild(methodInput);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function showRejectModal(id) {
    $('#rejectForm').attr('action', `/admin/job-offers/reservations/${id}/update-status`);
    $('#rejectModal').modal('show');
}
</script>
@endsection
