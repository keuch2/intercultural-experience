@extends('layouts.admin')

@section('title', 'Matching Intern/Trainee')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Sistema de Matching Intern/Trainee</h1>
        <a href="{{ route('admin.intern-trainee.dashboard') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros de Búsqueda</h6>
        </div>
        <div class="card-body">
            <form method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tipo de Programa</label>
                            <select name="program_type" class="form-control">
                                <option value="">Todos</option>
                                <option value="intern" {{ request('program_type') == 'intern' ? 'selected' : '' }}>Interns</option>
                                <option value="trainee" {{ request('program_type') == 'trainee' ? 'selected' : '' }}>Trainees</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Industria</label>
                            <select name="industry" class="form-control">
                                <option value="">Todas</option>
                                <option value="IT">IT & Software</option>
                                <option value="Finance">Finance</option>
                                <option value="Engineering">Engineering</option>
                                <option value="Marketing">Marketing</option>
                                <option value="Healthcare">Healthcare</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search"></i> Buscar Matches
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Participants with Matches -->
    @forelse($participants as $participant)
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-gradient-primary text-white">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-user"></i> {{ $participant->user->name }}
                    </h6>
                    <small>
                        @if($participant->program_type == 'intern')
                            <span class="badge badge-light">Intern</span>
                        @else
                            <span class="badge badge-light">Trainee</span>
                        @endif
                        {{ $participant->industry_sector }} - {{ $participant->duration_months }} meses
                    </small>
                </div>
                <div class="col-md-6 text-right">
                    <span class="badge badge-light">
                        {{ $participant->matches->count() }} matches encontrados
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Participant Info -->
            <div class="row mb-3">
                <div class="col-md-12">
                    <div class="alert alert-info">
                        <strong>Perfil:</strong>
                        @if($participant->program_type == 'intern')
                            {{ $participant->university_name }} - {{ $participant->degree_field }}
                            @if($participant->gpa) (GPA: {{ $participant->gpa }}) @endif
                        @else
                            {{ $participant->field_of_expertise }} - {{ $participant->years_of_experience }} años de experiencia
                        @endif
                        
                        @if($participant->technical_skills)
                            <br><strong>Skills:</strong> 
                            @foreach($participant->technical_skills as $skill)
                                <span class="badge badge-secondary">{{ $skill }}</span>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Matches Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">Rank</th>
                            <th width="8%">Score</th>
                            <th width="25%">Empresa</th>
                            <th width="15%">Industria</th>
                            <th width="12%">Ubicación</th>
                            <th width="15%">Detalles</th>
                            <th width="10%">Duración</th>
                            <th width="10%">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($participant->matches as $index => $match)
                        <tr class="{{ $match['score'] >= 80 ? 'table-success' : ($match['score'] >= 70 ? 'table-info' : '') }}">
                            <td class="text-center">
                                <strong class="text-primary">#{{ $index + 1 }}</strong>
                            </td>
                            <td>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar 
                                        {{ $match['score'] >= 80 ? 'bg-success' : ($match['score'] >= 70 ? 'bg-info' : 'bg-warning') }}" 
                                         style="width: {{ $match['score'] }}%">
                                        <strong>{{ $match['score'] }}%</strong>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $match['company']->company_name }}</strong>
                                <br>
                                <small class="text-muted">
                                    @if($match['company']->is_verified)
                                        <i class="fas fa-check-circle text-success"></i> Verificada
                                    @endif
                                    @if($match['company']->rating)
                                        <br>
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $match['company']->rating)
                                                <i class="fas fa-star text-warning"></i>
                                            @else
                                                <i class="far fa-star text-warning"></i>
                                            @endif
                                        @endfor
                                        ({{ $match['company']->rating }})
                                    @endif
                                </small>
                            </td>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $match['company']->industry_sector }}
                                </span>
                            </td>
                            <td>
                                <i class="fas fa-map-marker-alt text-danger"></i>
                                {{ $match['company']->city }}, {{ $match['company']->state }}
                            </td>
                            <td>
                                <small>
                                    @if($match['company']->offers_stipend)
                                        <i class="fas fa-dollar-sign text-success"></i> 
                                        ${{ number_format($match['company']->stipend_range_min ?? 0) }}-${{ number_format($match['company']->stipend_range_max ?? 0) }}
                                        <br>
                                    @endif
                                    @if($match['company']->provides_housing)
                                        <i class="fas fa-home text-info"></i> Housing
                                        <br>
                                    @endif
                                    @if($match['company']->has_training_program)
                                        <i class="fas fa-graduation-cap text-primary"></i> Training Program
                                    @endif
                                </small>
                            </td>
                            <td class="text-center">
                                {{ $match['company']->min_duration_months }}-{{ $match['company']->max_duration_months }} meses
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.intern-trainee.company.show', $match['company']->id) }}" 
                                   class="btn btn-sm btn-info" title="Ver Empresa">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-success" 
                                        data-toggle="modal" 
                                        data-target="#matchModal{{ $participant->id }}_{{ $match['company']->id }}"
                                        title="Crear Match">
                                    <i class="fas fa-handshake"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Match Modal -->
                        <div class="modal fade" id="matchModal{{ $participant->id }}_{{ $match['company']->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-success text-white">
                                        <h5 class="modal-title">
                                            <i class="fas fa-handshake"></i> Crear Match
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal">
                                            <span>&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-primary">Participante</h6>
                                                <p><strong>{{ $participant->user->name }}</strong></p>
                                                <p>
                                                    <strong>Tipo:</strong> 
                                                    {{ $participant->program_type == 'intern' ? 'Intern' : 'Trainee' }}
                                                </p>
                                                <p><strong>Industria:</strong> {{ $participant->industry_sector }}</p>
                                                <p><strong>Duración:</strong> {{ $participant->duration_months }} meses</p>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-success">Empresa</h6>
                                                <p><strong>{{ $match['company']->company_name }}</strong></p>
                                                <p><strong>Industria:</strong> {{ $match['company']->industry_sector }}</p>
                                                <p><strong>Ubicación:</strong> {{ $match['company']->city }}, {{ $match['company']->state }}</p>
                                                <p>
                                                    <strong>Score:</strong> 
                                                    <span class="badge badge-success">{{ $match['score'] }}%</span>
                                                </p>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            Al confirmar el match, se creará un Training Plan en estado "draft" 
                                            que deberá ser completado y aprobado por todas las partes.
                                        </div>

                                        <form action="#" method="POST">
                                            @csrf
                                            <input type="hidden" name="participant_id" value="{{ $participant->id }}">
                                            <input type="hidden" name="company_id" value="{{ $match['company']->id }}">
                                            
                                            <div class="form-group">
                                                <label>Notas adicionales:</label>
                                                <textarea name="notes" class="form-control" rows="3"></textarea>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-check"></i> Confirmar Match
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                <i class="fas fa-exclamation-circle"></i>
                                No se encontraron matches con score superior a 50%
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @empty
    <div class="card shadow">
        <div class="card-body text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No hay participantes aprobados disponibles para matching</h5>
            <p class="text-muted">Los participantes deben estar en estado "aprobado" para aparecer aquí.</p>
            <a href="{{ route('admin.intern-trainee.validations') }}" class="btn btn-primary">
                Ver Validaciones
            </a>
        </div>
    </div>
    @endforelse

    <!-- Pagination -->
    @if($participants->hasPages())
    <div class="d-flex justify-content-center">
        {{ $participants->appends(request()->query())->links() }}
    </div>
    @endif

    <!-- Available Companies Info -->
    <div class="card shadow mt-4">
        <div class="card-header py-3 bg-info text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-building"></i> Empresas Disponibles ({{ $companies->count() }})
            </h6>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($companies as $company)
                <div class="col-md-4 mb-3">
                    <div class="card border-left-info h-100">
                        <div class="card-body">
                            <h6 class="font-weight-bold text-primary">{{ $company->company_name }}</h6>
                            <p class="small mb-1">
                                <strong>Industria:</strong> {{ $company->industry_sector }}<br>
                                <strong>Ubicación:</strong> {{ $company->city }}, {{ $company->state }}<br>
                                <strong>Posiciones:</strong> {{ $company->positions_available }}
                            </p>
                            @if($company->is_verified)
                                <span class="badge badge-success badge-sm">Verificada</span>
                            @endif
                            <a href="{{ route('admin.intern-trainee.company.show', $company->id) }}" 
                               class="btn btn-sm btn-info mt-2">
                                Ver Detalles
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table-success td {
        background-color: #d4edda !important;
    }
    .table-info td {
        background-color: #d1ecf1 !important;
    }
</style>
@endpush
@endsection
