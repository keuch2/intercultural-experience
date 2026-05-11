@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
    <h1 class="h3 mb-0"><i class="fas fa-users-cog me-2"></i> Gestión de Pagos</h1>
    <small class="text-muted">Aplicaciones con costo de programa creado</small>
</div>

@if(session('success'))
<div class="alert alert-success py-2 px-3">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger py-2 px-3">{{ session('error') }}</div>
@endif

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-0">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre o email" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">Programa</label>
                <select name="program_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-0">Año</label>
                <select name="year" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filtrar</button>
                <a href="{{ route('admin.payment-management.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        @if($applications->isEmpty())
            <div class="alert alert-info py-2 px-3 mb-0">
                <i class="fas fa-info-circle me-1"></i> No hay aplicaciones con costo de programa creado.
                Para crear un costo, ingresa al perfil del participante y configurarlo desde la sección de Pagos del programa.
            </div>
        @else
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Participante</th>
                        <th>Programa</th>
                        <th>Año</th>
                        <th class="text-end">Costo Total</th>
                        <th class="text-end">Pagado</th>
                        <th class="text-end">Saldo</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:90px;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($applications as $app)
                    @php
                        $paid = (float) ($paidByApp[$app->id] ?? 0);
                        $cost = (float) ($app->total_cost ?? 0);
                        $balance = $cost - $paid;
                        $costCurrency = $app->cost_currency ?? 'USD';
                        $appDate = $app->cost_manual_date ?? $app->created_at;
                        $isPaid = $cost > 0 && $balance <= 0;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ optional($app->user)->name ?? '—' }}</strong>
                            <br><small class="text-muted">{{ optional($app->user)->email }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ optional($app->program)->main_category ?? '—' }}</span>
                            {{ optional($app->program)->subcategory ?? optional($app->program)->name }}
                        </td>
                        <td><small>{{ $appDate ? \Illuminate\Support\Carbon::parse($appDate)->format('Y') : '—' }}</small></td>
                        <td class="text-end"><strong>{{ $costCurrency }} {{ number_format($cost, 2) }}</strong></td>
                        <td class="text-end text-success">{{ $costCurrency }} {{ number_format($paid, 2) }}</td>
                        <td class="text-end text-{{ $balance > 0 ? 'warning' : 'success' }}">{{ $costCurrency }} {{ number_format(max($balance, 0), 2) }}</td>
                        <td class="text-center">
                            @if($isPaid)
                                <span class="badge bg-success">Pagado</span>
                            @elseif($paid > 0)
                                <span class="badge bg-warning text-dark">Parcial</span>
                            @else
                                <span class="badge bg-danger">Restante</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('admin.payment-management.show', $app->id) }}" class="btn btn-sm btn-outline-primary" title="Ver perfil financiero">
                                <i class="fas fa-eye me-1"></i>Ver
                            </a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $applications->links() }}</div>
        @endif
    </div>
</div>
@endsection
