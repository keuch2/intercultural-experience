@extends('layouts.admin')

@section('title', 'Matching Work & Travel')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Sistema de Matching</h1>
        <a href="{{ route('admin.work-travel.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <!-- Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary shadow">
                <div class="card-body">
                    <h5 class="text-primary">{{ count($students) }}</h5>
                    <small>Estudiantes Disponibles</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success shadow">
                <div class="card-body">
                    <h5 class="text-success">{{ count($employers) }}</h5>
                    <small>Empleadores con Posiciones</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-info shadow">
                <div class="card-body">
                    <h5 class="text-info">{{ count($matches) }}</h5>
                    <small>Matches Potenciales</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Matches Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-magic"></i> Sugerencias de Matching
            </h6>
        </div>
        <div class="card-body">
            @if(count($matches) > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Empleador</th>
                                <th>Ubicación</th>
                                <th>Temporada</th>
                                <th>Score</th>
                                <th>Detalles</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matches as $match)
                            <tr>
                                <td>
                                    <strong>{{ $match['student']->user->name ?? 'N/A' }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $match['student']->university_name }}</small>
                                    <br>
                                    <small>
                                        @if($match['student']->season == 'summer')
                                            <span class="badge badge-warning"><i class="fas fa-sun"></i> Summer</span>
                                        @else
                                            <span class="badge badge-info"><i class="fas fa-snowflake"></i> Winter</span>
                                        @endif
                                        
                                        @if($match['student']->is_presencial_validated)
                                            <span class="badge badge-success">Presencial</span>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <strong>{{ $match['employer']->company_name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $match['employer']->business_type }}</small>
                                    <br>
                                    <small>
                                        <i class="fas fa-briefcase"></i> {{ $match['employer']->positions_available }} posiciones
                                        @if($match['employer']->rating)
                                            <br>
                                            <i class="fas fa-star text-warning"></i> {{ number_format($match['employer']->rating, 1) }}
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    {{ $match['employer']->city }}, {{ $match['employer']->state }}
                                    <br>
                                    <small class="text-muted">{{ $match['employer']->country }}</small>
                                </td>
                                <td>
                                    @if($match['employer']->seasons_hiring)
                                        @foreach($match['employer']->seasons_hiring as $season)
                                            @if($season == 'summer')
                                                <span class="badge badge-warning"><i class="fas fa-sun"></i> Summer</span>
                                            @else
                                                <span class="badge badge-info"><i class="fas fa-snowflake"></i> Winter</span>
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $scoreClass = 'secondary';
                                        if ($match['score'] >= 80) $scoreClass = 'success';
                                        elseif ($match['score'] >= 60) $scoreClass = 'info';
                                        elseif ($match['score'] >= 40) $scoreClass = 'warning';
                                    @endphp
                                    <h4>
                                        <span class="badge badge-{{ $scoreClass }}">
                                            {{ $match['score'] }}
                                        </span>
                                    </h4>
                                    <small>Compatibilidad</small>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" 
                                            data-toggle="modal" 
                                            data-target="#detailsModal{{ $loop->index }}">
                                        <i class="fas fa-info-circle"></i> Ver Detalles
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success" 
                                            data-toggle="modal" 
                                            data-target="#createModal{{ $loop->index }}">
                                        <i class="fas fa-handshake"></i> Crear Match
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
                                                    <h6 class="text-primary">Estudiante</h6>
                                                    <p>
                                                        <strong>{{ $match['student']->user->name ?? 'N/A' }}</strong><br>
                                                        Universidad: {{ $match['student']->university_name }}<br>
                                                        GPA: {{ $match['student']->gpa ?? 'N/A' }}<br>
                                                        Semestre: {{ $match['student']->current_semester }}/{{ $match['student']->total_semesters }}<br>
                                                        Temporada: {{ ucfirst($match['student']->season) }}<br>
                                                    </p>
                                                    <h6>Requisitos Cumplidos:</h6>
                                                    <ul>
                                                        <li>Edad: {{ $match['student']->meets_age_requirement ? '✅' : '❌' }}</li>
                                                        <li>Académico: {{ $match['student']->meets_academic_requirement ? '✅' : '❌' }}</li>
                                                        <li>Inglés: {{ $match['student']->meets_english_requirement ? '✅' : '❌' }}</li>
                                                        <li>Pasaporte: {{ $match['student']->has_valid_passport ? '✅' : '❌' }}</li>
                                                    </ul>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="text-success">Empleador</h6>
                                                    <p>
                                                        <strong>{{ $match['employer']->company_name }}</strong><br>
                                                        Tipo: {{ $match['employer']->business_type }}<br>
                                                        Ubicación: {{ $match['employer']->city }}, {{ $match['employer']->state }}<br>
                                                        Posiciones: {{ $match['employer']->positions_available }}<br>
                                                        Rating: {{ $match['employer']->rating ? number_format($match['employer']->rating, 1) : 'N/A' }}<br>
                                                    </p>
                                                    <h6>Beneficios:</h6>
                                                    <ul>
                                                        <li>E-Verify: {{ $match['employer']->e_verify_enrolled ? '✅' : '❌' }}</li>
                                                        <li>Insurance: {{ $match['employer']->workers_comp_insurance ? '✅' : '❌' }}</li>
                                                        <li>Años en programa: {{ $match['employer']->years_in_program }}</li>
                                                    </ul>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="text-center">
                                                <h4>Score de Compatibilidad: 
                                                    <span class="badge badge-{{ $scoreClass }}">{{ $match['score'] }}/100</span>
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Create Match Modal -->
                            <div class="modal fade" id="createModal{{ $loop->index }}">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.work-travel.contract.create') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="student_id" value="{{ $match['student']->user_id }}">
                                            <input type="hidden" name="employer_id" value="{{ $match['employer']->id }}">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Crear Contrato</h5>
                                                <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Desea crear un contrato para este match?</p>
                                                <div class="alert alert-info">
                                                    <strong>Estudiante:</strong> {{ $match['student']->user->name ?? 'N/A' }}<br>
                                                    <strong>Empleador:</strong> {{ $match['employer']->company_name }}
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success">Crear Contrato</button>
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
                    <p>No se encontraron estudiantes validados sin contratos o empleadores con posiciones disponibles.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
