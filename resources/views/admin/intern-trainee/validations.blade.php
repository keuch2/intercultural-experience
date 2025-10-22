@extends('layouts.admin')

@section('title', 'Validaciones Intern/Trainee')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Validaciones Intern/Trainee</h1>
        <a href="{{ route('admin.intern-trainee.dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>

    <!-- Filters Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.intern-trainee.validations') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Tipo de Programa</label>
                            <select name="program_type" class="form-control">
                                <option value="">Todos</option>
                                <option value="intern" {{ request('program_type') == 'intern' ? 'selected' : '' }}>Intern</option>
                                <option value="trainee" {{ request('program_type') == 'trainee' ? 'selected' : '' }}>Trainee</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="status" class="form-control">
                                <option value="">Todos</option>
                                <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>Incompleto</option>
                                <option value="pending_review" {{ request('status') == 'pending_review' ? 'selected' : '' }}>Pendiente</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Aprobado</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Industria</label>
                            <select name="industry" class="form-control">
                                <option value="">Todas</option>
                                @foreach($industries as $industry)
                                    <option value="{{ $industry }}" {{ request('industry') == $industry ? 'selected' : '' }}>
                                        {{ $industry }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Buscar</label>
                            <input type="text" name="search" class="form-control" placeholder="Nombre o email..." 
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('admin.intern-trainee.validations') }}" class="btn btn-secondary">
                            <i class="fas fa-redo"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Validations Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Validaciones ({{ $validations->total() }})
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Participante</th>
                            <th>Tipo</th>
                            <th>Industria</th>
                            <th>Educación/Experiencia</th>
                            <th>Duración</th>
                            <th>Empresa</th>
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
                                @if($validation->program_type == 'intern')
                                    <span class="badge badge-info">
                                        <i class="fas fa-graduation-cap"></i> Intern
                                    </span>
                                @else
                                    <span class="badge badge-success">
                                        <i class="fas fa-briefcase"></i> Trainee
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{ $validation->industry_sector ?? 'N/A' }}
                            </td>
                            <td>
                                @if($validation->program_type == 'intern')
                                    <small>
                                        {{ $validation->university_name ?? 'N/A' }}<br>
                                        {{ $validation->degree_field ?? '' }}
                                        @if($validation->gpa)
                                            <br>GPA: {{ $validation->gpa }}
                                        @endif
                                    </small>
                                @else
                                    <small>
                                        {{ $validation->field_of_expertise ?? 'N/A' }}<br>
                                        {{ $validation->years_of_experience ?? 0 }} años
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong>{{ $validation->duration_months ?? 0 }}</strong> meses
                            </td>
                            <td>
                                @if($validation->hostCompany)
                                    <a href="{{ route('admin.intern-trainee.company.show', $validation->hostCompany->id) }}">
                                        {{ $validation->hostCompany->company_name }}
                                    </a>
                                @else
                                    <span class="text-muted">Sin asignar</span>
                                @endif
                            </td>
                            <td>
                                @if($validation->validation_status == 'approved')
                                    <span class="badge badge-success">Aprobado</span>
                                @elseif($validation->validation_status == 'pending_review')
                                    <span class="badge badge-warning">Pendiente</span>
                                @elseif($validation->validation_status == 'rejected')
                                    <span class="badge badge-danger">Rechazado</span>
                                @else
                                    <span class="badge badge-secondary">Incompleto</span>
                                @endif
                                
                                @if($validation->validation_completed_at)
                                    <br>
                                    <small class="text-muted">
                                        {{ $validation->validation_completed_at->format('d/m/Y') }}
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.intern-trainee.validation.show', $validation->id) }}" 
                                   class="btn btn-sm btn-info" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($validation->validation_status == 'pending_review')
                                    <button type="button" class="btn btn-sm btn-success" 
                                            data-toggle="modal" 
                                            data-target="#validateModal{{ $validation->id }}"
                                            title="Validar">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- Validate Modal -->
                        <div class="modal fade" id="validateModal{{ $validation->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form method="POST" action="{{ route('admin.intern-trainee.validation.validate', $validation->id) }}">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Validar Participante</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p><strong>Participante:</strong> {{ $validation->user->name }}</p>
                                            
                                            <h6 class="mt-3">Requisitos:</h6>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" 
                                                       name="meets_age_requirement" value="1" id="age{{ $validation->id }}">
                                                <label class="form-check-label" for="age{{ $validation->id }}">
                                                    Cumple requisito de edad
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" 
                                                       name="meets_education_requirement" value="1" id="edu{{ $validation->id }}">
                                                <label class="form-check-label" for="edu{{ $validation->id }}">
                                                    Cumple requisito educativo
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" 
                                                       name="meets_experience_requirement" value="1" id="exp{{ $validation->id }}">
                                                <label class="form-check-label" for="exp{{ $validation->id }}">
                                                    Cumple requisito de experiencia
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" 
                                                       name="meets_english_requirement" value="1" id="eng{{ $validation->id }}">
                                                <label class="form-check-label" for="eng{{ $validation->id }}">
                                                    Cumple nivel de inglés
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" 
                                                       name="has_valid_passport" value="1" id="pass{{ $validation->id }}">
                                                <label class="form-check-label" for="pass{{ $validation->id }}">
                                                    Tiene pasaporte válido
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" 
                                                       name="has_clean_record" value="1" id="record{{ $validation->id }}">
                                                <label class="form-check-label" for="record{{ $validation->id }}">
                                                    Antecedentes limpios
                                                </label>
                                            </div>

                                            <div class="form-group mt-3">
                                                <label>Acción:</label>
                                                <select name="action" class="form-control" required>
                                                    <option value="approve">Aprobar</option>
                                                    <option value="reject">Rechazar</option>
                                                </select>
                                            </div>

                                            <div class="form-group" id="rejection_reason_group{{ $validation->id }}" style="display: none;">
                                                <label>Razón de Rechazo:</label>
                                                <textarea name="rejection_reason" class="form-control" rows="3"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-primary">Procesar Validación</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No hay validaciones registradas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $validations->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle rejection reason field
    document.querySelectorAll('select[name="action"]').forEach(select => {
        select.addEventListener('change', function() {
            const modal = this.closest('.modal');
            const reasonGroup = modal.querySelector('[id^="rejection_reason_group"]');
            if (this.value === 'reject') {
                reasonGroup.style.display = 'block';
                reasonGroup.querySelector('textarea').required = true;
            } else {
                reasonGroup.style.display = 'none';
                reasonGroup.querySelector('textarea').required = false;
            }
        });
    });
</script>
@endpush
@endsection
