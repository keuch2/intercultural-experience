@extends('layouts.admin')

@section('title', 'Nueva Asignación de Programa')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-plus fa-sm text-gray-400 mr-2"></i>
        Nueva Asignación de Programa
    </h1>
    <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary shadow-sm">
        <i class="fas fa-arrow-left fa-sm"></i> Volver a Lista
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-plus mr-2"></i>Información de Asignación
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.assignments.store') }}">
                    @csrf

                    <!-- Usuario -->
                    <div class="form-group">
                        <label for="user_id" class="form-label">
                            <i class="fas fa-user mr-1"></i>Usuario *
                        </label>
                        <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                            <option value="">Selecciona un usuario</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" 
                                        {{ (old('user_id') ?? ($selectedUser?->id ?? '')) == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Selecciona el usuario al que se le asignará el programa.
                        </small>
                    </div>

                    <!-- Programa -->
                    <div class="form-group">
                        <label for="program_id" class="form-label">
                            <i class="fas fa-graduation-cap mr-1"></i>Programa *
                        </label>
                        <select name="program_id" id="program_id" class="form-control @error('program_id') is-invalid @enderror" required>
                            <option value="">Selecciona un programa</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" 
                                        data-capacity="{{ $program->capacity }}"
                                        data-available="{{ $program->getAvailableSpots() }}"
                                        {{ (old('program_id') ?? ($selectedProgram?->id ?? '')) == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }} - {{ $program->country }}
                                    ({{ $program->getAvailableSpots() }} espacios disponibles)
                                </option>
                            @endforeach
                        </select>
                        @error('program_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Selecciona el programa que se asignará al usuario.
                        </small>
                        <div id="program-info" class="mt-2" style="display: none;">
                            <div class="alert alert-info">
                                <strong>Información del Programa:</strong>
                                <div id="program-details"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Notas de Asignación -->
                    <div class="form-group">
                        <label for="assignment_notes" class="form-label">
                            <i class="fas fa-sticky-note mr-1"></i>Notas de Asignación
                        </label>
                        <textarea name="assignment_notes" id="assignment_notes" 
                                  class="form-control @error('assignment_notes') is-invalid @enderror" 
                                  rows="3" placeholder="Opcional: Información adicional sobre esta asignación">{{ old('assignment_notes') }}</textarea>
                        @error('assignment_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Notas que verá el usuario sobre su asignación.
                        </small>
                    </div>

                    <!-- Fecha Límite -->
                    <div class="form-group">
                        <label for="application_deadline" class="form-label">
                            <i class="fas fa-calendar-alt mr-1"></i>Fecha Límite para Aplicar
                        </label>
                        <input type="date" name="application_deadline" id="application_deadline" 
                               class="form-control @error('application_deadline') is-invalid @enderror"
                               value="{{ old('application_deadline') }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                        @error('application_deadline')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Opcional: Fecha límite para que el usuario complete su aplicación.
                        </small>
                    </div>

                    <!-- Opciones -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-cog mr-1"></i>Opciones
                        </label>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="can_apply" value="1" id="can_apply" 
                                   class="custom-control-input" {{ old('can_apply', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="can_apply">
                                <strong>Puede aplicar</strong>
                                <small class="d-block text-muted">El usuario puede enviar su aplicación al programa</small>
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="is_priority" value="1" id="is_priority" 
                                   class="custom-control-input" {{ old('is_priority') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_priority">
                                <strong>Asignación prioritaria</strong>
                                <small class="d-block text-muted">Marcar como asignación de alta prioridad</small>
                            </label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" name="send_notification" value="1" id="send_notification" 
                                   class="custom-control-input" {{ old('send_notification', true) ? 'checked' : '' }}>
                            <label class="custom-control-label" for="send_notification">
                                <strong>Enviar notificación</strong>
                                <small class="d-block text-muted">Notificar al usuario sobre su nueva asignación</small>
                            </label>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-2"></i>Crear Asignación
                        </button>
                        <a href="{{ route('admin.assignments.index') }}" class="btn btn-secondary ml-2">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Información de Ayuda -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">
                    <i class="fas fa-info-circle mr-2"></i>Información
                </h6>
            </div>
            <div class="card-body">
                <h6 class="text-primary">¿Qué es una asignación?</h6>
                <p class="small text-gray-600">
                    Una asignación permite que un usuario específico pueda aplicar a un programa. 
                    Solo los usuarios con asignaciones pueden enviar aplicaciones desde la app móvil.
                </p>

                <h6 class="text-primary">Estados de Asignación</h6>
                <ul class="small text-gray-600">
                    <li><span class="badge badge-warning">Asignado</span> - Usuario puede aplicar</li>
                    <li><span class="badge badge-info">Aplicado</span> - Aplicación enviada</li>
                    <li><span class="badge badge-primary">En Revisión</span> - Siendo evaluada</li>
                    <li><span class="badge badge-success">Aceptado</span> - Aplicación aprobada</li>
                    <li><span class="badge badge-danger">Rechazado</span> - Aplicación denegada</li>
                </ul>

                <h6 class="text-primary">Notificaciones</h6>
                <p class="small text-gray-600">
                    Si activas las notificaciones, el usuario recibirá una alerta en la app móvil 
                    informándole sobre su nueva asignación.
                </p>
            </div>
        </div>

        <!-- Vista Previa del Usuario -->
        <div class="card shadow mb-4" id="user-preview" style="display: none;">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-success">
                    <i class="fas fa-user mr-2"></i>Usuario Seleccionado
                </h6>
            </div>
            <div class="card-body">
                <div id="user-details">
                    <!-- Detalles del usuario se cargarán aquí -->
                </div>
            </div>
        </div>

        <!-- Resumen de Asignaciones -->
        <div class="card shadow" id="assignment-summary" style="display: none;">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Asignaciones Existentes
                </h6>
            </div>
            <div class="card-body">
                <div id="existing-assignments">
                    <!-- Asignaciones existentes se cargarán aquí -->
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.getElementById('user_id');
    const programSelect = document.getElementById('program_id');
    const userPreview = document.getElementById('user-preview');
    const assignmentSummary = document.getElementById('assignment-summary');
    const programInfo = document.getElementById('program-info');

    // Manejar cambio de usuario
    userSelect.addEventListener('change', function() {
        const userId = this.value;
        
        if (userId) {
            loadUserDetails(userId);
            loadUserAssignments(userId);
            userPreview.style.display = 'block';
        } else {
            userPreview.style.display = 'none';
            assignmentSummary.style.display = 'none';
        }
    });

    // Manejar cambio de programa
    programSelect.addEventListener('change', function() {
        const programOption = this.options[this.selectedIndex];
        
        if (this.value) {
            const capacity = programOption.dataset.capacity;
            const available = programOption.dataset.available;
            
            document.getElementById('program-details').innerHTML = `
                <i class="fas fa-users mr-1"></i> <strong>Capacidad:</strong> ${capacity} participantes<br>
                <i class="fas fa-user-check mr-1"></i> <strong>Espacios disponibles:</strong> ${available}
            `;
            
            programInfo.style.display = 'block';
            
            if (available <= 0) {
                programInfo.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Atención:</strong> Este programa ha alcanzado su capacidad máxima.
                    </div>
                `;
            }
        } else {
            programInfo.style.display = 'none';
        }
    });

    function loadUserDetails(userId) {
        fetch(`/api/admin/users/${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const user = data.user;
                    document.getElementById('user-details').innerHTML = `
                        <div class="text-center mb-3">
                            <div class="h6 mb-1">${user.name}</div>
                            <div class="small text-muted">${user.email}</div>
                        </div>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="small text-muted">Teléfono</div>
                                <div class="small">${user.phone || 'No especificado'}</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Nacionalidad</div>
                                <div class="small">${user.nationality || 'No especificada'}</div>
                            </div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error al cargar usuario:', error);
                document.getElementById('user-details').innerHTML = 
                    '<div class="text-danger small">Error al cargar información del usuario</div>';
            });
    }

    function loadUserAssignments(userId) {
        fetch(`/api/assignments?user_id=${userId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.assignments.length > 0) {
                    let html = '<div class="small">';
                    
                    data.assignments.forEach(assignment => {
                        const statusColors = {
                            'assigned': 'warning',
                            'applied': 'info',
                            'under_review': 'primary',
                            'accepted': 'success',
                            'rejected': 'danger',
                            'completed': 'dark',
                            'cancelled': 'secondary'
                        };
                        
                        const color = statusColors[assignment.status] || 'secondary';
                        
                        html += `
                            <div class="mb-2">
                                <strong>${assignment.program.name}</strong>
                                <span class="badge badge-${color} badge-sm ml-1">
                                    ${assignment.status_name}
                                </span>
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    
                    document.getElementById('existing-assignments').innerHTML = html;
                    assignmentSummary.style.display = 'block';
                } else {
                    assignmentSummary.style.display = 'none';
                }
            })
            .catch(error => {
                console.error('Error al cargar asignaciones:', error);
                assignmentSummary.style.display = 'none';
            });
    }

    // Cargar datos si hay valores preseleccionados
    if (userSelect.value) {
        userSelect.dispatchEvent(new Event('change'));
    }
    
    if (programSelect.value) {
        programSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@endsection 