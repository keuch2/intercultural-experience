@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
    <h1 class="h3 mb-0"><i class="fas fa-users-cog me-2"></i> Gestión de Pagos</h1>
    <div class="d-flex gap-2 align-items-center">
        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#createUserCostModal">
            <i class="fas fa-plus me-1"></i> Crear Costo de Programa
        </button>
        @if(request('cost_filter') === 'without_cost')
            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#bulkAssignCostModal" id="bulkAssignCostBtn" disabled>
                <i class="fas fa-money-check-alt me-1"></i> Asignar Costo (selección)
                <span class="badge bg-light text-primary ms-1" id="bulkSelectionCount">0</span>
            </button>
        @endif
        <small class="text-muted">{{ request('cost_filter') === 'without_cost' ? 'Aplicaciones sin costo' : 'Aplicaciones con costo de programa' }}</small>
    </div>
</div>

{{-- A8: Modal Crear Costo de Programa (buscar usuario + programa + costo) --}}
<div class="modal fade" id="createUserCostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.payment-management.store-user-cost') }}">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-money-check-alt me-1"></i> Crear Costo de Programa</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 position-relative">
                        <label class="form-label small">Participante <span class="text-danger">*</span></label>
                        <input type="text" id="cost_user_search" class="form-control form-control-sm" autocomplete="off" placeholder="Buscar por nombre o email (mín. 2)">
                        <input type="hidden" name="user_id" id="cost_user_id">
                        <div id="cost_user_results" class="position-absolute bg-white border rounded shadow-sm w-100" style="z-index:1080; display:none; max-height:220px; overflow-y:auto;"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Programa <span class="text-danger">*</span></label>
                        <select name="program_id" class="form-select form-select-sm" required>
                            <option value="">Seleccionar...</option>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-7">
                            <label class="form-label small">Costo total <span class="text-danger">*</span></label>
                            <input type="number" name="total_cost" step="0.01" min="0" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label small">Moneda <span class="text-danger">*</span></label>
                            <select name="cost_currency" class="form-select form-select-sm" required>
                                <option value="USD">USD</option>
                                <option value="PYG">PYG</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Fecha límite de pago</label>
                            <input type="date" name="payment_deadline" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-save me-1"></i>Crear</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const search = document.getElementById('cost_user_search');
        const idInput = document.getElementById('cost_user_id');
        const results = document.getElementById('cost_user_results');
        if (!search) return;
        let t = null;
        search.addEventListener('input', function () {
            clearTimeout(t);
            const q = this.value.trim();
            idInput.value = '';
            if (q.length < 2) { results.style.display = 'none'; results.innerHTML = ''; return; }
            t = setTimeout(() => {
                fetch(`{{ route('admin.finance.search-participants') }}?q=${encodeURIComponent(q)}`)
                    .then(r => r.json())
                    .then(users => {
                        results.innerHTML = '';
                        if (!users.length) {
                            results.innerHTML = '<div class="px-3 py-2 text-muted small">Sin resultados</div>';
                        } else {
                            users.forEach(u => {
                                const item = document.createElement('div');
                                item.className = 'px-3 py-2 border-bottom';
                                item.style.cursor = 'pointer';
                                item.innerHTML = `<strong>${u.name}</strong> <small class="text-muted">${u.email}</small>`;
                                item.addEventListener('click', () => {
                                    idInput.value = u.id;
                                    search.value = `${u.name} (${u.email})`;
                                    search.classList.add('border-success');
                                    results.style.display = 'none';
                                });
                                item.addEventListener('mouseenter', () => item.classList.add('bg-light'));
                                item.addEventListener('mouseleave', () => item.classList.remove('bg-light'));
                                results.appendChild(item);
                            });
                        }
                        results.style.display = 'block';
                    })
                    .catch(() => { results.innerHTML = '<div class="px-3 py-2 text-danger small">Error</div>'; results.style.display = 'block'; });
            }, 300);
        });
        document.addEventListener('click', function (e) {
            if (!search.contains(e.target) && !results.contains(e.target)) results.style.display = 'none';
        });
    })();
