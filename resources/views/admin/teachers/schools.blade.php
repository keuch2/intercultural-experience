@extends('layouts.admin')

@section('title', 'Escuelas')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestión de Escuelas</h1>
        <div>
            <a href="{{ route('admin.teachers.school.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Nueva Escuela
            </a>
            <a href="{{ route('admin.teachers.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.teachers.schools') }}" class="row">
                <div class="col-md-2 mb-3">
                    <label>Estado</label>
                    <select name="state" class="form-control">
                        <option value="">Todos</option>
                        @foreach($states as $state)
                            <option value="{{ $state }}" {{ request('state') == $state ? 'selected' : '' }}>
                                {{ $state }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Tipo</label>
                    <select name="type" class="form-control">
                        <option value="">Todos</option>
                        <option value="public" {{ request('type') == 'public' ? 'selected' : '' }}>Pública</option>
                        <option value="private" {{ request('type') == 'private' ? 'selected' : '' }}>Privada</option>
                        <option value="charter" {{ request('type') == 'charter' ? 'selected' : '' }}>Charter</option>
                        <option value="religious" {{ request('type') == 'religious' ? 'selected' : '' }}>Religiosa</option>
                        <option value="international" {{ request('type') == 'international' ? 'selected' : '' }}>Internacional</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Verificada</label>
                    <select name="verified" class="form-control">
                        <option value="">Todas</option>
                        <option value="1" {{ request('verified') == '1' ? 'selected' : '' }}>Sí</option>
                        <option value="0" {{ request('verified') == '0' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label>Con Posiciones</label>
                    <select name="positions" class="form-control">
                        <option value="">Todas</option>
                        <option value="1" {{ request('positions') == '1' ? 'selected' : '' }}>Sí</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Buscar</label>
                    <input type="text" name="search" class="form-control" placeholder="Nombre, distrito, ciudad..." value="{{ request('search') }}">
                </div>
                <div class="col-md-1 mb-3">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Schools Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Lista de Escuelas ({{ $schools->total() }} registros)
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Escuela</th>
                            <th>Ubicación</th>
                            <th>Tipo</th>
                            <th>Niveles</th>
                            <th>Estadísticas</th>
                            <th>Posiciones</th>
                            <th>Salario</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($schools as $school)
                        <tr>
                            <td>{{ $school->id }}</td>
                            <td>
                                <strong>{{ $school->school_name }}</strong>
                                @if($school->district_name)
                                    <br>
                                    <small class="text-muted">{{ $school->district_name }}</small>
                                @endif
                                @if($school->school_code)
                                    <br>
                                    <small class="text-muted">Código: {{ $school->school_code }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $school->city }}, {{ $school->state }}
                                <br>
                                <small class="text-muted">{{ $school->zip_code }}</small>
                            </td>
                            <td>
                                @php
                                    $typeColors = [
                                        'public' => 'primary',
                                        'private' => 'info',
                                        'charter' => 'success',
                                        'religious' => 'warning',
                                        'international' => 'dark'
                                    ];
                                    $typeLabels = [
                                        'public' => 'Pública',
                                        'private' => 'Privada',
                                        'charter' => 'Charter',
                                        'religious' => 'Religiosa',
                                        'international' => 'Internacional'
                                    ];
                                @endphp
                                <span class="badge badge-{{ $typeColors[$school->school_type] ?? 'secondary' }}">
                                    {{ $typeLabels[$school->school_type] ?? ucfirst($school->school_type) }}
                                </span>
                            </td>
                            <td>
                                @if($school->grade_levels)
                                    @foreach($school->grade_levels as $level)
                                        <small class="badge badge-light">{{ $level }}</small>
                                    @endforeach
                                @else
                                    <small class="text-muted">No especificado</small>
                                @endif
                            </td>
                            <td>
                                @if($school->total_students)
                                    <small>
                                        <i class="fas fa-user-graduate"></i> {{ number_format($school->total_students) }} estudiantes
                                    </small>
                                    <br>
                                @endif
                                @if($school->total_teachers)
                                    <small>
                                        <i class="fas fa-chalkboard-teacher"></i> {{ number_format($school->total_teachers) }} profesores
                                    </small>
                                    <br>
                                @endif
                                @if($school->student_teacher_ratio)
                                    <small>
                                        <i class="fas fa-percentage"></i> Ratio: {{ $school->student_teacher_ratio }}:1
                                    </small>
                                @endif
                            </td>
                            <td class="text-center">
                                <h5>
                                    <span class="badge badge-{{ $school->positions_available > 0 ? 'success' : 'secondary' }}">
                                        {{ $school->positions_available }}
                                    </span>
                                </h5>
                                @if($school->teachers_hired_current_year > 0)
                                    <small class="text-muted">
                                        {{ $school->teachers_hired_current_year }} este año
                                    </small>
                                @endif
                            </td>
                            <td>
                                @if($school->salary_range_min && $school->salary_range_max)
                                    <small>
                                        ${{ number_format($school->salary_range_min, 0) }} - 
                                        ${{ number_format($school->salary_range_max, 0) }}
                                    </small>
                                @else
                                    <small class="text-muted">No especificado</small>
                                @endif
                                
                                @if($school->sponsors_visa)
                                    <br>
                                    <span class="badge badge-success">
                                        <i class="fas fa-passport"></i> Visa
                                    </span>
                                @endif
                                
                                @if($school->provides_housing_assistance)
                                    <span class="badge badge-info">
                                        <i class="fas fa-home"></i> Housing
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($school->is_verified)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check-circle"></i> Verificada
                                    </span>
                                    @if($school->verification_date)
                                        <br>
                                        <small class="text-muted">
                                            {{ $school->verification_date->format('d/m/Y') }}
                                        </small>
                                    @endif
                                @else
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i> Pendiente
                                    </span>
                                @endif
                                
                                @if($school->rating)
                                    <br>
                                    <small>
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= floor($school->rating))
                                                <i class="fas fa-star text-warning"></i>
                                            @elseif($i - 0.5 <= $school->rating)
                                                <i class="fas fa-star-half-alt text-warning"></i>
                                            @else
                                                <i class="far fa-star text-warning"></i>
                                            @endif
                                        @endfor
                                        ({{ number_format($school->rating, 1) }})
                                    </small>
                                    <br>
                                    <small class="text-muted">{{ $school->total_reviews }} reviews</small>
                                @endif
                                
                                @if(!$school->is_active)
                                    <br>
                                    <span class="badge badge-danger">Inactiva</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.teachers.school.show', $school->id) }}" 
                                   class="btn btn-sm btn-info" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if(!$school->is_verified)
                                    <form action="{{ route('admin.teachers.school.verify', $school->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success" 
                                                title="Verificar"
                                                onclick="return confirm('¿Verificar esta escuela?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                <button type="button" class="btn btn-sm btn-primary" 
                                        data-toggle="modal" 
                                        data-target="#editModal{{ $school->id }}"
                                        title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $school->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('admin.teachers.school.update', $school->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title">Editar Escuela: {{ $school->school_name }}</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Posiciones Disponibles</label>
                                                        <input type="number" name="positions_available" 
                                                               class="form-control" 
                                                               value="{{ $school->positions_available }}" min="0">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Experiencia Mínima (años)</label>
                                                        <input type="number" name="minimum_experience_years" 
                                                               class="form-control" 
                                                               value="{{ $school->minimum_experience_years }}" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Salario Mínimo</label>
                                                        <input type="number" name="salary_range_min" 
                                                               class="form-control" 
                                                               value="{{ $school->salary_range_min }}" min="0" step="1000">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Salario Máximo</label>
                                                        <input type="number" name="salary_range_max" 
                                                               class="form-control" 
                                                               value="{{ $school->salary_range_max }}" min="0" step="1000">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="form-group">
                                                <label>Materias Necesarias</label>
                                                <select name="subjects_needed[]" class="form-control" multiple>
                                                    <option value="Math">Matemáticas</option>
                                                    <option value="Science">Ciencias</option>
                                                    <option value="English">Inglés</option>
                                                    <option value="History">Historia</option>
                                                    <option value="PE">Educación Física</option>
                                                    <option value="Art">Arte</option>
                                                    <option value="Music">Música</option>
                                                    <option value="Spanish">Español</option>
                                                    <option value="Special Ed">Educación Especial</option>
                                                </select>
                                                <small class="text-muted">Mantener Ctrl para selección múltiple</small>
                                            </div>
                                            
                                            <div class="form-check">
                                                <input type="checkbox" name="is_active" value="1" 
                                                       class="form-check-input" id="active{{ $school->id }}"
                                                       {{ $school->is_active ? 'checked' : '' }}>
                                                <label class="form-check-label" for="active{{ $school->id }}">
                                                    Escuela Activa
                                                </label>
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
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No se encontraron escuelas</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center">
                {{ $schools->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Escuelas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schools->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-school fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Verificadas
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schools->where('is_verified', true)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Con Posiciones
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schools->where('positions_available', '>', 0)->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-briefcase fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Posiciones
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $schools->sum('positions_available') }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
