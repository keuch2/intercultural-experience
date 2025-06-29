@extends('layouts.admin')

@section('title', 'Agregar Nueva Moneda')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Agregar Nueva Moneda</h3>
                        <a href="{{ route('admin.currencies.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver a Lista
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <h5>Por favor corrige los siguientes errores:</h5>
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.currencies.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Código de Moneda *</label>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code') }}" 
                                           maxlength="3"
                                           placeholder="USD, EUR, BRL, etc."
                                           style="text-transform: uppercase;"
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Código de 3 letras según ISO 4217</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre de la Moneda *</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           placeholder="Dólar Estadounidense, Euro, etc."
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="symbol" class="form-label">Símbolo *</label>
                                    <input type="text" 
                                           class="form-control @error('symbol') is-invalid @enderror" 
                                           id="symbol" 
                                           name="symbol" 
                                           value="{{ old('symbol') }}" 
                                           maxlength="10"
                                           placeholder="$, €, R$, etc."
                                           required>
                                    @error('symbol')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Símbolo que se mostrará junto al precio</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="exchange_rate_to_pyg" class="form-label">Cotización en Guaraníes *</label>
                                    <div class="input-group">
                                        <input type="number" 
                                               step="0.0001" 
                                               class="form-control @error('exchange_rate_to_pyg') is-invalid @enderror" 
                                               id="exchange_rate_to_pyg" 
                                               name="exchange_rate_to_pyg" 
                                               value="{{ old('exchange_rate_to_pyg') }}" 
                                               placeholder="7300.0000"
                                               required>
                                        <span class="input-group-text">₲</span>
                                        @error('exchange_rate_to_pyg')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="text-muted">¿Cuántos guaraníes equivale 1 unidad de esta moneda?</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="is_active" 
                                               name="is_active" 
                                               value="1"
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Moneda Activa
                                        </label>
                                    </div>
                                    <small class="text-muted">Solo las monedas activas aparecerán en los formularios</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Información Importante:</h6>
                                    <ul class="mb-0">
                                        <li>El Guaraní Paraguayo (PYG) es la moneda base del sistema</li>
                                        <li>La cotización indica cuántos guaraníes equivale 1 unidad de esta moneda</li>
                                        <li>Ejemplo: Si 1 USD = 7,300 PYG, ingrese 7300 en el campo de cotización</li>
                                        <li>Puede actualizar las cotizaciones en cualquier momento desde la lista</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('admin.currencies.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Guardar Moneda
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-uppercase para el código de moneda
        const codeInput = document.getElementById('code');
        codeInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
        });

        // Sugerencias de símbolos comunes
        const symbolSuggestions = {
            'USD': '$',
            'EUR': '€',
            'BRL': 'R$',
            'ARS': '$',
            'PYG': '₲',
            'GBP': '£',
            'JPY': '¥',
            'CAD': 'C$',
            'AUD': 'A$'
        };

        codeInput.addEventListener('change', function() {
            const symbolInput = document.getElementById('symbol');
            if (symbolSuggestions[this.value] && !symbolInput.value) {
                symbolInput.value = symbolSuggestions[this.value];
            }
        });
    });
</script>
@endpush 