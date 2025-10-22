@extends('layouts.admin')

@section('title', 'Detalle de Validación')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detalle de Validación</h1>
        <a href="{{ route('admin.work-travel.validations') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Validation Status Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-{{ $validation->validation_status == 'approved' ? 'success' : ($validation->validation_status == 'rejected' ? 'danger' : 'warning') }} text-white">
            <h6 class="m-0 font-weight-bold">
                Estado de Validación: 
                @if($validation->validation_status == 'approved')
                    <i class="fas fa-check-circle"></i> APROBADO
                @elseif($validation->validation_status == 'rejected')
                    <i class="fas fa-times-circle"></i> RECHAZADO
                @else
                    <i class="fas fa-clock"></i> PENDIENTE
                @endif
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-primary">Información del Estudiante</h5>
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
                        <tr>
                            <th>Fecha de Nacimiento:</th>
                            <td>{{ $validation->user->date_of_birth ? \Carbon\Carbon::parse($validation->user->date_of_birth)->format('d/m/Y') : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Nacionalidad:</th>
                            <td>{{ $validation->user->nationality ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <h5 class="text-success">Información Académica</h5>
                    <table class="table table-sm">
                        <tr>
                            <th width="40%">Universidad:</th>
                            <td>{{ $validation->university_name }}</td>
                        </tr>
                        <tr>
                            <th>Tipo de Estudio:</th>
                            <td>
                                <span class="badge badge-{{ $validation->study_type == 'presencial' ? 'success' : 'warning' }}">
                                    {{ ucfirst($validation->study_type) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>ID Estudiante:</th>
                            <td>{{ $validation->student_id_number }}</td>
                        </tr>
                        <tr>
                            <th>Semestre Actual:</th>
                            <td>{{ $validation->current_semester }} de {{ $validation->total_semesters }}</td>
                        </tr>
                        <tr>
                            <th>GPA:</th>
                            <td>{{ $validation->gpa ? number_format($validation->gpa, 2) : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Horas Semanales:</th>
                            <td>{{ $validation->weekly_class_hours }} horas</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Program Details -->
    <div class="row">
        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detalles del Programa</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="50%">Temporada:</th>
                            <td>
                                @if($validation->season == 'summer')
                                    <span class="badge badge-warning"><i class="fas fa-sun"></i> Summer</span>
                                @else
                                    <span class="badge badge-info"><i class="fas fa-snowflake"></i> Winter</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Fecha de Inicio:</th>
                            <td>{{ \Carbon\Carbon::parse($validation->program_start_date)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Fecha de Fin:</th>
                            <td>{{ \Carbon\Carbon::parse($validation->program_end_date)->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Duración:</th>
                            <td>
                                {{ \Carbon\Carbon::parse($validation->program_start_date)->diffInDays(\Carbon\Carbon::parse($validation->program_end_date)) }} días
                            </td>
                        </tr>
                        <tr>
                            <th>Graduación Esperada:</th>
                            <td>{{ \Carbon\Carbon::parse($validation->expected_graduation)->format('d/m/Y') }}</td>
                        </tr>
                    </table>

                    @if($validation->current_courses)
                        <h6 class="mt-3">Cursos Actuales:</h6>
                        <div>
                            @foreach($validation->current_courses as $course)
                                <span class="badge badge-light">{{ $course }}</span>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Requisitos y Validaciones</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="50%">Validación Presencial:</th>
                            <td>
                                @if($validation->is_presencial_validated)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                                    @if($validation->validation_date)
                                        <br><small class="text-muted">{{ $validation->validation_date->format('d/m/Y') }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> No</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Requisito de Edad:</th>
                            <td>
                                @if($validation->meets_age_requirement)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Cumple</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> No Cumple</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Requisito Académico:</th>
                            <td>
                                @if($validation->meets_academic_requirement)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Cumple</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> No Cumple</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Requisito de Inglés:</th>
                            <td>
                                @if($validation->meets_english_requirement)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Cumple</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> No Cumple</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Pasaporte Válido:</th>
                            <td>
                                @if($validation->has_valid_passport)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> No</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Antecedentes Limpios:</th>
                            <td>
                                @if($validation->has_clean_record)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> No</span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    @if($validation->enrollment_certificate_path)
                        <div class="mt-3">
                            <strong>Certificado de Matrícula:</strong><br>
                            <a href="{{ Storage::url($validation->enrollment_certificate_path) }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-file-pdf"></i> Ver Certificado
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Validation Info -->
    @if($validation->validated_by)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Información de Validación</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>Validado Por:</strong><br>
                    {{ $validation->validator->name ?? 'N/A' }}
                </div>
                <div class="col-md-4">
                    <strong>Fecha de Validación:</strong><br>
                    {{ $validation->validation_date ? $validation->validation_date->format('d/m/Y H:i') : 'N/A' }}
                </div>
                <div class="col-md-4">
                    <strong>Estado:</strong><br>
                    @if($validation->validation_status == 'approved')
                        <span class="badge badge-success badge-lg">Aprobado</span>
                    @elseif($validation->validation_status == 'rejected')
                        <span class="badge badge-danger badge-lg">Rechazado</span>
                    @else
                        <span class="badge badge-warning badge-lg">Pendiente</span>
                    @endif
                </div>
            </div>

            @if($validation->rejection_reason)
                <div class="alert alert-danger mt-3">
                    <strong>Razón de Rechazo:</strong><br>
                    {{ $validation->rejection_reason }}
                </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Actions -->
    @if($validation->validation_status == 'pending' || $validation->validation_status == 'incomplete')
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Acciones</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.work-travel.validation.validate', $validation->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Acción</label>
                            <select name="action" class="form-control" required>
                                <option value="">Seleccionar...</option>
                                <option value="approve">Aprobar</option>
                                <option value="reject">Rechazar</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Razón de Rechazo (si aplica)</label>
                            <textarea name="rejection_reason" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="is_presencial_validated" value="1" 
                                   class="form-check-input" {{ $validation->is_presencial_validated ? 'checked' : '' }}>
                            <label class="form-check-label">Validación Presencial</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="meets_age_requirement" value="1" 
                                   class="form-check-input" {{ $validation->meets_age_requirement ? 'checked' : '' }}>
                            <label class="form-check-label">Requisito de Edad</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="meets_academic_requirement" value="1" 
                                   class="form-check-input" {{ $validation->meets_academic_requirement ? 'checked' : '' }}>
                            <label class="form-check-label">Requisito Académico</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="meets_english_requirement" value="1" 
                                   class="form-check-input" {{ $validation->meets_english_requirement ? 'checked' : '' }}>
                            <label class="form-check-label">Requisito de Inglés</label>
                        </div>
                    </div>
                </div>

                <div class="row mt-2">
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="has_valid_passport" value="1" 
                                   class="form-check-input" {{ $validation->has_valid_passport ? 'checked' : '' }}>
                            <label class="form-check-label">Pasaporte Válido</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check">
                            <input type="checkbox" name="has_clean_record" value="1" 
                                   class="form-check-input" {{ $validation->has_clean_record ? 'checked' : '' }}>
                            <label class="form-check-label">Antecedentes Limpios</label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Procesar Validación
                    </button>
                    <a href="{{ route('admin.work-travel.validations') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
