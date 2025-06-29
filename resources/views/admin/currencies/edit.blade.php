@extends('layouts.admin')

@section('title', 'Editar Moneda')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Editar Moneda: {{ $currency->name }} ({{ $currency->code }})</h3>
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

                    <form action="{{ route('admin.currencies.update', $currency) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Código de Moneda *</label>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code', $currency->code) }}" 
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
                                           value="{{ old('name', $currency->name) }}" 
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
                                           value="{{ old('symbol', $currency->symbol) }}" 
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
                                               value="{{ old('exchange_rate_to_pyg', $currency->exchange_rate_to_pyg) }}" 
                                               placeholder="7300.0000"
                                               {{ $currency->code === 'PYG' ? 'readonly' : '' }}
                                               required>
                                        <span class="input-group-text">₲</span>
                                        @error('exchange_rate_to_pyg')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @if($currency->code === 'PYG')
                                        <small class="text-warning">El Guaraní es la moneda base, su cotización siempre es 1</small>
                                    @else
                                        <small class="text-muted">¿Cuántos guaraníes equivale 1 unidad de esta moneda?</small>
                                    @endif
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
                                               {{ old('is_active', $currency->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Moneda Activa
                                        </label>
                                    </div>
                                    <small class="text-muted">Solo las monedas activas aparecerán en los formularios</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Programas que usan esta moneda</label>
                                    <div class="form-control-plaintext">
                                        <span class="badge bg-info">{{ $currency->programs()->count() }} programas</span>
                                    </div>
                                    @if($currency->programs()->count() > 0)
                                        <small class="text-warning">Tenga cuidado al cambiar la cotización, afectará el precio de los programas</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Información Importante:</h6>
                                    <ul class="mb-0">
                                        <li>Los cambios en la cotización afectarán inmediatamente a todos los programas que usan esta moneda</li>
                                        <li>Si esta moneda está siendo usada por programas, no podrá ser eliminada</li>
                                        <li>Puede desactivar la moneda para evitar que se use en nuevos programas</li>
                                        @if($currency->code === 'PYG')
                                            <li><strong>Esta es la moneda base del sistema y no puede ser eliminada</strong></li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <div>
                                        @if($currency->programs()->count() > 0)
                                            <a href="{{ route('admin.currencies.show', $currency) }}" class="btn btn-info">
                                                <i class="fas fa-eye"></i> Ver Programas Asociados
                                            </a>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.currencies.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Actualizar Moneda
                                        </button>
                                    </div>
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

        // Si es PYG, mantener cotización en 1
        @if($currency->code === 'PYG')
        const exchangeRateInput = document.getElementById('exchange_rate_to_pyg');
        exchangeRateInput.value = '1.0000';
        exchangeRateInput.addEventListener('input', function() {
            this.value = '1.0000';
        });
        @endif
    });
</script>
@endpush 