@extends('layouts.admin')

@section('title', 'Sistema de Matching Au Pair')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-heart text-danger"></i> Sistema de Matching Au Pair
        </h1>
        <a href="{{ route('admin.au-pair.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>

    <!-- Filtros de Matching -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-gradient-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-filter"></i> Criterios de Matching
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.au-pair.matching') }}">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Au Pair:</label>
                            <select name="au_pair" class="form-control">
                                <option value="">-- Seleccionar Au Pair --</option>
                                @if(isset($auPairs))
                                    @foreach($auPairs as $auPair)
                                        <option value="{{ $auPair->id }}" {{ request('au_pair') == $auPair->id ? 'selected' : '' }}>
                                            {{ $auPair->user->name }} ({{ $auPair->user->country }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Familia:</label>
                            <select name="family" class="form-control">
                                <option value="">-- Seleccionar Familia --</option>
                                @if(isset($families))
                                    @foreach($families as $family)
                                        <option value="{{ $family->id }}" {{ request('family') == $family->id ? 'selected' : '' }}>
                                            {{ $family->family_name }} ({{ $family->city }}, {{ $family->state }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Estado del Match:</label>
                            <select name="status" class="form-control">
                                <option value="">Todos</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="interested" {{ request('status') == 'interested' ? 'selected' : '' }}>Interesados</option>
                                <option value="matched" {{ request('status') == 'matched' ? 'selected' : '' }}>Confirmado</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Buscar Matches
                        </button>
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#suggestMatchModal">
                            <i class="fas fa-magic"></i> Sugerir Match Automático
                        </button>
                        <a href="{{ route('admin.au-pair.matching') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpiar Filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Matches Activos -->
    <div class="row">
        <!-- Matches Pendientes -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-left-warning">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-clock"></i> Matches Pendientes
                    </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if(isset($pendingMatches) && $pendingMatches->count() > 0)
                        @foreach($pendingMatches as $match)
                            <div class="card mb-3">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $match->auPairProfile->user->name }}</h6>
                                            <small class="text-muted">{{ $match->auPairProfile->user->country }}</small>
                                        </div>
                                        <i class="fas fa-arrow-right text-muted"></i>
                                        <div class="text-right">
                                            <h6 class="mb-1">{{ $match->familyProfile->family_name }}</h6>
                                            <small class="text-muted">{{ $match->familyProfile->state }}</small>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between">
                                        <span class="badge badge-warning">
                                            Au Pair: {{ ucfirst($match->au_pair_status) }}
                                        </span>
                                        <span class="badge badge-info">
                                            Familia: {{ ucfirst($match->family_status) }}
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-primary btn-block" 
                                                onclick="viewMatchDetails({{ $match->id }})">
                                            <i class="fas fa-eye"></i> Ver Detalles
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No hay matches pendientes</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Matches Mutuamente Interesados -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-left-info">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-handshake"></i> Mutuamente Interesados
                    </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if(isset($interestedMatches) && $interestedMatches->count() > 0)
                        @foreach($interestedMatches as $match)
                            <div class="card mb-3 border-info">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $match->auPairProfile->user->name }}</h6>
                                            <small class="text-muted">{{ $match->auPairProfile->user->country }}</small>
                                        </div>
                                        <i class="fas fa-heart text-danger"></i>
                                        <div class="text-right">
                                            <h6 class="mb-1">{{ $match->familyProfile->family_name }}</h6>
                                            <small class="text-muted">{{ $match->familyProfile->state }}</small>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="text-center">
                                        <small class="text-muted">
                                            <i class="fas fa-comments"></i> {{ $match->messages_count }} mensajes<br>
                                            <i class="fas fa-video"></i> {{ $match->video_calls_count }} videollamadas
                                        </small>
                                    </div>
                                    <div class="mt-2">
                                        <form action="{{ route('admin.au-pair.matching.confirm', $match->id) }}" 
                                              method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success btn-block"
                                                    onclick="return confirm('¿Confirmar este match?')">
                                                <i class="fas fa-check"></i> Confirmar Match
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No hay matches con interés mutuo</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Matches Confirmados -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow border-left-success">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-check-circle"></i> Matches Confirmados
                    </h6>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @if(isset($confirmedMatches) && $confirmedMatches->count() > 0)
                        @foreach($confirmedMatches as $match)
                            <div class="card mb-3 border-success">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $match->auPairProfile->user->name }}</h6>
                                            <small class="text-muted">{{ $match->auPairProfile->user->country }}</small>
                                        </div>
                                        <i class="fas fa-check-circle text-success"></i>
                                        <div class="text-right">
                                            <h6 class="mb-1">{{ $match->familyProfile->family_name }}</h6>
                                            <small class="text-muted">{{ $match->familyProfile->state }}</small>
                                        </div>
                                    </div>
                                    <hr class="my-2">
                                    <div class="text-center">
                                        <span class="badge badge-success">
                                            Confirmado: {{ $match->matched_at->format('d/m/Y') }}
                                        </span>
                                    </div>
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-info btn-block"
                                                onclick="viewMatchDetails({{ $match->id }})">
                                            <i class="fas fa-file-contract"></i> Ver Contrato
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center">No hay matches confirmados</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Matriz de Compatibilidad -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-th"></i> Matriz de Compatibilidad
            </h6>
        </div>
        <div class="card-body">
            @if(isset($compatibilityMatrix) && count($compatibilityMatrix) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Au Pair / Familia</th>
                                @foreach($families as $family)
                                    <th class="text-center" style="writing-mode: vertical-lr;">
                                        {{ $family->family_name }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auPairs as $auPair)
                            <tr>
                                <th>{{ $auPair->user->name }}</th>
                                @foreach($families as $family)
                                    @php
                                        $compatibility = $compatibilityMatrix[$auPair->id][$family->id] ?? 0;
                                        $bgClass = '';
                                        if ($compatibility >= 80) $bgClass = 'bg-success text-white';
                                        elseif ($compatibility >= 60) $bgClass = 'bg-warning';
                                        elseif ($compatibility >= 40) $bgClass = 'bg-info text-white';
                                        else $bgClass = 'bg-light';
                                    @endphp
                                    <td class="text-center {{ $bgClass }}">
                                        {{ $compatibility }}%
                                        @if($compatibility >= 60)
                                            <br>
                                            <button class="btn btn-xs btn-primary mt-1"
                                                    onclick="createMatch({{ $auPair->id }}, {{ $family->id }})">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    <span class="badge badge-success">80-100% Excelente</span>
                    <span class="badge badge-warning">60-79% Bueno</span>
                    <span class="badge badge-info text-white">40-59% Regular</span>
                    <span class="badge badge-light">0-39% Bajo</span>
                </div>
            @else
                <p class="text-center text-muted">
                    Selecciona un Au Pair y/o Familia para ver la matriz de compatibilidad
                </p>
            @endif
        </div>
    </div>

    <!-- Estadísticas de Matching -->
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Matches</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ isset($stats) ? $stats['total_matches'] : 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-heart fa-2x text-gray-300"></i>
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
                                Tasa de Éxito</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ isset($stats) ? $stats['success_rate'] : 0 }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
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
                                Tiempo Promedio</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ isset($stats) ? $stats['avg_matching_days'] : 0 }} días
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                En Proceso</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ isset($stats) ? $stats['in_process'] : 0 }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hourglass-half fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Sugerir Match -->
<div class="modal fade" id="suggestMatchModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-magic"></i> Sugerir Match Automático
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.au-pair.matching.suggest') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Algoritmo de Matching:</label>
                        <select name="algorithm" class="form-control" required>
                            <option value="compatibility">Por Compatibilidad</option>
                            <option value="location">Por Ubicación</option>
                            <option value="experience">Por Experiencia</option>
                            <option value="balanced">Balanceado</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Número de Sugerencias:</label>
                        <input type="number" name="suggestions" class="form-control" 
                               value="5" min="1" max="10" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Compatibilidad Mínima:</label>
                        <input type="number" name="min_compatibility" class="form-control" 
                               value="60" min="0" max="100" required>
                        <small class="form-text text-muted">
                            Porcentaje mínimo de compatibilidad requerido
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-magic"></i> Generar Sugerencias
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function viewMatchDetails(matchId) {
    // Implementar vista de detalles
    window.location.href = '/admin/au-pair/matches/' + matchId;
}

function createMatch(auPairId, familyId) {
    if (confirm('¿Crear un match entre este Au Pair y esta familia?')) {
        // Implementar creación de match
        $.post('{{ route("admin.au-pair.matching.suggest") }}', {
            _token: '{{ csrf_token() }}',
            au_pair_id: auPairId,
            family_id: familyId
        }).done(function(response) {
            alert('Match creado exitosamente');
            location.reload();
        });
    }
}
</script>
@endpush
