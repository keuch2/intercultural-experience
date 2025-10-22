@extends('layouts.admin')

@section('title', 'Historial de Reservas')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-history"></i> Historial de Reservas
        </h1>
        <a href="{{ route('admin.job-offers.show', $offer->id) }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Oferta
        </a>
    </div>

    <!-- Información de la Oferta -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Información de la Oferta</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4>{{ $offer->position }}</h4>
                    <p>
                        <strong>Empresa:</strong> {{ $offer->hostCompany->name }}<br>
                        <strong>Ubicación:</strong> {{ $offer->city }}, {{ $offer->state }}<br>
                        <strong>ID:</strong> {{ $offer->job_offer_id }}
                    </p>
                </div>
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-6 text-center">
                            <h5 class="text-muted">Total Reservas</h5>
                            <h2>{{ $reservations->total() }}</h2>
                        </div>
                        <div class="col-6 text-center">
                            <h5 class="text-muted">Cupos Disponibles</h5>
                            <h2 class="text-{{ $offer->available_slots > 0 ? 'success' : 'danger' }}">
                                {{ $offer->available_slots }}/{{ $offer->total_slots }}
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Historial de Reservas -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Todas las Reservas ({{ $reservations->total() }})
            </h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if($reservations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Participante</th>
                                <th>Estado</th>
                                <th>Fecha Reserva</th>
                                <th>Fecha Confirmación</th>
                                <th>Notas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservations as $reservation)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img class="img-profile rounded-circle mr-2" 
                                                 src="https://ui-avatars.com/api/?name={{ urlencode($reservation->user->name) }}&background=4e73df&color=ffffff&size=40" 
                                                 width="40" height="40">
                                            <div>
                                                <strong>{{ $reservation->user->name }}</strong><br>
                                                <small class="text-muted">{{ $reservation->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-lg badge-{{ 
                                            $reservation->status == 'confirmed' ? 'success' : 
                                            ($reservation->status == 'pending' ? 'warning' : 
                                            ($reservation->status == 'rejected' ? 'danger' : 'secondary')) 
                                        }}">
                                            {{ strtoupper($reservation->status) }}
                                        </span>
                                        @if($reservation->status == 'rejected' && $reservation->rejection_reason)
                                            <br>
                                            <small class="text-danger">
                                                <i class="fas fa-info-circle"></i>
                                                {{ $reservation->rejection_reason }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $reservation->reserved_at ? $reservation->reserved_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        @if($reservation->confirmed_at)
                                            <i class="fas fa-check text-success"></i>
                                            {{ $reservation->confirmed_at->format('d/m/Y H:i') }}
                                        @elseif($reservation->rejected_at)
                                            <i class="fas fa-times text-danger"></i>
                                            {{ $reservation->rejected_at->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reservation->notes)
                                            <small>{{ Str::limit($reservation->notes, 50) }}</small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reservation->status == 'pending')
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="updateReservation({{ $reservation->id }}, 'confirmed')"
                                                    title="Confirmar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="showRejectModal({{ $reservation->id }})"
                                                    title="Rechazar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" 
                                                    onclick="updateReservation({{ $reservation->id }}, 'cancelled')"
                                                    title="Cancelar">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @elseif($reservation->status == 'confirmed')
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    onclick="updateReservation({{ $reservation->id }}, 'cancelled')"
                                                    title="Cancelar">
                                                <i class="fas fa-ban"></i> Cancelar
                                            </button>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                        <a href="{{ route('admin.participants.show', $reservation->user->id) }}" 
                                           class="btn btn-sm btn-info" title="Ver Participante">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $reservations->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    No hay reservas registradas para esta oferta laboral.
                </div>
            @endif
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
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Rechazar Reserva</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="status" value="rejected">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atención:</strong> Al rechazar esta reserva:
                        <ul class="mb-0">
                            <li>El cupo se liberará automáticamente</li>
                            <li>El participante será notificado</li>
                            <li>Se sumará 1 rechazo al historial del participante</li>
                            <li>Con 3 rechazos, el participante será bloqueado</li>
                        </ul>
                    </div>
                    
                    <div class="form-group">
                        <label>Motivo del Rechazo *</label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required
                                  placeholder="Explica por qué se rechaza esta reserva..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-times"></i> Rechazar Reserva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function updateReservation(id, status) {
    const messages = {
        'confirmed': '¿Confirmar esta reserva?',
        'cancelled': '¿Cancelar esta reserva? El cupo se liberará.',
    };
    
    if(confirm(messages[status])) {
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

@section('styles')
<style>
.badge-lg {
    font-size: 0.9rem;
    padding: 0.4rem 0.6rem;
}
</style>
@endsection
