{{-- Tab: Pagos (G) — Módulo 12: Restructured payments module --}}
@php
    $proc = $tabData['process'] ?? null;
    $payments = $tabData['payments'] ?? collect();
    $currencies = $tabData['currencies'] ?? collect();
    $installmentPlan = $tabData['installmentPlan'] ?? null;
    $totalPaid = $payments->where('status', 'verified')->sum('amount');
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
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#registerPaymentModal">
            <i class="fas fa-plus me-1"></i> Registrar Pago
        </button>
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
                <button class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal" data-bs-target="#editCostModal">
                    <i class="fas fa-edit me-1"></i> Editar
                </button>
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
                        <th style="width:100px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                    <tr>
                        <td><small>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : $payment->created_at->format('d/m/Y') }}</small></td>
                        <td>{{ $payment->concept ?? '-' }}</td>
                        <td class="text-end fw-semibold">{{ number_format($payment->amount, 2) }}</td>
                        <td><small>{{ $payment->currency->code ?? '-' }}</small></td>
                        <td><small>{{ $payment->payment_method ?? '-' }}</small></td>
                        <td><small>{{ $payment->reference_number ?? '-' }}</small></td>
                        <td class="text-center">
                            <span class="badge bg-{{ $payment->status_color }}">{{ $payment->status_label }}</span>
                        </td>
                        <td><small>{{ $payment->verifiedBy->name ?? '-' }}</small></td>
                        <td>
                            <div class="d-flex gap-1">
                                @if($payment->status === 'pending')
                                    <form method="POST" action="{{ route('admin.payments.verify', $payment->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success py-0" title="Verificar"><i class="fas fa-check"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.payments.reject', $payment->id) }}" class="d-inline" onsubmit="return confirm('¿Rechazar este pago?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0" title="Rechazar"><i class="fas fa-times"></i></button>
                                    </form>
                                @endif
                                @if($payment->receipt_path)
                                    <a href="{{ Storage::disk('public')->url($payment->receipt_path) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0" title="Ver comprobante">
                                        <i class="fas fa-receipt"></i>
                                    </a>
                                @endif
                            </div>
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

{{-- G4: Notas de Pagos --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-sticky-note text-primary me-2"></i> G4. Notas de Pagos
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.aupair.profiles.update-payment-notes', $user->id) }}">
            @csrf @method('PUT')
            <textarea name="payment_notes" class="form-control form-control-sm" rows="3" placeholder="Notas internas sobre pagos, acuerdos de pago, observaciones...">{{ $proc->notes ?? '' }}</textarea>
            <button type="submit" class="btn btn-sm btn-primary mt-2"><i class="fas fa-save me-1"></i> Guardar Notas</button>
        </form>
    </div>
</div>

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

{{-- Register Payment Modal --}}
<div class="modal fade" id="registerPaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.payments.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                @if($application)
                <input type="hidden" name="application_id" value="{{ $application->id }}">
                <input type="hidden" name="program_id" value="{{ $application->program_id }}">
                @endif
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i> Registrar Pago</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Concepto <span class="text-danger">*</span></label>
                            <select name="concept" class="form-select form-select-sm" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="Inscripción">Inscripción (1er Pago)</option>
                                <option value="Programa">Programa (2do Pago)</option>
                                <option value="Cuota">Cuota</option>
                                <option value="Examen de Inglés">Examen de Inglés</option>
                                <option value="Tarifa Consular">Tarifa Consular</option>
                                <option value="SEVIS">SEVIS</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="otherConceptWrapper" style="display:none;">
                            <label class="form-label small">Especificar concepto</label>
                            <input type="text" name="other_concept" class="form-control form-control-sm" placeholder="Detalle del concepto...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Monto <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control form-control-sm" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Moneda <span class="text-danger">*</span></label>
                            <select name="currency_id" class="form-select form-select-sm" required>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->code }} ({{ $currency->symbol }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Método de Pago <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select form-select-sm" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta de Crédito">Tarjeta de Crédito</option>
                                <option value="Tarjeta de Débito">Tarjeta de Débito</option>
                                <option value="Giro">Giro</option>
                                <option value="Western Union">Western Union</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">N° Referencia <span class="text-danger">*</span></label>
                            <input type="text" name="reference_number" class="form-control form-control-sm" required placeholder="N° de transacción o comprobante">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Estado <span class="text-danger">*</span></label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="pending">Pendiente de verificación</option>
                                <option value="verified">Verificado (Realizado)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Notas</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Observaciones adicionales..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Program Cost Modal --}}
<div class="modal fade" id="editCostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.profiles.update-program-cost', $user->id) }}">
                @csrf @method('PUT')
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-tag me-2"></i> Editar Costo del Programa</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Costo Total <span class="text-danger">*</span></label>
                            <input type="number" name="total_cost" class="form-control form-control-sm" step="0.01" min="0" value="{{ $application->total_cost ?? '' }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Moneda</label>
                            <select name="cost_currency" class="form-select form-select-sm">
                                <option value="USD" {{ ($application->cost_currency ?? 'USD') === 'USD' ? 'selected' : '' }}>USD</option>
                                <option value="PYG" {{ ($application->cost_currency ?? '') === 'PYG' ? 'selected' : '' }}>PYG</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Tipo de Cambio</label>
                            <input type="number" name="exchange_rate" class="form-control form-control-sm" step="0.01" value="{{ $application->exchange_rate ?? '' }}" placeholder="Ej: 7500">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Fecha límite de pago</label>
                            <input type="date" name="payment_deadline" class="form-control form-control-sm" value="{{ $application && $application->payment_deadline ? $application->payment_deadline->format('Y-m-d') : '' }}">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

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
});
</script>
@endpush
