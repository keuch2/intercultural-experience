@extends('layouts.admin')

@section('title', 'Detalle de Escuela')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $school->school_name }}</h1>
        <div>
            @if(!$school->is_verified)
                <form action="{{ route('admin.teachers.school.verify', $school->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('¿Verificar esta escuela?')">
                        <i class="fas fa-check"></i> Verificar
                    </button>
                </form>
            @endif
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editModal">
                <i class="fas fa-edit"></i> Editar
            </button>
            <a href="{{ route('admin.teachers.schools') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-primary">{{ $school->total_students }}</h4>
                        <small>Estudiantes</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-success">{{ $school->total_teachers }}</h4>
                        <small>Profesores</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-info">{{ $school->positions_available }}</h4>
                        <small>Posiciones Disponibles</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-warning">{{ $school->teachers_hired_current_year }}</h4>
                        <small>Contratados Este Año</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- School Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información de la Escuela</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Tipo:</th>
                                    <td>
                                        <span class="badge badge-{{ $school->school_type == 'public' ? 'primary' : 'info' }}">
                                            {{ ucfirst($school->school_type) }}
                                        </span>
                                    </td>
                                </tr>
                                @if($school->district_name)
                                <tr>
                                    <th>Distrito:</th>
                                    <td>{{ $school->district_name }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Código:</th>
                                    <td>{{ $school->school_code ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Años en Programa:</th>
                                    <td>{{ $school->years_in_program }} años</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Dirección:</th>
                                    <td>{{ $school->address }}</td>
                                </tr>
                                <tr>
                                    <th>Ciudad, Estado:</th>
                                    <td>{{ $school->city }}, {{ $school->state }} {{ $school->zip_code }}</td>
                                </tr>
                                <tr>
                                    <th>Teléfono:</th>
                                    <td>{{ $school->phone }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $school->email }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($school->website)
                        <p><strong>Website:</strong> <a href="{{ $school->website }}" target="_blank">{{ $school->website }}</a></p>
                    @endif
                </div>
            </div>

            <!-- Contact Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contactos</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Director/Principal</h6>
                            <p>
                                <strong>{{ $school->principal_name }}</strong><br>
                                {{ $school->principal_email }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Recursos Humanos</h6>
                            <p>
                                <strong>{{ $school->hr_contact_name }}</strong><br>
                                {{ $school->hr_contact_email }}<br>
                                {{ $school->hr_contact_phone }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Academic Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información Académica</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Niveles Educativos</h6>
                            @if($school->grade_levels)
                                @foreach($school->grade_levels as $level)
                                    <span class="badge badge-primary">{{ $level }}</span>
                                @endforeach
                            @else
                                <p class="text-muted">No especificado</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <h6>Estadísticas</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Total Estudiantes:</th>
                                    <td>{{ number_format($school->total_students) }}</td>
                                </tr>
                                <tr>
                                    <th>Total Profesores:</th>
                                    <td>{{ number_format($school->total_teachers) }}</td>
                                </tr>
                                <tr>
                                    <th>Ratio Estudiante/Profesor:</th>
                                    <td>{{ number_format($school->student_teacher_ratio, 2) }}:1</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Positions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Posiciones y Requisitos</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Materias Necesitadas</h6>
                            @if($school->subjects_needed)
                                @foreach($school->subjects_needed as $subject)
                                    <span class="badge badge-success">{{ $subject }}</span>
                                @endforeach
                            @else
                                <p class="text-muted">No especificado</p>
                            @endif

                            <h6 class="mt-3">Certificaciones Requeridas</h6>
                            @if($school->required_certifications)
                                <ul class="small">
                                    @foreach($school->required_certifications as $cert)
                                        <li>{{ $cert }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">No especificado</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Posiciones Disponibles:</th>
                                    <td><h4 class="text-success">{{ $school->positions_available }}</h4></td>
                                </tr>
                                <tr>
                                    <th>Experiencia Mínima:</th>
                                    <td>{{ $school->minimum_experience_years ?? 0 }} años</td>
                                </tr>
                                <tr>
                                    <th>Total Contratados:</th>
                                    <td>{{ $school->teachers_hired_total }}</td>
                                </tr>
                                <tr>
                                    <th>Contratados Este Año:</th>
                                    <td>{{ $school->teachers_hired_current_year }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compensation & Benefits -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Compensación y Beneficios</h6>
                </div>
                <div class="card-body">
                    @if($school->salary_range_min && $school->salary_range_max)
                        <div class="alert alert-info">
                            <h6>Rango Salarial</h6>
                            <h4>${{ number_format($school->salary_range_min, 0) }} - ${{ number_format($school->salary_range_max, 0) }}</h4>
                            <small>por año</small>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Beneficios Ofrecidos</h6>
                            @if($school->benefits_offered)
                                <ul>
                                    @foreach($school->benefits_offered as $benefit)
                                        <li>{{ $benefit }}</li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted">No especificado</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Sponsor Visa:</th>
                                    <td>
                                        @if($school->sponsors_visa)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Asistencia Housing:</th>
                                    <td>
                                        @if($school->provides_housing_assistance)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                            @if($school->housing_details)
                                <p class="small text-muted">{{ $school->housing_details }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estado</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Estado General:</strong><br>
                        @if($school->is_active)
                            <span class="badge badge-success badge-lg">Activa</span>
                        @else
                            <span class="badge badge-secondary badge-lg">Inactiva</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <strong>Verificación:</strong><br>
                        @if($school->is_verified)
                            <span class="badge badge-success badge-lg">Verificada</span>
                            @if($school->verification_date)
                                <br><small class="text-muted">{{ $school->verification_date->format('d/m/Y') }}</small>
                            @endif
                        @else
                            <span class="badge badge-warning badge-lg">Pendiente</span>
                        @endif
                    </div>

                    @if($school->rating)
                        <div class="mb-3">
                            <strong>Rating:</strong><br>
                            <h4 class="text-warning mb-0">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= floor($school->rating))
                                        <i class="fas fa-star"></i>
                                    @elseif($i - 0.5 <= $school->rating)
                                        <i class="fas fa-star-half-alt"></i>
                                    @else
                                        <i class="far fa-star"></i>
                                    @endif
                                @endfor
                            </h4>
                            <p class="mb-0">{{ number_format($school->rating, 1) }}/5.0</p>
                            <small class="text-muted">{{ $school->total_reviews }} reviews</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estadísticas</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Profesores Totales:</th>
                            <td>{{ $school->teachers_hired_total }}</td>
                        </tr>
                        <tr>
                            <th>Este Año:</th>
                            <td>{{ $school->teachers_hired_current_year }}</td>
                        </tr>
                        <tr>
                            <th>Años en Programa:</th>
                            <td>{{ $school->years_in_program }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Documents -->
            @if($school->license_document_path || $school->accreditation_document_path)
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Documentos</h6>
                </div>
                <div class="card-body">
                    @if($school->license_document_path)
                        <a href="{{ Storage::url($school->license_document_path) }}" target="_blank" class="btn btn-sm btn-info btn-block mb-2">
                            <i class="fas fa-file-pdf"></i> Licencia
                        </a>
                    @endif
                    @if($school->accreditation_document_path)
                        <a href="{{ Storage::url($school->accreditation_document_path) }}" target="_blank" class="btn btn-sm btn-info btn-block">
                            <i class="fas fa-certificate"></i> Acreditación
                        </a>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.teachers.school.update', $school->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Editar Escuela</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Posiciones Disponibles</label>
                                <input type="number" name="positions_available" class="form-control" 
                                       value="{{ $school->positions_available }}" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Experiencia Mínima (años)</label>
                                <input type="number" name="minimum_experience_years" class="form-control" 
                                       value="{{ $school->minimum_experience_years }}" min="0">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Salario Mínimo</label>
                                <input type="number" name="salary_range_min" class="form-control" 
                                       value="{{ $school->salary_range_min }}" min="0" step="1000">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Salario Máximo</label>
                                <input type="number" name="salary_range_max" class="form-control" 
                                       value="{{ $school->salary_range_max }}" min="0" step="1000">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" 
                               {{ $school->is_active ? 'checked' : '' }}>
                        <label class="form-check-label">Escuela Activa</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
