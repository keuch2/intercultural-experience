@extends('layouts.agent')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-plus-circle me-2"></i>Asignar Programa
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('agent.participants.show', $participant->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user-circle me-2"></i>Asignar a: {{ $participant->name }}
                </h6>
            </div>
            <div class="card-body">
                @if($programs->count() > 0)
                    <form method="POST" action="{{ route('agent.participants.assign-program.store', $participant->id) }}">
                        @csrf

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Selecciona el programa al que deseas asignar a este participante. 
                            Solo se muestran programas con cupos disponibles.
                        </div>

                        <div class="mb-4">
                            <label for="program_id" class="form-label">
                                Programa <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('program_id') is-invalid @enderror" 
                                    id="program_id" name="program_id" required>
                                <option value="">Seleccionar programa...</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                        {{ $program->name }} 
                                        ({{ $program->available_slots }} cupos | 
                                        {{ $program->location }})
                                    </option>
                                @endforeach
                            </select>
                            @error('program_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Los programas mostrados tienen cupos disponibles
                            </small>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">Notas (Opcional)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Agrega cualquier nota o comentario sobre esta asignación...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                Máximo 1000 caracteres
                            </small>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('agent.participants.show', $participant->id) }}" 
                               class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Asignar Programa
                            </button>
                        </div>
                    </form>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5 class="text-muted">No hay programas disponibles</h5>
                        <p class="text-muted">
                            No hay programas con cupos disponibles en este momento, 
                            o el participante ya está asignado a todos los programas disponibles.
                        </p>
                        <a href="{{ route('agent.participants.show', $participant->id) }}" 
                           class="btn btn-primary mt-3">
                            <i class="fas fa-arrow-left me-2"></i>Volver al Perfil
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Info del Participante -->
        <div class="card shadow mb-3">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user me-2"></i>Información del Participante
                </h6>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <td class="text-muted">Nombre:</td>
                        <td><strong>{{ $participant->name }}</strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email:</td>
                        <td>{{ $participant->email }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">País:</td>
                        <td>{{ $participant->country ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nivel Académico:</td>
                        <td>{{ $participant->academic_level ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Programas Actuales -->
        <div class="card shadow">
            <div class="card-header py-3 bg-success text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-graduation-cap me-2"></i>Programas Actuales
                </h6>
            </div>
            <div class="card-body">
                @if($participant->programAssignments->count() > 0)
                    <ul class="list-group list-group-flush">
                        @foreach($participant->programAssignments as $assignment)
                            <li class="list-group-item px-0">
                                <small>
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    <strong>{{ $assignment->program->name }}</strong><br>
                                    <span class="text-muted">{{ $assignment->program->location }}</span>
                                </small>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted small mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Este será el primer programa asignado
                    </p>
                @endif
            </div>
        </div>

        <!-- Información Importante -->
        <div class="card shadow mt-3">
            <div class="card-header py-3 bg-warning text-dark">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i>Importante
                </h6>
            </div>
            <div class="card-body">
                <ul class="small mb-0">
                    <li>Al asignar, se consumirá un cupo del programa</li>
                    <li>El participante recibirá una notificación por email</li>
                    <li>El estado inicial será "Activo"</li>
                    <li>Puedes asignar al participante a múltiples programas</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
