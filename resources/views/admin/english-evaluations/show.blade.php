@extends('layouts.admin')

@section('title', 'Detalle de Evaluación')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-alt"></i> Evaluación de Inglés
        </h1>
        <a href="{{ route('admin.english-evaluations.index') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <div class="row">
        <!-- Información del Participante -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Participante</h6>
                </div>
                <div class="card-body text-center">
                    <img class="img-profile rounded-circle mb-3" 
                         src="https://ui-avatars.com/api/?name={{ urlencode($evaluation->user->name) }}&background=4e73df&color=ffffff&size=150" 
                         width="150" height="150">
                    <h4>{{ $evaluation->user->name }}</h4>
                    <p class="text-muted">{{ $evaluation->user->email }}</p>
                    @if($evaluation->user->phone)
                        <p><i class="fas fa-phone"></i> {{ $evaluation->user->phone }}</p>
                    @endif
                </div>
            </div>

            <!-- Estadísticas del Participante -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estadísticas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Total de Evaluaciones</small>
                        <h5>{{ $userEvaluations->count() }}/3</h5>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Mejor Puntaje</small>
                        <h5>{{ $userEvaluations->max('score') }}/100</h5>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Promedio</small>
                        <h5>{{ number_format($userEvaluations->avg('score'), 1) }}/100</h5>
                    </div>
                    @if($userEvaluations->count() < 3)
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-info-circle"></i>
                            Quedan {{ 3 - $userEvaluations->count() }} intento(s)
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-check-circle"></i>
                            Intentos completados
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Detalles de la Evaluación -->
        <div class="col-lg-8">
            <!-- Resultado Principal -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Resultado - Intento {{ $evaluation->attempt_number }}
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4 text-center">
                            <h2 class="text-{{ $evaluation->score >= 80 ? 'success' : ($evaluation->score >= 60 ? 'primary' : 'danger') }}">
                                {{ $evaluation->score }}/100
                            </h2>
                            <small class="text-muted">Puntaje General</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <h2 class="text-info">{{ $evaluation->cefr_level }}</h2>
                            <small class="text-muted">Nivel CEFR</small>
                        </div>
                        <div class="col-md-4 text-center">
                            <span class="badge badge-lg badge-{{ 
                                $evaluation->classification == 'EXCELLENT' ? 'success' : 
                                ($evaluation->classification == 'GREAT' ? 'primary' : 
                                ($evaluation->classification == 'GOOD' ? 'info' : 'warning')) 
                            }}">
                                {{ $evaluation->classification }}
                            </span>
                            <br>
                            <small class="text-muted">Clasificación</small>
                        </div>
                    </div>

                    <hr>

                    <!-- Puntajes por Habilidad -->
                    @if($evaluation->listening_score || $evaluation->reading_score || $evaluation->writing_score || $evaluation->speaking_score)
                        <h6 class="font-weight-bold text-primary mb-3">Puntajes por Habilidad</h6>
                        <div class="row mb-4">
                            @if($evaluation->listening_score)
                                <div class="col-md-6 mb-3">
                                    <label class="mb-1">
                                        <i class="fas fa-headphones text-primary"></i> Listening
                                    </label>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-primary" role="progressbar" 
                                             style="width: {{ $evaluation->listening_score }}%">
                                            {{ $evaluation->listening_score }}/100
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($evaluation->reading_score)
                                <div class="col-md-6 mb-3">
                                    <label class="mb-1">
                                        <i class="fas fa-book-open text-success"></i> Reading
                                    </label>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ $evaluation->reading_score }}%">
                                            {{ $evaluation->reading_score }}/100
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($evaluation->writing_score)
                                <div class="col-md-6 mb-3">
                                    <label class="mb-1">
                                        <i class="fas fa-pen text-info"></i> Writing
                                    </label>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-info" role="progressbar" 
                                             style="width: {{ $evaluation->writing_score }}%">
                                            {{ $evaluation->writing_score }}/100
                                        </div>
                                    </div>
                                </div>
                            @endif
                            
                            @if($evaluation->speaking_score)
                                <div class="col-md-6 mb-3">
                                    <label class="mb-1">
                                        <i class="fas fa-comments text-warning"></i> Speaking
                                    </label>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar bg-warning" role="progressbar" 
                                             style="width: {{ $evaluation->speaking_score }}%">
                                            {{ $evaluation->speaking_score }}/100
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Información Adicional -->
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Fecha de Evaluación:</strong><br>
                            {{ $evaluation->evaluated_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-md-6">
                            <strong>Evaluador:</strong><br>
                            {{ $evaluation->evaluated_by ?? 'Sistema' }}
                        </div>
                    </div>

                    @if($evaluation->notes)
                        <hr>
                        <div>
                            <strong class="text-primary">Notas / Observaciones:</strong>
                            <p class="mt-2">{{ $evaluation->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Historial de Evaluaciones -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Historial de Evaluaciones ({{ $userEvaluations->count() }}/3)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Intento</th>
                                    <th>Fecha</th>
                                    <th>Puntaje</th>
                                    <th>Nivel CEFR</th>
                                    <th>Clasificación</th>
                                    <th>Evaluador</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($userEvaluations as $eval)
                                    <tr class="{{ $eval->id == $evaluation->id ? 'table-active' : '' }}">
                                        <td>
                                            <strong>{{ $eval->attempt_number }}</strong>
                                            @if($eval->id == $evaluation->id)
                                                <span class="badge badge-primary ml-2">Actual</span>
                                            @endif
                                        </td>
                                        <td>{{ $eval->evaluated_at->format('d/m/Y') }}</td>
                                        <td>
                                            <strong class="text-{{ $eval->score >= 80 ? 'success' : ($eval->score >= 60 ? 'primary' : 'danger') }}">
                                                {{ $eval->score }}/100
                                            </strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $eval->cefr_level }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ 
                                                $eval->classification == 'EXCELLENT' ? 'success' : 
                                                ($eval->classification == 'GREAT' ? 'primary' : 
                                                ($eval->classification == 'GOOD' ? 'info' : 'warning')) 
                                            }}">
                                                {{ $eval->classification }}
                                            </span>
                                        </td>
                                        <td>{{ $eval->evaluated_by ?? 'Sistema' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($userEvaluations->count() < 3)
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle"></i>
                            El participante puede realizar {{ 3 - $userEvaluations->count() }} evaluación(es) más.
                            <a href="{{ route('admin.english-evaluations.create') }}" class="btn btn-sm btn-primary ml-3">
                                <i class="fas fa-plus"></i> Registrar Nueva Evaluación
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.badge-lg {
    font-size: 1.2rem;
    padding: 0.6rem 1.2rem;
}
</style>
@endsection
