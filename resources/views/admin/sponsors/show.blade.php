@extends('layouts.admin')

@section('title', 'Detalle del Sponsor')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $sponsor->name }}</h1>
            <p class="text-muted">
                <span class="badge badge-secondary">{{ $sponsor->code }}</span>
                @if($sponsor->is_active)
                    <span class="badge badge-success">Activo</span>
                @else
                    <span class="badge badge-danger">Inactivo</span>
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('admin.sponsors.edit', $sponsor->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.sponsors.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Información General -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información General</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Nombre</small>
                            <p class="mb-0"><strong>{{ $sponsor->name }}</strong></p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted">Código</small>
                            <p class="mb-0"><span class="badge badge-secondary">{{ $sponsor->code }}</span></p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted">País</small>
                            <p class="mb-0"><i class="fas fa-globe"></i> {{ $sponsor->country }}</p>
                        </div>
                    </div>

                    @if($sponsor->contact_email || $sponsor->contact_phone)
                        <hr>
                        <h6 class="font-weight-bold text-primary mb-3">Contacto</h6>
                        <div class="row">
                            @if($sponsor->contact_email)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted">Email</small>
                                    <p class="mb-0">
                                        <i class="fas fa-envelope"></i>
                                        <a href="mailto:{{ $sponsor->contact_email }}">{{ $sponsor->contact_email }}</a>
                                    </p>
                                </div>
                            @endif
                            @if($sponsor->contact_phone)
                                <div class="col-md-6 mb-3">
                                    <small class="text-muted">Teléfono</small>
                                    <p class="mb-0">
                                        <i class="fas fa-phone"></i> {{ $sponsor->contact_phone }}
                                    </p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($sponsor->website)
                        <div class="mb-3">
                            <small class="text-muted">Sitio Web</small>
                            <p class="mb-0">
                                <i class="fas fa-link"></i>
                                <a href="{{ $sponsor->website }}" target="_blank">{{ $sponsor->website }}</a>
                            </p>
                        </div>
                    @endif

                    @if($sponsor->terms_and_conditions)
                        <hr>
                        <h6 class="font-weight-bold text-primary mb-3">Términos y Condiciones</h6>
                        <p class="text-muted">{{ $sponsor->terms_and_conditions }}</p>
                    @endif
                </div>
            </div>

            <!-- Ofertas Laborales -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Ofertas Laborales ({{ $sponsor->job_offers_count }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($sponsor->jobOffers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Posición</th>
                                        <th>Empresa Host</th>
                                        <th>Ubicación</th>
                                        <th>Cupos</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sponsor->jobOffers as $offer)
                                        <tr>
                                            <td>{{ $offer->job_offer_id }}</td>
                                            <td><strong>{{ $offer->position }}</strong></td>
                                            <td>{{ $offer->hostCompany->name }}</td>
                                            <td>{{ $offer->city }}, {{ $offer->state }}</td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $offer->available_slots }}/{{ $offer->total_slots }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($offer->status == 'available')
                                                    <span class="badge badge-success">Disponible</span>
                                                @elseif($offer->status == 'full')
                                                    <span class="badge badge-warning">Lleno</span>
                                                @else
                                                    <span class="badge badge-danger">Cancelado</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Este sponsor no tiene ofertas laborales registradas.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Estadísticas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Estadísticas
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h2 class="text-primary">{{ $sponsor->job_offers_count }}</h2>
                        <small class="text-muted">Ofertas Laborales</small>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted">Creado</small>
                        <p class="mb-0">{{ $sponsor->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Última actualización</small>
                        <p class="mb-0">{{ $sponsor->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.sponsors.toggle-status', $sponsor->id) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-{{ $sponsor->is_active ? 'warning' : 'success' }} btn-block">
                            <i class="fas fa-{{ $sponsor->is_active ? 'ban' : 'check' }}"></i>
                            {{ $sponsor->is_active ? 'Desactivar' : 'Activar' }} Sponsor
                        </button>
                    </form>
                    <a href="{{ route('admin.sponsors.edit', $sponsor->id) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Editar Información
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
