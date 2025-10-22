@extends('layouts.admin')

@section('title', 'Validaciones de Profesores')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Validaciones de Profesores</h1>
        <a href="{{ route('admin.teachers.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>Incompleto</option>
                        <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>En Revisión</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprobado</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="mec_status" class="form-control">
                        <option value="">MEC - Todos</option>
                        <option value="pending" {{ request('mec_status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="approved" {{ request('mec_status') == 'approved' ? 'selected' : '' }}>Aprobado</option>
                        <option value="rejected" {{ request('mec_status') == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="job_fair" class="form-control">
                        <option value="">Job Fair</option>
                        <option value="1" {{ request('job_fair') == '1' ? 'selected' : '' }}>Registrados</option>
                        <option value="0" {{ request('job_fair') == '0' ? 'selected' : '' }}>No Registrados</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Buscar..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Validations Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Profesores ({{ $validations->total() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Profesor</th>
                            <th>Formación</th>
                            <th>MEC</th>
                            <th>Experiencia</th>
                            <th>Certificaciones</th>
                            <th>Job Fair</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($validations as $validation)
                        <tr>
                            <td>
                                <strong>{{ $validation->user->name ?? 'N/A' }}</strong>
                                <br>
                                <small class="text-muted">{{ $validation->user->email ?? '' }}</small>
                            </td>
                            <td>
                                <strong>{{ $validation->teaching_degree_title }}</strong>
                                <br>
                                <small class="text-muted">{{ $validation->university_name }}</small>
                                <br>
                                <small>Graduación: {{ $validation->graduation_year }}</small>
                            </td>
                            <td>
                                @if($validation->has_mec_validation)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Aprobado
                                    </span>
                                    <br>
                                    <small>{{ $validation->mec_registration_number }}</small>
                                @else
                                    <span class="badge badge-{{ $validation->mec_status == 'rejected' ? 'danger' : 'warning' }}">
                                        {{ ucfirst($validation->mec_status) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $validation->teaching_years_verified }} años</strong>
                                @if($validation->subjects_taught)
                                    <br>
                                    @foreach(array_slice($validation->subjects_taught, 0, 2) as $subject)
                                        <small class="badge badge-light">{{ $subject }}</small>
                                    @endforeach
                                    @if(count($validation->subjects_taught) > 2)
                                        <small class="badge badge-light">+{{ count($validation->subjects_taught) - 2 }}</small>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if($validation->has_tefl_certification)
                                    <span class="badge badge-success">TEFL</span>
                                @endif
                                @if($validation->has_tesol_certification)
                                    <span class="badge badge-success">TESOL</span>
                                @endif
                                @if(!$validation->has_tefl_certification && !$validation->has_tesol_certification)
                                    <small class="text-muted">Ninguna</small>
                                @endif
                                
                                @if($validation->diploma_apostilled)
                                    <br><small class="text-success"><i class="fas fa-check"></i> Diploma apostillado</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($validation->registered_for_job_fair)
                                    <span class="badge badge-success">
                                        <i class="fas fa-calendar-check"></i> Registrado
                                    </span>
                                    @if($validation->job_fair_date)
                                        <br><small>{{ $validation->job_fair_date->format('d/m/Y') }}</small>
                                    @endif
                                    @if($validation->interviews_scheduled > 0)
                                        <br><small>{{ $validation->interviews_scheduled }} entrevistas</small>
                                    @endif
                                @else
                                    <small class="text-muted">No registrado</small>
                                @endif
                            </td>
                            <td>
                                @if($validation->validation_status == 'approved')
                                    <span class="badge badge-success">Aprobado</span>
                                @elseif($validation->validation_status == 'rejected')
                                    <span class="badge badge-danger">Rechazado</span>
                                @elseif($validation->validation_status == 'pending_review')
                                    <span class="badge badge-warning">En Revisión</span>
                                @else
                                    <span class="badge badge-secondary">Incompleto</span>
                                @endif
                                
                                @if($validation->validated_by)
                                    <br><small class="text-muted">Por: {{ $validation->validator->name ?? 'N/A' }}</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.teachers.validation.show', $validation->id) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(!$validation->has_mec_validation && $validation->mec_status == 'pending')
                                    <button type="button" class="btn btn-sm btn-primary" 
                                            data-toggle="modal" 
                                            data-target="#mecModal{{ $validation->id }}">
                                        <i class="fas fa-certificate"></i>
                                    </button>
                                @endif
                                @if($validation->validation_status != 'approved')
                                    <button type="button" class="btn btn-sm btn-success" 
                                            data-toggle="modal" 
                                            data-target="#validateModal{{ $validation->id }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- MEC Validation Modal -->
                        <div class="modal fade" id="mecModal{{ $validation->id }}">
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

                        <!-- Validation Modal -->
                        <div class="modal fade" id="validateModal{{ $validation->id }}">
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
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No se encontraron validaciones</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $validations->links() }}
        </div>
    </div>

    <!-- Stats -->
    <div class="row">
        <div class="col-md-3">
            <div class="card border-left-success shadow py-2">
                <div class="card-body text-center">
                    <h5>{{ $validations->where('has_mec_validation', true)->count() }}</h5>
                    <small>MEC Aprobados</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-primary shadow py-2">
                <div class="card-body text-center">
                    <h5>{{ $validations->where('validation_status', 'approved')->count() }}</h5>
                    <small>Validados</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow py-2">
                <div class="card-body text-center">
                    <h5>{{ $validations->where('registered_for_job_fair', true)->count() }}</h5>
                    <small>Registrados Job Fair</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow py-2">
                <div class="card-body text-center">
                    <h5>{{ $validations->where('validation_status', 'pending_review')->count() }}</h5>
                    <small>Pendientes</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
