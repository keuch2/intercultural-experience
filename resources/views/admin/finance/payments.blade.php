@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Gestión de Pagos</h1>
        <div>
            <a href="{{ route('admin.finance.payments.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Registrar Pago Manual
            </a>
            <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm ml-2">
                <i class="fas fa-download fa-sm text-white-50"></i> Exportar
            </a>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filtros</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.finance.payments') }}" class="row">
                <div class="col-md-3 mb-3">
                    <label for="status">Estado</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verificado</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="program_id">Programa</label>
                    <select name="program_id" id="program_id" class="form-control">
                        <option value="">Todos</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="date_from">Fecha Desde</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="date_to">Fecha Hasta</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">Filtrar</button>
                    <a href="{{ route('admin.finance.payments') }}" class="btn btn-secondary ml-2">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Pagos -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Pagos Registrados</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow: visible;">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Programa</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Referencia</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>
                                {{ $payment->user_name }}
                                <small class="d-block text-muted">{{ $payment->email }}</small>
                            </td>
                            <td>{{ $payment->program_name }}</td>
                            <td>{{ $payment->requisite_name }}</td>
                            <td>{{ ($payment->currency_symbol ?? '₲') }} {{ number_format((float) $payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_method ?? 'No especificado' }}</td>
                            <td>{{ $payment->payment_reference ?? $payment->file_path }}</td>
                            <td>
                                @if($payment->status == 'pending')
                                    <span class="badge badge-warning">Pendiente</span>
                                @elseif($payment->status == 'verified')
                                    <span class="badge badge-success">Verificado</span>
                                @elseif($payment->status == 'rejected')
                                    <span class="badge badge-danger">Rechazado</span>
                                @endif
                            </td>
                            <td>{{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('d/m/Y H:i') : 'N/A' }}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-edit"></i> Cambiar Estado
                                    </button>
                                    <ul class="dropdown-menu">
                                        @if($payment->status != 'pending')
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#pendingModal{{ $payment->id }}">
                                                <i class="fas fa-clock text-warning"></i> Marcar como Pendiente
                                            </a>
                                        </li>
                                        @endif
                                        @if($payment->status != 'verified')
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $payment->id }}">
                                                <i class="fas fa-check text-success"></i> Verificar
                                            </a>
                                        </li>
                                        @endif
                                        @if($payment->status != 'rejected')
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $payment->id }}">
                                                <i class="fas fa-times text-danger"></i> Rechazar
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                                
                                <!-- Modal Verificar -->
                                <div class="modal fade" id="verifyModal{{ $payment->id }}" tabindex="-1" aria-labelledby="verifyModalLabel{{ $payment->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="verifyModalLabel{{ $payment->id }}">Verificar Pago</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Estás seguro de que deseas verificar este pago?</p>
                                                <p><strong>Usuario:</strong> {{ $payment->user_name ?? 'No disponible' }}</p>
                                                <p><strong>Concepto:</strong> {{ $payment->requisite_name ?? 'No disponible' }}</p>
                                                <p><strong>Monto:</strong> {{ ($payment->currency_symbol ?? '₲') }} {{ number_format((float) ($payment->amount ?? 0), 2) }}</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <form action="{{ route('admin.finance.payments.verify', $payment->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success">Verificar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Modal Rechazar -->
                                <div class="modal fade" id="rejectModal{{ $payment->id }}" tabindex="-1" aria-labelledby="rejectModalLabel{{ $payment->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="rejectModalLabel{{ $payment->id }}">Rechazar Pago</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.finance.payments.reject', $payment->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>¿Estás seguro de que deseas rechazar este pago?</p>
                                                    <p><strong>Usuario:</strong> {{ $payment->user_name ?? 'No disponible' }}</p>
                                                    <p><strong>Concepto:</strong> {{ $payment->requisite_name ?? 'No disponible' }}</p>
                                                    <p><strong>Monto:</strong> {{ ($payment->currency_symbol ?? '₲') }} {{ number_format((float) ($payment->amount ?? 0), 2) }}</p>
                                                    
                                                    <div class="mb-3">
                                                        <label for="rejection_reason" class="form-label">Motivo del rechazo</label>
                                                        <textarea class="form-control" id="rejection_reason" name="rejection_reason" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-danger">Rechazar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Modal Marcar como Pendiente -->
                                <div class="modal fade" id="pendingModal{{ $payment->id }}" tabindex="-1" aria-labelledby="pendingModalLabel{{ $payment->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="pendingModalLabel{{ $payment->id }}">Marcar como Pendiente</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('admin.finance.payments.pending', $payment->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <p>¿Estás seguro de que deseas marcar este pago como pendiente?</p>
                                                    <p><strong>Usuario:</strong> {{ $payment->user_name ?? 'No disponible' }}</p>
                                                    <p><strong>Concepto:</strong> {{ $payment->requisite_name ?? 'No disponible' }}</p>
                                                    <p><strong>Monto:</strong> {{ ($payment->currency_symbol ?? '₲') }} {{ number_format((float) ($payment->amount ?? 0), 2) }}</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-warning">Marcar como Pendiente</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div class="d-flex justify-content-center mt-4">
                {{ $payments->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
