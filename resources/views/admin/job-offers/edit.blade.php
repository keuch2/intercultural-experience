@extends('layouts.admin')

@section('title', 'Editar Oferta Laboral')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Editar Oferta Laboral</h1>
            <p class="text-muted">{{ $offer->job_offer_id }} - {{ $offer->position }}</p>
        </div>
        <a href="{{ route('admin.job-offers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <div class="col-md-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información de la Oferta</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.job-offers.update', $offer->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        @include('admin.job-offers.form')
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Oferta
                            </button>
                            <a href="{{ route('admin.job-offers.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estadísticas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Cupos Ocupados</small>
                        <h5>{{ $offer->total_slots - $offer->available_slots }}/{{ $offer->total_slots }}</h5>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Reservas</small>
                        <h5>{{ $offer->reservations()->count() }}</h5>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Estado</small>
                        <h5>
                            @if($offer->status == 'available')
                                <span class="badge badge-success">Disponible</span>
                            @elseif($offer->status == 'full')
                                <span class="badge badge-warning">Lleno</span>
                            @else
                                <span class="badge badge-danger">Cancelado</span>
                            @endif
                        </h5>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4 border-left-danger">
                <div class="card-header py-3 bg-danger text-white">
                    <h6 class="m-0 font-weight-bold">Zona de Peligro</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        Eliminar esta oferta es permanente y no se puede deshacer.
                    </p>
                    <form action="{{ route('admin.job-offers.destroy', $offer->id) }}" method="POST"
                          onsubmit="return confirm('¿Estás seguro de eliminar esta oferta? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm btn-block">
                            <i class="fas fa-trash"></i> Eliminar Oferta
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
