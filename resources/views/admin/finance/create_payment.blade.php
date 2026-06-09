@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Registrar Pago Manual</h1>
        <a href="{{ route('admin.finance.payments') }}" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Volver a Pagos
        </a>
    </div>

    @if($errors->any())
    <div class="alert alert-danger py-2 px-3">
        <ul class="mb-0">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="card shadow mb-4">
        <div class="card-body text-center py-5">
            <i class="fas fa-money-bill-wave fa-2x text-primary mb-3"></i>
            <p class="text-muted">Registrá un pago buscando al participante y su programa.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#financeRegisterPayment">
                <i class="fas fa-plus me-1"></i> Registrar Pago
            </button>
        </div>
    </div>
</div>

{{-- Form unificado (mismos campos que Gestión de Pagos), en modo selección de participante --}}
@include('admin.payments._form', [
    'selectMode' => true,
    'programs' => $programs,
    'currencies' => $currencies,
    'application' => null,
    'modalId' => 'financeRegisterPayment',
    'redirectTo' => route('admin.finance.payments'),
    'title' => 'Registrar Pago',
])
@endsection
