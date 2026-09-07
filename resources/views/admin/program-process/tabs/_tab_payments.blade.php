@php
    $payments = $tabData['payments']; $totalPaid = $tabData['total_paid']; $totalCost = $tabData['total_cost']; $costCurrency = $tabData['currency']; $pct = $tabData['pct']; $plan = $tabData['installment_plan']; $gates = $tabData['gates'];
@endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-chart-pie text-primary me-2"></i> Resumen financiero</h5>
        @if($application && Route::has('admin.payment-management.show'))<a href="{{ route('admin.payment-management.show', $application->id) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-external-link-alt me-1"></i> Gestionar pagos</a>@endif
    </div>
    <div class="card-body">
        @if($totalCost > 0)
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-end mb-1"><span class="small fw-bold text-muted">Progreso de pago</span><span class="small fw-bold">{{ number_format($totalPaid, 2) }} / {{ number_format($totalCost, 2) }} {{ $costCurrency }}</span></div>
            <div class="progress" style="height:20px"><div class="progress-bar {{ $pct >= 100 ? 'bg-success' : ($pct >= 50 ? 'bg-info' : 'bg-warning') }}" style="width: {{ $pct }}%">{{ $pct }}%</div></div>
            @if($totalCost - $totalPaid > 0)<small class="text-muted mt-1 d-block">Saldo pendiente: <strong>{{ number_format($totalCost - $totalPaid, 2) }} {{ $costCurrency }}</strong></small>@endif
        </div>
        @endif

        <h6 class="small fw-bold text-muted mb-2">Hitos de pago (gates)</h6>
        <div class="row g-3 mb-3">
            @forelse($gates as $gate)
            @php $row = $process->gates->firstWhere('gate_key', $gate->key); $ok = (bool) $row?->is_verified; @endphp
            <div class="col-md-6"><div class="border rounded p-3 {{ $ok ? 'border-success bg-success bg-opacity-10' : 'border-warning bg-warning bg-opacity-10' }}">
                <div class="d-flex align-items-center justify-content-between">
                    <div><h6 class="mb-1">{{ $gate->label }}</h6><small class="text-muted">@if($gate->amount !== null){{ number_format((float) $gate->amount, 2) }} {{ $gate->currency?->code }} · @endif Habilita documentos y avance de etapa</small>@if($ok && $row->verified_at)<br><small class="text-success">Verificado {{ $row->verified_at->format('d/m/Y') }} @if($row->verifiedBy)por {{ $row->verifiedBy->name }}@endif</small>@endif</div>
                    <div class="d-flex align-items-center gap-2">
                        @if($ok)
                            <span class="badge bg-success fs-6"><i class="fas fa-check-circle"></i> Verificado</span>
                            <form method="POST" action="{{ route('admin.program.gates.update', [$program->slug, $process->id, $gate->key]) }}" onsubmit="return confirm('¿Desmarcar este pago como verificado?')">@csrf @method('PUT')<input type="hidden" name="value" value="0"><button class="btn btn-sm btn-outline-secondary py-0" title="Desmarcar"><i class="fas fa-undo"></i></button></form>
                        @else
                            <span class="badge bg-warning text-dark fs-6"><i class="fas fa-clock"></i> Pendiente</span>
                            <form method="POST" action="{{ route('admin.program.gates.update', [$program->slug, $process->id, $gate->key]) }}">@csrf @method('PUT')<input type="hidden" name="value" value="1"><button class="btn btn-sm btn-outline-success py-0" title="Marcar verificado"><i class="fas fa-check"></i></button></form>
                        @endif
                    </div>
                </div>
            </div></div>
            @empty
            <div class="col-12"><small class="text-muted">Este programa no tiene gates de pago configurados.</small></div>
            @endforelse
        </div>

        <div class="border rounded p-3 bg-light">
            <div class="d-flex justify-content-between align-items-center"><h6 class="mb-0 small fw-bold text-muted"><i class="fas fa-tag me-1"></i> Costo del programa</h6><button class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="collapse" data-bs-target="#editCost"><i class="fas fa-edit me-1"></i> Editar</button></div>
            <div class="row mt-2">
                <div class="col-auto"><small class="text-muted">Costo total:</small> <span class="fw-bold">{{ $totalCost > 0 ? number_format($totalCost, 2).' '.$costCurrency : 'Sin asignar' }}</span></div>
                @if($application?->exchange_rate)<div class="col-auto"><small class="text-muted">Tipo de cambio:</small> <span class="fw-bold">{{ number_format($application->exchange_rate, 2) }}</span></div>@endif
                @if($application?->payment_deadline)<div class="col-auto"><small class="text-muted">Fecha límite:</small> <span class="fw-bold {{ $application->payment_deadline->isPast() ? 'text-danger' : '' }}">{{ $application->payment_deadline->format('d/m/Y') }}</span></div>@endif
            </div>
            <div class="collapse mt-3" id="editCost">
                <form method="POST" action="{{ route('admin.program.payments.cost', [$program->slug, $process->id]) }}" class="row g-2 align-items-end">@csrf @method('PUT')
                    <div class="col-md-3"><label class="form-label small">Costo total *</label><input type="number" step="0.01" min="0" name="total_cost" class="form-control form-control-sm" value="{{ $application?->total_cost }}" required></div>
                    <div class="col-md-2"><label class="form-label small">Moneda</label><select name="cost_currency" class="form-select form-select-sm"><option value="USD" {{ $costCurrency === 'USD' ? 'selected' : '' }}>USD</option><option value="PYG" {{ $costCurrency === 'PYG' ? 'selected' : '' }}>PYG</option></select></div>
                    <div class="col-md-3"><label class="form-label small">Tipo de cambio</label><input type="number" step="0.01" min="0" name="exchange_rate" class="form-control form-control-sm" value="{{ $application?->exchange_rate }}"></div>
                    <div class="col-md-3"><label class="form-label small">Fecha límite</label><input type="date" name="payment_deadline" class="form-control form-control-sm" value="{{ $application?->payment_deadline?->format('Y-m-d') }}"></div>
                    <div class="col-md-1"><button class="btn btn-sm btn-primary w-100"><i class="fas fa-save"></i></button></div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h5 class="card-title mb-0"><i class="fas fa-receipt text-primary me-2"></i> Pagos registrados</h5></div>
    <div class="card-body">
        @if($payments->count())
        <div class="table-responsive"><table class="table table-sm table-bordered table-hover align-middle">
            <thead class="table-light"><tr><th>Fecha</th><th>Concepto</th><th class="text-end">Monto</th><th>Moneda</th><th>Método</th><th>Referencia</th><th class="text-center">Estado</th><th>Verificado por</th><th style="width:60px">Comp.</th></tr></thead>
            <tbody>
            @foreach($payments as $payment)
            <tr>
                <td><small>{{ ($payment->payment_date ?? $payment->created_at)->format('d/m/Y') }}</small></td><td>{{ $payment->concept ?? '-' }}</td>
                <td class="text-end fw-semibold">{{ number_format($payment->amount, 2) }}@if($payment->converted_amount && $payment->exchange_rate)<br><small class="text-muted">= {{ number_format($payment->converted_amount, 2) }} {{ $costCurrency }}</small>@endif</td>
                <td><small>{{ $payment->currency->code ?? '-' }}</small></td><td><small>{{ $payment->payment_method ?? '-' }}</small></td><td><small>{{ $payment->reference_number ?? '-' }}</small></td>
                <td class="text-center"><span class="badge bg-{{ $payment->status_color ?? 'secondary' }}">{{ $payment->status_label ?? $payment->status }}</span></td>
                <td><small>{{ $payment->verifiedBy->name ?? '-' }}</small></td>
                <td>@if($payment->receipt_path)<a href="{{ asset('storage/'.$payment->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-receipt"></i></a>@else<small class="text-muted">—</small>@endif</td>
            </tr>
            @endforeach
            </tbody>
            <tfoot class="table-light"><tr><td colspan="2" class="fw-bold">Total verificado</td><td class="text-end fw-bold text-success">{{ number_format($totalPaid, 2) }}</td><td colspan="6"></td></tr></tfoot>
        </table></div>
        @else
        <div class="text-center py-4"><i class="fas fa-receipt fa-2x text-muted mb-2 d-block opacity-25"></i><p class="text-muted small mb-0">No se registraron pagos para este participante.</p></div>
        @endif
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center"><h5 class="card-title mb-0"><i class="fas fa-calendar-check text-primary me-2"></i> Plan de cuotas</h5>
        @if(!$plan && $totalCost > 0)<button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#newPlan"><i class="fas fa-plus me-1"></i> Crear plan</button>@endif</div>
    <div class="card-body">
        @if($plan)
        <div class="row g-3 mb-3">
            <div class="col-auto"><small class="text-muted d-block">Plan</small><span class="fw-bold">{{ $plan->plan_name ?? 'Plan de Cuotas' }}</span></div>
            <div class="col-auto"><small class="text-muted d-block">Total</small><span class="fw-bold">{{ number_format($plan->total_amount, 2) }} {{ $plan->currency->code ?? '' }}</span></div>
            <div class="col-auto"><small class="text-muted d-block">Cuotas</small><span class="fw-bold">{{ $plan->paid_installments_count }}/{{ $plan->total_installments }}</span></div>
            <div class="col-auto"><span class="badge bg-{{ $plan->status === 'active' ? 'primary' : ($plan->status === 'completed' ? 'success' : 'danger') }}">{{ ucfirst($plan->status) }}</span></div>
        </div>
        <div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th class="text-end">Monto</th><th>Vencimiento</th><th>Pagado</th><th class="text-center">Estado</th></tr></thead>
            <tbody>@foreach($plan->installmentDetails->sortBy('installment_number') as $d)<tr class="{{ $d->isOverdue() ? 'table-danger' : ($d->status === 'paid' ? 'table-success' : '') }}"><td>{{ $d->installment_number }}</td><td class="text-end">{{ number_format($d->amount, 2) }}</td><td>{{ $d->due_date?->format('d/m/Y') ?? '-' }}</td><td>{{ $d->paid_date?->format('d/m/Y') ?? '-' }}</td><td class="text-center"><span class="badge bg-{{ ['paid' => 'success', 'overdue' => 'danger', 'pending' => 'warning text-dark'][$d->status] ?? 'secondary' }}">{{ ucfirst($d->status) }}</span></td></tr>@endforeach</tbody>
        </table></div>
        @else
        <div class="collapse {{ $totalCost > 0 ? '' : 'show' }}" id="newPlan">
            @if($totalCost > 0)
            <form method="POST" action="{{ route('admin.program.payments.installment-plan', [$program->slug, $process->id]) }}" class="row g-2 align-items-end">@csrf
                <div class="col-md-3"><label class="form-label small">Nombre</label><input type="text" name="plan_name" class="form-control form-control-sm" placeholder="Plan de Cuotas"></div>
                <div class="col-md-2"><label class="form-label small">Cuotas *</label><input type="number" name="total_installments" class="form-control form-control-sm" min="2" max="24" value="3" required></div>
                <div class="col-md-3"><label class="form-label small">Monto total *</label><input type="number" step="0.01" name="total_amount" class="form-control form-control-sm" value="{{ $totalCost }}" required></div>
                <div class="col-md-3"><label class="form-label small">Primer vencimiento *</label><input type="date" name="first_due_date" class="form-control form-control-sm" required></div>
                <div class="col-md-1"><button class="btn btn-sm btn-primary w-100"><i class="fas fa-plus"></i></button></div>
            </form>
            @else
            <p class="text-muted small mb-0"><i class="fas fa-info-circle"></i> Definí el costo del programa para poder crear un plan de cuotas.</p>
            @endif
        </div>
        @endif
    </div>
</div>
