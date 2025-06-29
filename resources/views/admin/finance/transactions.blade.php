@extends('layouts.admin')

@section('title', 'Transacciones Financieras')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-exchange-alt"></i> Transacciones Financieras
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.finance.transactions.create') }}" class="btn btn-success">
                                <i class="fas fa-plus"></i> Nueva Transacción
                            </a>
                            <a href="{{ route('admin.finance.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver a Finanzas
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter"></i> Filtros
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.finance.transactions') }}">
                        <div class="row">
                            <div class="col-md-2">
                                <label for="type" class="form-label">Tipo</label>
                                <select name="type" id="type" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="income" {{ request('type') === 'income' ? 'selected' : '' }}>Ingresos</option>
                                    <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>Egresos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="category" class="form-label">Categoría</label>
                                <select name="category" id="category" class="form-select">
                                    <option value="">Todas</option>
                                    <optgroup label="Ingresos">
                                        @foreach($incomeCategories as $key => $value)
                                            <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Egresos">
                                        @foreach($expenseCategories as $key => $value)
                                            <option value="{{ $key }}" {{ request('category') === $key ? 'selected' : '' }}>{{ $value }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Estado</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                                    <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_from" class="form-label">Desde</label>
                                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to" class="form-label">Hasta</label>
                                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen -->
    @php
        $totalIncome = $transactions->where('type', 'income')->where('status', 'confirmed')->sum('amount_pyg');
        $totalExpense = $transactions->where('type', 'expense')->where('status', 'confirmed')->sum('amount_pyg');
        $netBalance = $totalIncome - $totalExpense;
    @endphp
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Total Ingresos</h5>
                    <h3>₲ {{ number_format($totalIncome, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5>Total Egresos</h5>
                    <h3>₲ {{ number_format($totalExpense, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-{{ $netBalance >= 0 ? 'primary' : 'warning' }} text-white">
                <div class="card-body">
                    <h5>Balance Neto</h5>
                    <h3>₲ {{ number_format($netBalance, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5>Total Transacciones</h5>
                    <h3>{{ $transactions->total() }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Transacciones -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Lista de Transacciones</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Categoría</th>
                                    <th>Descripción</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Relacionado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $transaction)
                                <tr>
                                    <td>{{ $transaction->transaction_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->type === 'income' ? 'success' : 'danger' }}">
                                            {{ $transaction->getTypeLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $categories = $transaction->type === 'income' ? $incomeCategories : $expenseCategories;
                                        @endphp
                                        <small>{{ $categories[$transaction->category] ?? $transaction->category }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $transaction->description }}</strong>
                                        @if($transaction->reference)
                                            <br><small class="text-muted">Ref: {{ $transaction->reference }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $transaction->getFormattedAmountPygAttribute() }}</strong>
                                        @if($transaction->currency && $transaction->currency->code !== 'PYG')
                                            <br><small class="text-muted">{{ $transaction->getFormattedAmountAttribute() }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $transaction->status === 'confirmed' ? 'success' : ($transaction->status === 'pending' ? 'warning' : 'secondary') }}">
                                            {{ $transaction->getStatusLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($transaction->user)
                                            <small>Usuario: {{ $transaction->user->name }}</small><br>
                                        @endif
                                        @if($transaction->program)
                                            <small>Programa: {{ $transaction->program->name }}</small><br>
                                        @endif
                                        @if($transaction->application)
                                            <small>App ID: {{ $transaction->application->id }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.finance.transactions.show', $transaction) }}" class="btn btn-info btn-sm" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.finance.transactions.edit', $transaction) }}" class="btn btn-warning btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ route('admin.finance.transactions.destroy', $transaction) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('¿Está seguro de eliminar esta transacción?')" 
                                                        title="Eliminar">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">
                                        <i class="fas fa-inbox fa-2x mb-2"></i>
                                        <p>No hay transacciones registradas</p>
                                        <a href="{{ route('admin.finance.transactions.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Crear Primera Transacción
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    @if($transactions->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $transactions->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 