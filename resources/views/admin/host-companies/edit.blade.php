@extends('layouts.admin')

@section('title', 'Editar Empresa Host')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Editar Empresa Host</h1>
            <p class="text-muted">{{ $company->name }}</p>
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
                    <form action="{{ route('admin.host-companies.update', $company->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @include('admin.host-companies.form')
                        <hr>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.host-companies.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar Empresa
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
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <h3 class="text-primary">{{ $company->jobOffers()->count() }}</h3>
                        <small class="text-muted">Ofertas Laborales</small>
                    </div>
                    <hr>
                    <div class="mb-2">
                        <small class="text-muted">Creado</small>
                        <p class="mb-0">{{ $company->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <small class="text-muted">Actualizado</small>
                        <p class="mb-0">{{ $company->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">
                        <i class="fas fa-trash"></i> Zona de Peligro
                    </h6>
                </div>
                <div class="card-body">
                    <p class="small text-muted">Eliminar esta empresa es permanente.</p>
                    <form action="{{ route('admin.host-companies.destroy', $company->id) }}" 
                          method="POST"
                          onsubmit="return confirm('¿Estás seguro de eliminar esta empresa?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash"></i> Eliminar Empresa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
