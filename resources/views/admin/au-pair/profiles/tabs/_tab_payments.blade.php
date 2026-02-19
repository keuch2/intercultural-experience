{{-- Tab: Pagos (G) --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-money-bill-wave text-primary me-2"></i> G. Pagos
        </h5>
        <a href="{{ route('admin.finance.payments.create') }}" class="btn btn-sm btn-outline-primary">
            <i class="fas fa-plus"></i> Registrar Pago
        </a>
    </div>
    <div class="card-body">

        {{-- Estado de pagos requeridos --}}
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="border rounded p-3 {{ $stages['_meta']['payment1_verified'] ? 'border-success bg-success bg-opacity-10' : 'border-warning bg-warning bg-opacity-10' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1">1er Pago - Inscripción</h6>
                            <small class="text-muted">Habilita documentos de aplicación</small>
                        </div>
                        @if($stages['_meta']['payment1_verified'])
                            <span class="badge bg-success fs-6"><i class="fas fa-check-circle"></i> Verificado</span>
                        @else
                            <span class="badge bg-warning text-dark fs-6"><i class="fas fa-clock"></i> Pendiente</span>
                        @endif
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
                        @if($stages['_meta']['payment2_verified'])
                            <span class="badge bg-success fs-6"><i class="fas fa-check-circle"></i> Verificado</span>
                        @else
                            <span class="badge bg-warning text-dark fs-6"><i class="fas fa-clock"></i> Pendiente</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Historial de pagos --}}
        <h6 class="text-uppercase text-muted small fw-bold mb-3">Historial de Pagos</h6>

        @if(isset($tabData['payments']) && $tabData['payments']->count() > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Fecha</th>
                        <th>Concepto</th>
                        <th class="text-end">Monto</th>
                        <th>Método</th>
                        <th>Referencia</th>
                        <th class="text-center">Estado</th>
                        <th>Verificado por</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tabData['payments'] as $payment)
                    <tr>
                        <td><small>{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : $payment->created_at->format('d/m/Y') }}</small></td>
                        <td>{{ $payment->concept ?? '-' }}</td>
                        <td class="text-end fw-semibold">{{ $payment->formatted_amount ?? number_format($payment->amount, 2) }}</td>
                        <td><small>{{ $payment->payment_method ?? '-' }}</small></td>
                        <td><small>{{ $payment->reference_number ?? '-' }}</small></td>
                        <td class="text-center">
                            <span class="badge bg-{{ $payment->status_color }}">{{ $payment->status_label }}</span>
                        </td>
                        <td><small>{{ $payment->verifiedBy->name ?? '-' }}</small></td>
                        <td>
                            @if($payment->receipt_path)
                            <a href="#" class="btn btn-sm btn-outline-secondary py-0" title="Ver comprobante">
                                <i class="fas fa-receipt"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-4">
            <i class="fas fa-receipt fa-2x text-muted mb-2 d-block opacity-25"></i>
            <p class="text-muted small mb-0">No se han registrado pagos para este participante.</p>
        </div>
        @endif

        {{-- Alerta de pagos faltantes --}}
        @if(!$stages['_meta']['payment1_verified'] || !$stages['_meta']['payment2_verified'])
        <div class="alert alert-warning py-2 px-3 mt-3 mb-0">
            <small>
                <i class="fas fa-exclamation-triangle me-1"></i>
                <strong>Pagos pendientes:</strong>
                @if(!$stages['_meta']['payment1_verified'])
                    1er pago (inscripción) no verificado.
                @endif
                @if(!$stages['_meta']['payment2_verified'])
                    2do pago (programa) no verificado.
                @endif
                Los documentos correspondientes permanecen bloqueados hasta la verificación.
            </small>
        </div>
        @endif
    </div>
</div>
