@extends('layouts.admin')

@section('title', 'Evaluaciones de Inglés')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-language"></i> Evaluaciones de Inglés
        </h1>
        <div>
            <a href="{{ route('admin.english-evaluations.dashboard') }}" class="btn btn-sm btn-info">
                <i class="fas fa-chart-bar"></i> Dashboard
            </a>
            <a href="{{ route('admin.english-evaluations.create') }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus"></i> Nueva Evaluación
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Evaluaciones
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['total'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Excelentes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['excellent'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Promedio General
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['average_score'], 1) }}/100
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Insuficientes
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['insufficient'] }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.english-evaluations.index') }}" method="GET" class="row">
                <div class="col-md-4 mb-3">
                    <label for="search">Buscar Participante</label>
                    <input type="text" name="search" id="search" class="form-control" 
                           value="{{ request('search') }}" placeholder="Nombre o email...">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="cefr_level">Nivel CEFR</label>
                    <select name="cefr_level" id="cefr_level" class="form-control">
                        <option value="">Todos</option>
                        <option value="A1" {{ request('cefr_level') == 'A1' ? 'selected' : '' }}>A1</option>
                        <option value="A2" {{ request('cefr_level') == 'A2' ? 'selected' : '' }}>A2</option>
                        <option value="B1" {{ request('cefr_level') == 'B1' ? 'selected' : '' }}>B1</option>
                        <option value="B2" {{ request('cefr_level') == 'B2' ? 'selected' : '' }}>B2</option>
                        <option value="C1" {{ request('cefr_level') == 'C1' ? 'selected' : '' }}>C1</option>
                        <option value="C2" {{ request('cefr_level') == 'C2' ? 'selected' : '' }}>C2</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="classification">Clasificación</label>
                    <select name="classification" id="classification" class="form-control">
                        <option value="">Todas</option>
                        <option value="EXCELLENT" {{ request('classification') == 'EXCELLENT' ? 'selected' : '' }}>Excelente</option>
                        <option value="GREAT" {{ request('classification') == 'GREAT' ? 'selected' : '' }}>Muy Bueno</option>
                        <option value="GOOD" {{ request('classification') == 'GOOD' ? 'selected' : '' }}>Bueno</option>
                        <option value="INSUFFICIENT" {{ request('classification') == 'INSUFFICIENT' ? 'selected' : '' }}>Insuficiente</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Evaluations Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                Lista de Evaluaciones ({{ $evaluations->total() }})
            </h6>
        </div>
        <div class="card-body">
            @if($evaluations->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Participante</th>
                                <th>Intento</th>
                                <th>Fecha</th>
                                <th>Puntaje</th>
                                <th>Nivel CEFR</th>
                                <th>Clasificación</th>
                                <th>Evaluador</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($evaluations as $evaluation)
                                <tr>
                                    <td>
                                        <strong>{{ $evaluation->user->name }}</strong><br>
                                        <small class="text-muted">{{ $evaluation->user->email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            Intento {{ $evaluation->attempt_number }}/3
                                        </span>
                                    </td>
                                    <td>{{ $evaluation->evaluated_at->format('d/m/Y') }}</td>
                                    <td>
                                        <strong class="text-{{ $evaluation->score >= 80 ? 'success' : ($evaluation->score >= 60 ? 'primary' : 'danger') }}">
                                            {{ $evaluation->score }}/100
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info badge-lg">
                                            {{ $evaluation->cefr_level }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ 
                                            $evaluation->classification == 'EXCELLENT' ? 'success' : 
                                            ($evaluation->classification == 'GREAT' ? 'primary' : 
                                            ($evaluation->classification == 'GOOD' ? 'info' : 'warning')) 
                                        }}">
                                            {{ $evaluation->classification }}
                                        </span>
                                    </td>
                                    <td>{{ $evaluation->evaluated_by ?? 'Sistema' }}</td>
                                    <td>
                                        <a href="{{ route('admin.english-evaluations.show', $evaluation->id) }}" 
                                           class="btn btn-sm btn-info" title="Ver Detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.english-evaluations.destroy', $evaluation->id) }}" 
                                              method="POST" style="display: inline-block;"
                                              onsubmit="return confirm('¿Estás seguro de eliminar esta evaluación?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $evaluations->links() }}
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No se encontraron evaluaciones con los filtros aplicados.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
