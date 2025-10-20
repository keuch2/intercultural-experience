@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-file-invoice me-2"></i>Nueva Factura
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-9">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-edit me-2"></i>Datos de la Factura
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.invoices.store') }}">
                    @csrf

                    <!-- Cliente -->
                    <h5 class="mb-3">Datos del Cliente</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="user_id" class="form-label">Participante <span class="text-danger">*</span></label>
                            <select class="form-select @error('user_id') is-invalid @enderror" 
                                    id="user_id" name="user_id" required>
                                <option value="">Seleccionar...</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="billing_name" class="form-label">Nombre para Factura <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('billing_name') is-invalid @enderror" 
                                   id="billing_name" name="billing_name" value="{{ old('billing_name') }}" required>
                            @error('billing_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="billing_email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('billing_email') is-invalid @enderror" 
                                   id="billing_email" name="billing_email" value="{{ old('billing_email') }}" required>
                            @error('billing_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="billing_tax_id" class="form-label">RUC / Tax ID</label>
                            <input type="text" class="form-control @error('billing_tax_id') is-invalid @enderror" 
                                   id="billing_tax_id" name="billing_tax_id" value="{{ old('billing_tax_id') }}">
                            @error('billing_tax_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="billing_address" class="form-label">Dirección</label>
                            <input type="text" class="form-control @error('billing_address') is-invalid @enderror" 
                                   id="billing_address" name="billing_address" value="{{ old('billing_address') }}">
                            @error('billing_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="billing_city" class="form-label">Ciudad</label>
                            <input type="text" class="form-control @error('billing_city') is-invalid @enderror" 
                                   id="billing_city" name="billing_city" value="{{ old('billing_city') }}">
                            @error('billing_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="billing_country" class="form-label">País</label>
                            <input type="text" class="form-control @error('billing_country') is-invalid @enderror" 
                                   id="billing_country" name="billing_country" value="{{ old('billing_country') }}">
                            @error('billing_country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr>

                    <!-- Concepto y Montos -->
                    <h5 class="mb-3">Concepto y Montos</h5>
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="concept" class="form-label">Concepto <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('concept') is-invalid @enderror" 
                                   id="concept" name="concept" value="{{ old('concept') }}" 
                                   placeholder="Ej: Pago de programa Work and Travel 2025" required>
                            @error('concept')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="subtotal" class="form-label">Subtotal <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('subtotal') is-invalid @enderror" 
                                   id="subtotal" name="subtotal" value="{{ old('subtotal') }}" required>
                            @error('subtotal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="tax_amount" class="form-label">Impuestos</label>
                            <input type="number" step="0.01" class="form-control @error('tax_amount') is-invalid @enderror" 
                                   id="tax_amount" name="tax_amount" value="{{ old('tax_amount', 0) }}">
                            @error('tax_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="discount_amount" class="form-label">Descuento</label>
                            <input type="number" step="0.01" class="form-control @error('discount_amount') is-invalid @enderror" 
                                   id="discount_amount" name="discount_amount" value="{{ old('discount_amount', 0) }}">
                            @error('discount_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="currency_id" class="form-label">Moneda</label>
                            <select class="form-select @error('currency_id') is-invalid @enderror" 
                                    id="currency_id" name="currency_id">
                                <option value="">Guaraníes (₲)</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                        {{ $currency->name }} ({{ $currency->symbol }})
                                    </option>
                                @endforeach
                            </select>
                            @error('currency_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Total</label>
                            <input type="text" class="form-control" id="total_display" readonly 
                                   value="0.00" style="font-weight:bold;font-size:1.2em;">
                        </div>
                    </div>

                    <hr>

                    <!-- Fechas y Estado -->
                    <h5 class="mb-3">Fechas y Estado</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="issue_date" class="form-label">Fecha de Emisión <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('issue_date') is-invalid @enderror" 
                                   id="issue_date" name="issue_date" value="{{ old('issue_date', date('Y-m-d')) }}" required>
                            @error('issue_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="due_date" class="form-label">Fecha de Vencimiento</label>
                            <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                   id="due_date" name="due_date" value="{{ old('due_date') }}">
                            @error('due_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="status" class="form-label">Estado <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status" required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                                <option value="issued" {{ old('status', 'issued') == 'issued' ? 'selected' : '' }}>Emitir</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="notes" class="form-label">Notas</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                  id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary me-md-2">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Crear Factura
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card shadow mb-3">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-info-circle me-2"></i>Información
                </h6>
            </div>
            <div class="card-body">
                <h6><i class="fas fa-check-circle text-success me-2"></i>Campos Obligatorios</h6>
                <ul class="small">
                    <li>Participante</li>
                    <li>Nombre para factura</li>
                    <li>Email</li>
                    <li>Concepto</li>
                    <li>Subtotal</li>
                    <li>Fecha de emisión</li>
                </ul>

                <hr>

                <h6><i class="fas fa-calculator text-warning me-2"></i>Cálculo</h6>
                <p class="small">
                    Total = Subtotal + Impuestos - Descuento
                </p>

                <hr>

                <h6><i class="fas fa-file-pdf text-danger me-2"></i>PDF</h6>
                <p class="small">
                    Si el estado es "Emitir", se generará el PDF automáticamente.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Calcular total automáticamente
function calculateTotal() {
    const subtotal = parseFloat(document.getElementById('subtotal').value) || 0;
    const tax = parseFloat(document.getElementById('tax_amount').value) || 0;
    const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
    
    const total = subtotal + tax - discount;
    document.getElementById('total_display').value = total.toFixed(2);
}

document.getElementById('subtotal').addEventListener('input', calculateTotal);
document.getElementById('tax_amount').addEventListener('input', calculateTotal);
document.getElementById('discount_amount').addEventListener('input', calculateTotal);

// Calcular al cargar
calculateTotal();
</script>
@endsection
