{{-- Tab: Admisión (A) --}}
@php
    $docs = $tabData['documents'] ?? collect();
    $admissionDocDefs = \App\Models\AuPairDocument::documentTypesForStage('admission');
@endphp

{{-- A1: Datos Personales --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-id-card text-primary me-2"></i> A1. Datos Personales
        </h5>
        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#editPersonalData" aria-expanded="false">
            <i class="fas fa-edit"></i> Editar
        </button>
    </div>
    <div class="card-body">
        {{-- Read-only display --}}
        <div id="personalDataDisplay">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-0">Fecha de Inscripción</label>
                    <p class="mb-0 fw-semibold">{{ $process && $process->enrollment_date ? $process->enrollment_date->format('d/m/Y') : ($application ? $application->created_at->format('d/m/Y') : '-') }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-0">Lugar (Ciudad, País)</label>
                    <p class="mb-0 fw-semibold">{{ $process->enrollment_city ?? $user->city ?? '-' }}, {{ $process->enrollment_country ?? $user->country ?? '-' }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-0">Nacionalidad</label>
                    <p class="mb-0 fw-semibold">{{ $user->nationality ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-0">Nombres y Apellidos</label>
                    <p class="mb-0 fw-semibold">{{ $user->name }}</p>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-0">Edad</label>
                    <p class="mb-0 fw-semibold">{{ $user->age ?? '-' }} años</p>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-0">Fecha de Nacimiento</label>
                    <p class="mb-0 fw-semibold">{{ $user->birth_date ? $user->birth_date->format('d/m/Y') : ($user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : '-') }}</p>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-0">CI</label>
                    <p class="mb-0 fw-semibold">{{ $user->ci_number ?? '-' }}</p>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-0">Celular</label>
                    <p class="mb-0 fw-semibold">{{ $user->phone ?? '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-0">Domicilio Particular</label>
                    <p class="mb-0 fw-semibold">{{ $user->address ?? '-' }}</p>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-0">Ciudad</label>
                    <p class="mb-0 fw-semibold">{{ $user->city ?? '-' }}</p>
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-0">Estado Civil</label>
                    <p class="mb-0 fw-semibold">{{ $user->marital_status ? ucfirst($user->marital_status) : '-' }}</p>
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-muted mb-0">Email</label>
                    <p class="mb-0 fw-semibold">{{ $user->email }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-0">Estudio Actual</label>
                    <p class="mb-0 fw-semibold">{{ $user->academic_level ?? '-' }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-0">Institución</label>
                    <p class="mb-0 fw-semibold">{{ $user->university ?? '-' }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-0">Trabajo Actual</label>
                    <p class="mb-0 fw-semibold">{{ $user->current_job ?? '-' }}</p>
                </div>
                <div class="col-md-4">
                    <label class="form-label small text-muted mb-0">Cargo / Función</label>
                    <p class="mb-0 fw-semibold">{{ $user->job_position ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Editable form (collapsed) --}}
        <div class="collapse mt-3" id="editPersonalData">
            <form method="POST" action="{{ route('admin.aupair.profiles.update-personal', $user->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small">Fecha de Inscripción</label>
                        <input type="date" name="enrollment_date" class="form-control form-control-sm" value="{{ $process && $process->enrollment_date ? $process->enrollment_date->format('Y-m-d') : '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Ciudad de Inscripción</label>
                        <input type="text" name="enrollment_city" class="form-control form-control-sm" value="{{ $process->enrollment_city ?? $user->city ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">País de Inscripción</label>
                        <input type="text" name="enrollment_country" class="form-control form-control-sm" value="{{ $process->enrollment_country ?? $user->country ?? '' }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Nombres y Apellidos <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control form-control-sm" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Nacionalidad</label>
                        <input type="text" name="nationality" class="form-control form-control-sm" value="{{ old('nationality', $user->nationality) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">CI</label>
                        <input type="text" name="ci_number" class="form-control form-control-sm" value="{{ old('ci_number', $user->ci_number) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Celular</label>
                        <input type="text" name="phone" class="form-control form-control-sm" value="{{ old('phone', $user->phone) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Domicilio Particular</label>
                        <input type="text" name="address" class="form-control form-control-sm" value="{{ old('address', $user->address) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Ciudad</label>
                        <input type="text" name="city" class="form-control form-control-sm" value="{{ old('city', $user->city) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">País</label>
                        <input type="text" name="country" class="form-control form-control-sm" value="{{ old('country', $user->country) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Estado Civil</label>
                        <select name="marital_status" class="form-select form-select-sm">
                            <option value="">-- Seleccionar --</option>
                            @foreach(['single' => 'Soltero/a', 'married' => 'Casado/a', 'divorced' => 'Divorciado/a', 'widowed' => 'Viudo/a'] as $val => $lbl)
                                <option value="{{ $val }}" {{ old('marital_status', $user->marital_status) == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Estudio Actual</label>
                        <input type="text" name="academic_level" class="form-control form-control-sm" value="{{ old('academic_level', $user->academic_level) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Institución</label>
                        <input type="text" name="university" class="form-control form-control-sm" value="{{ old('university', $user->university) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Trabajo Actual</label>
                        <input type="text" name="current_job" class="form-control form-control-sm" value="{{ old('current_job', $user->current_job) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Cargo / Función</label>
                        <input type="text" name="job_position" class="form-control form-control-sm" value="{{ old('job_position', $user->job_position) }}">
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Guardar Cambios</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#editPersonalData">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Formulario de inscripción --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-file-signature text-primary me-2"></i>
                <strong>Formulario de Inscripción</strong>
                <br>
                <small class="text-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    El formulario debe estar firmado de forma manuscrita por el participante.
                </small>
            </div>
            <div class="d-flex gap-2">
                @php $enrollmentDoc = $docs->where('document_type', 'enrollment_form')->first(); @endphp
                @if($enrollmentDoc)
                    <a href="{{ route('admin.aupair.profiles.download-doc', [$user->id, $enrollmentDoc->id]) }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-download"></i> Descargar Firmado
                    </a>
                    <span class="badge bg-{{ $enrollmentDoc->status_color }} align-self-center">{{ $enrollmentDoc->status_label }}</span>
                @endif
                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#uploadEnrollmentForm">
                    <i class="fas fa-upload"></i> Subir Firmado
                </button>
            </div>
        </div>
    </div>
</div>

{{-- A2: Documentos de Admisión --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-folder-open text-primary me-2"></i> A2. Documentos de Admisión
        </h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Documento</th>
                        <th class="text-center" style="width: 90px;">Obligatorio</th>
                        <th class="text-center" style="width: 130px;">Estado</th>
                        <th style="width: 300px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($admissionDocDefs as $docKey => $docDef)
                    @if($docKey === 'enrollment_form') @continue @endif
                    @php
                        $doc = $docs->where('document_type', $docKey)->first();
                    @endphp
                    <tr>
                        <td>
                            <i class="fas fa-file me-1 text-muted"></i>
                            {{ $docDef['label'] }}
                            @if($doc && $doc->original_filename)
                                <br><small class="text-muted"><i class="fas fa-paperclip"></i> {{ $doc->original_filename }} ({{ $doc->file_size_formatted }})</small>
                            @endif
                            @if($doc && $doc->rejection_reason)
                                <br><small class="text-danger"><i class="fas fa-comment-alt"></i> {{ $doc->rejection_reason }}</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $docDef['required'] ? 'danger' : 'secondary' }}">{{ $docDef['required'] ? 'Sí' : 'No' }}</span>
                        </td>
                        <td class="text-center">
                            @if($doc)
                                <span class="badge bg-{{ $doc->status_color }}">
                                    @if($doc->status === 'approved') <i class="fas fa-check-circle"></i>
                                    @elseif($doc->status === 'rejected') <i class="fas fa-times-circle"></i>
                                    @else <i class="fas fa-clock"></i>
                                    @endif
                                    {{ $doc->status_label }}
                                </span>
                            @else
                                <span class="badge bg-light text-dark"><i class="fas fa-minus-circle"></i> Sin subir</span>
                            @endif
                        </td>
                        <td>
                            @if($doc)
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="{{ route('admin.aupair.profiles.download-doc', [$user->id, $doc->id]) }}" class="btn btn-sm btn-outline-primary" title="Descargar">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    @if($doc->status !== 'approved')
                                        <form method="POST" action="{{ route('admin.aupair.profiles.review-doc', [$user->id, $doc->id]) }}" class="d-inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="action" value="approve">
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Aprobar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <button class="btn btn-sm btn-outline-danger" title="Rechazar" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $doc->id }}">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif
                                    <form method="POST" action="{{ route('admin.aupair.profiles.delete-doc', [$user->id, $doc->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este documento?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#uploadModal{{ $docKey }}">
                                    <i class="fas fa-upload"></i> Subir
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Gate: avance a Aplicación --}}
        @php
            $requiredTypes = collect($admissionDocDefs)->filter(fn($d) => $d['required'])->keys();
            $approvedRequired = $docs->whereIn('document_type', $requiredTypes)->where('status', 'approved');
            $canAdvance = $approvedRequired->count() >= $requiredTypes->count();
            $isAlreadyAdvanced = $process && $process->current_stage !== 'admission';
        @endphp
        @if(!$isAlreadyAdvanced)
        <div class="mt-4 p-3 rounded {{ $canAdvance ? 'bg-success bg-opacity-10 border border-success' : 'bg-warning bg-opacity-10 border border-warning' }}">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    @if($canAdvance)
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Documentos obligatorios verificados.</strong> Se puede avanzar a la etapa de Aplicación.
                    @else
                        <i class="fas fa-lock text-warning me-2"></i>
                        <strong>Documentos obligatorios pendientes.</strong>
                        Se requiere aprobar: {{ $requiredTypes->diff($approvedRequired->pluck('document_type'))->map(fn($t) => $admissionDocDefs[$t]['label'] ?? $t)->implode(', ') }}
                    @endif
                </div>
                @if($canAdvance)
                <form method="POST" action="{{ route('admin.aupair.profiles.advance-stage', $user->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                        Avanzar a Aplicación <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @else
        <div class="mt-4 p-3 rounded bg-success bg-opacity-10 border border-success">
            <i class="fas fa-check-circle text-success me-2"></i>
            <strong>Etapa de Admisión completada.</strong> El proceso se encuentra en: <span class="badge bg-primary">{{ ucfirst($process->current_stage) }}</span>
        </div>
        @endif
    </div>
</div>

{{-- Upload Modals --}}
@foreach($admissionDocDefs as $docKey => $docDef)
@if(!$docs->where('document_type', $docKey)->first())
<div class="modal fade" id="uploadModal{{ $docKey }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.profiles.upload-doc', $user->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="document_type" value="{{ $docKey }}">
                <input type="hidden" name="stage" value="admission">
                <div class="modal-header">
                    <h6 class="modal-title">Subir: {{ $docDef['label'] }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small">Archivo <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                        <small class="text-muted">Máx. 20MB. Formatos: PDF, JPG, PNG, DOC</small>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small">Notas (opcional)</label>
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Observaciones...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i> Subir Documento</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

{{-- Enrollment Form Upload Modal --}}
<div class="modal fade" id="uploadEnrollmentForm" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.profiles.upload-doc', $user->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="document_type" value="enrollment_form">
                <input type="hidden" name="stage" value="admission">
                <div class="modal-header">
                    <h6 class="modal-title">Subir Formulario de Inscripción Firmado</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 px-3 mb-3">
                        <small><i class="fas fa-exclamation-triangle me-1"></i> El formulario debe estar firmado de forma manuscrita por el participante.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Archivo escaneado/foto <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="mb-0">
                        <label class="form-label small">Notas</label>
                        <input type="text" name="notes" class="form-control form-control-sm">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i> Subir</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Reject Modals --}}
@foreach($docs->where('status', '!=', 'approved') as $doc)
<div class="modal fade" id="rejectModal{{ $doc->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.profiles.review-doc', [$user->id, $doc->id]) }}">
                @csrf @method('PUT')
                <input type="hidden" name="action" value="reject">
                <div class="modal-header">
                    <h6 class="modal-title">Rechazar Documento</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Documento: <strong>{{ $admissionDocDefs[$doc->document_type]['label'] ?? $doc->document_type }}</strong></p>
                    <div class="mb-0">
                        <label class="form-label small">Motivo del rechazo <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control form-control-sm" rows="3" required placeholder="Indique el motivo del rechazo..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times me-1"></i> Rechazar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
