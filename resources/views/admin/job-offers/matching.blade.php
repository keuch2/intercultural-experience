@extends('layouts.admin')

@section('title', 'Candidatos para ' . $offer->position)

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-user-check"></i> Candidatos Elegibles
        </h1>
        <a href="{{ route('admin.job-offers.show', $offer->id) }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Oferta
        </a>
    </div>

    <!-- Información de la Oferta -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Oferta Laboral</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4>{{ $offer->position }}</h4>
                    <p class="mb-1">
                        <strong>Empresa:</strong> {{ $offer->hostCompany->name }}<br>
                        <strong>Ubicación:</strong> {{ $offer->city }}, {{ $offer->state }}<br>
                        <strong>Sponsor:</strong> {{ $offer->sponsor->name }}
                    </p>
                </div>
                <div class="col-md-4 text-right">
                    <h5>Cupos Disponibles</h5>
                    <h2 class="text-{{ $offer->available_slots > 0 ? 'success' : 'danger' }}">
                        {{ $offer->available_slots }}/{{ $offer->total_slots }}
                    </h2>
                </div>
            </div>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <strong>Requisitos:</strong><br>
                    <span class="badge badge-info">Inglés: {{ $offer->required_english_level }}</span>
                    <span class="badge badge-secondary">Género: {{ ucfirst($offer->required_gender) }}</span>
                </div>
                <div class="col-md-6">
                    <strong>Salario:</strong> ${{ number_format($offer->salary_min, 2) }} - ${{ number_format($offer->salary_max, 2) }}/mes<br>
                    <strong>Horas:</strong> {{ $offer->hours_per_week }}h/semana
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Candidatos -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Candidatos Elegibles ({{ $candidates->count() }})
            </h6>
        </div>
        <div class="card-body">
            @if($candidates->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Participante</th>
                                <th>Match Score</th>
                                <th>Nivel Inglés</th>
                                <th>Puntaje</th>
                                <th>Género</th>
                                <th>Rechazos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($candidates as $candidate)
                                @php
                                    $lastEval = $candidate->englishEvaluations->first();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img class="img-profile rounded-circle mr-2" 
                                                 src="https://ui-avatars.com/api/?name={{ urlencode($candidate->name) }}&background=4e73df&color=ffffff&size=40" 
                                                 width="40" height="40">
                                            <div>
                                                <strong>{{ $candidate->name }}</strong><br>
                                                <small class="text-muted">{{ $candidate->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="mb-1">
                                            <strong class="text-{{ $candidate->match_score >= 100 ? 'success' : 'warning' }}">
                                                {{ $candidate->match_score }}%
                                            </strong>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $candidate->match_score >= 100 ? 'success' : 'warning' }}" 
                                                 style="width: {{ min($candidate->match_score, 100) }}%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($lastEval)
                                            <span class="badge badge-info badge-lg">
                                                {{ $lastEval->cefr_level }}
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($lastEval)
                                            <strong>{{ $lastEval->score }}/100</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($candidate->gender ?? 'N/A') }}</td>
                                    <td>
                                        @if($candidate->rejection_count > 0)
                                            <span class="badge badge-warning">
                                                {{ $candidate->rejection_count }}/3
                                            </span>
                                        @else
                                            <span class="badge badge-success">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($offer->available_slots > 0)
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="showAssignModal({{ $candidate->id }}, '{{ $candidate->name }}')">
                                                <i class="fas fa-user-plus"></i> Asignar
                                            </button>
                                        @else
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                Sin Cupos
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.participants.show', $candidate->id) }}" 
                                           class="btn btn-sm btn-info" title="Ver Perfil">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>No hay candidatos elegibles.</strong><br>
                    No se encontraron participantes que cumplan con los requisitos de esta oferta.
                    <ul class="mt-2 mb-0">
                        <li>Nivel de inglés requerido: <strong>{{ $offer->required_english_level }}</strong></li>
                        <li>Género requerido: <strong>{{ ucfirst($offer->required_gender) }}</strong></li>
                        <li>Participantes sin evaluación de inglés no son elegibles</li>
                        <li>Participantes con 3 rechazos están bloqueados</li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal para asignar participante -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignForm" method="POST" action="{{ route('admin.job-offers.assign-participant', $offer->id) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Asignar Participante</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="user_id" id="user_id">
                    
                    <div class="alert alert-info">
                        <strong>Participante:</strong> <span id="participant_name"></span>
                    </div>
                    
                    <div class="form-group">
                        <label>Notas (Opcional)</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Información adicional sobre esta asignación..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        Al asignar este participante:
                        <ul class="mb-0">
                            <li>Se creará una reserva con estado "Pendiente"</li>
                            <li>Se reducirá 1 cupo disponible</li>
                            <li>El participante será notificado</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-user-plus"></i> Asignar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function showAssignModal(userId, userName) {
    $('#user_id').val(userId);
    $('#participant_name').text(userName);
    $('#assignModal').modal('show');
}
</script>
@endsection

@section('styles')
<style>
.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 0.75rem;
}
</style>
@endsection
