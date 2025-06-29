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
                    <div class="col-md-6 mb-3">
                        <label for="user_id" class="form-label">Usuario <span class="text-danger">*</span></label>
                        <select name="user_id" id="user_id" class="form-control @error('user_id') is-invalid @enderror" required>
                            <option value="">Seleccionar usuario</option>
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
                    
                    <div class="col-md-6 mb-3">
                        <label for="program_id" class="form-label">Programa <span class="text-danger">*</span></label>
                        <select name="program_id" id="program_id" class="form-control @error('program_id') is-invalid @enderror" required>
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
                    
                    <div class="col-md-6 mb-3">
                        <label for="program_requisite_id" class="form-label">Concepto de Pago <span class="text-danger">*</span></label>
                        <select name="program_requisite_id" id="program_requisite_id" class="form-control @error('program_requisite_id') is-invalid @enderror" required>
                            <option value="">Seleccionar concepto</option>
                            @foreach($requisites as $requisite)
                                <option value="{{ $requisite->id }}" {{ old('program_requisite_id') == $requisite->id ? 'selected' : '' }}>
                                    {{ $requisite->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('program_requisite_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Monto <span id="currency-label">(₲)</span> <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="payment_method" class="form-label">Método de Pago <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-control @error('payment_method') is-invalid @enderror" required>
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
                    
                    <div class="col-md-6 mb-3">
                        <label for="reference" class="form-label">Referencia <span class="text-danger">*</span></label>
                        <input type="text" name="reference" id="reference" class="form-control @error('reference') is-invalid @enderror" value="{{ old('reference') }}" required>
                        <small class="form-text text-muted">Número de transferencia, recibo, etc.</small>
                        @error('reference')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-12 mb-3">
                        <label for="notes" class="form-label">Notas</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <p><strong>Nota:</strong> El sistema utilizará el campo "Observaciones" para almacenar el monto del pago y el campo "Ruta del archivo" para almacenar la referencia del pago. Esto es debido a la estructura actual de la base de datos.</p>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('admin.finance.payments') }}" class="btn btn-secondary me-md-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Registrar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cargar los requisitos de pago cuando se selecciona un programa
        const programSelect = document.getElementById('program_id');
        const requisiteSelect = document.getElementById('program_requisite_id');
        const currencyLabel = document.getElementById('currency-label');
        
        programSelect.addEventListener('change', function() {
            const programId = this.value;
            
            if (programId) {
                // Limpiar el select de requisitos
                requisiteSelect.innerHTML = '<option value="">Cargando...</option>';
                
                // Hacer la petición AJAX para obtener los requisitos
                fetch(`{{ route('admin.finance.payment-requisites') }}?program_id=${programId}`)
                    .then(response => response.json())
                    .then(data => {
                        requisiteSelect.innerHTML = '<option value="">Seleccionar concepto</option>';
                        
                        // Actualizar la moneda
                        if (data.program && data.program.currency) {
                            currencyLabel.textContent = `(${data.program.currency.symbol})`;
                        } else {
                            currencyLabel.textContent = '(₲)';
                        }
                        
                        if (data.requisites.length === 0) {
                            requisiteSelect.innerHTML += '<option value="" disabled>No hay requisitos de pago para este programa</option>';
                        } else {
                            data.requisites.forEach(requisite => {
                                requisiteSelect.innerHTML += `<option value="${requisite.id}">${requisite.name}</option>`;
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        requisiteSelect.innerHTML = '<option value="">Error al cargar los requisitos</option>';
                    });
            } else {
                requisiteSelect.innerHTML = '<option value="">Seleccionar concepto</option>';
                currencyLabel.textContent = '(₲)';
            }
        });
    });
</script>
@endpush
