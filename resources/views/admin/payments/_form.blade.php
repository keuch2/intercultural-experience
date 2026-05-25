{{-- Modal/form unificado para registrar un Payment.
     Espera:
       - $application (Application)
       - $currencies (Collection<Currency>)
       - $modalId (string, default 'registerPaymentModal')
       - $redirectTo (string, default route('admin.payment-management.show', $application->id))
       - $title (string, default 'Registrar Pago')
--}}
@php
    $modalId = $modalId ?? 'registerPaymentModal';
    $redirectTo = $redirectTo ?? route('admin.payment-management.show', $application->id);
    $title = $title ?? 'Registrar Pago';
@endphp
<div class="modal fade" id="{{ $modalId }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.payments.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="application_id" value="{{ $application->id }}">
                <input type="hidden" name="user_id" value="{{ $application->user_id }}">
                <input type="hidden" name="program_id" value="{{ $application->program_id }}">
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-money-bill-wave me-1"></i> {{ $title }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small">Concepto <span class="text-danger">*</span></label>
                            <select name="concept" class="form-select form-select-sm" required>
                                <option value="Inscripción">Inscripción</option>
                                <option value="Programa">Programa</option>
                                <option value="Cuota">Cuota</option>
                                <option value="Examen de Inglés">Examen de Inglés</option>
                                <option value="Tarifa Consular">Tarifa Consular</option>
                                <option value="SEVIS">SEVIS</option>
                                <option value="Otro">Otro (especificar)</option>
                            </select>
                            <input type="text" name="other_concept" class="form-control form-control-sm mt-1" placeholder="Especificar...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Monto <span class="text-danger">*</span></label>
                            <input type="number" name="amount" step="0.01" min="0" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Moneda <span class="text-danger">*</span></label>
                            <select name="currency_id" class="form-select form-select-sm" required>
                                @foreach($currencies as $c)
                                    <option value="{{ $c->id }}" data-code="{{ $c->code }}">{{ $c->code }} ({{ $c->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Tipo Cambio</label>
                            <input type="number" name="exchange_rate" step="0.01" min="0" class="form-control form-control-sm" placeholder="Ej: 7300">
                            <small class="text-muted">Si moneda ≠ costo</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small">Método <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select form-select-sm" required>
                                <option value="Transferencia Bancaria">Transferencia Bancaria</option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="Tarjeta de Crédito">Tarjeta Crédito</option>
                                <option value="Tarjeta de Débito">Tarjeta Débito</option>
                                <option value="Giro">Giro</option>
                                <option value="Western Union">Western Union</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Referencia <span class="text-danger">*</span></label>
                            <input type="text" name="reference_number" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Fecha</label>
                            <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small">Estado <span class="text-danger">*</span></label>
                            <select name="status" class="form-select form-select-sm" required>
                                <option value="pending">Pendiente (de verificación)</option>
                                <option value="verified">Realizado (verificado)</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small">Notas</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i>Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>
