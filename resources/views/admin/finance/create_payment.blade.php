@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Registrar Pago Manual</h1>
        <a href="{{ route('admin.finance.payments') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver a Pagos
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Datos del Pago</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.finance.payments.store') }}">
                @csrf
                
                <div class="row">
                    {{-- Participante --}}
                    <div class="col-md-6 mb-3">
                        <label for="user_search" class="form-label">Participante <span class="text-danger">*</span></label>
                        <input type="hidden" name="user_id" id="user_id" value="{{ old('user_id') }}" required>
                        <div class="position-relative">
                            <input type="text" id="user_search" class="form-control @error('user_id') is-invalid @enderror" placeholder="Buscar por nombre o email..." autocomplete="off">
                            <div id="user_results" class="position-absolute w-100 bg-white border rounded-bottom shadow-sm" style="z-index: 1050; max-height: 250px; overflow-y: auto; display: none;"></div>
                        </div>
                        <small class="form-text text-muted">Escribí al menos 2 caracteres para buscar (solo participantes)</small>
                        @error('user_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    {{-- Programa --}}
                    <div class="col-md-6 mb-3">
                        <label for="program_id" class="form-label">Programa <span class="text-danger">*</span></label>
                        <select name="program_id" id="program_id" class="form-select @error('program_id') is-invalid @enderror" required>
                            <option value="">Seleccionar programa</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}" {{ old('program_id') == $program->id ? 'selected' : '' }}>
                                    {{ $program->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('program_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    {{-- Concepto de Pago (cargado dinámicamente + opción libre) --}}
                    <div class="col-md-6 mb-3">
                        <label for="program_requisite_id" class="form-label">Concepto de Pago <span class="text-danger">*</span></label>
                        <select name="program_requisite_id" id="program_requisite_id" class="form-select @error('program_requisite_id') is-invalid @enderror">
                            <option value="">Seleccionar concepto</option>
                        </select>
                        <input type="hidden" name="concept" id="concept_name" value="{{ old('concept') }}">
                        @error('program_requisite_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Concepto personalizado (visible si elige "Otro") --}}
                    <div class="col-md-6 mb-3" id="customConceptField" style="display: none;">
                        <label for="custom_concept" class="form-label">Especificar Concepto <span class="text-danger">*</span></label>
                        <input type="text" id="custom_concept" class="form-control" placeholder="Escriba el concepto del pago...">
                    </div>
                    
                    {{-- Monto --}}
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Monto <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" min="0" placeholder="0.00" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    {{-- Método de Pago --}}
                    <div class="col-md-6 mb-3">
                        <label for="payment_method" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            <option value="">Seleccionar método</option>
                            <option value="Transferencia bancaria" {{ old('payment_method') == 'Transferencia bancaria' ? 'selected' : '' }}>Transferencia bancaria</option>
                            <option value="Tarjeta de crédito" {{ old('payment_method') == 'Tarjeta de crédito' ? 'selected' : '' }}>Tarjeta de crédito</option>
                            <option value="PayPal" {{ old('payment_method') == 'PayPal' ? 'selected' : '' }}>PayPal</option>
                            <option value="Efectivo" {{ old('payment_method') == 'Efectivo' ? 'selected' : '' }}>Efectivo</option>
                            <option value="Otro" {{ old('payment_method') == 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    {{-- Referencia --}}
                    <div class="col-md-6 mb-3">
                        <label for="reference_number" class="form-label">Referencia <span class="text-danger">*</span></label>
                        <input type="text" name="reference_number" id="reference_number" class="form-control @error('reference_number') is-invalid @enderror" value="{{ old('reference_number') }}" placeholder="Número de transferencia, recibo, etc." required>
                        @error('reference_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Moneda --}}
                    <div class="col-md-6 mb-3">
                        <label for="currency_id" class="form-label">Moneda <span class="text-danger">*</span></label>
                        <select name="currency_id" id="currency_id" class="form-select @error('currency_id') is-invalid @enderror" required>
                            <option value="">Seleccionar moneda</option>
                            @foreach(\App\Models\Currency::all() as $currency)
                                <option value="{{ $currency->id }}" {{ $currency->code === 'USD' ? 'selected' : '' }}>
                                    {{ $currency->code }} - {{ $currency->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('currency_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Estado del Pago --}}
                    <div class="col-md-6 mb-3">
                        <label for="status" class="form-label">Estado del Pago <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="verified">Realizado</option>
                            <option value="pending" selected>Pendiente</option>
                        </select>
                        <small class="text-muted">
                            <strong>Realizado:</strong> Confirmado, se suma al total.
                            <strong>Pendiente:</strong> En proceso de verificación.
                        </small>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    {{-- Notas --}}
                    <div class="col-md-12 mb-3">
                        <label for="notes" class="form-label">Notas</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Observaciones adicionales...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('admin.finance.payments') }}" class="btn btn-secondary me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Registrar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // =============================================
        // Autocomplete de Participantes
        // =============================================
        const userSearch = document.getElementById('user_search');
        const userIdInput = document.getElementById('user_id');
        const userResults = document.getElementById('user_results');
        let searchTimeout = null;

        userSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                userResults.style.display = 'none';
                userResults.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                fetch(`{{ route('admin.finance.search-participants') }}?q=${encodeURIComponent(query)}`)
                    .then(r => r.json())
                    .then(users => {
                        userResults.innerHTML = '';
                        if (users.length === 0) {
                            userResults.innerHTML = '<div class="px-3 py-2 text-muted small">No se encontraron participantes</div>';
                        } else {
                            users.forEach(u => {
                                const item = document.createElement('div');
                                item.className = 'px-3 py-2 border-bottom cursor-pointer';
                                item.style.cursor = 'pointer';
                                item.innerHTML = `<strong>${u.name}</strong> <small class="text-muted">${u.email}</small>`;
                                item.addEventListener('click', () => {
                                    userIdInput.value = u.id;
                                    userSearch.value = `${u.name} (${u.email})`;
                                    userSearch.classList.add('border-success');
                                    userResults.style.display = 'none';
                                });
                                item.addEventListener('mouseenter', () => item.classList.add('bg-light'));
                                item.addEventListener('mouseleave', () => item.classList.remove('bg-light'));
                                userResults.appendChild(item);
                            });
                        }
                        userResults.style.display = 'block';
                    })
                    .catch(() => {
                        userResults.innerHTML = '<div class="px-3 py-2 text-danger small">Error al buscar</div>';
                        userResults.style.display = 'block';
                    });
            }, 300);
        });

        userSearch.addEventListener('focus', function() {
            if (userResults.children.length > 0) userResults.style.display = 'block';
        });

        document.addEventListener('click', function(e) {
            if (!userSearch.contains(e.target) && !userResults.contains(e.target)) {
                userResults.style.display = 'none';
            }
        });

        userSearch.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') userResults.style.display = 'none';
        });

        // Si se borra el texto, limpiar el user_id
        userSearch.addEventListener('change', function() {
            if (!this.value.trim()) {
                userIdInput.value = '';
                userSearch.classList.remove('border-success');
            }
        });

        // =============================================
        // Cargar requisitos de pago al seleccionar programa
        // =============================================
        const programSelect = document.getElementById('program_id');
        const requisiteSelect = document.getElementById('program_requisite_id');
        const conceptNameInput = document.getElementById('concept_name');
        const customConceptField = document.getElementById('customConceptField');
        const customConceptInput = document.getElementById('custom_concept');
        
        programSelect.addEventListener('change', function() {
            const programId = this.value;
            
            if (programId) {
                requisiteSelect.innerHTML = '<option value="">Cargando...</option>';
                
                fetch(`{{ route('admin.finance.payment-requisites') }}?program_id=${programId}`)
                    .then(response => response.json())
                    .then(data => {
                        requisiteSelect.innerHTML = '<option value="">Seleccionar concepto</option>';
                        
                        if (data.requisites.length === 0) {
                            requisiteSelect.innerHTML += '<option value="" disabled>No hay requisitos de pago para este programa</option>';
                        } else {
                            data.requisites.forEach(requisite => {
                                const amountLabel = requisite.payment_amount ? ` - $${parseFloat(requisite.payment_amount).toFixed(2)}` : '';
                                requisiteSelect.innerHTML += `<option value="${requisite.id}" data-amount="${requisite.payment_amount || ''}" data-name="${requisite.name}">${requisite.name}${amountLabel}</option>`;
                            });
                        }
                        // Always add "Otro" option for free text
                        requisiteSelect.innerHTML += '<option value="other" data-name="Otro">Otro (especificar)</option>';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        requisiteSelect.innerHTML = '<option value="">Error al cargar los requisitos</option>';
                    });
            } else {
                requisiteSelect.innerHTML = '<option value="">Seleccionar concepto</option>';
            }
        });
        
        // Handle concept selection: set hidden concept name and auto-fill amount
        requisiteSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            
            if (this.value === 'other') {
                customConceptField.style.display = 'block';
                conceptNameInput.value = '';
                // Clear the name attribute so "other" doesn't get sent as a real ID
                this.setAttribute('name', '');
            } else {
                customConceptField.style.display = 'none';
                this.setAttribute('name', 'program_requisite_id');
                conceptNameInput.value = selected.dataset.name || '';
                
                // Auto-fill amount if defined in requisite
                const suggestedAmount = selected.dataset.amount;
                if (suggestedAmount && !document.getElementById('amount').value) {
                    document.getElementById('amount').value = parseFloat(suggestedAmount).toFixed(2);
                }
            }
        });
        
        // Sync custom concept to hidden input
        if (customConceptInput) {
            customConceptInput.addEventListener('input', function() {
                conceptNameInput.value = this.value;
            });
        }
    });
</script>
@endpush
