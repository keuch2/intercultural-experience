@extends('layouts.admin')

@section('title', 'Detalle del Contrato')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Contrato #{{ $contract->contract_number }}</h1>
        <div>
            @if($contract->status == 'draft' || $contract->status == 'pending_signature')
                <button type="button" class="btn btn-success" data-toggle="modal" data-target="#verifyModal">
                    <i class="fas fa-check"></i> Verificar
                </button>
            @endif
            <a href="{{ route('admin.work-travel.contracts') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <!-- Status Alert -->
    <div class="alert alert-{{ $contract->status == 'active' ? 'success' : ($contract->status == 'cancelled' ? 'danger' : 'warning') }}">
        <h5 class="alert-heading">
            Estado del Contrato: 
            <strong>{{ strtoupper(str_replace('_', ' ', $contract->status)) }}</strong>
        </h5>
        @if($contract->employer_verified && $contract->position_verified)
            <p class="mb-0"><i class="fas fa-check-circle"></i> Empleador y posición verificados</p>
        @endif
        @if($contract->contract_signed)
            <p class="mb-0"><i class="fas fa-signature"></i> Firmado el {{ $contract->signed_at->format('d/m/Y') }}</p>
        @endif
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-md-8">
            <!-- Participant Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información del Participante</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Nombre:</th>
                                    <td>{{ $contract->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $contract->user->email ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Teléfono:</th>
                                    <td>{{ $contract->user->phone ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Fecha Nacimiento:</th>
                                    <td>{{ $contract->user->date_of_birth ? \Carbon\Carbon::parse($contract->user->date_of_birth)->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Nacionalidad:</th>
                                    <td>{{ $contract->user->nationality ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Pasaporte:</th>
                                    <td>{{ $contract->user->passport_number ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employer Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información del Empleador</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Empresa:</th>
                                    <td>
                                        <a href="{{ route('admin.work-travel.employer.show', $contract->employer_id) }}">
                                            {{ $contract->employer->company_name ?? 'N/A' }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Contacto:</th>
                                    <td>{{ $contract->employer->contact_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $contract->employer->contact_email ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th width="40%">Teléfono:</th>
                                    <td>{{ $contract->employer->contact_phone ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Rating:</th>
                                    <td>
                                        @if($contract->employer->rating)
                                            <i class="fas fa-star text-warning"></i> {{ number_format($contract->employer->rating, 1) }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Verificado:</th>
                                    <td>
                                        @if($contract->employer->is_verified)
                                            <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                                        @else
                                            <span class="badge badge-warning">No</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Job Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detalles del Trabajo</h6>
                </div>
                <div class="card-body">
                    <h5>{{ $contract->position_title }}</h5>
                    <p class="text-muted">{{ $contract->job_description }}</p>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Ubicación:</h6>
                            <address>
                                {{ $contract->work_location_address }}<br>
                                {{ $contract->work_location_city }}, {{ $contract->work_location_state }} {{ $contract->work_location_zip }}
                            </address>
                        </div>
                        <div class="col-md-6">
                            <h6>Período:</h6>
                            <p>
                                <strong>Inicio:</strong> {{ $contract->start_date->format('d/m/Y') }}<br>
                                <strong>Fin:</strong> {{ $contract->end_date->format('d/m/Y') }}<br>
                                <strong>Duración:</strong> {{ $contract->duration_weeks }} semanas
                                @if($contract->is_flexible_dates)
                                    <span class="badge badge-info">Fechas Flexibles</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compensation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Compensación</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm">
                                <tr>
                                    <th>Tarifa por Hora:</th>
                                    <td><strong>${{ number_format($contract->hourly_rate, 2) }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Horas por Semana:</th>
                                    <td>{{ $contract->hours_per_week }} hrs</td>
                                </tr>
                                @if($contract->overtime_rate)
                                <tr>
                                    <th>Tarifa Overtime:</th>
                                    <td>${{ number_format($contract->overtime_rate, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th>Frecuencia de Pago:</th>
                                    <td>{{ ucfirst($contract->payment_frequency) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <h6>Ganancias Estimadas</h6>
                                <h4>${{ number_format($contract->net_weekly_earnings, 2) }}/semana</h4>
                                <p class="mb-0">
                                    <strong>Total estimado:</strong> 
                                    ${{ number_format($contract->calculateTotalEarnings(), 2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($contract->deductions)
                        <h6 class="mt-3">Deducciones:</h6>
                        <ul>
                            @foreach($contract->deductions as $key => $value)
                                <li>{{ ucfirst($key) }}: {{ $value }}%</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>

            <!-- Benefits -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Beneficios Adicionales</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>
                                @if($contract->provides_housing)
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <i class="fas fa-times text-danger"></i>
                                @endif
                                Housing
                            </h6>
                            @if($contract->provides_housing && $contract->housing_cost_per_week)
                                <p class="text-muted">${{ number_format($contract->housing_cost_per_week, 2) }}/semana</p>
                            @endif
                            @if($contract->housing_details)
                                <small>{{ $contract->housing_details }}</small>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h6>
                                @if($contract->provides_meals)
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <i class="fas fa-times text-danger"></i>
                                @endif
                                Comidas
                            </h6>
                            @if($contract->meals_details)
                                <small>{{ $contract->meals_details }}</small>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <h6>
                                @if($contract->provides_transportation)
                                    <i class="fas fa-check text-success"></i>
                                @else
                                    <i class="fas fa-times text-danger"></i>
                                @endif
                                Transporte
                            </h6>
                            @if($contract->transportation_details)
                                <small>{{ $contract->transportation_details }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Contract Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información del Contrato</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Número:</th>
                            <td>{{ $contract->contract_number }}</td>
                        </tr>
                        <tr>
                            <th>Tipo:</th>
                            <td>
                                <span class="badge badge-info">{{ ucfirst($contract->contract_type) }}</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                @if($contract->status == 'active')
                                    <span class="badge badge-success">Activo</span>
                                @elseif($contract->status == 'pending_signature')
                                    <span class="badge badge-warning">Pendiente Firma</span>
                                @elseif($contract->status == 'completed')
                                    <span class="badge badge-primary">Completado</span>
                                @elseif($contract->status == 'cancelled')
                                    <span class="badge badge-danger">Cancelado</span>
                                @else
                                    <span class="badge badge-secondary">Borrador</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Creado:</th>
                            <td>{{ $contract->created_at->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Verification Status -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estado de Verificación</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Empleador:</strong><br>
                        @if($contract->employer_verified)
                            <span class="badge badge-success"><i class="fas fa-check"></i> Verificado</span>
                        @else
                            <span class="badge badge-danger"><i class="fas fa-times"></i> No Verificado</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <strong>Posición:</strong><br>
                        @if($contract->position_verified)
                            <span class="badge badge-success"><i class="fas fa-check"></i> Verificada</span>
                        @else
                            <span class="badge badge-danger"><i class="fas fa-times"></i> No Verificada</span>
                        @endif
                    </div>

                    @if($contract->verified_by)
                        <div class="mb-3">
                            <strong>Verificado por:</strong><br>
                            {{ $contract->verifier->name ?? 'N/A' }}
                            @if($contract->verification_date)
                                <br><small class="text-muted">{{ $contract->verification_date->format('d/m/Y H:i') }}</small>
                            @endif
                        </div>
                    @endif

                    <div class="mb-3">
                        <strong>Firmado:</strong><br>
                        @if($contract->contract_signed)
                            <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                            <br><small class="text-muted">{{ $contract->signed_at->format('d/m/Y') }}</small>
                        @else
                            <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Documents -->
            @if($contract->contract_pdf_path || $contract->job_offer_letter_path || $contract->signature_path)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Documentos</h6>
                </div>
                <div class="card-body">
                    @if($contract->contract_pdf_path)
                        <a href="{{ Storage::url($contract->contract_pdf_path) }}" target="_blank" class="btn btn-sm btn-info btn-block mb-2">
                            <i class="fas fa-file-pdf"></i> Ver Contrato
                        </a>
                    @endif
                    @if($contract->job_offer_letter_path)
                        <a href="{{ Storage::url($contract->job_offer_letter_path) }}" target="_blank" class="btn btn-sm btn-info btn-block mb-2">
                            <i class="fas fa-file-alt"></i> Carta de Oferta
                        </a>
                    @endif
                    @if($contract->signature_path)
                        <a href="{{ Storage::url($contract->signature_path) }}" target="_blank" class="btn btn-sm btn-info btn-block">
                            <i class="fas fa-signature"></i> Ver Firma
                        </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Actions -->
            @if($contract->status == 'active')
            <div class="card shadow bg-danger text-white">
                <div class="card-body">
                    <h6>Cancelar Contrato</h6>
                    <p class="small">Esta acción no se puede deshacer</p>
                    <button type="button" class="btn btn-light btn-block" data-toggle="modal" data-target="#cancelModal">
                        Cancelar Contrato
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Verify Modal -->
@if($contract->status == 'draft' || $contract->status == 'pending_signature')
<div class="modal fade" id="verifyModal">
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
                        <input type="checkbox" name="employer_verified" value="1" class="form-check-input" 
                               {{ $contract->employer_verified ? 'checked' : '' }}>
                        <label class="form-check-label">Empleador Verificado</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" name="position_verified" value="1" class="form-check-input" 
                               {{ $contract->position_verified ? 'checked' : '' }}>
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

<!-- Cancel Modal -->
@if($contract->status == 'active')
<div class="modal fade" id="cancelModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.work-travel.contract.cancel', $contract->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Cancelar Contrato</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-danger"><strong>Advertencia:</strong> Esta acción no se puede deshacer.</p>
                    <div class="form-group">
                        <label>Razón de Cancelación *</label>
                        <textarea name="cancellation_reason" class="form-control" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No, mantener</button>
                    <button type="submit" class="btn btn-danger">Sí, cancelar contrato</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
