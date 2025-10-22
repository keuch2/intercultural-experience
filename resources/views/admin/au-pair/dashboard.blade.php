@extends('layouts.admin')

@section('title', 'Dashboard Au Pair')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-heart text-danger"></i> Dashboard Au Pair Program
        </h1>
        <div>
            <a href="{{ route('admin.au-pair.profiles') }}" class="btn btn-primary">
                <i class="fas fa-users"></i> Ver Perfiles
            </a>
            <a href="{{ route('admin.au-pair.families.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Nueva Familia
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Perfiles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_profiles'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-circle fa-2x text-gray-300"></i>
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
                                Perfiles Activos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['active_profiles'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Matches Exitosos</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['matched_profiles'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-heart fa-2x text-gray-300"></i>
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
                                Familias Registradas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total_families'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-home fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Distribución por Estado -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i> Distribución por Estado
                    </h6>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 300px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                    <div class="mt-3">
                        @foreach($profilesByStatus as $status => $count)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>
                                @switch($status)
                                    @case('draft')
                                        <span class="badge badge-secondary">Borrador</span>
                                        @break
                                    @case('pending')
                                        <span class="badge badge-warning">Pendiente Revisión</span>
                                        @break
                                    @case('active')
                                        <span class="badge badge-success">Activo</span>
                                        @break
                                    @case('matched')
                                        <span class="badge badge-info">Match Confirmado</span>
                                        @break
                                    @case('inactive')
                                        <span class="badge badge-dark">Inactivo</span>
                                        @break
                                @endswitch
                            </span>
                            <strong>{{ $count }}</strong>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Perfiles Incompletos -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-exclamation-triangle"></i> Perfiles Incompletos
                    </h6>
                    <a href="{{ route('admin.au-pair.profiles') }}?complete=no" class="btn btn-sm btn-warning">
                        Ver Todos
                    </a>
                </div>
                <div class="card-body">
                    @if($incompleteProfiles->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Participante</th>
                                        <th>Email</th>
                                        <th>Fecha</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incompleteProfiles as $profile)
                                    <tr>
                                        <td>{{ $profile->user->name }}</td>
                                        <td>{{ $profile->user->email }}</td>
                                        <td>{{ $profile->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('admin.au-pair.profile.show', $profile->id) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center">No hay perfiles incompletos</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Matches Recientes -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-success">
                <i class="fas fa-heart"></i> Matches Recientes
            </h6>
            <a href="{{ route('admin.au-pair.matching') }}" class="btn btn-sm btn-success">
                Ver Sistema de Matching
            </a>
        </div>
        <div class="card-body">
            @if($recentMatches->count() > 0)
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Au Pair</th>
                                <th>Familia</th>
                                <th>Ubicación</th>
                                <th>Fecha Match</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentMatches as $match)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $match->auPairProfile->user->name }}</strong><br>
                                        <small class="text-muted">{{ $match->auPairProfile->user->country ?? 'N/A' }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $match->familyProfile->family_name }}</strong><br>
                                        <small class="text-muted">{{ $match->familyProfile->number_of_children }} niño(s)</small>
                                    </div>
                                </td>
                                <td>
                                    {{ $match->familyProfile->city }}, {{ $match->familyProfile->state }}
                                </td>
                                <td>
                                    {{ $match->matched_at->format('d/m/Y H:i') }}
                                </td>
                                <td>
                                    <a href="{{ route('admin.au-pair.profile.show', $match->auPairProfile->id) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-muted text-center">No hay matches recientes</p>
            @endif
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-3x text-info mb-3"></i>
                    <h5>Pendientes de Revisión</h5>
                    <h2 class="text-info">{{ $stats['pending_review'] }}</h2>
                    <a href="{{ route('admin.au-pair.profiles') }}?status=pending" class="btn btn-sm btn-info mt-2">
                        Revisar Ahora
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <i class="fas fa-handshake fa-3x text-warning mb-3"></i>
                    <h5>Matches Pendientes</h5>
                    <h2 class="text-warning">{{ $stats['pending_matches'] }}</h2>
                    <a href="{{ route('admin.au-pair.matching') }}" class="btn btn-sm btn-warning mt-2">
                        Gestionar Matches
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <i class="fas fa-percentage fa-3x text-success mb-3"></i>
                    <h5>Tasa de Éxito</h5>
                    <h2 class="text-success">
                        @if($stats['total_profiles'] > 0)
                            {{ round(($stats['matched_profiles'] / $stats['total_profiles']) * 100, 1) }}%
                        @else
                            0%
                        @endif
                    </h2>
                    <a href="{{ route('admin.au-pair.stats') }}" class="btn btn-sm btn-success mt-2">
                        Ver Estadísticas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Chart
    const statusData = @json($profilesByStatus);
    
    const ctx = document.getElementById('statusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(statusData).map(status => {
                const labels = {
                    'draft': 'Borrador',
                    'pending': 'Pendiente',
                    'active': 'Activo',
                    'matched': 'Matched',
                    'inactive': 'Inactivo'
                };
                return labels[status] || status;
            }),
            datasets: [{
                data: Object.values(statusData),
                backgroundColor: [
                    '#6c757d',
                    '#ffc107',
                    '#28a745',
                    '#17a2b8',
                    '#343a40'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false, // Desactivar animaciones para mejor rendimiento
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>
@endpush
