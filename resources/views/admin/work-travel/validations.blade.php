@extends('layouts.admin')

@section('title', 'Validaciones Work & Travel')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Validaciones de Estudiantes</h1>
        <a href="{{ route('admin.work-travel.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.work-travel.validations') }}" class="row">
                <div class="col-md-3 mb-3">
                    <label>Estado</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprobado</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                        <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>Incompleto</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Tipo de Estudio</label>
                    <select name="study_type" class="form-control">
                        <option value="">Todos</option>
                        <option value="presencial" {{ request('study_type') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                        <option value="online" {{ request('study_type') == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="hybrid" {{ request('study_type') == 'hybrid' ? 'selected' : '' }}>Híbrido</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Temporada</label>
                    <select name="season" class="form-control">
                        <option value="">Todas</option>
                        <option value="summer" {{ request('season') == 'summer' ? 'selected' : '' }}>Summer</option>
                        <option value="winter" {{ request('season') == 'winter' ? 'selected' : '' }}>Winter</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="Nombre, email, universidad..." value="{{ request('search') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.work-travel.validations') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Validations Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Lista de Validaciones ({{ $validations->total() }} registros)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Estudiante</th>
                            <th>Universidad</th>
                            <th>Tipo Estudio</th>
                            <th>Semestre</th>
                            <th>Temporada</th>
                            <th>Período</th>
                            <th>Requisitos</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($validations as $validation)
                        <tr>
                            <td>{{ $validation->id }}</td>
                            <td>
                                <strong>{{ $validation->user->name ?? 'N/A' }}</strong>
                                <br>
                                <small class="text-muted">{{ $validation->user->email ?? '' }}</small>
                            </td>
                            <td>
                                {{ $validation->university_name }}
                                <br>
                                <small class="text-muted">ID: {{ $validation->student_id_number }}</small>
                            </td>
                            <td>
                                <span class="badge badge-{{ $validation->study_type == 'presencial' ? 'success' : ($validation->study_type == 'online' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($validation->study_type) }}
                                </span>
                                @if($validation->is_presencial_validated)
                                    <br><small class="text-success"><i class="fas fa-check"></i> Validado</small>
                                @endif
                            </td>
                            <td>
                                {{ $validation->current_semester }}/{{ $validation->total_semesters }}
                                @if($validation->gpa)
                                    <br><small>GPA: {{ $validation->gpa }}</small>
                                @endif
                            </td>
                            <td>
                                @if($validation->season == 'summer')
                                    <span class="text-warning"><i class="fas fa-sun"></i> Summer</span>
                                @else
                                    <span class="text-info"><i class="fas fa-snowflake"></i> Winter</span>
                                @endif
                            </td>
                            <td>
                                <small>
                                    {{ \Carbon\Carbon::parse($validation->program_start_date)->format('d/m/Y') }}
                                    <br>
                                    {{ \Carbon\Carbon::parse($validation->program_end_date)->format('d/m/Y') }}
                                </small>
                            </td>
                            <td>
                                <div class="small">
                                    <span class="badge badge-{{ $validation->meets_age_requirement ? 'success' : 'danger' }}">
                                        Edad
                                    </span>
                                    <span class="badge badge-{{ $validation->meets_academic_requirement ? 'success' : 'danger' }}">
                                        Académico
                                    </span>
                                    <span class="badge badge-{{ $validation->meets_english_requirement ? 'success' : 'danger' }}">
                                        Inglés
                                    </span>
                                    <span class="badge badge-{{ $validation->has_valid_passport ? 'success' : 'danger' }}">
                                        Pasaporte
                                    </span>
                                    <span class="badge badge-{{ $validation->has_clean_record ? 'success' : 'danger' }}">
                                        Antecedentes
                                    </span>
                                </div>
                            </td>
                            <td>
                                @if($validation->validation_status == 'approved')
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Aprobado
                                    </span>
                                @elseif($validation->validation_status == 'rejected')
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times"></i> Rechazado
                                    </span>
                                @elseif($validation->validation_status == 'incomplete')
                                    <span class="badge badge-warning">
                                        <i class="fas fa-exclamation"></i> Incompleto
                                    </span>
                                @else
                                    <span class="badge badge-secondary">
                                        <i class="fas fa-clock"></i> Pendiente
                                    </span>
                                @endif
                                @if($validation->validated_by)
                                    <br>
                                    <small class="text-muted">
                                        Por: {{ $validation->validator->name ?? '' }}
                                        <br>
                                        {{ $validation->validation_date ? $validation->validation_date->format('d/m/Y') : '' }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.work-travel.validation.show', $validation->id) }}" 
                                   class="btn btn-sm btn-info" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($validation->validation_status == 'pending' || $validation->validation_status == 'incomplete')
                                    <button type="button" class="btn btn-sm btn-success" 
                                            data-toggle="modal" 
                                            data-target="#validateModal{{ $validation->id }}"
                                            title="Validar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- Validation Modal -->
                        @if($validation->validation_status == 'pending' || $validation->validation_status == 'incomplete')
                        <div class="modal fade" id="validateModal{{ $validation->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('admin.work-travel.validation.validate', $validation->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Validar Estudiante: {{ $validation->user->name ?? 'N/A' }}</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>Acción</label>
                                                <select name="action" class="form-control" required onchange="toggleRejectionReason(this, {{ $validation->id }})">
                                                    <option value="">Seleccionar...</option>
                                                    <option value="approve">Aprobar</option>
                                                    <option value="reject">Rechazar</option>
                                                </select>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="is_presencial_validated" value="1" 
                                                               class="form-check-input" id="presencial{{ $validation->id }}"
                                                               {{ $validation->is_presencial_validated ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="presencial{{ $validation->id }}">
                                                            Validación Presencial
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="meets_age_requirement" value="1" 
                                                               class="form-check-input" id="age{{ $validation->id }}"
                                                               {{ $validation->meets_age_requirement ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="age{{ $validation->id }}">
                                                            Cumple Requisito de Edad
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="meets_academic_requirement" value="1" 
                                                               class="form-check-input" id="academic{{ $validation->id }}"
                                                               {{ $validation->meets_academic_requirement ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="academic{{ $validation->id }}">
                                                            Cumple Requisito Académico
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check">
                                                        <input type="checkbox" name="meets_english_requirement" value="1" 
                                                               class="form-check-input" id="english{{ $validation->id }}"
                                                               {{ $validation->meets_english_requirement ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="english{{ $validation->id }}">
                                                            Cumple Requisito de Inglés
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="has_valid_passport" value="1" 
                                                               class="form-check-input" id="passport{{ $validation->id }}"
                                                               {{ $validation->has_valid_passport ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="passport{{ $validation->id }}">
                                                            Tiene Pasaporte Válido
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input type="checkbox" name="has_clean_record" value="1" 
                                                               class="form-check-input" id="record{{ $validation->id }}"
                                                               {{ $validation->has_clean_record ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="record{{ $validation->id }}">
                                                            Antecedentes Limpios
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group mt-3" id="rejectionReason{{ $validation->id }}" style="display: none;">
                                                <label>Razón de Rechazo</label>
                                                <textarea name="rejection_reason" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Guardar Validación</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No se encontraron validaciones</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $validations->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleRejectionReason(select, id) {
    const reasonDiv = document.getElementById('rejectionReason' + id);
    if (select.value === 'reject') {
        reasonDiv.style.display = 'block';
        reasonDiv.querySelector('textarea').setAttribute('required', 'required');
    } else {
        reasonDiv.style.display = 'none';
        reasonDiv.querySelector('textarea').removeAttribute('required');
    }
}
</script>
@endpush
@endsection