</script>
@endpush

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
            <div class="col-md-2">
                <label class="form-label small mb-0">Costo</label>
                <select name="cost_filter" class="form-select form-select-sm">
                    <option value="with_cost" {{ request('cost_filter', 'with_cost') === 'with_cost' ? 'selected' : '' }}>Con costo</option>
                    <option value="without_cost" {{ request('cost_filter') === 'without_cost' ? 'selected' : '' }}>Sin costo</option>
                    <option value="all" {{ request('cost_filter') === 'all' ? 'selected' : '' }}>Todas</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search me-1"></i>Filtrar</button>
                <a href="{{ route('admin.payment-management.index') }}" class="btn btn-sm btn-outline-secondary mt-1">Limpiar</a>
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
        @php $bulkMode = request('cost_filter') === 'without_cost'; @endphp
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        @if($bulkMode)
                            <th style="width:30px;">
                                <input type="checkbox" id="bulkSelectAll" class="form-check-input">
                            </th>
                        @endif
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
                        @if($bulkMode)
                            <td>
                                <input type="checkbox" name="bulk_application_ids[]" value="{{ $app->id }}" class="form-check-input bulk-app-checkbox">
                            </td>
                        @endif
                        <td>
                            <strong>{{ optional($app->user)->name ?? '—' }}</strong>
                            <br><small class="text-muted">{{ optional($app->user)->email }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark">{{ optional($app->program)->main_category ?? '—' }}</span>
                            {{ optional($app->program)->subcategory ?? optional($app->program)->name }}
                        </td>
                        <td><small>{{ $appDate ? \Illuminate\Support\Carbon::parse($appDate)->format('Y') : '—' }}</small></td>
                        <td class="text-end">
                            @if($cost > 0)
                                <strong>{{ $costCurrency }} {{ number_format($cost, 2) }}</strong>
                            @else
                                <span class="text-muted small">Sin asignar</span>
                            @endif
                        </td>
                        <td class="text-end text-success">{{ $costCurrency }} {{ number_format($paid, 2) }}</td>
                        <td class="text-end text-{{ $balance > 0 ? 'warning' : 'success' }}">{{ $costCurrency }} {{ number_format(max($balance, 0), 2) }}</td>
                        <td class="text-center">
                            @if($cost <= 0)
                                <span class="badge bg-secondary">Sin costo</span>
                            @elseif($isPaid)
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

@if(request('cost_filter') === 'without_cost')
{{-- Modal: Registrar Costo de Programa masivamente --}}
<div class="modal fade" id="bulkAssignCostModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.payment-management.bulk-assign-cost') }}" id="bulkAssignCostForm">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title">
                        <i class="fas fa-money-check-alt me-1"></i> Registrar Costo de Programa
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 px-3 mb-3">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Se asignará el mismo costo a <strong id="bulkSelectionCountModal">0</strong> aplicación(es) seleccionada(s).
                            Las aplicaciones con costo bloqueado serán rechazadas.
                        </small>
                    </div>
                    <div id="bulkSelectedAppIds"></div>
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small">Costo Total <span class="text-danger">*</span></label>
                            <input type="number" name="total_cost" step="0.01" min="0" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small">Moneda <span class="text-danger">*</span></label>
                            <select name="cost_currency" class="form-select form-select-sm" required>
                                <option value="USD">USD - Dólar</option>
                                <option value="PYG">PYG - Guaraní</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Fecha límite de pago</label>
                            <input type="date" name="payment_deadline" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Tipo de cambio</label>
                            <input type="number" name="exchange_rate" step="0.01" min="0" class="form-control form-control-sm" placeholder="Ej: 7300">
                            <small class="text-muted">Si moneda ≠ del pago</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-save me-1"></i> Asignar Costo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    const checkboxes = document.querySelectorAll('.bulk-app-checkbox');
    const selectAll = document.getElementById('bulkSelectAll');
    const btn = document.getElementById('bulkAssignCostBtn');
    const countBadge = document.getElementById('bulkSelectionCount');
    const countModal = document.getElementById('bulkSelectionCountModal');
    const hiddenIds = document.getElementById('bulkSelectedAppIds');

    const refresh = () => {
        const checked = Array.from(checkboxes).filter(c => c.checked);
        const count = checked.length;
        if (countBadge) countBadge.textContent = count;
        if (countModal) countModal.textContent = count;
        if (btn) btn.disabled = count === 0;
        if (hiddenIds) {
            hiddenIds.innerHTML = checked.map(c =>
                `<input type="hidden" name="application_ids[]" value="${c.value}">`
            ).join('');
        }
    };

    checkboxes.forEach(c => c.addEventListener('change', refresh));
    if (selectAll) {
        selectAll.addEventListener('change', () => {
            checkboxes.forEach(c => { c.checked = selectAll.checked; });
            refresh();
        });
    }
    refresh();
})();
</script>
@endpush
@endif
@endsection
