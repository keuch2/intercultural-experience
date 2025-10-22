@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Participante: {{ $participant->full_name }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('admin.participants.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Volver
            </a>
            <a href="{{ route('admin.participants.show', $participant) }}" class="btn btn-sm btn-info">
                <i class="fas fa-eye me-1"></i> Ver Participante
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información del Participante</h6>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <h5>Por favor corrige los siguientes errores:</h5>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.participants.update', $participant) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Información Básica -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="full_name" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                   id="full_name" name="full_name" value="{{ old('full_name', $participant->full_name) }}" required>
                            @error('full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" 
                                   id="email" value="{{ $participant->user->email ?? 'Sin email asignado' }}" disabled>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                El email pertenece al usuario asociado y no puede editarse aquí.
                            </div>
                        </div>
                    </div>

                    <!-- Información Personal -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Teléfono</label>
                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $participant->phone) }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="birth_date" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                   id="birth_date" name="birth_date" value="{{ old('birth_date', $participant->birth_date ? $participant->birth_date->format('Y-m-d') : '') }}">
                            @error('birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Ubicación -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="city" class="form-label">Ciudad</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" value="{{ old('city', $participant->city) }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="country" class="form-label">País</label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                   id="country" name="country" value="{{ old('country', $participant->country) }}">
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="address" class="form-label">Dirección</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address', $participant->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Documentos de Identidad -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="cedula" class="form-label">Cédula</label>
                            <input type="text" class="form-control @error('cedula') is-invalid @enderror" 
                                   id="cedula" name="cedula" value="{{ old('cedula', $participant->cedula) }}">
                            @error('cedula')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="passport_number" class="form-label">Número de Pasaporte</label>
                            <input type="text" class="form-control @error('passport_number') is-invalid @enderror" 
                                   id="passport_number" name="passport_number" value="{{ old('passport_number', $participant->passport_number) }}">
                            @error('passport_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="passport_expiry" class="form-label">Vencimiento Pasaporte</label>
                            <input type="date" class="form-control @error('passport_expiry') is-invalid @enderror" 
                                   id="passport_expiry" name="passport_expiry" 
                                   value="{{ old('passport_expiry', $participant->passport_expiry ? $participant->passport_expiry->format('Y-m-d') : '') }}">
                            @error('passport_expiry')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">Estado</label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                <option value="pending" {{ old('status', $participant->status) == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="in_review" {{ old('status', $participant->status) == 'in_review' ? 'selected' : '' }}>En Revisión</option>
                                <option value="approved" {{ old('status', $participant->status) == 'approved' ? 'selected' : '' }}>Aprobado</option>
                                <option value="rejected" {{ old('status', $participant->status) == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Información del Proceso -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="current_stage" class="form-label">Etapa Actual</label>
                            <select class="form-select @error('current_stage') is-invalid @enderror" 
                                    id="current_stage" name="current_stage">
                                @php
                                    $stages = [
                                        'registration' => 'Inscripción',
                                        'documentation' => 'Documentación',
                                        'evaluation' => 'Evaluación',
                                        'in_review' => 'En Revisión',
                                        'approved' => 'Aprobado',
                                        'in_program' => 'En Programa',
                                        'completed' => 'Completado',
                                        'withdrawn' => 'Retirado'
                                    ];
                                @endphp
                                @foreach($stages as $value => $label)
                                    <option value="{{ $value }}" 
                                        {{ old('current_stage', $participant->current_stage) == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('current_stage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="progress_percentage" class="form-label">Progreso (%)</label>
                            <input type="number" class="form-control @error('progress_percentage') is-invalid @enderror" 
                                   id="progress_percentage" name="progress_percentage" 
                                   value="{{ old('progress_percentage', $participant->progress_percentage) }}"
                                   min="0" max="100">
                            @error('progress_percentage')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Asignación de Programa -->
                    <div class="row mb-3">
                        <div class="col-md-10">
                            <label for="program_id" class="form-label">Programa Asignado</label>
                            <select class="form-select @error('program_id') is-invalid @enderror" 
                                    id="program_id" name="program_id" 
                                    data-subcategory="{{ optional($participant->program)->subcategory ?? '' }}">
                                <option value="">Sin programa asignado</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" 
                                        data-subcategory="{{ $program->subcategory }}"
                                        {{ old('program_id', $participant->program_id) == $program->id ? 'selected' : '' }}>
                                        [{{ $program->main_category }}] {{ $program->name }} - {{ $program->country }}
                                    </option>
                                @endforeach
                            </select>
                            @error('program_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Selecciona el programa al que pertenece esta aplicación.
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="button" class="btn btn-success w-100" 
                                    data-bs-toggle="modal" data-bs-target="#applyNewProgramModal"
                                    title="Aplicar a otro programa en simultáneo">
                                <i class="bi bi-plus-circle me-1"></i>
                                Nuevo
                            </button>
                        </div>
                    </div>
                    
                    {{-- Alert de programas simultáneos --}}
                    @if($participant->user && $participant->user->applications()->where('status', '!=', 'completed')->count() > 1)
                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <strong>Programas simultáneos:</strong> Este participante tiene 
                            {{ $participant->user->applications()->where('status', '!=', 'completed')->count() }} 
                            aplicaciones activas. 
                            <a href="{{ route('admin.participants.program-history', $participant->id) }}" class="alert-link">
                                Ver historial completo
                            </a>
                        </div>
                    @endif

                    {{-- Formularios Específicos por Programa --}}
                    <div id="specific-program-form-container" class="mt-4">
                        @if(isset($formView) && $formView)
                            <hr>
                            <div id="specific-form-content">
                                @if($formView === 'work_travel')
                                    @include('admin.participants.forms.work_travel', ['workTravelData' => $workTravelData ?? null])
                                @elseif($formView === 'au_pair')
                                    @include('admin.participants.forms.au_pair', ['auPairData' => $auPairData ?? null])
                                @elseif($formView === 'teacher')
                                    @include('admin.participants.forms.teacher', ['teacherData' => $teacherData ?? null])
                                @endif
                            </div>
                        @endif
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('admin.participants.index') }}" class="btn btn-secondary me-md-2">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-info">Información</h6>
            </div>
            <div class="card-body">
                <div class="small text-muted">
                    <p><strong>Participante desde:</strong><br>
                    {{ $participant->created_at->format('d/m/Y H:i') }}</p>
                    
                    <p><strong>Última actualización:</strong><br>
                    {{ $participant->updated_at->format('d/m/Y H:i') }}</p>
                    
                    @if($participant->email_verified_at)
                    <p><strong>Email verificado:</strong><br>
                    <span class="badge bg-success">Sí</span></p>
                    @else
                    <p><strong>Email verificado:</strong><br>
                    <span class="badge bg-warning">No</span></p>
                    @endif
                    
                    <p class="text-info">
                        <i class="fas fa-info-circle me-1"></i>
                        Deja los campos de contraseña vacíos si no deseas cambiarla.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Aplicar a Nuevo Programa --}}
<div class="modal fade" id="applyNewProgramModal" tabindex="-1" aria-labelledby="applyNewProgramModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="applyNewProgramModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>
                    Aplicar a Nuevo Programa (Simultáneo)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    <strong>Aplicación simultánea:</strong> Se creará una nueva aplicación para este participante 
                    manteniendo su aplicación actual activa. Ambos programas podrán procesarse en paralelo.
                </div>

                <form id="newApplicationForm" action="{{ route('admin.participants.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $participant->user_id }}">
                    <input type="hidden" name="copy_from_application" value="{{ $participant->id }}">
                    
                    <div class="mb-3">
                        <label for="new_program_id" class="form-label">
                            Seleccionar Programa <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="new_program_id" name="program_id" required>
                            <option value="">-- Seleccionar programa --</option>
                            @foreach($programs as $program)
                                {{-- Excluir el programa actual --}}
                                @if($program->id != $participant->program_id)
                                    <option value="{{ $program->id }}" data-subcategory="{{ $program->subcategory }}">
                                        [{{ $program->main_category }}] {{ $program->name }} - {{ $program->country }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        <div class="form-text">
                            <i class="fas fa-info-circle me-1"></i>
                            Los programas ya asignados no aparecen en la lista.
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="copy_basic_data" name="copy_basic_data" checked>
                            <label class="form-check-label" for="copy_basic_data">
                                <strong>Copiar datos básicos</strong> (nombre, cédula, pasaporte, contacto, etc.)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="set_as_current" name="set_as_current">
                            <label class="form-check-label" for="set_as_current">
                                Marcar como programa principal actual
                            </label>
                            <small class="form-text text-muted d-block">
                                Si se activa, se desmarcará el programa anterior como "actual"
                            </small>
                        </div>
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Importante:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Se creará una nueva aplicación independiente</li>
                            <li>Cada programa tendrá sus propios datos específicos</li>
                            <li>El participante podrá estar en diferentes etapas en cada programa</li>
                            <li>Los datos básicos se copiarán para agilizar el proceso</li>
                        </ul>
                    </div>

                    <div id="new-program-info" class="d-none">
                        <hr>
                        <h6 class="text-success">Programa seleccionado:</h6>
                        <p id="selected-program-details" class="text-muted"></p>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cancelar
                </button>
                <button type="button" class="btn btn-success" id="confirmNewApplication">
                    <i class="bi bi-check-circle me-1"></i>
                    Crear Nueva Aplicación
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const programSelect = document.getElementById('program_id');
    const formContainer = document.getElementById('specific-program-form-container');
    
    // Mapeo de subcategorías a formularios
    const programFormMap = {
        'Work and Travel': 'work_travel',
        'Au Pair': 'au_pair',
        "Teacher's Program": 'teacher'
    };
    
    // Escuchar cambios en el select de programa
    programSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const subcategory = selectedOption.dataset.subcategory;
        const programId = this.value;
        
        if (!programId || !subcategory) {
            // Si no hay programa seleccionado, ocultar formulario
            formContainer.innerHTML = '';
            return;
        }
        
        const formType = programFormMap[subcategory];
        
        if (!formType) {
            console.warn('No hay formulario específico para: ' + subcategory);
            formContainer.innerHTML = '<div class="alert alert-info mt-3"><i class="fas fa-info-circle me-2"></i>Este programa no requiere datos específicos adicionales.</div>';
            return;
        }
        
        // Mostrar loading
        formContainer.innerHTML = `
            <hr>
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3 text-muted">Cargando formulario específico de ${subcategory}...</p>
            </div>
        `;
        
        // Hacer petición AJAX para cargar el formulario específico
        const url = "{{ route('admin.participants.program-form', ['participant' => $participant->id, 'formType' => 'FORM_TYPE']) }}".replace('FORM_TYPE', formType);
        
        console.log('Cargando formulario desde:', url); // Debug
        
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html',
            },
            credentials: 'same-origin'
        })
            .then(response => {
                console.log('Respuesta recibida:', response.status, response.statusText); // Debug
                
                if (!response.ok) {
                    return response.text().then(text => {
                        console.error('Error response:', text); // Debug
                        throw new Error(`Error ${response.status}: No se pudo cargar el formulario`);
                    });
                }
                return response.text();
            })
            .then(html => {
                formContainer.innerHTML = '<hr><div id="specific-form-content">' + html + '</div>';
                
                // Mostrar notificación de cambio
                showNotification('success', 'Formulario actualizado', `Se cargó el formulario específico de ${subcategory}`);
            })
            .catch(error => {
                console.error('Error completo:', error);
                formContainer.innerHTML = `
                    <hr>
                    <div class="alert alert-danger mt-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Error:</strong> No se pudo cargar el formulario específico. 
                        <br><small class="text-muted">${error.message}</small>
                        <br><small>Por favor, guarda los cambios e intenta nuevamente.</small>
                    </div>
                `;
            });
    });
    
    // Función para mostrar notificaciones
    function showNotification(type, title, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
        
        const notification = document.createElement('div');
        notification.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="fas ${iconClass} me-2"></i>
            <strong>${title}:</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-cerrar después de 5 segundos
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
    
    // ===== MODAL DE NUEVA APLICACIÓN =====
    const newProgramSelect = document.getElementById('new_program_id');
    const newProgramInfo = document.getElementById('new-program-info');
    const selectedProgramDetails = document.getElementById('selected-program-details');
    const confirmButton = document.getElementById('confirmNewApplication');
    
    // Mostrar info del programa seleccionado
    if (newProgramSelect) {
        newProgramSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (this.value) {
                const programText = selectedOption.textContent;
                const subcategory = selectedOption.dataset.subcategory;
                
                selectedProgramDetails.innerHTML = `
                    <strong>${programText}</strong><br>
                    <small>Tipo: ${subcategory}</small>
                `;
                newProgramInfo.classList.remove('d-none');
            } else {
                newProgramInfo.classList.add('d-none');
            }
        });
    }
    
    // Confirmar nueva aplicación
    if (confirmButton) {
        confirmButton.addEventListener('click', function() {
            const form = document.getElementById('newApplicationForm');
            const programId = newProgramSelect.value;
            
            if (!programId) {
                alert('Por favor selecciona un programa');
                return;
            }
            
            // Confirmar con el usuario
            if (confirm('¿Estás seguro de crear una nueva aplicación para este participante? Se creará una aplicación independiente.')) {
                // Mostrar loading en el botón
                this.disabled = true;
                this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creando...';
                
                // Enviar formulario
                form.submit();
            }
        });
    }
});
</script>
@endpush

@endsection
