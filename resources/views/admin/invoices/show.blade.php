@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-file-invoice me-2"></i>Factura {{ $invoice->invoice_number }}
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            @if($invoice->status !== 'draft')
                <a href="{{ route('admin.invoices.download', $invoice->id) }}" class="btn btn-success">
                    <i class="fas fa-file-pdf me-2"></i>Descargar PDF
                </a>
            @endif
            @if($invoice->status === 'issued')
                <form action="{{ route('admin.invoices.mark-paid', $invoice->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-check me-2"></i>Marcar como Pagado
                    </button>
                </form>
            @endif
            @if($invoice->status !== 'paid' && $invoice->status !== 'cancelled')
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
            @endif
        </div>
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información de la Factura -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-file-invoice me-2"></i>Detalle de Factura
                </h6>
                @if($invoice->status === 'draft')
                    <span class="badge bg-secondary">{{ $invoice->status_label }}</span>
                @elseif($invoice->status === 'issued')
                    @if($invoice->isOverdue())
                        <span class="badge bg-danger">Vencida</span>
                    @else
                        <span class="badge bg-warning text-dark">{{ $invoice->status_label }}</span>
                    @endif
                @elseif($invoice->status === 'paid')
                    <span class="badge bg-success">{{ $invoice->status_label }}</span>
                @else
                    <span class="badge bg-dark">{{ $invoice->status_label }}</span>
                @endif
            </div>
            <div class="card-body">
                <!-- Encabezado -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="text-primary">Intercultural Experience</h5>
                        <p class="mb-0">
                            Asunción, Paraguay<br>
                            Email: admin@interculturalexperience.com<br>
                            Tel: +595 21 123 4567
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h3 class="text-primary">FACTURA</h3>
                        <p class="mb-0">
                            <strong>Número:</strong> {{ $invoice->invoice_number }}<br>
                            <strong>Fecha:</strong> {{ $invoice->issue_date->format('d/m/Y') }}
                            @if($invoice->due_date)
                                <br><strong>Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}
                            @endif
                        </p>
                    </div>
                </div>

                <hr>

                <!-- Datos del Cliente -->
                <div class="mb-4">
                    <h6 class="text-primary">FACTURAR A:</h6>
                    <p class="mb-0">
                        <strong>{{ $invoice->billing_name }}</strong><br>
                        {{ $invoice->billing_email }}<br>
                        @if($invoice->billing_tax_id)
                            RUC/Tax ID: {{ $invoice->billing_tax_id }}<br>
                        @endif
                        @if($invoice->billing_address)
                            {{ $invoice->billing_address }}<br>
                        @endif
                        @if($invoice->billing_city || $invoice->billing_country)
                            {{ $invoice->billing_city }}{{ $invoice->billing_city && $invoice->billing_country ? ', ' : '' }}{{ $invoice->billing_country }}
                        @endif
                    </p>
                </div>

                <hr>

                <!-- Concepto -->
                <div class="mb-4">
                    <h6 class="text-primary">CONCEPTO:</h6>
                    <p>{{ $invoice->concept }}</p>
                    @if($invoice->application)
                        <p class="mb-0">
                            <small class="text-muted">
                                <i class="fas fa-link me-1"></i>Relacionado con: {{ $invoice->application->program->name }}
                            </small>
                        </p>
                    @endif
                </div>

                <hr>

                <!-- Detalle de Montos -->
                <div class="table-responsive mb-4">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="text-end"><strong>Subtotal:</strong></td>
                                <td class="text-end" style="width:150px;">
                                    {{ $invoice->currency ? $invoice->currency->symbol : '₲' }} 
                                    {{ number_format($invoice->subtotal, 2, ',', '.') }}
                                </td>
                            </tr>
                            @if($invoice->tax_amount > 0)
                            <tr>
                                <td class="text-end">Impuestos:</td>
                                <td class="text-end">
                                    {{ $invoice->currency ? $invoice->currency->symbol : '₲' }} 
                                    {{ number_format($invoice->tax_amount, 2, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                            @if($invoice->discount_amount > 0)
                            <tr>
                                <td class="text-end text-success">Descuento:</td>
                                <td class="text-end text-success">
                                    -{{ $invoice->currency ? $invoice->currency->symbol : '₲' }} 
                                    {{ number_format($invoice->discount_amount, 2, ',', '.') }}
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-end"><h5><strong>TOTAL:</strong></h5></td>
                                <td class="text-end"><h5><strong>{{ $invoice->formatted_total }}</strong></h5></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                @if($invoice->notes)
                <div class="alert alert-info">
                    <strong><i class="fas fa-sticky-note me-2"></i>Notas:</strong><br>
                    {{ $invoice->notes }}
                </div>
                @endif

                @if($invoice->paid_date)
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Pagado el:</strong> {{ $invoice->paid_date->format('d/m/Y') }}
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Información Adicional -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow mb-3">
            <div class="card-header py-3 bg-primary text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-user me-2"></i>Cliente
                </h6>
            </div>
            <div class="card-body">
                <p><strong>{{ $invoice->user->name }}</strong></p>
                <p class="mb-0">
                    <i class="fas fa-envelope me-2"></i>{{ $invoice->user->email }}<br>
                    @if($invoice->user->phone)
                        <i class="fas fa-phone me-2"></i>{{ $invoice->user->phone }}
                    @endif
                </p>
            </div>
        </div>

        <div class="card shadow mb-3">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-info-circle me-2"></i>Información
                </h6>
            </div>
            <div class="card-body">
                <p class="mb-2">
                    <strong>Estado:</strong> {{ $invoice->status_label }}
                </p>
                <p class="mb-2">
                    <strong>Fecha de Emisión:</strong> {{ $invoice->issue_date->format('d/m/Y') }}
                </p>
                @if($invoice->due_date)
                <p class="mb-2">
                    <strong>Vencimiento:</strong> {{ $invoice->due_date->format('d/m/Y') }}
                    @if($invoice->isOverdue())
                        <br><span class="badge bg-danger">Vencida</span>
                    @endif
                </p>
                @endif
                @if($invoice->paid_date)
                <p class="mb-2">
                    <strong>Fecha de Pago:</strong> {{ $invoice->paid_date->format('d/m/Y') }}
                </p>
                @endif
                <p class="mb-0">
                    <strong>Creado por:</strong> {{ $invoice->createdBy->name }}
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cancelar -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cancelar Factura</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de cancelar esta factura?</p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
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
@endsection
