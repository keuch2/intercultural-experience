@extends('layouts.admin')

@section('title', 'Gestión de Valores - Monedas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Gestión de Valores - Monedas</h3>
                        <div class="btn-group me-2">
                            <a href="{{ route('admin.currencies.create') }}" class="btn btn-sm btn-success">
                                <i class="fas fa-plus"></i> Nueva Moneda
                            </a>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#converterModal">
                                <i class="fas fa-exchange-alt"></i> Conversor
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Formulario para actualizar cotizaciones rápidamente -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Actualización Rápida de Cotizaciones</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.currencies.updateRates') }}" method="POST">
                                @csrf
                                <div class="row">
                                    @foreach($currencies as $currency)
                                        @if($currency->code !== 'PYG')
                                            <div class="col-md-3 mb-3">
                                                <label for="currency_{{ $currency->id }}" class="form-label">
                                                    {{ $currency->code }} ({{ $currency->symbol }})
                                                </label>
                                                <div class="input-group">
                                                    <input type="number" 
                                                           step="0.0001" 
                                                           class="form-control" 
                                                           id="currency_{{ $currency->id }}"
                                                           name="currencies[{{ $currency->id }}]" 
                                                           value="{{ $currency->exchange_rate_to_pyg }}"
                                                           required>
                                                    <span class="input-group-text">₲</span>
                                                </div>
                                                <small class="text-muted">1 {{ $currency->code }} = X Guaraníes</small>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-sync"></i> Actualizar Cotizaciones
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de monedas -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Símbolo</th>
                                    <th>Cotización en ₲</th>
                                    <th>Estado</th>
                                    <th>Programas</th>
                                    <th>Última Actualización</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($currencies as $currency)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">{{ $currency->code }}</span>
                                        </td>
                                        <td>{{ $currency->name }}</td>
                                        <td>
                                            <span class="fw-bold">{{ $currency->symbol }}</span>
                                        </td>
                                        <td>
                                            @if($currency->code === 'PYG')
                                                <span class="text-muted">Base</span>
                                            @else
                                                <span class="fw-bold">₲ {{ number_format($currency->exchange_rate_to_pyg, 2) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $currency->is_active ? 'success' : 'danger' }}">
                                                {{ $currency->is_active ? 'Activa' : 'Inactiva' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $currency->programs_count ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $currency->updated_at->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.currencies.show', $currency) }}" 
                                                   class="btn btn-sm btn-outline-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.currencies.edit', $currency) }}" 
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($currency->programs_count == 0)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteModal{{ $currency->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Modal de confirmación de eliminación -->
                                    <div class="modal fade" id="deleteModal{{ $currency->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Confirmar Eliminación</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    ¿Está seguro que desea eliminar la moneda <strong>{{ $currency->name }} ({{ $currency->code }})</strong>?
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <form action="{{ route('admin.currencies.destroy', $currency) }}" method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-coins fa-3x mb-3 d-block"></i>
                                            No hay monedas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal Conversor de Monedas -->
<div class="modal fade" id="converterModal" tabindex="-1" aria-labelledby="converterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="converterModalLabel">
                    <i class="fas fa-exchange-alt"></i> Conversor de Monedas
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Convertir Monto</h6>
                            </div>
                            <div class="card-body">
                                <form id="converterForm">
                                    <div class="mb-3">
                                        <label class="form-label">Monto</label>
                                        <input type="number" step="0.01" class="form-control" id="amount" placeholder="0.00" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label">De</label>
                                            <select class="form-select" id="fromCurrency" required>
                                                @foreach($currencies as $currency)
                                                    <option value="{{ $currency->code }}">{{ $currency->code }} - {{ $currency->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">A</label>
                                            <select class="form-select" id="toCurrency" required>
                                                @foreach($currencies as $currency)
                                                    <option value="{{ $currency->code }}" {{ $currency->code == 'PYG' ? 'selected' : '' }}>{{ $currency->code }} - {{ $currency->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-calculator"></i> Convertir
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">Resultado</h6>
                            </div>
                            <div class="card-body">
                                <div id="conversionResult" class="text-center p-4">
                                    <i class="fas fa-calculator text-muted fa-3x mb-3"></i>
                                    <p class="text-muted">Ingresa un monto y selecciona las monedas para convertir</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card mt-3">
                            <div class="card-header">
                                <h6 class="mb-0">Tasas Actuales (Base: PYG)</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Moneda</th>
                                                <th>Tasa</th>
                                                <th>Actualizado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($currencies->where('code', '!=', 'PYG') as $currency)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-primary">{{ $currency->code }}</span>
                                                    {{ $currency->symbol }}
                                                </td>
                                                <td>{{ number_format($currency->exchange_rate_to_pyg, 0) }} PYG</td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $currency->updated_at->diffForHumans() }}
                                                    </small>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.getElementById('converterForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const amount = document.getElementById('amount').value;
    const fromCurrency = document.getElementById('fromCurrency').value;
    const toCurrency = document.getElementById('toCurrency').value;
    
    if (!amount || !fromCurrency || !toCurrency) {
        alert('Por favor completa todos los campos');
        return;
    }
    
    try {
        const response = await fetch('{{ route("admin.currencies.convert") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                amount: parseFloat(amount),
                from_currency: fromCurrency,
                to_currency: toCurrency
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const resultDiv = document.getElementById('conversionResult');
            resultDiv.innerHTML = `
                <div class="conversion-success">
                    <h4 class="text-success">${formatCurrency(data.converted_amount)} ${data.to_currency}</h4>
                    <p class="mb-2">
                        <strong>${formatCurrency(data.original_amount)} ${data.from_currency}</strong>
                        <i class="fas fa-arrow-right mx-2 text-primary"></i>
                        <strong>${formatCurrency(data.converted_amount)} ${data.to_currency}</strong>
                    </p>
                    <small class="text-muted">
                        Tasa utilizada: 1 ${data.from_currency} = ${data.rate_used} ${data.to_currency}<br>
                        Calculado: ${new Date(data.calculation_date).toLocaleString()}
                    </small>
                </div>
            `;
        } else {
            throw new Error('Error en la conversión');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al realizar la conversión. Por favor intenta de nuevo.');
    }
});

function formatCurrency(amount) {
    return new Intl.NumberFormat('es-PY').format(amount);
}
</script>
@endsection

@push('scripts')
<script>
    // Auto-submit form cuando se cambian las cotizaciones
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = document.querySelectorAll('input[name^="currencies"]');
        inputs.forEach(input => {
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.form.submit();
                }
            });
        });
    });
</script>
@endpush 