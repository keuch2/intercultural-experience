@extends('layouts.admin')

@section('title', 'Crear Empresa Host')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Crear Nueva Empresa Host</h1>
            <p class="text-muted">Registra una nueva empresa que ofrecerá posiciones laborales</p>
        </div>
        <a href="{{ route('admin.host-companies.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información de la Empresa</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.host-companies.store') }}" method="POST">
                        @csrf
                        @include('admin.host-companies.form')
                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.host-companies.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Empresa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Información
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">¿Qué es una Empresa Host?</h6>
                    <p class="small text-muted">
                        Las empresas host son los empleadores que ofrecen posiciones laborales 
                        a participantes de programas de intercambio cultural.
                    </p>
                    <h6 class="font-weight-bold mt-3">Ejemplos:</h6>
                    <ul class="small text-muted">
                        <li>Hoteles y resorts</li>
                        <li>Parques temáticos</li>
                        <li>Restaurantes</li>
                        <li>Tiendas retail</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
