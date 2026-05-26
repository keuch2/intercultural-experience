@extends('layouts.admin')

@section('content')
@php
    $costCurrency = $application->cost_currency ?? 'USD';
    $programLabel = optional($application->program)->name ?? '—';
    $costAppliedDate = $application->cost_manual_date ?? $application->cost_locked_at ?? $application->created_at;
    $progress = ($application->total_cost > 0) ? min(100, round(($verifiedTotal / $application->total_cost) * 100)) : 0;
@endphp

<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-file-invoice-dollar me-2"></i>
            Perfil Financiero
        </h1>
        <small class="text-muted">
            <strong>{{ optional($application->user)->name }}</strong>
            · {{ $programLabel }}
            · Año {{ $costAppliedDate ? \Illuminate\Support\Carbon::parse($costAppliedDate)->format('Y') : '—' }}
        </small>
    </div>
    <a href="{{ route('admin.payment-management.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-1"></i> Volver al listado
    </a>
</div>

@if(session('success'))<div class="alert alert-success py-2 px-3">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger py-2 px-3">{{ session('error') }}</div>@endif

{{-- Resumen --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card bg-primary text-white"><div class="card-body py-2">
            <small>Costo Total</small>
            <h4 class="mb-0">{{ $costCurrency }} {{ number_format($application->total_cost ?? 0, 2) }}</h4>
            @if($application->payment_deadline)
            <small><i class="fas fa-clock me-1"></i>Vence: {{ $application->payment_deadline->format('d/m/Y') }}</small>
            @endif
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white"><div class="card-body py-2">
            <small>Total Pagado (verificado)</small>
            <h4 class="mb-0">{{ $costCurrency }} {{ number_format($verifiedTotal, 2) }}</h4>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark"><div class="card-body py-2">
            <small>Saldo Pendiente</small>
            <h4 class="mb-0">{{ $costCurrency }} {{ number_format(max(($application->total_cost ?? 0) - $verifiedTotal, 0), 2) }}</h4>
        </div></div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white"><div class="card-body py-2">
            <small>Progreso</small>
            <h4 class="mb-0">{{ $progress }}%</h4>
            <div class="progress mt-1" style="height: 6px;">
                <div class="progress-bar bg-light" style="width: {{ $progress }}%"></div>
            </div>
        </div></div>
    </div>
</div>

{{-- Costo del Programa --}}
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-edit text-primary me-1"></i> Costo del Programa</h6></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.payment-management.update-cost', $application->id) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Costo Total <span class="text-danger">*</span></label>
                    <input type="number" name="total_cost" step="0.01" min="0" value="{{ $application->total_cost ?? '' }}" class="form-control form-control-sm" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Moneda <span class="text-danger">*</span></label>
                    <select name="cost_currency" class="form-select form-select-sm" required>
                        <option value="USD" {{ $costCurrency == 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="PYG" {{ $costCurrency == 'PYG' ? 'selected' : '' }}>PYG</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Fecha Límite (opcional)</label>
                    <input type="date" name="payment_deadline" value="{{ optional($application->payment_deadline)->format('Y-m-d') }}" class="form-control form-control-sm">
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Fecha del Costo (override)</label>
                    <input type="date" name="cost_manual_date" value="{{ optional($application->cost_manual_date)->format('Y-m-d') }}" class="form-control form-control-sm">
                    <small class="text-muted">Para cargar pagos de años anteriores</small>
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-save"></i></button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Plan de Cuotas --}}
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-calendar-check text-primary me-1"></i> Plan de Cuotas</h6>
        @if(!$installmentPlan && $application->total_cost > 0)
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#installmentPlanModal">
            <i class="fas fa-plus me-1"></i> Crear Plan
        </button>
        @endif
    </div>
    <div class="card-body">
        @if($installmentPlan)
            <div class="row g-2 mb-3">
                <div class="col-md-3"><small class="text-muted">Plan:</small> <strong>{{ $installmentPlan->plan_name }}</strong></div>
                <div class="col-md-3"><small class="text-muted">Total:</small> {{ number_format($installmentPlan->total_amount, 2) }} {{ optional($installmentPlan->currency)->code }}</div>
                <div class="col-md-3"><small class="text-muted">Cuotas:</small> {{ $installmentPlan->total_installments }}</div>
                <div class="col-md-3"><small class="text-muted">Estado:</small> <span class="badge bg-{{ $installmentPlan->status == 'active' ? 'primary' : ($installmentPlan->status == 'completed' ? 'success' : 'danger') }}">{{ ucfirst($installmentPlan->status) }}</span></div>
            </div>
            @php
                // Fase 3: pagos verificados aún no vinculados a ninguna cuota — disponibles para link manual.
                $linkedPaymentIds = $installmentPlan->installmentDetails->pluck('payment_id')->filter()->all();
                $availablePayments = $payments
                    ->where('status', 'verified')
                    ->whereNotIn('id', $linkedPaymentIds);
            @endphp
            <div class="table-responsive">
                <table class="table table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th><th>Vencimiento</th><th>Monto</th><th>Pagado el</th><th>Estado</th><th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($installmentPlan->installmentDetails->sortBy('installment_number') as $d)
                        <tr>
                            <td>{{ $d->installment_number }}</td>
                            <td>{{ optional($d->due_date)->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ number_format($d->amount, 2) }}</td>
                            <td>
                                @if($d->paid_date)
                                    {{ $d->paid_date->format('d/m/Y') }}
                                    @if($d->payment_id)
                                        <small class="text-muted d-block">Pago #{{ $d->payment_id }}</small>
                                    @endif
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $d->status == 'paid' ? 'success' : ($d->status == 'overdue' ? 'danger' : 'warning') }}">
                                    {{ ucfirst($d->status) }}
                                </span>
                            </td>
                            <td>
                                @if($d->status !== 'paid' && $availablePayments->isNotEmpty())
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-success py-0 dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-link me-1"></i>Marcar pagada
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><h6 class="dropdown-header small">Vincular con pago verificado</h6></li>
                                            @foreach($availablePayments as $p)
                                                <li>
                                                    <form method="POST" action="{{ route('admin.payment-management.mark-installment-paid', $d->id) }}" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="payment_id" value="{{ $p->id }}">
                                                        <button type="submit" class="dropdown-item small">
                                                            <strong>{{ number_format($p->amount, 2) }} {{ optional($p->currency)->code ?? '' }}</strong>
                                                            <span class="text-muted">— {{ $p->payment_date->format('d/m/Y') }}</span>
                                                            <small class="d-block text-muted">{{ $p->concept }} · ref: {{ $p->reference_number }}</small>
                                                        </button>
                                                    </form>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @elseif($d->status !== 'paid')
                                    <small class="text-muted">Sin pagos disponibles</small>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-muted small mb-0">No hay un plan de cuotas activo.</p>
        @endif
    </div>
</div>

{{-- Pagos Registrados (TODOS para este perfil) + botón Registrar Pago --}}
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-receipt text-primary me-1"></i> Pagos del Participante</h6>
        @if($application->total_cost > 0)
        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#registerPaymentModal">
            <i class="fas fa-plus me-1"></i> Registrar Pago
        </button>
        @endif
    </div>
    <div class="card-body">
        @if($payments->isEmpty())
            <p class="text-muted small mb-0">No hay pagos registrados.</p>
        @else
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Fecha</th><th>Concepto</th>
                        <th class="text-end">Monto</th><th>Método</th><th>Referencia</th>
                        <th>Estado</th><th>Verificado por</th><th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($payments as $p)
                <tr>
                    <td>#{{ $p->id }}</td>
                    <td><small>{{ optional($p->payment_date)->format('d/m/Y') ?? '—' }}</small></td>
                    <td><small>{{ $p->concept }}</small></td>
                    <td class="text-end">
                        <strong>{{ optional($p->currency)->code ?? '' }} {{ number_format($p->amount, 2) }}</strong>
                        @if($p->converted_amount && $p->exchange_rate)
                        <br><small class="text-muted">= {{ number_format($p->converted_amount, 2) }} {{ $costCurrency }} (TC {{ number_format($p->exchange_rate, 2) }})</small>
                        @endif
                    </td>
                    <td><small>{{ $p->payment_method }}</small></td>
                    <td><small class="text-muted">{{ Str::limit($p->reference_number, 30) }}</small></td>
                    <td><span class="badge bg-{{ $p->status_color }}">{{ $p->status_label }}</span></td>
                    <td><small>{{ optional($p->verifiedBy)->name ?? '—' }}<br>{{ optional($p->verified_at)->format('d/m/Y H:i') }}</small></td>
                    <td class="text-end">
                        @if($p->status === 'pending')
                            <form method="POST" action="{{ route('admin.payments.verify', $p->id) }}" class="d-inline">@csrf
                                <button type="submit" class="btn btn-sm btn-outline-success py-0" title="Verificar"><i class="fas fa-check"></i></button>
                            </form>
                            <button class="btn btn-sm btn-outline-danger py-0" data-bs-toggle="modal" data-bs-target="#rejectPaymentModal{{ $p->id }}" title="Rechazar"><i class="fas fa-times"></i></button>
                        @elseif($p->status === 'verified')
                            <button class="btn btn-sm btn-outline-warning py-0" data-bs-toggle="modal" data-bs-target="#revertPaymentModal{{ $p->id }}" title="Revertir verificación"><i class="fas fa-undo"></i></button>
                        @endif
                        @if($p->receipt_path)
                            <a href="{{ asset('storage/' . $p->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-info py-0" title="Comprobante"><i class="fas fa-file-pdf"></i></a>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

{{-- Modal: Registrar Pago — Fase 2: extraído al partial unificado admin.payments._form --}}
@include('admin.payments._form', [
    'application' => $application,
    'currencies' => $currencies,
    'modalId' => 'registerPaymentModal',
])

{{-- Modal: Crear Plan de Cuotas --}}
@if(!$installmentPlan && $application->total_cost > 0)
<div class="modal fade" id="installmentPlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.payment-management.installment-plan', $application->id) }}">
                @csrf
                <div class="modal-header"><h6 class="modal-title"><i class="fas fa-calendar-check me-1"></i> Crear Plan de Cuotas</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label small">Nombre del Plan</label><input type="text" name="plan_name" value="Plan de Cuotas" class="form-control form-control-sm"></div>
                    <div class="row g-2 mb-2">
                        <div class="col-6"><label class="form-label small">Cantidad de cuotas <span class="text-danger">*</span></label><input type="number" name="total_installments" min="2" max="24" value="6" class="form-control form-control-sm" required></div>
                        <div class="col-6"><label class="form-label small">Monto Total <span class="text-danger">*</span></label><input type="number" name="total_amount" step="0.01" min="0" value="{{ $application->total_cost }}" class="form-control form-control-sm" required></div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6"><label class="form-label small">Primer vencimiento <span class="text-danger">*</span></label><input type="date" name="first_due_date" value="{{ now()->addMonth()->toDateString() }}" class="form-control form-control-sm" required></div>
                        <div class="col-6"><label class="form-label small">Moneda <span class="text-danger">*</span></label>
                            <select name="currency_id" class="form-select form-select-sm" required>
                                @foreach($currencies as $c)
                                    <option value="{{ $c->id }}" {{ $c->code == $costCurrency ? 'selected' : '' }}>{{ $c->code }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>Crear Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- Modales por pago: reject + revert --}}
@foreach($payments as $p)
    @if($p->status === 'pending')
    <div class="modal fade" id="rejectPaymentModal{{ $p->id }}" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="{{ route('admin.payments.reject', $p->id) }}">
                @csrf
                <div class="modal-header bg-danger text-white"><h6 class="modal-title">Rechazar Pago #{{ $p->id }}</h6><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><label class="form-label small">Motivo <span class="text-danger">*</span></label><textarea name="rejection_reason" class="form-control form-control-sm" rows="3" required></textarea></div>
                <div class="modal-footer"><button class="btn btn-sm btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button><button type="submit" class="btn btn-sm btn-danger">Rechazar</button></div>
            </form>
        </div></div>
    </div>
    @elseif($p->status === 'verified')
    <div class="modal fade" id="revertPaymentModal{{ $p->id }}" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="{{ route('admin.payments.revert', $p->id) }}">
                @csrf
                <div class="modal-header bg-warning"><h6 class="modal-title">Revertir Pago #{{ $p->id }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2 px-3"><small>Vuelves el pago a estado pendiente. Debe indicar el motivo de la reversión.</small></div>
                    <label class="form-label small">Motivo <span class="text-danger">*</span></label><textarea name="revert_reason" class="form-control form-control-sm" rows="3" required></textarea>
                </div>
                <div class="modal-footer"><button class="btn btn-sm btn-secondary" data-bs-dismiss="modal" type="button">Cancelar</button><button type="submit" class="btn btn-sm btn-warning">Revertir</button></div>
            </form>
        </div></div>
    </div>
    @endif
@endforeach
@endsection
