@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-file-invoice me-2"></i>Facturas y Recibos
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Factura
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.invoices.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="status" class="form-label">Estado</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                    <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Emitido</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Pagado</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="date_from" class="form-label">Desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to" class="form-label">Hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label d-block">&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Facturas -->
<div class="card shadow">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>Lista de Facturas ({{ $invoices->total() }})
        </h6>
    </div>
    <div class="card-body">
        @if($invoices->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Concepto</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>
                                    <strong>{{ $invoice->invoice_number }}</strong>
                                </td>
                                <td>
                                    <i class="fas fa-user me-1"></i>{{ $invoice->user->name }}
                                    <br><small class="text-muted">{{ $invoice->billing_email }}</small>
                                </td>
                                <td>{{ Str::limit($invoice->concept, 30) }}</td>
                                <td>
                                    <strong>{{ $invoice->formatted_total }}</strong>
                                </td>
                                <td>
                                    <small>{{ $invoice->issue_date->format('d/m/Y') }}</small>
                                    @if($invoice->due_date)
                                        <br><small class="text-muted">Vence: {{ $invoice->due_date->format('d/m/Y') }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($invoice->status === 'draft')
                                        <span class="badge bg-secondary">{{ $invoice->status_label }}</span>
                                    @elseif($invoice->status === 'issued')
                                        @if($invoice->isOverdue())
                                            <span class="badge bg-danger">Vencido</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ $invoice->status_label }}</span>
                                        @endif
                                    @elseif($invoice->status === 'paid')
                                        <span class="badge bg-success">{{ $invoice->status_label }}</span>
                                    @else
                                        <span class="badge bg-dark">{{ $invoice->status_label }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.invoices.show', $invoice->id) }}" 
                                           class="btn btn-outline-primary"
                                           title="Ver">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($invoice->status !== 'draft')
                                            <a href="{{ route('admin.invoices.download', $invoice->id) }}" 
                                               class="btn btn-outline-success"
                                               title="Descargar PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @endif
                                        @if($invoice->status === 'issued')
                                            <form action="{{ route('admin.invoices.mark-paid', $invoice->id) }}" 
                                                  method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-info" title="Marcar pagado">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                                            <button type="button" 
                                                    class="btn btn-outline-danger"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#cancelModal{{ $invoice->id }}"
                                                    title="Cancelar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Cancelar -->
                            @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                            <div class="modal fade" id="cancelModal{{ $invoice->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Cancelar Factura</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p>¿Estás seguro de cancelar la factura <strong>{{ $invoice->invoice_number }}</strong>?</p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
                                            <form action="{{ route('admin.invoices.cancel', $invoice->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                @method('PUT')
                                                <button type="submit" class="btn btn-danger">Sí, Cancelar</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Mostrando {{ $invoices->firstItem() }} a {{ $invoices->lastItem() }} 
                    de {{ $invoices->total() }} facturas
                </div>
                <div>
                    {{ $invoices->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No hay facturas registradas</h5>
                <a href="{{ route('admin.invoices.create') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-plus me-2"></i>Crear Primera Factura
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
