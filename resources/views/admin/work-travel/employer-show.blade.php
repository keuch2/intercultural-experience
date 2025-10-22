@extends('layouts.admin')

@section('title', 'Detalle del Empleador')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ $employer->company_name }}</h1>
        <div>
            @if(!$employer->is_verified)
                <form action="{{ route('admin.work-travel.employer.verify', $employer->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success" onclick="return confirm('¿Verificar este empleador?')">
                        <i class="fas fa-check"></i> Verificar
                    </button>
                </form>
            @endif
            <a href="{{ route('admin.work-travel.employers') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-primary">{{ $stats['active_contracts'] }}</h4>
                        <small>Contratos Activos</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-success">{{ $stats['total_contracts'] }}</h4>
                        <small>Total Contratos</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-info">{{ $stats['current_participants'] }}</h4>
                        <small>Participantes Actuales</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-center">
                        <h4 class="text-warning">{{ $employer->positions_available }}</h4>
                        <small>Posiciones Disponibles</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Company Info -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información de la Empresa</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Tipo de Negocio:</th>
                                    <td>{{ $employer->business_type }}</td>
                                </tr>
                                <tr>
                                    <th>EIN:</th>
                                    <td>{{ $employer->ein_number ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Establecida:</th>
                                    <td>{{ $employer->established_year ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>N° Empleados:</th>
                                    <td>{{ $employer->number_of_employees ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Años en Programa:</th>
                                    <td>{{ $employer->years_in_program }} años</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Dirección:</th>
                                    <td>{{ $employer->address }}</td>
                                </tr>
                                <tr>
                                    <th>Ciudad, Estado:</th>
                                    <td>{{ $employer->city }}, {{ $employer->state }} {{ $employer->zip_code }}</td>
                                </tr>
                                <tr>
                                    <th>Teléfono:</th>
                                    <td>{{ $employer->phone }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $employer->email }}</td>
                                </tr>
                                <tr>
                                    <th>Website:</th>
                                    <td>
                                        @if($employer->website)
                                            <a href="{{ $employer->website }}" target="_blank">{{ $employer->website }}</a>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <h6>Contacto Principal</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Nombre:</th>
                                    <td>{{ $employer->contact_name }}</td>
                                </tr>
                                <tr>
                                    <th>Cargo:</th>
                                    <td>{{ $employer->contact_title }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Teléfono:</th>
                                    <td>{{ $employer->contact_phone }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $employer->contact_email }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contracts -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contratos ({{ $employer->contracts->count() }})</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Número</th>
                                    <th>Participante</th>
                                    <th>Posición</th>
                                    <th>Período</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employer->contracts as $contract)
                                <tr>
                                    <td>{{ $contract->contract_number }}</td>
                                    <td>{{ $contract->user->name ?? 'N/A' }}</td>
                                    <td>{{ $contract->position_title }}</td>
                                    <td>
                                        <small>
                                            {{ $contract->start_date->format('d/m/Y') }} - 
                                            {{ $contract->end_date->format('d/m/Y') }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($contract->status == 'active')
                                            <span class="badge badge-success">Activo</span>
                                        @elseif($contract->status == 'pending_signature')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($contract->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.work-travel.contract.show', $contract->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay contratos registrados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estado</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Estado General:</strong><br>
                        @if($employer->is_active)
                            <span class="badge badge-success badge-lg">Activo</span>
                        @else
                            <span class="badge badge-secondary badge-lg">Inactivo</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <strong>Verificación:</strong><br>
                        @if($employer->is_verified)
                            <span class="badge badge-success badge-lg">Verificado</span>
                            @if($employer->verification_date)
                                <br><small class="text-muted">{{ $employer->verification_date->format('d/m/Y') }}</small>
                            @endif
                        @else
                            <span class="badge badge-warning badge-lg">Pendiente</span>
                        @endif
                    </div>

                    @if($employer->is_blacklisted)
                        <div class="alert alert-danger">
                            <strong>BLOQUEADO</strong><br>
                            <small>{{ $employer->blacklist_reason }}</small>
                        </div>
                    @endif

                    @if($employer->rating)
                        <div class="mb-3">
                            <strong>Rating:</strong><br>
                            <h4 class="text-warning mb-0">
                                <i class="fas fa-star"></i> {{ number_format($employer->rating, 1) }}
                            </h4>
                            <small class="text-muted">{{ $employer->total_reviews }} reviews</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Program Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información del Programa</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Participantes Totales:</th>
                            <td>{{ $employer->participants_hired_total }}</td>
                        </tr>
                        <tr>
                            <th>Este Año:</th>
                            <td>{{ $employer->participants_hired_this_year }}</td>
                        </tr>
                        <tr>
                            <th>Posiciones:</th>
                            <td>{{ $employer->positions_available }}</td>
                        </tr>
                    </table>

                    @if($employer->job_categories)
                        <strong>Categorías de Trabajo:</strong><br>
                        @foreach($employer->job_categories as $category)
                            <span class="badge badge-light">{{ $category }}</span>
                        @endforeach
                    @endif

                    <div class="mt-3">
                        <strong>Temporadas:</strong><br>
                        @if($employer->seasons_hiring)
                            @foreach($employer->seasons_hiring as $season)
                                @if($season == 'summer')
                                    <span class="badge badge-warning"><i class="fas fa-sun"></i> Summer</span>
                                @else
                                    <span class="badge badge-info"><i class="fas fa-snowflake"></i> Winter</span>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>

            <!-- Compliance -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Cumplimiento</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>E-Verify:</th>
                            <td>
                                @if($employer->e_verify_enrolled)
                                    <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Workers Comp:</th>
                            <td>
                                @if($employer->workers_comp_insurance)
                                    <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Liability Insurance:</th>
                            <td>
                                @if($employer->liability_insurance)
                                    <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                @else
                                    <span class="badge badge-danger"><i class="fas fa-times"></i></span>
                                @endif
                            </td>
                        </tr>
                    </table>

                    @if($employer->last_audit_date)
                        <small class="text-muted">
                            Última auditoría: {{ $employer->last_audit_date->format('d/m/Y') }}
                        </small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
