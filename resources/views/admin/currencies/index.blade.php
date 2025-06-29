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
                        <a href="{{ route('admin.currencies.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Agregar Nueva Moneda
                        </a>
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