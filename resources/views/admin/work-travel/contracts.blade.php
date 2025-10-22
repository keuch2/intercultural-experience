@extends('layouts.admin')

@section('title', 'Contratos Work & Travel')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestión de Contratos</h1>
        <a href="{{ route('admin.work-travel.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">Todos los Estados</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                        <option value="pending_signature" {{ request('status') == 'pending_signature' ? 'selected' : '' }}>Pendiente Firma</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completado</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="season" class="form-control">
                        <option value="">Todas las Temporadas</option>
                        <option value="summer" {{ request('season') == 'summer' ? 'selected' : '' }}>Summer</option>
                        <option value="winter" {{ request('season') == 'winter' ? 'selected' : '' }}>Winter</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Buscar..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Contracts Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Contratos ({{ $contracts->total() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Estudiante</th>
                            <th>Empleador</th>
                            <th>Posición</th>
                            <th>Período</th>
                            <th>Compensación</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contracts as $contract)
                        <tr>
                            <td>
                                <strong>{{ $contract->contract_number }}</strong>
                                <br>
                                <small class="badge badge-{{ $contract->contract_type == 'seasonal' ? 'warning' : 'info' }}">
                                    {{ ucfirst($contract->contract_type) }}
                                </small>
                            </td>
                            <td>
                                <strong>{{ $contract->user->name ?? 'N/A' }}</strong>
                                <br>
                                <small class="text-muted">{{ $contract->user->email ?? '' }}</small>
                            </td>
                            <td>
                                <strong>{{ $contract->employer->company_name ?? 'N/A' }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $contract->work_location_city }}, {{ $contract->work_location_state }}
                                </small>
                            </td>
                            <td>
                                <strong>{{ $contract->position_title }}</strong>
                                <br>
                                <small class="text-muted">{{ $contract->duration_weeks }} semanas</small>
                            </td>
                            <td>
                                <small>
                                    {{ $contract->start_date->format('d/m/Y') }}
                                    <br>
                                    {{ $contract->end_date->format('d/m/Y') }}
                                </small>
                                @if($contract->is_flexible_dates)
                                    <br><span class="badge badge-info">Flexible</span>
                                @endif
                            </td>
                            <td>
                                <strong>${{ number_format($contract->hourly_rate, 2) }}/hr</strong>
                                <br>
                                <small>{{ $contract->hours_per_week }} hrs/semana</small>
                                <br>
                                <small class="text-success">
                                    ~${{ number_format($contract->net_weekly_earnings, 2) }}/semana
                                </small>
                                
                                @if($contract->provides_housing)
                                    <br><span class="badge badge-info"><i class="fas fa-home"></i> Housing</span>
                                @endif
                            </td>
                            <td>
                                @if($contract->status == 'draft')
                                    <span class="badge badge-secondary">Borrador</span>
                                @elseif($contract->status == 'pending_signature')
                                    <span class="badge badge-warning">Pendiente Firma</span>
                                @elseif($contract->status == 'active')
                                    <span class="badge badge-success">Activo</span>
                                @elseif($contract->status == 'completed')
                                    <span class="badge badge-primary">Completado</span>
                                @else
                                    <span class="badge badge-danger">Cancelado</span>
                                @endif
                                
                                <br>
                                @if($contract->employer_verified && $contract->position_verified)
                                    <small class="text-success"><i class="fas fa-check"></i> Verificado</small>
                                @else
                                    <small class="text-warning"><i class="fas fa-clock"></i> Pendiente</small>
                                @endif
                                
                                @if($contract->contract_signed)
                                    <br><small class="text-success"><i class="fas fa-pen"></i> Firmado</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.work-travel.contract.show', $contract->id) }}" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($contract->status == 'draft' || $contract->status == 'pending_signature')
                                    <button type="button" class="btn btn-sm btn-success" 
                                            data-toggle="modal" 
                                            data-target="#verifyModal{{ $contract->id }}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>

                        <!-- Verify Modal -->
                        @if($contract->status == 'draft' || $contract->status == 'pending_signature')
                        <div class="modal fade" id="verifyModal{{ $contract->id }}">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.work-travel.contract.verify', $contract->id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Verificar Contrato</h5>
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-check">
                                                <input type="checkbox" name="employer_verified" value="1" 
                                                       class="form-check-input" {{ $contract->employer_verified ? 'checked' : '' }}>
                                                <label class="form-check-label">Empleador Verificado</label>
                                            </div>
                                            <div class="form-check">
                                                <input type="checkbox" name="position_verified" value="1" 
                                                       class="form-check-input" {{ $contract->position_verified ? 'checked' : '' }}>
                                                <label class="form-check-label">Posición Verificada</label>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="submit" class="btn btn-success">Verificar</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endif
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No se encontraron contratos</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $contracts->links() }}
        </div>
    </div>

    <!-- Stats -->
    <div class="row">
        <div class="col-md-2">
            <div class="card border-left-warning shadow py-2">
                <div class="card-body text-center">
                    <h5>{{ $contracts->where('status', 'pending_signature')->count() }}</h5>
                    <small>Pendientes</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-left-success shadow py-2">
                <div class="card-body text-center">
                    <h5>{{ $contracts->where('status', 'active')->count() }}</h5>
                    <small>Activos</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-left-primary shadow py-2">
                <div class="card-body text-center">
                    <h5>{{ $contracts->where('status', 'completed')->count() }}</h5>
                    <small>Completados</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-left-danger shadow py-2">
                <div class="card-body text-center">
                    <h5>{{ $contracts->where('status', 'cancelled')->count() }}</h5>
                    <small>Cancelados</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-info shadow py-2">
                <div class="card-body text-center">
                    <h5>${{ number_format($contracts->sum('estimated_total_earnings'), 2) }}</h5>
                    <small>Earnings Totales Estimados</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
