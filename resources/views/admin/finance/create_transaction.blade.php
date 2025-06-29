@extends('layouts.admin')

@section('title', 'Nueva Transacción Financiera')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-plus-circle"></i> Nueva Transacción Financiera
                        </h3>
                        <a href="{{ route('admin.finance.transactions') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulario -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('admin.finance.transactions.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <!-- Información Básica -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Información Básica</h5>
                                
                                <div class="mb-3">
                                    <label for="type" class="form-label">Tipo de Transacción <span class="text-danger">*</span></label>
                                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="">Seleccionar tipo</option>
                                        <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Ingreso</option>
                                        <option value="expense" {{ old('type') === 'expense' ? 'selected' : '' }}>Egreso</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="category" class="form-label">Categoría <span class="text-danger">*</span></label>
                                    <select name="category" id="category" class="form-select @error('category') is-invalid @enderror" required>
                                        <option value="">Seleccionar categoría</option>
                                    </select>
                                    @error('category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Descripción <span class="text-danger">*</span></label>
                                    <input type="text" name="description" id="description" 
                                           class="form-control @error('description') is-invalid @enderror" 
                                           value="{{ old('description') }}" required>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="transaction_date" class="form-label">Fecha de Transacción <span class="text-danger">*</span></label>
                                    <input type="date" name="transaction_date" id="transaction_date" 
                                           class="form-control @error('transaction_date') is-invalid @enderror" 
                                           value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                    @error('transaction_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Información Financiera -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Información Financiera</h5>
                                
                                <div class="mb-3">
                                    <label for="amount" class="form-label">Monto <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" id="amount" step="0.01" min="0"
                                           class="form-control @error('amount') is-invalid @enderror" 
                                           value="{{ old('amount') }}" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="currency_id" class="form-label">Moneda</label>
                                    <select name="currency_id" id="currency_id" class="form-select @error('currency_id') is-invalid @enderror">
                                        <option value="">Guaraníes (PYG)</option>
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                                {{ $currency->name }} ({{ $currency->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('currency_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="payment_method" class="form-label">Método de Pago</label>
                                    <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
                                        <option value="">Seleccionar método</option>
                                        @foreach($paymentMethods as $key => $value)
                                            <option value="{{ $key }}" {{ old('payment_method') === $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="reference" class="form-label">Referencia/Número</label>
                                    <input type="text" name="reference" id="reference" 
                                           class="form-control @error('reference') is-invalid @enderror" 
                                           value="{{ old('reference') }}" placeholder="Ej: Factura #123, Transferencia #456">
                                    @error('reference')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Relaciones -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3">Relaciones (Opcional)</h5>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Usuario Relacionado</label>
                                    <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror">
                                        <option value="">Ninguno</option>
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
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="program_id" class="form-label">Programa Relacionado</label>
                                    <select name="program_id" id="program_id" class="form-select @error('program_id') is-invalid @enderror">
                                        <option value="">Ninguno</option>
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
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="application_id" class="form-label">Aplicación Relacionada</label>
                                    <select name="application_id" id="application_id" class="form-select @error('application_id') is-invalid @enderror">
                                        <option value="">Ninguna</option>
                                        @foreach($applications as $application)
                                            <option value="{{ $application->id }}" {{ old('application_id') == $application->id ? 'selected' : '' }}>
                                                ID {{ $application->id }} - {{ $application->user->name }} ({{ $application->program->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('application_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notas y Archivo -->
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notas Adicionales</label>
                                    <textarea name="notes" id="notes" rows="4" 
                                              class="form-control @error('notes') is-invalid @enderror" 
                                              placeholder="Cualquier información adicional relevante">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="receipt_file" class="form-label">Comprobante/Recibo</label>
                                    <input type="file" name="receipt_file" id="receipt_file" 
                                           class="form-control @error('receipt_file') is-invalid @enderror" 
                                           accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="form-text text-muted">
                                        Archivos permitidos: PDF, JPG, PNG. Tamaño máximo: 5MB
                                    </small>
                                    @error('receipt_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.finance.transactions') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save"></i> Guardar Transacción
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const categorySelect = document.getElementById('category');
    
    const incomeCategories = @json($incomeCategories);
    const expenseCategories = @json($expenseCategories);
    
    function updateCategories() {
        const type = typeSelect.value;
        const categories = type === 'income' ? incomeCategories : expenseCategories;
        
        // Limpiar opciones
        categorySelect.innerHTML = '<option value="">Seleccionar categoría</option>';
        
        // Agregar nuevas opciones
        Object.entries(categories).forEach(([key, value]) => {
            const option = document.createElement('option');
            option.value = key;
            option.textContent = value;
            if (key === '{{ old("category") }}') {
                option.selected = true;
            }
            categorySelect.appendChild(option);
        });
    }
    
    typeSelect.addEventListener('change', updateCategories);
    
    // Inicializar categorías si hay un tipo seleccionado
    if (typeSelect.value) {
        updateCategories();
    }
});
</script>
@endsection 