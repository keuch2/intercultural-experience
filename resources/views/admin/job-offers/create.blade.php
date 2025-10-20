@extends('layouts.admin')

@section('title', 'Nueva Oferta Laboral')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Nueva Oferta Laboral</h1>
            <p class="text-muted">Crea una nueva oferta de trabajo para participantes</p>
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
                    <form action="{{ route('admin.job-offers.store') }}" method="POST">
                        @csrf
                        
                        @include('admin.job-offers.form')
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Crear Oferta Laboral
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
                    <h6 class="m-0 font-weight-bold text-primary">Información</h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">
                        <i class="fas fa-info-circle"></i> 
                        Completa todos los campos requeridos para crear una nueva oferta laboral.
                    </p>
                    <hr>
                    <p class="small text-muted">
                        <i class="fas fa-lightbulb"></i> 
                        El ID de oferta debe ser único (ej: JO-2025-001).
                    </p>
                    <hr>
                    <p class="small text-muted">
                        <i class="fas fa-users"></i> 
                        Los cupos disponibles deben ser menores o iguales a los cupos totales.
                    </p>
                    <hr>
                    <p class="small text-muted">
                        <i class="fas fa-calendar"></i> 
                        La fecha de inicio debe ser posterior a hoy.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
