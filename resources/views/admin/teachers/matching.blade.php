@extends('layouts.admin')

@section('title', 'Matching Teachers')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-magic"></i> Sistema de Matching Teacher-School
        </h1>
        <a href="{{ route('admin.teachers.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow">
                <div class="card-body">
                    <h4 class="text-primary">{{ count($teachers) }}</h4>
                    <small>Profesores Elegibles</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow">
                <div class="card-body">
                    <h4 class="text-success">{{ count($schools) }}</h4>
                    <small>Escuelas con Posiciones</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow">
                <div class="card-body">
                    <h4 class="text-info">{{ count($matches) }}</h4>
                    <small>Matches Potenciales</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow">
                <div class="card-body">
                    <h4 class="text-warning">{{ collect($matches)->where('score', '>=', 80)->count() }}</h4>
                    <small>Matches Alta Compatibilidad</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Matches Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Sugerencias de Matching (Ordenadas por Compatibilidad)
            </h6>
        </div>
        <div class="card-body">
            @if(count($matches) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Profesor</th>
                                <th>Escuela</th>
                                <th>Experiencia</th>
                                <th>Materias Match</th>
                                <th>Niveles Match</th>
                                <th>Score</th>
                                <th>Detalles</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matches as $match)
                            <tr>
                                <td>
                                    <strong>{{ $match['teacher']->user->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $match['teacher']->university_name }}</small>
                                    <br>
                                    <small>
                                        @if($match['teacher']->has_mec_validation)
                                            <span class="badge badge-success">MEC</span>
                                        @endif
                                        @if($match['teacher']->has_tefl_certification)
                                            <span class="badge badge-info">TEFL</span>
                                        @endif
                                        @if($match['teacher']->has_tesol_certification)
                                            <span class="badge badge-info">TESOL</span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <strong>{{ $match['school']->school_name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $match['school']->city }}, {{ $match['school']->state }}
                                    </small>
                                    <br>
                                    <small>
                                        <span class="badge badge-{{ $match['school']->school_type == 'public' ? 'primary' : 'info' }}">
                                            {{ ucfirst($match['school']->school_type) }}
                                        </span>
                                        @if($match['school']->rating)
                                            <i class="fas fa-star text-warning"></i> {{ number_format($match['school']->rating, 1) }}
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <strong>{{ $match['teacher']->teaching_years_verified }} años</strong>
                                    <br>
                                    <small class="text-muted">
                                        Mínimo requerido: {{ $match['school']->minimum_experience_years ?? 0 }} años
                                    </small>
                                    @if($match['teacher']->teaching_years_verified >= ($match['school']->minimum_experience_years ?? 0))
                                        <br><small class="text-success"><i class="fas fa-check"></i> Cumple</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $teacherSubjects = $match['teacher']->subjects_taught ?? [];
                                        $schoolSubjects = $match['school']->subjects_needed ?? [];
                                        $matchingSubjects = array_intersect($teacherSubjects, $schoolSubjects);
                                    @endphp
                                    @if(count($matchingSubjects) > 0)
                                        @foreach(array_slice($matchingSubjects, 0, 3) as $subject)
                                            <span class="badge badge-success">{{ $subject }}</span>
                                        @endforeach
                                        @if(count($matchingSubjects) > 3)
                                            <span class="badge badge-success">+{{ count($matchingSubjects) - 3 }}</span>
                                        @endif
                                    @else
                                        <small class="text-muted">Sin coincidencias</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $teacherLevels = $match['teacher']->grade_levels_taught ?? [];
                                        $schoolLevels = $match['school']->grade_levels ?? [];
                                        $matchingLevels = array_intersect($teacherLevels, $schoolLevels);
                                    @endphp
                                    @if(count($matchingLevels) > 0)
                                        @foreach($matchingLevels as $level)
                                            <span class="badge badge-primary">{{ $level }}</span>
                                        @endforeach
                                    @else
                                        <small class="text-muted">Sin coincidencias</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $scoreClass = 'secondary';
                                        if ($match['score'] >= 80) $scoreClass = 'success';
                                        elseif ($match['score'] >= 60) $scoreClass = 'info';
                                        elseif ($match['score'] >= 40) $scoreClass = 'warning';
                                        else $scoreClass = 'danger';
                                    @endphp
                                    <h3>
                                        <span class="badge badge-{{ $scoreClass }}">
                                            {{ $match['score'] }}
                                        </span>
                                    </h3>
                                    <small>
                                        Teacher: {{ $match['teacher_score'] }}<br>
                                        School: {{ $match['school_score'] }}
                                    </small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-toggle="modal" 
                                            data-target="#detailsModal{{ $loop->index }}">
                                        <i class="fas fa-info-circle"></i> Ver
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            data-toggle="modal" 
                                            data-target="#registerModal{{ $loop->index }}">
                                        <i class="fas fa-calendar-plus"></i> Registrar
                                    </button>
                                </td>
                            </tr>

                            <!-- Details Modal -->
                            <div class="modal fade" id="detailsModal{{ $loop->index }}">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Detalles del Match</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <h6 class="text-primary"><i class="fas fa-chalkboard-teacher"></i> Profesor</h6>
                                                    <p>
                                                        <strong>{{ $match['teacher']->user->name ?? 'N/A' }}</strong><br>
                                                        <strong>Formación:</strong> {{ $match['teacher']->teaching_degree_title }}<br>
                                                        <strong>Universidad:</strong> {{ $match['teacher']->university_name }}<br>
                                                        <strong>Graduación:</strong> {{ $match['teacher']->graduation_year }}<br>
                                                        <strong>Experiencia:</strong> {{ $match['teacher']->teaching_years_verified }} años<br>
                                                    </p>
                                                    <p>
                                                        <strong>Materias que enseña:</strong><br>
                                                        @if($match['teacher']->subjects_taught)
                                                            @foreach($match['teacher']->subjects_taught as $subject)
                                                                <span class="badge badge-light">{{ $subject }}</span>
                                                            @endforeach
                                                        @endif
                                                    </p>
                                                    <p>
                                                        <strong>Niveles:</strong><br>
                                                        @if($match['teacher']->grade_levels_taught)
                                                            @foreach($match['teacher']->grade_levels_taught as $level)
                                                                <span class="badge badge-light">{{ $level }}</span>
                                                            @endforeach
                                                        @endif
                                                    </p>
                                                    <p>
                                                        <strong>Certificaciones:</strong><br>
                                                        @if($match['teacher']->has_mec_validation)
                                                            <span class="badge badge-success">MEC Aprobado</span>
                                                        @endif
                                                        @if($match['teacher']->has_tefl_certification)
                                                            <span class="badge badge-info">TEFL</span>
                                                        @endif
                                                        @if($match['teacher']->has_tesol_certification)
                                                            <span class="badge badge-info">TESOL</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="text-success"><i class="fas fa-school"></i> Escuela</h6>
                                                    <p>
                                                        <strong>{{ $match['school']->school_name }}</strong><br>
                                                        <strong>Tipo:</strong> {{ ucfirst($match['school']->school_type) }}<br>
                                                        <strong>Ubicación:</strong> {{ $match['school']->city }}, {{ $match['school']->state }}<br>
                                                        @if($match['school']->total_students)
                                                            <strong>Estudiantes:</strong> {{ number_format($match['school']->total_students) }}<br>
                                                        @endif
                                                        @if($match['school']->rating)
                                                            <strong>Rating:</strong> {{ number_format($match['school']->rating, 1) }} ⭐<br>
                                                        @endif
                                                    </p>
                                                    <p>
                                                        <strong>Materias necesarias:</strong><br>
                                                        @if($match['school']->subjects_needed)
                                                            @foreach($match['school']->subjects_needed as $subject)
                                                                <span class="badge badge-light">{{ $subject }}</span>
                                                            @endforeach
                                                        @endif
                                                    </p>
                                                    <p>
                                                        <strong>Niveles:</strong><br>
                                                        @if($match['school']->grade_levels)
                                                            @foreach($match['school']->grade_levels as $level)
                                                                <span class="badge badge-light">{{ $level }}</span>
                                                            @endforeach
                                                        @endif
                                                    </p>
                                                    @if($match['school']->salary_range_min && $match['school']->salary_range_max)
                                                        <p>
                                                            <strong>Salario:</strong><br>
                                                            ${{ number_format($match['school']->salary_range_min, 0) }} - 
                                                            ${{ number_format($match['school']->salary_range_max, 0) }}
                                                        </p>
                                                    @endif
                                                    <p>
                                                        <strong>Beneficios:</strong><br>
                                                        @if($match['school']->sponsors_visa)
                                                            <span class="badge badge-success">Sponsor Visa</span>
                                                        @endif
                                                        @if($match['school']->provides_housing_assistance)
                                                            <span class="badge badge-info">Housing</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="text-center">
                                                <h4>Score de Compatibilidad: 
                                                    <span class="badge badge-{{ $scoreClass }}">{{ $match['score'] }}/100</span>
                                                </h4>
                                                <p class="text-muted">
                                                    Score del Profesor: {{ $match['teacher_score'] }} | 
                                                    Score de la Escuela: {{ $match['school_score'] }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Register for Job Fair Modal -->
                            <div class="modal fade" id="registerModal{{ $loop->index }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.teachers.register-job-fair', $match['teacher']->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="school_preference[]" value="{{ $match['school']->id }}">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Registrar en Job Fair</h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Registrar a <strong>{{ $match['teacher']->user->name ?? 'N/A' }}</strong> en un Job Fair con preferencia por <strong>{{ $match['school']->school_name }}</strong>?</p>
                                                
                                                <div class="form-group">
                                                    <label>Seleccionar Job Fair</label>
                                                    <select name="job_fair_id" class="form-control" required>
                                                        <option value="">Seleccionar...</option>
                                                        <!-- Aquí se cargarían los job fairs disponibles -->
                                                    </select>
                                                </div>
                                                
                                                <div class="alert alert-info">
                                                    <strong>Score de Compatibilidad:</strong> {{ $match['score'] }}/100
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success">Registrar</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle fa-3x mb-3"></i>
                    <h5>No hay matches disponibles</h5>
                    <p>No se encontraron profesores validados o escuelas con posiciones disponibles.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
