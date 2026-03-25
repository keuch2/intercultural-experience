{{-- Módulo 18: Gestión de Pagos — participant-centric payment overview --}}
@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Gestión de Pagos</h2>
        <p class="text-muted mb-0">Vista por participante: costo de programa, pagos y saldo</p>
    </div>
</div>

{{-- Filtros --}}
<div class="card shadow-sm mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.finance.payment-management') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Buscar</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Nombre o email..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Programa</label>
                <select name="program_id" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($programs as $program)
                    <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Estado de Pago</label>
                <select name="payment_status" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Pagado</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Con saldo</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Año</label>
                <select name="year" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    @foreach($years as $year)
                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="fas fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

{{-- Tabla de participantes --}}
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Participante</th>
                    <th>Programa</th>
                    <th>Año</th>
                    <th class="text-end">Costo Programa</th>
                    <th class="text-end">Total Pagado</th>
                    <th class="text-end">Saldo</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Pagos</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $app)
                @php
                    $totalCost = $app->total_cost ?? 0;
                    $costCurrency = $app->cost_currency ?? 'USD';
                    $verifiedPayments = $app->payments->where('status', 'verified');
                    $totalPaid = $verifiedPayments->sum(fn($p) => $p->converted_amount ?? $p->amount);
                    $balance = $totalCost - $totalPaid;
                    $pct = $totalCost > 0 ? min(100, round(($totalPaid / $totalCost) * 100)) : 0;
                @endphp
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="{{ $app->user->avatar_url }}" alt="" class="rounded-circle me-2" width="32" height="32">
                            <div>
                                <div class="fw-semibold small">{{ $app->user->name }}</div>
                                <small class="text-muted">{{ $app->user->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td><small>{{ $app->program->name ?? '-' }}</small></td>
                    <td><small>{{ $app->created_at->year }}</small></td>
                    <td class="text-end">
                        @if($totalCost > 0)
                            <span class="fw-semibold">{{ number_format($totalCost, 2) }}</span> <small class="text-muted">{{ $costCurrency }}</small>
                        @else
                            <span class="text-muted small">Sin asignar</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <span class="fw-semibold text-success">{{ number_format($totalPaid, 2) }}</span> <small class="text-muted">{{ $costCurrency }}</small>
                    </td>
                    <td class="text-end">
                        @if($totalCost > 0)
                            <span class="fw-semibold {{ $balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($balance, 2) }}</span> <small class="text-muted">{{ $costCurrency }}</small>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($totalCost > 0 && $pct >= 100)
                            <span class="badge bg-success">Pagado</span>
                        @elseif($totalCost > 0 && $pct > 0)
                            <span class="badge bg-warning text-dark">{{ $pct }}%</span>
                        @elseif($totalCost > 0)
                            <span class="badge bg-danger">Pendiente</span>
                        @else
                            <span class="badge bg-light text-dark">Sin costo</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <small>{{ $verifiedPayments->count() }} / {{ $app->payments->count() }}</small>
                    </td>
                    <td>
                        {{-- Link to Au Pair profile payments tab if Au Pair, otherwise general participant --}}
                        @php
                            $subcategory = $app->program->subcategory ?? '';
                        @endphp
                        @if($subcategory === 'Au Pair')
                            <a href="{{ route('admin.aupair.profiles.show', ['id' => $app->user_id, 'tab' => 'payments']) }}" class="btn btn-sm btn-outline-primary py-0" title="Ver perfil financiero">
                                <i class="fas fa-eye"></i>
                            </a>
                        @else
                            <a href="{{ route('admin.participants.show', $app->user_id) }}" class="btn btn-sm btn-outline-primary py-0" title="Ver perfil">
                                <i class="fas fa-eye"></i>
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        <i class="fas fa-search fa-3x mb-3 d-block opacity-25"></i>
                        No se encontraron participantes.
                        @if(request()->hasAny(['search', 'program_id', 'payment_status', 'year']))
                            <br><a href="{{ route('admin.finance.payment-management') }}">Limpiar filtros</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($applications->hasPages())
    <div class="card-footer">
        {{ $applications->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection
