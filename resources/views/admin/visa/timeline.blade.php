@extends('layouts.admin')

@section('title', 'Timeline de Visa - ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-timeline"></i> Timeline de Visa: {{ $user->name }}
        </h1>
        <div>
            <a href="{{ route('admin.participants.show', $user->id) }}" class="btn btn-sm btn-info">
                <i class="fas fa-user"></i> Ver Participante
            </a>
            <a href="{{ route('admin.visa.dashboard') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Proceso de Visa</h6>
                    <span class="badge badge-info badge-lg">{{ $visaProcess->progress_percentage }}% Completado</span>
                </div>
                <div class="card-body">
                    <div class="progress mb-4" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar" 
                             style="width: {{ $visaProcess->progress_percentage }}%" 
                             aria-valuenow="{{ $visaProcess->progress_percentage }}" 
                             aria-valuemin="0" aria-valuemax="100">
                            {{ $visaProcess->progress_percentage }}%
                        </div>
                    </div>

                    <!-- Timeline Steps -->
                    <div class="visa-timeline">
                        <!-- Step 1: Documentación -->
                        <div class="timeline-item {{ $visaProcess->documentation_complete ? 'completed' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-{{ $visaProcess->documentation_complete ? 'check-circle text-success' : 'circle text-muted' }} fa-2x"></i>
                            </div>
                            <div class="timeline-content">
                                <h5><strong>1. Documentación Completa</strong></h5>
                                @if($visaProcess->documentation_complete)
                                    <p class="text-success mb-2">
                                        <i class="fas fa-check"></i> Completado el {{ $visaProcess->documentation_complete_date->format('d/m/Y') }}
                                    </p>
                                @else
                                    <p class="text-muted">Pendiente de completar</p>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updateStepModal" 
                                            data-step="documentation" data-title="Documentación Completa">
                                        <i class="fas fa-check"></i> Marcar como Completo
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Step 2: Sponsor Interview -->
                        <div class="timeline-item {{ $visaProcess->sponsor_interview_status == 'approved' ? 'completed' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-{{ $visaProcess->sponsor_interview_status == 'approved' ? 'check-circle text-success' : 'circle text-muted' }} fa-2x"></i>
                            </div>
                            <div class="timeline-content">
                                <h5><strong>2. Entrevista con Sponsor</strong></h5>
                                <p>Estado: 
                                    <span class="badge badge-{{ $visaProcess->sponsor_interview_status == 'approved' ? 'success' : ($visaProcess->sponsor_interview_status == 'scheduled' ? 'info' : 'warning') }}">
                                        {{ ucfirst($visaProcess->sponsor_interview_status) }}
                                    </span>
                                </p>
                                @if($visaProcess->sponsor_interview_date)
                                    <p class="mb-2">
                                        <i class="fas fa-calendar"></i> {{ $visaProcess->sponsor_interview_date->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                                @if($visaProcess->sponsor_interview_notes)
                                    <p class="text-muted small">{{ $visaProcess->sponsor_interview_notes }}</p>
                                @endif
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updateInterviewModal" 
                                        data-step="sponsor_interview" data-title="Entrevista con Sponsor">
                                    <i class="fas fa-edit"></i> Actualizar
                                </button>
                            </div>
                        </div>

                        <!-- Step 3: Job Interview -->
                        <div class="timeline-item {{ $visaProcess->job_interview_status == 'approved' ? 'completed' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-{{ $visaProcess->job_interview_status == 'approved' ? 'check-circle text-success' : 'circle text-muted' }} fa-2x"></i>
                            </div>
                            <div class="timeline-content">
                                <h5><strong>3. Entrevista de Trabajo</strong></h5>
                                <p>Estado: 
                                    <span class="badge badge-{{ $visaProcess->job_interview_status == 'approved' ? 'success' : ($visaProcess->job_interview_status == 'scheduled' ? 'info' : 'warning') }}">
                                        {{ ucfirst($visaProcess->job_interview_status) }}
                                    </span>
                                </p>
                                @if($visaProcess->job_interview_date)
                                    <p class="mb-2">
                                        <i class="fas fa-calendar"></i> {{ $visaProcess->job_interview_date->format('d/m/Y H:i') }}
                                    </p>
                                @endif
                                @if($visaProcess->job_interview_notes)
                                    <p class="text-muted small">{{ $visaProcess->job_interview_notes }}</p>
                                @endif
                                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updateInterviewModal" 
                                        data-step="job_interview" data-title="Entrevista de Trabajo">
                                    <i class="fas fa-edit"></i> Actualizar
                                </button>
                            </div>
                        </div>

                        <!-- Step 4: DS-160 -->
                        <div class="timeline-item {{ $visaProcess->ds160_completed ? 'completed' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-{{ $visaProcess->ds160_completed ? 'check-circle text-success' : 'circle text-muted' }} fa-2x"></i>
                            </div>
                            <div class="timeline-content">
                                <h5><strong>4. DS-160 Completado</strong></h5>
                                @if($visaProcess->ds160_completed)
                                    <p class="text-success mb-2">
                                        <i class="fas fa-check"></i> Completado el {{ $visaProcess->ds160_completed_date->format('d/m/Y') }}
                                    </p>
                                    @if($visaProcess->ds160_confirmation_number)
                                        <p><strong>Confirmación:</strong> {{ $visaProcess->ds160_confirmation_number }}</p>
                                    @endif
                                    @if($visaProcess->ds160_file_path)
                                        <a href="{{ route('admin.visa.download', [$user->id, 'ds160']) }}" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-download"></i> Descargar
                                        </a>
                                    @endif
                                @else
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updateDocumentModal" 
                                            data-step="ds160" data-title="DS-160">
                                        <i class="fas fa-upload"></i> Registrar DS-160
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Step 5: DS-2019 -->
                        <div class="timeline-item {{ $visaProcess->ds2019_received ? 'completed' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-{{ $visaProcess->ds2019_received ? 'check-circle text-success' : 'circle text-muted' }} fa-2x"></i>
                            </div>
                            <div class="timeline-content">
                                <h5><strong>5. DS-2019 Recibido</strong></h5>
                                @if($visaProcess->ds2019_received)
                                    <p class="text-success mb-2">
                                        <i class="fas fa-check"></i> Recibido el {{ $visaProcess->ds2019_received_date->format('d/m/Y') }}
                                    </p>
                                    @if($visaProcess->ds2019_file_path)
                                        <a href="{{ route('admin.visa.download', [$user->id, 'ds2019']) }}" class="btn btn-sm btn-info" target="_blank">
                                            <i class="fas fa-download"></i> Descargar
                                        </a>
                                    @endif
                                @else
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updateDocumentModal" 
                                            data-step="ds2019" data-title="DS-2019">
                                        <i class="fas fa-upload"></i> Registrar DS-2019
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Step 6: SEVIS Pagado -->
                        <div class="timeline-item {{ $visaProcess->sevis_paid ? 'completed' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-{{ $visaProcess->sevis_paid ? 'check-circle text-success' : 'circle text-muted' }} fa-2x"></i>
                            </div>
                            <div class="timeline-content">
                                <h5><strong>6. SEVIS Pagado</strong></h5>
                                @if($visaProcess->sevis_paid)
                                    <p class="text-success mb-2">
                                        <i class="fas fa-check"></i> Pagado el {{ $visaProcess->sevis_paid_date->format('d/m/Y') }}
                                    </p>
                                    <p><strong>Monto:</strong> USD {{ number_format($visaProcess->sevis_amount, 2) }}</p>
                                @else
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updatePaymentModal" 
                                            data-step="sevis" data-title="SEVIS">
                                        <i class="fas fa-dollar-sign"></i> Registrar Pago
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Step 7: Tasa Consular Pagada -->
                        <div class="timeline-item {{ $visaProcess->consular_fee_paid ? 'completed' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-{{ $visaProcess->consular_fee_paid ? 'check-circle text-success' : 'circle text-muted' }} fa-2x"></i>
                            </div>
                            <div class="timeline-content">
                                <h5><strong>7. Tasa Consular Pagada</strong></h5>
                                @if($visaProcess->consular_fee_paid)
                                    <p class="text-success mb-2">
                                        <i class="fas fa-check"></i> Pagado el {{ $visaProcess->consular_fee_paid_date->format('d/m/Y') }}
                                    </p>
                                    <p><strong>Monto:</strong> USD {{ number_format($visaProcess->consular_fee_amount, 2) }}</p>
                                @else
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updatePaymentModal" 
                                            data-step="consular_fee" data-title="Tasa Consular">
                                        <i class="fas fa-dollar-sign"></i> Registrar Pago
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Step 8: Cita Consular -->
                        <div class="timeline-item {{ $visaProcess->consular_appointment_scheduled ? 'completed' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-{{ $visaProcess->consular_appointment_scheduled ? 'check-circle text-success' : 'circle text-muted' }} fa-2x"></i>
                            </div>
                            <div class="timeline-content">
                                <h5><strong>8. Cita Consular Programada</strong></h5>
                                @if($visaProcess->consular_appointment_scheduled)
                                    <p class="text-success mb-2">
                                        <i class="fas fa-check"></i> Programada para el {{ $visaProcess->consular_appointment_date->format('d/m/Y H:i') }}
                                    </p>
                                    <p><strong>Ubicación:</strong> {{ $visaProcess->consular_appointment_location }}</p>
                                @else
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updateAppointmentModal">
                                        <i class="fas fa-calendar-plus"></i> Programar Cita
                                    </button>
                                @endif
                            </div>
                        </div>

                        <!-- Step 9: Resultado -->
                        <div class="timeline-item {{ $visaProcess->visa_result && $visaProcess->visa_result != 'pending' ? 'completed' : '' }}">
                            <div class="timeline-marker">
                                <i class="fas fa-{{ $visaProcess->visa_result == 'approved' ? 'check-circle text-success' : ($visaProcess->visa_result == 'rejected' ? 'times-circle text-danger' : 'circle text-muted') }} fa-2x"></i>
                            </div>
                            <div class="timeline-content">
                                <h5><strong>9. Resultado de Visa</strong></h5>
                                @if($visaProcess->visa_result && $visaProcess->visa_result != 'pending')
                                    <p class="mb-2">
                                        <span class="badge badge-{{ $visaProcess->visa_result == 'approved' ? 'success' : ($visaProcess->visa_result == 'rejected' ? 'danger' : 'warning') }} badge-lg">
                                            {{ strtoupper($visaProcess->visa_result) }}
                                        </span>
                                    </p>
                                    <p>Fecha: {{ $visaProcess->visa_result_date->format('d/m/Y') }}</p>
                                    @if($visaProcess->visa_result_notes)
                                        <p class="text-muted">{{ $visaProcess->visa_result_notes }}</p>
                                    @endif
                                @else
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#updateResultModal">
                                        <i class="fas fa-flag-checkered"></i> Registrar Resultado
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Documentos</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-3">
                            <i class="fas fa-passport text-primary"></i>
                            <strong>Pasaporte:</strong>
                            @if($visaProcess->passport_file_path)
                                <a href="{{ route('admin.visa.download', [$user->id, 'passport']) }}" class="btn btn-sm btn-info" target="_blank">
                                    <i class="fas fa-download"></i> Ver
                                </a>
                            @else
                                <span class="text-muted">Pendiente</span>
                            @endif
                        </li>
                        <li class="mb-3">
                            <i class="fas fa-image text-primary"></i>
                            <strong>Foto Visa:</strong>
                            @if($visaProcess->visa_photo_path)
                                <a href="{{ route('admin.visa.download', [$user->id, 'visa_photo']) }}" class="btn btn-sm btn-info" target="_blank">
                                    <i class="fas fa-download"></i> Ver
                                </a>
                            @else
                                <span class="text-muted">Pendiente</span>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Notas del Proceso</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.visa.update-step', $user->id) }}" method="POST">
                        @csrf
                        <input type="hidden" name="step" value="notes">
                        <textarea name="notes" class="form-control" rows="6" placeholder="Agregar notas internas del proceso...">{{ $visaProcess->process_notes }}</textarea>
                        <button type="submit" class="btn btn-primary btn-sm mt-2 btn-block">
                            <i class="fas fa-save"></i> Guardar Notas
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODALES PARA ACTUALIZAR PASOS -->

<!-- Modal: Actualizar Paso Genérico (Documentación) -->
<div class="modal fade" id="updateStepModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.visa.update-step', $user->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Actualizar: <span id="modal-step-title"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="step" id="modal-step-name">
                    <input type="hidden" name="status" value="completed">
                    
                    <div class="form-group">
                        <label>Fecha de Completación *</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Notas</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Agregar notas sobre este paso..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Actualizar Entrevista -->
<div class="modal fade" id="updateInterviewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.visa.update-step', $user->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Actualizar: <span id="interview-modal-title"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="step" id="interview-step-name">
                    
                    <div class="form-group">
                        <label>Estado *</label>
                        <select name="status" class="form-control" required>
                            <option value="pending">Pendiente</option>
                            <option value="scheduled">Programada</option>
                            <option value="approved">Aprobada</option>
                            <option value="rejected">Rechazada</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Fecha de Entrevista</label>
                        <input type="datetime-local" name="date" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label>Notas</label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Comentarios sobre la entrevista..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Actualizar Documento (DS-160, DS-2019) -->
<div class="modal fade" id="updateDocumentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.visa.update-step', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Registrar: <span id="document-modal-title"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="step" id="document-step-name">
                    <input type="hidden" name="status" value="completed">
                    
                    <div class="form-group">
                        <label>Fecha de Completación *</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="form-group" id="confirmation-number-group" style="display: none;">
                        <label>Número de Confirmación</label>
                        <input type="text" name="confirmation_number" class="form-control" placeholder="Ej: AA123456789">
                    </div>
                    
                    <div class="form-group">
                        <label>Subir Documento (PDF, max 10MB)</label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">Formatos permitidos: PDF, JPG, PNG</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Actualizar Pago (SEVIS, Tasa Consular) -->
<div class="modal fade" id="updatePaymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.visa.update-step', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Pago: <span id="payment-modal-title"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="step" id="payment-step-name">
                    <input type="hidden" name="status" value="paid">
                    
                    <div class="form-group">
                        <label>Fecha de Pago *</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Monto (USD) *</label>
                        <input type="number" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Subir Recibo (PDF, max 10MB)</label>
                        <input type="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="form-text text-muted">Recibo o comprobante de pago</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Programar Cita Consular -->
<div class="modal fade" id="updateAppointmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.visa.update-step', $user->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Programar Cita Consular</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="step" value="appointment">
                    <input type="hidden" name="status" value="scheduled">
                    
                    <div class="form-group">
                        <label>Fecha y Hora de la Cita *</label>
                        <input type="datetime-local" name="date" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Ubicación (Consulado) *</label>
                        <input type="text" name="location" class="form-control" placeholder="Ej: Consulado de EE.UU. en Buenos Aires" required>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Recordatorio:</strong> Asegúrate de que el participante tenga todos los documentos necesarios antes de la cita.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-calendar-check"></i> Programar Cita
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Registrar Resultado de Visa -->
<div class="modal fade" id="updateResultModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form action="{{ route('admin.visa.update-step', $user->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Resultado de Visa</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="step" value="result">
                    
                    <div class="form-group">
                        <label>Resultado *</label>
                        <select name="status" class="form-control" required>
                            <option value="">Seleccionar resultado...</option>
                            <option value="approved">✅ Aprobada</option>
                            <option value="correspondence">📧 En Correspondencia</option>
                            <option value="rejected">❌ Rechazada</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Fecha del Resultado *</label>
                        <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Notas / Detalles</label>
                        <textarea name="notes" class="form-control" rows="4" placeholder="Información adicional sobre el resultado..."></textarea>
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>Importante:</strong> Este paso marcará el proceso como completado.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-flag-checkered"></i> Registrar Resultado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// JavaScript para manejar los modales dinámicos
$(document).ready(function() {
    // Modal genérico para pasos simples
    $('[data-toggle="modal"][data-target="#updateStepModal"]').click(function() {
        const step = $(this).data('step');
        const title = $(this).data('title');
        $('#modal-step-name').val(step);
        $('#modal-step-title').text(title);
    });
    
    // Modal para entrevistas
    $('[data-toggle="modal"][data-target="#updateInterviewModal"]').click(function() {
        const step = $(this).data('step');
        const title = $(this).data('title');
        $('#interview-step-name').val(step);
        $('#interview-modal-title').text(title);
    });
    
    // Modal para documentos
    $('[data-toggle="modal"][data-target="#updateDocumentModal"]').click(function() {
        const step = $(this).data('step');
        const title = $(this).data('title');
        $('#document-step-name').val(step);
        $('#document-modal-title').text(title);
        
        // Mostrar campo de confirmación solo para DS-160
        if(step === 'ds160') {
            $('#confirmation-number-group').show();
        } else {
            $('#confirmation-number-group').hide();
        }
    });
    
    // Modal para pagos
    $('[data-toggle="modal"][data-target="#updatePaymentModal"]').click(function() {
        const step = $(this).data('step');
        const title = $(this).data('title');
        $('#payment-step-name').val(step);
        $('#payment-modal-title').text(title);
    });
});
</script>
@endsection

@section('styles')
<style>
.visa-timeline {
    position: relative;
    padding-left: 50px;
}

.visa-timeline::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e3e6f0;
}

.timeline-item {
    position: relative;
    padding-bottom: 40px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -31px;
    top: 0;
}

.timeline-content {
    padding: 20px;
    background: #f8f9fc;
    border-radius: 8px;
    border-left: 3px solid #e3e6f0;
}

.timeline-item.completed .timeline-content {
    background: #d1ecf1;
    border-left-color: #28a745;
}

.badge-lg {
    font-size: 1rem;
    padding: 0.5rem 1rem;
}
</style>
@endsection
