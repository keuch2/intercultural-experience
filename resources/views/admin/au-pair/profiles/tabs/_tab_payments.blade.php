{{-- Tab: Pagos (G) — Módulo 12: Restructured payments module --}}
@php
    $proc = $tabData['process'] ?? null;
    $payments = $tabData['payments'] ?? collect();
    $currencies = $tabData['currencies'] ?? collect();
    $installmentPlan = $tabData['installmentPlan'] ?? null;
    // Módulo 18: For multi-currency, use converted_amount when available
    $totalPaid = $payments->where('status', 'verified')->sum(function($p) {
        return $p->converted_amount ?? $p->amount;
    });
    $totalCost = $application->total_cost ?? 0;
    $costCurrency = $application->cost_currency ?? 'USD';
    $progressPct = $totalCost > 0 ? min(100, round(($totalPaid / $totalCost) * 100)) : 0;
@endphp

{{-- G1: Resumen Financiero --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-chart-pie text-primary me-2"></i> G1. Resumen Financiero
        </h5>
        {{-- Fase 4: Registrar pagos solo desde Gestión de Pagos global --}}
        @if($application)
        <a href="{{ route('admin.payment-management.show', $application->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-external-link-alt me-1"></i> Gestionar Pagos
        </a>
        @endif
    </div>
    <div class="card-body">
        {{-- Progreso de pago --}}
        @if($totalCost > 0)
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-end mb-1">
                <span class="small fw-bold text-muted">Progreso de Pago</span>
                <span class="small fw-bold">{{ number_format($totalPaid, 2) }} / {{ number_format($totalCost, 2) }} {{ $costCurrency }}</span>
            </div>
            <div class="progress" style="height: 20px;">
                <div class="progress-bar {{ $progressPct >= 100 ? 'bg-success' : ($progressPct >= 50 ? 'bg-info' : 'bg-warning') }}"
                     role="progressbar" style="width: {{ $progressPct }}%" aria-valuenow="{{ $progressPct }}" aria-valuemin="0" aria-valuemax="100">
                    {{ $progressPct }}%
                </div>
            </div>
            @if($totalCost - $totalPaid > 0)
            <small class="text-muted mt-1 d-block">Saldo pendiente: <strong>{{ number_format($totalCost - $totalPaid, 2) }} {{ $costCurrency }}</strong></small>
            @endif
        </div>
        @endif

        {{-- Estado de pagos requeridos --}}
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="border rounded p-3 {{ $stages['_meta']['payment1_verified'] ? 'border-success bg-success bg-opacity-10' : 'border-warning bg-warning bg-opacity-10' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1">1er Pago - Inscripción</h6>
                            <small class="text-muted">Habilita documentos de aplicación</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($stages['_meta']['payment1_verified'])
                                <span class="badge bg-success fs-6"><i class="fas fa-check-circle"></i> Verificado</span>
                            @else
                                <span class="badge bg-warning text-dark fs-6"><i class="fas fa-clock"></i> Pendiente</span>
                                {{-- Módulo 12: Quick toggle to verify payment 1 --}}
                                <form method="POST" action="{{ route('admin.aupair.profiles.update-payment-flag', $user->id) }}" class="d-inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="flag" value="payment_1_verified">
                                    <input type="hidden" name="value" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline-success py-0" title="Marcar como verificado"><i class="fas fa-check"></i></button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 {{ $stages['_meta']['payment2_verified'] ? 'border-success bg-success bg-opacity-10' : 'border-warning bg-warning bg-opacity-10' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1">2do Pago - Programa</h6>
                            <small class="text-muted">Habilita documentos finales</small>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($stages['_meta']['payment2_verified'])
                                <span class="badge bg-success fs-6"><i class="fas fa-check-circle"></i> Verificado</span>
                            @else
                                <span class="badge bg-warning text-dark fs-6"><i class="fas fa-clock"></i> Pendiente</span>
                                <form method="POST" action="{{ route('admin.aupair.profiles.update-payment-flag', $user->id) }}" class="d-inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="flag" value="payment_2_verified">
                                    <input type="hidden" name="value" value="1">
                                    <button type="submit" class="btn btn-sm btn-outline-success py-0" title="Marcar como verificado"><i class="fas fa-check"></i></button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Costo del programa --}}
        <div class="border rounded p-3 bg-light mb-0">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 small fw-bold text-muted"><i class="fas fa-tag me-1"></i> Costo del Programa</h6>
                </div>
                @if($application)
                <a href="{{ route('admin.payment-management.show', $application->id) }}" class="btn btn-sm btn-outline-primary py-0">
                    <i class="fas fa-edit me-1"></i> Editar en Gestión de Pagos
                </a>
                @endif
            </div>
            <div class="row mt-2">
                <div class="col-auto">
                    <small class="text-muted">Costo Total:</small>
                    <span class="fw-bold">{{ $totalCost > 0 ? number_format($totalCost, 2) . ' ' . $costCurrency : 'Sin asignar' }}</span>
                </div>
                @if($application && $application->exchange_rate)
                <div class="col-auto">
                    <small class="text-muted">Tipo de Cambio:</small>
                    <span class="fw-bold">{{ number_format($application->exchange_rate, 2) }}</span>
                </div>
                @endif
                @if($application && $application->payment_deadline)
                <div class="col-auto">
                    <small class="text-muted">Fecha límite:</small>
                    <span class="fw-bold {{ $application->payment_deadline->isPast() ? 'text-danger' : '' }}">{{ $application->payment_deadline->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- G2: Pagos Registrados --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-receipt text-primary me-2"></i> G2. Pagos Registrados
        </h5>
    </div>
    <div class="card-body">
        @if($payments->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th class="text-end">Monto</th>
                        <th>Moneda</th>
                        <th>Método</th>
                        <th>Referencia</th>
                        <th class="text-center">Estado</th>
                        <th>Verificado por</th>
                        <th style="width:60px;">Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td><small>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : $payment->created_at->format('d/m/Y') }}</small></td>
                        <td>{{ $payment->concept ?? '-' }}</td>
                        <td class="text-end fw-semibold">
                            {{ number_format($payment->amount, 2) }}
                            {{-- Módulo 18: Show converted amount for multi-currency payments --}}
                            @if($payment->converted_amount && $payment->exchange_rate)
                                <br><small class="text-muted">= {{ number_format($payment->converted_amount, 2) }} {{ $costCurrency }} (TC: {{ number_format($payment->exchange_rate, 2) }})</small>
                            @endif
                        </td>
                        <td><small>{{ $payment->currency->code ?? '-' }}</small></td>
                        <td><small>{{ $payment->payment_method ?? '-' }}</small></td>
                        <td><small>{{ $payment->reference_number ?? '-' }}</small></td>
                        <td class="text-center">
                            <span class="badge bg-{{ $payment->status_color }}">{{ $payment->status_label }}</span>
                        </td>
                        <td><small>{{ $payment->verifiedBy->name ?? '-' }}</small></td>
                        {{-- Fase 4: Acciones de pago se gestionan en Gestión de Pagos --}}
                        <td>
                            @if($payment->receipt_path)
                                <a href="{{ asset('storage/' . $payment->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0" title="Ver comprobante">
                                    <i class="fas fa-receipt"></i>
                                </a>
                            @else
                                <small class="text-muted">—</small>
                            @endif
                        </td>
                    </tr>
                    @if($payment->notes)
                    <tr>
                        <td colspan="9" class="py-1 px-3 bg-light"><small class="text-muted"><i class="fas fa-comment me-1"></i>{{ $payment->notes }}</small></td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tfoot class="table-light">
                    <tr>
                        <td colspan="2" class="fw-bold">Total Verificado</td>
                        <td class="text-end fw-bold text-success">{{ number_format($totalPaid, 2) }}</td>
                        <td colspan="6"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="fas fa-receipt fa-2x text-muted mb-2 d-block opacity-25"></i>
            <p class="text-muted small mb-0">No se han registrado pagos para este participante.</p>
        </div>
        @endif
    </div>
</div>

{{-- G3: Plan de Cuotas --}}
@if(!$installmentPlan && $totalCost > 0)
{{-- Módulo 18: Create installment plan when none exists --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-calendar-check text-primary me-2"></i> G3. Plan de Cuotas
        </h5>
        @if($application)
        <a href="{{ route('admin.payment-management.show', $application->id) }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus me-1"></i> Crear Plan en Gestión de Pagos
        </a>
        @endif
    </div>
    <div class="card-body">
        <div class="text-center py-3">
            <i class="fas fa-calendar-alt fa-2x text-muted mb-2 d-block opacity-25"></i>
            <p class="text-muted small mb-0">No hay un plan de cuotas configurado. Puede crear uno para este participante.</p>
        </div>
    </div>
</div>
@endif
@if($installmentPlan)
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-calendar-check text-primary me-2"></i> G3. Plan de Cuotas
        </h5>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-auto">
                <small class="text-muted d-block">Plan</small>
                <span class="fw-bold">{{ $installmentPlan->plan_name ?? 'Plan de Cuotas' }}</span>
            </div>
            <div class="col-auto">
                <small class="text-muted d-block">Total</small>
                <span class="fw-bold">{{ number_format($installmentPlan->total_amount, 2) }} {{ $installmentPlan->currency->code ?? '' }}</span>
            </div>
            <div class="col-auto">
                <small class="text-muted d-block">Cuotas</small>
                <span class="fw-bold">{{ $installmentPlan->paid_installments_count }}/{{ $installmentPlan->total_installments }}</span>
            </div>
            <div class="col-auto">
                <small class="text-muted d-block">Progreso</small>
                <div class="progress mt-1" style="width: 100px; height: 8px;">
                    <div class="progress-bar bg-success" style="width: {{ $installmentPlan->progress_percentage }}%"></div>
                </div>
            </div>
            <div class="col-auto">
                <span class="badge bg-{{ $installmentPlan->status === 'active' ? 'primary' : ($installmentPlan->status === 'completed' ? 'success' : 'danger') }}">
                    {{ ucfirst($installmentPlan->status) }}
                </span>
            </div>
        </div>

        @if($installmentPlan->installmentDetails->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th class="text-end">Monto</th>
                        <th>Vencimiento</th>
                        <th>Pagado</th>
                        <th class="text-center">Estado</th>
                        @if($installmentPlan->installmentDetails->sum('late_fee') > 0)
                        <th class="text-end">Mora</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($installmentPlan->installmentDetails->sortBy('installment_number') as $detail)
                    <tr class="{{ $detail->isOverdue() ? 'table-danger' : ($detail->status === 'paid' ? 'table-success' : '') }}">
                        <td>{{ $detail->installment_number }}</td>
                        <td class="text-end">{{ number_format($detail->amount, 2) }}</td>
                        <td>{{ $detail->due_date ? $detail->due_date->format('d/m/Y') : '-' }}</td>
                        <td>{{ $detail->paid_date ? $detail->paid_date->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ match($detail->status) { 'paid' => 'success', 'overdue' => 'danger', 'pending' => 'warning', default => 'secondary' } }}">
                                {{ ucfirst($detail->status) }}
                            </span>
                        </td>
                        @if($installmentPlan->installmentDetails->sum('late_fee') > 0)
                        <td class="text-end">{{ $detail->late_fee > 0 ? number_format($detail->late_fee, 2) : '-' }}</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endif

{{-- Módulo 14: Notas movidas a sección independiente en show.blade.php --}}

{{-- Alerta de pagos faltantes --}}
@if(!$stages['_meta']['payment1_verified'] || !$stages['_meta']['payment2_verified'])
<div class="alert alert-warning py-2 px-3 mb-0">
    <small>
        <i class="fas fa-exclamation-triangle me-1"></i>
        <strong>Pagos pendientes:</strong>
        @if(!$stages['_meta']['payment1_verified']) 1er pago (inscripción) no verificado. @endif
        @if(!$stages['_meta']['payment2_verified']) 2do pago (programa) no verificado. @endif
        Los documentos correspondientes permanecen bloqueados hasta la verificación.
    </small>
</div>
@endif


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Módulo 12: Show/hide other concept field
    const conceptSelect = document.querySelector('select[name="concept"]');
    if (conceptSelect) {
        conceptSelect.addEventListener('change', function() {
            const wrapper = document.getElementById('otherConceptWrapper');
            wrapper.style.display = this.value === 'Otro' ? '' : 'none';
        });
    }

    // Módulo 18: Show/hide exchange rate field based on currency vs program currency
    const programCurrency = '{{ $costCurrency }}';
    const currencySelect = document.getElementById('paymentCurrency');
    const exchangeWrapper = document.getElementById('exchangeRateWrapper');
    const exchangeInput = document.getElementById('paymentExchangeRate');
    const amountInput = document.getElementById('paymentAmount');
    const hint = document.getElementById('convertedAmountHint');

    function updateExchangeVisibility() {
        if (!currencySelect || !exchangeWrapper) return;
        const selected = currencySelect.options[currencySelect.selectedIndex];
        const code = selected ? selected.getAttribute('data-code') : '';
        exchangeWrapper.style.display = (code && code !== programCurrency) ? '' : 'none';
        if (code === programCurrency) {
            exchangeInput.value = '';
            hint.textContent = '';
        }
    }

    function updateConvertedHint() {
        if (!exchangeInput || !amountInput || !hint) return;
        const amount = parseFloat(amountInput.value) || 0;
        const rate = parseFloat(exchangeInput.value) || 0;
        if (amount > 0 && rate > 0) {
            const selected = currencySelect.options[currencySelect.selectedIndex];
            const code = selected ? selected.getAttribute('data-code') : '';
            let converted;
            if (programCurrency === 'USD' && code === 'PYG') {
                converted = (amount / rate).toFixed(2);
            } else {
                converted = (amount * rate).toFixed(2);
            }
            hint.textContent = '= ' + converted + ' ' + programCurrency;
        } else {
            hint.textContent = '';
        }
    }

    if (currencySelect) {
        currencySelect.addEventListener('change', updateExchangeVisibility);
        updateExchangeVisibility();
    }
    if (exchangeInput) exchangeInput.addEventListener('input', updateConvertedHint);
    if (amountInput) amountInput.addEventListener('input', updateConvertedHint);
});
</script>
@endpush
