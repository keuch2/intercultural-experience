@extends('layouts.admin')

@section('title', 'Detalle del Profesor')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $validation->user->name ?? 'Profesor' }}</h1>
        <div>
            @if($validation->validation_status != 'approved')
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#validateModal">
                    <i class="fas fa-check"></i> Validar
                </button>
            @endif
            @if(!$validation->has_mec_validation && $validation->mec_status == 'pending')
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#mecModal">
                    <i class="fas fa-certificate"></i> MEC
                </button>
            @endif
            <a href="{{ route('admin.teachers.validations') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    <div class="alert alert-{{ $validation->validation_status == 'approved' ? 'success' : ($validation->validation_status == 'rejected' ? 'danger' : 'warning') }}">
        <h5 class="alert-heading">
            Estado: <strong>{{ strtoupper(str_replace('_', ' ', $validation->validation_status)) }}</strong>
        </h5>
        @if($validation->has_mec_validation)
            <p class="mb-0"><i class="fas fa-certificate"></i> MEC Aprobado: {{ $validation->mec_registration_number }}</p>
        @endif
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Personal Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información Personal</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Nombre:</th>
                                    <td>{{ $validation->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $validation->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Teléfono:</th>
                                    <td>{{ $validation->user->phone ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Fecha Nacimiento:</th>
                                    <td>{{ $validation->user->date_of_birth ? \Carbon\Carbon::parse($validation->user->date_of_birth)->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Nacionalidad:</th>
                                    <td>{{ $validation->user->nationality ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Pasaporte:</th>
                                    <td>{{ $validation->user->passport_number ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Formación Académica</h6>
                </div>
                <div class="card-body">
                    <h5>{{ $validation->teaching_degree_title }}</h5>
                    <p class="text-muted">{{ $validation->university_name }}</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Año de Graduación:</th>
                                    <td>{{ $validation->graduation_year }}</td>
                                </tr>
                                <tr>
                                    <th>Diploma Apostillado:</th>
                                    <td>
                                        @if($validation->diploma_apostilled)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                                        @else
                                            <span class="badge badge-danger"><i class="fas fa-times"></i> No</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Transcript Apostillado:</th>
                                    <td>
                                        @if($validation->transcript_apostilled)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                                        @else
                                            <span class="badge badge-danger"><i class="fas fa-times"></i> No</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Teaching Experience -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Experiencia Docente</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Años de Experiencia</h6>
                            <h3 class="text-primary">{{ $validation->teaching_years_verified }} años</h3>
                            <small class="text-muted">Total: {{ $validation->teaching_years_total }} años</small>
                        </div>
                        <div class="col-md-6">
                            <h6>Empleo Actual</h6>
                            <p>
                                <strong>Estado:</strong> {{ ucfirst($validation->current_employment_status) }}<br>
                                @if($validation->current_school_name)
                                    <strong>Escuela:</strong> {{ $validation->current_school_name }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Materias que Enseña</h6>
                            @if($validation->subjects_taught)
                                @foreach($validation->subjects_taught as $subject)
                                    <span class="badge badge-primary">{{ $subject }}</span>
                                @endforeach
                            @else
                                <p class="text-muted">No especificado</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6>Niveles Educativos</h6>
                            @if($validation->grade_levels_taught)
                                @foreach($validation->grade_levels_taught as $level)
                                    <span class="badge badge-info">{{ $level }}</span>
                                @endforeach
                            @else
                                <p class="text-muted">No especificado</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Certifications -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Certificaciones</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>TEFL</h6>
                            @if($validation->has_tefl_certification)
                                <span class="badge badge-success badge-lg"><i class="fas fa-check"></i> Certificado</span>
                            @else
                                <span class="badge badge-secondary">No</span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h6>TESOL</h6>
                            @if($validation->has_tesol_certification)
                                <span class="badge badge-success badge-lg"><i class="fas fa-check"></i> Certificado</span>
                            @else
                                <span class="badge badge-secondary">No</span>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h6>Antecedentes</h6>
                            @if($validation->has_child_abuse_clearance)
                                <span class="badge badge-success badge-lg"><i class="fas fa-check"></i> Limpio</span>
                            @else
                                <span class="badge badge-warning">Pendiente</span>
                            @endif
                        </div>
                    </div>

                    @if($validation->other_certifications && count($validation->other_certifications) > 0)
                        <hr>
                        <h6>Otras Certificaciones</h6>
                        @foreach($validation->other_certifications as $cert)
                            <span class="badge badge-light">{{ $cert }}</span>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Job Fair Info -->
            @if($validation->registered_for_job_fair)
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Job Fair</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Registrado:</th>
                                    <td><span class="badge badge-success"><i class="fas fa-check"></i> Sí</span></td>
                                </tr>
                                @if($validation->job_fair_date)
                                <tr>
                                    <th>Fecha:</th>
                                    <td>{{ $validation->job_fair_date->format('d/m/Y') }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        <span class="badge badge-info">{{ ucfirst($validation->job_fair_status ?? 'N/A') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Entrevistas:</th>
                                    <td>{{ $validation->interviews_scheduled }}</td>
                                </tr>
                                <tr>
                                    <th>Ofertas Recibidas:</th>
                                    <td>{{ $validation->offers_received }}</td>
                                </tr>
                                <tr>
                                    <th>Placement:</th>
                                    <td>
                                        @if($validation->placement_confirmed)
                                            <span class="badge badge-success">Confirmado</span>
                                        @else
                                            <span class="badge badge-secondary">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- MEC Validation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 bg-{{ $validation->has_mec_validation ? 'success' : 'warning' }} text-white">
                    <h6 class="m-0 font-weight-bold">Validación MEC</h6>
                </div>
                <div class="card-body">
                    @if($validation->has_mec_validation)
                        <p><strong>Número de Registro:</strong><br>{{ $validation->mec_registration_number }}</p>
                        <p><strong>Fecha de Validación:</strong><br>{{ $validation->mec_validation_date ? $validation->mec_validation_date->format('d/m/Y') : 'N/A' }}</p>
                        @if($validation->mec_certificate_path)
                            <a href="{{ Storage::url($validation->mec_certificate_path) }}" target="_blank" class="btn btn-info btn-block">
                                <i class="fas fa-file-pdf"></i> Ver Certificado
                            </a>
                        @endif
                    @else
                        <p class="text-muted">Estado: {{ ucfirst($validation->mec_status) }}</p>
                        @if($validation->mec_rejection_reason)
                            <div class="alert alert-danger">
                                {{ $validation->mec_rejection_reason }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Preferences -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Preferencias</h6>
                </div>
                <div class="card-body">
                    <h6>Estados Preferidos</h6>
                    @if($validation->preferred_states)
                        @foreach($validation->preferred_states as $state)
                            <span class="badge badge-primary">{{ $state }}</span>
                        @endforeach
                    @else
                        <p class="text-muted">No especificado</p>
                    @endif

                    <hr>

                    <h6>Materias Preferidas</h6>
                    @if($validation->preferred_subjects)
                        @foreach($validation->preferred_subjects as $subject)
                            <span class="badge badge-info">{{ $subject }}</span>
                        @endforeach
                    @else
                        <p class="text-muted">No especificado</p>
                    @endif

                    <hr>

                    <h6>Niveles Preferidos</h6>
                    @if($validation->preferred_grade_levels)
                        @foreach($validation->preferred_grade_levels as $level)
                            <span class="badge badge-success">{{ $level }}</span>
                        @endforeach
                    @else
                        <p class="text-muted">No especificado</p>
                    @endif

                    <hr>

                    <table class="table table-sm">
                        <tr>
                            <th>Tipo de Escuela:</th>
                            <td>{{ ucfirst($validation->school_type_preference) }}</td>
                        </tr>
                        <tr>
                            <th>Dispuesto a Relocate:</th>
                            <td>
                                @if($validation->willing_to_relocate)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Validation Info -->
            @if($validation->validated_by)
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Info de Validación</h6>
                </div>
                <div class="card-body">
                    <p><strong>Validado por:</strong><br>{{ $validation->validator->name ?? 'N/A' }}</p>
                    <p><strong>Fecha:</strong><br>{{ $validation->validation_completed_at ? $validation->validation_completed_at->format('d/m/Y H:i') : 'N/A' }}</p>
                    
                    @if($validation->rejection_reasons)
                        <div class="alert alert-danger">
                            <strong>Razones de Rechazo:</strong><br>
                            {{ $validation->rejection_reasons }}
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- MEC Modal -->
<div class="modal fade" id="mecModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.teachers.validation.mec', $validation->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Validación MEC</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Estado MEC</label>
                        <select name="mec_status" class="form-control" required>
                            <option value="approved">Aprobado</option>
                            <option value="rejected">Rechazado</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Número de Registro MEC</label>
                        <input type="text" name="mec_registration_number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Certificado MEC (PDF)</label>
                        <input type="file" name="mec_certificate" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Validate Modal -->
<div class="modal fade" id="validateModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.teachers.validation.validate', $validation->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Validar Profesor</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Acción</label>
                        <select name="action" class="form-control" required>
                            <option value="">Seleccionar...</option>
                            <option value="approve">Aprobar</option>
                            <option value="reject">Rechazar</option>
                        </select>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="diploma_apostilled" value="1" class="form-check-input">
                        <label class="form-check-label">Diploma Apostillado</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="transcript_apostilled" value="1" class="form-check-input">
                        <label class="form-check-label">Transcript Apostillado</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="has_child_abuse_clearance" value="1" class="form-check-input">
                        <label class="form-check-label">Clearance de Abuso Infantil</label>
                    </div>
                    <div class="form-group mt-3">
                        <label>Razones de Rechazo (si aplica)</label>
                        <textarea name="rejection_reasons" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Validar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
