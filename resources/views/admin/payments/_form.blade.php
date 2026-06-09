{{-- Modal/form unificado para registrar un Payment.
     Espera:
       - $application (Application|null)   — null cuando $selectMode=true
       - $currencies (Collection<Currency>)
       - $modalId (string, default 'registerPaymentModal')
       - $redirectTo (string)
       - $title (string, default 'Registrar Pago')
       - $selectMode (bool, default false) — true = buscar participante + elegir programa
       - $programs (Collection<Program>)   — requerido si $selectMode=true
       - $prefill (array, default [])       — ['amount'=>..,'concept'=>..,'installment_detail_id'=>..]
--}}
@php
    $modalId = $modalId ?? 'registerPaymentModal';
    $selectMode = $selectMode ?? false;
    $prefill = $prefill ?? [];
    $redirectTo = $redirectTo ?? ($application ? route('admin.payment-management.show', $application->id) : url()->current());
    $title = $title ?? 'Registrar Pago';
    $prefillAmount = $prefill['amount'] ?? null;
    $prefillConcept = $prefill['concept'] ?? null;
    $prefillInstallmentId = $prefill['installment_detail_id'] ?? null;
    $conceptOptions = ['Inscripción', 'Programa', 'Cuota', 'Examen de Inglés', 'Tarifa Consular', 'SEVIS'];
@endphp
<div class="modal fade" id="{{ $modalId }}" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.payments.store') }}" enctype="multipart/form-data">
                @csrf
                @if($selectMode)
                    {{-- user_id se setea por el autocomplete; program_id por el select --}}
                @else
                    <input type="hidden" name="application_id" value="{{ $application->id }}">
                    <input type="hidden" name="user_id" value="{{ $application->user_id }}">
                    <input type="hidden" name="program_id" value="{{ $application->program_id }}">
                @endif
                @if($prefillInstallmentId)
                    <input type="hidden" name="installment_detail_id" value="{{ $prefillInstallmentId }}">
                @endif
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-money-bill-wave me-1"></i> {{ $title }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        @if($selectMode)
                            <div class="col-md-7 position-relative">
                                <label class="form-label small">Participante <span class="text-danger">*</span></label>
                                <input type="text" id="{{ $modalId }}_user_search" class="form-control form-control-sm" autocomplete="off" placeholder="Buscar por nombre o email (mín. 2)">
                                <input type="hidden" name="user_id" id="{{ $modalId }}_user_id">
                                <div id="{{ $modalId }}_user_results" class="position-absolute bg-white border rounded shadow-sm w-100" style="z-index:1080; display:none; max-height:220px; overflow-y:auto;"></div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small">Programa <span class="text-danger">*</span></label>
                                <select name="program_id" class="form-select form-select-sm" required>
                                    <option value="">Seleccionar...</option>
                                    @foreach($programs as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <label class="form-label small">Concepto <span class="text-danger">*</span></label>
                            <select name="concept" class="form-select form-select-sm" required>
                                @foreach($conceptOptions as $opt)
                                    <option value="{{ $opt }}" {{ $prefillConcept === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                                <option value="Otro" {{ ($prefillConcept && !in_array($prefillConcept, $conceptOptions)) ? 'selected' : '' }}>Otro (especificar)</option>
                            </select>
                            <input type="text" name="other_concept" class="form-control form-control-sm mt-1" placeholder="Especificar..." value="{{ ($prefillConcept && !in_array($prefillConcept, $conceptOptions)) ? $prefillConcept : '' }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Monto <span class="text-danger">*</span></label>
                            <input type="number" name="amount" step="0.01" min="0" class="form-control form-control-sm" required value="{{ $prefillAmount }}">
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

@if($selectMode)
@push('scripts')
<script>
    (function () {
        const search = document.getElementById('{{ $modalId }}_user_search');
        const idInput = document.getElementById('{{ $modalId }}_user_id');
        const results = document.getElementById('{{ $modalId }}_user_results');
        if (!search) return;
        let t = null;
        search.addEventListener('input', function () {
            clearTimeout(t);
            const q = this.value.trim();
            idInput.value = '';
            if (q.length < 2) { results.style.display = 'none'; results.innerHTML = ''; return; }
            t = setTimeout(() => {
                fetch(`{{ route('admin.finance.search-participants') }}?q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(users => {
                        results.innerHTML = '';
                        if (!users.length) {
                            results.innerHTML = '<div class="px-3 py-2 text-muted small">Sin resultados</div>';
                        } else {
                            users.forEach(u => {
                                const item = document.createElement('div');
                                item.className = 'px-3 py-2 border-bottom';
                                item.style.cursor = 'pointer';
                                item.innerHTML = `<strong>${u.name}</strong> <small class="text-muted">${u.email}</small>`;
                                item.addEventListener('click', () => {
                                    idInput.value = u.id;
                                    search.value = `${u.name} (${u.email})`;
                                    search.classList.add('border-success');
                                    results.style.display = 'none';
                                });
                                item.addEventListener('mouseenter', () => item.classList.add('bg-light'));
                                item.addEventListener('mouseleave', () => item.classList.remove('bg-light'));
                                results.appendChild(item);
                            });
                        }
                        results.style.display = 'block';
                    })
                    .catch(() => { results.innerHTML = '<div class="px-3 py-2 text-danger small">Error</div>'; results.style.display = 'block'; });
            }, 300);
        });
        document.addEventListener('click', function (e) {
            if (!search.contains(e.target) && !results.contains(e.target)) results.style.display = 'none';
        });
    })();
</script>
@endpush
@endif
