@extends('layouts.admin')

@section('title', 'Editar Requisito - ' . $program->name)

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Editar Requisito</h1>
    
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        @if($program->main_category === 'IE')
            <li class="breadcrumb-item"><a href="{{ route('admin.ie-programs.index') }}">Programas IE</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.ie-programs.show', $program->id) }}">{{ $program->name }}</a></li>
        @else
            <li class="breadcrumb-item"><a href="{{ route('admin.yfu-programs.index') }}">Programas YFU</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.yfu-programs.show', $program->id) }}">{{ $program->name }}</a></li>
        @endif
        <li class="breadcrumb-item"><a href="{{ route('admin.programs.requisites.index', $program->id) }}">Requisitos</a></li>
        <li class="breadcrumb-item active">Editar</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            Editar Requisito
        </div>
        <div class="card-body">
            <form action="{{ route('admin.programs.requisites.update', [$program->id, $requisite->id]) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $requisite->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Descripción</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $requisite->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="type" class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required onchange="togglePaymentFields()">
                        <option value="document" {{ old('type', $requisite->type) == 'document' ? 'selected' : '' }}>Documento</option>
                        <option value="action" {{ old('type', $requisite->type) == 'action' ? 'selected' : '' }}>Acción</option>
                        <option value="payment" {{ old('type', $requisite->type) == 'payment' ? 'selected' : '' }}>Pago</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <!-- Campos de Pago (solo visible cuando type = payment) -->
                <div id="payment-fields" style="display: none;">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="payment_amount" class="form-label">Monto del Pago</label>
                            <input type="number" class="form-control @error('payment_amount') is-invalid @enderror" 
                                   id="payment_amount" name="payment_amount" value="{{ old('payment_amount', $requisite->payment_amount) }}" 
                                   min="0" step="0.01">
                            @error('payment_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="currency_id" class="form-label">Moneda</label>
                            <select class="form-select @error('currency_id') is-invalid @enderror" 
                                    id="currency_id" name="currency_id">
                                <option value="">Seleccionar moneda</option>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}" {{ old('currency_id', $requisite->currency_id) == $currency->id ? 'selected' : '' }}>
                                        {{ $currency->name }} ({{ $currency->symbol }})
                                    </option>
                                @endforeach
                            </select>
                            @error('currency_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input @error('is_required') is-invalid @enderror" type="checkbox" id="is_required" name="is_required" value="1" {{ old('is_required', $requisite->is_required) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_required">
                            Requisito obligatorio
                        </label>
                        @error('is_required')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="order" class="form-label">Orden</label>
                    <input type="number" class="form-control @error('order') is-invalid @enderror" id="order" name="order" value="{{ old('order', $requisite->order) }}" min="0">
                    @error('order')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex justify-content-end">
                    <a href="{{ route('admin.programs.requisites.index', $program->id) }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function togglePaymentFields() {
    const typeSelect = document.getElementById('type');
    const paymentFields = document.getElementById('payment-fields');
    
    if (typeSelect.value === 'payment') {
        paymentFields.style.display = 'block';
    } else {
        paymentFields.style.display = 'none';
    }
}

// Ejecutar al cargar la página para mostrar campos si hay errores de validación
document.addEventListener('DOMContentLoaded', function() {
    togglePaymentFields();
});
</script>
@endpush

@endsection
