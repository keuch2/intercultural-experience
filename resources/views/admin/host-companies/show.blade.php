@extends('layouts.admin')

@section('title', 'Detalle de Empresa Host')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $company->name }}</h1>
            <p class="text-muted">
                @if($company->industry)
                    <span class="badge badge-info">{{ $company->industry }}</span>
                @endif
                @if($company->is_active)
                    <span class="badge badge-success">Activo</span>
                @else
                    <span class="badge badge-danger">Inactivo</span>
                @endif
            </p>
        </div>
        <div>
            <a href="{{ route('admin.host-companies.edit', $company->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.host-companies.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información General</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <small class="text-muted">Nombre</small>
                            <p class="mb-0"><strong>{{ $company->name }}</strong></p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted">Industria</small>
                            <p class="mb-0">{{ $company->industry ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3 mb-3">
                            <small class="text-muted">Rating</small>
                            <p class="mb-0">
                                @if($company->rating)
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= $company->rating ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                    ({{ number_format($company->rating, 1) }})
                                @else
                                    Sin rating
                                @endif
                            </p>
                        </div>
                    </div>

                    <hr>
                    <h6 class="font-weight-bold text-primary mb-3">Ubicación</h6>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <small class="text-muted">Dirección Completa</small>
                            <p class="mb-0">
                                <i class="fas fa-map-marker-alt"></i>
                                {{ $company->address ?? '' }}<br>
                                {{ $company->city }}, {{ $company->state }}, {{ $company->country }}
                            </p>
                        </div>
                    </div>

                    @if($company->contact_person || $company->contact_email || $company->contact_phone)
                        <hr>
                        <h6 class="font-weight-bold text-primary mb-3">Contacto</h6>
                        <div class="row">
                            @if($company->contact_person)
                                <div class="col-md-12 mb-2">
                                    <small class="text-muted">Persona de Contacto</small>
                                    <p class="mb-0"><i class="fas fa-user"></i> {{ $company->contact_person }}</p>
                                </div>
                            @endif
                            @if($company->contact_email)
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted">Email</small>
                                    <p class="mb-0">
                                        <i class="fas fa-envelope"></i>
                                        <a href="mailto:{{ $company->contact_email }}">{{ $company->contact_email }}</a>
                                    </p>
                                </div>
                            @endif
                            @if($company->contact_phone)
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted">Teléfono</small>
                                    <p class="mb-0"><i class="fas fa-phone"></i> {{ $company->contact_phone }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($company->notes)
                        <hr>
                        <h6 class="font-weight-bold text-primary mb-3">Notas Internas</h6>
                        <p class="text-muted">{{ $company->notes }}</p>
                    @endif
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        Ofertas Laborales ({{ $company->job_offers_count }})
                    </h6>
                </div>
                <div class="card-body">
                    @if($company->jobOffers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Posición</th>
                                        <th>Sponsor</th>
                                        <th>Cupos</th>
                                        <th>Salario</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($company->jobOffers as $offer)
                                        <tr>
                                            <td>{{ $offer->job_offer_id }}</td>
                                            <td><strong>{{ $offer->position }}</strong></td>
                                            <td>{{ $offer->sponsor->name }}</td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $offer->available_slots }}/{{ $offer->total_slots }}
                                                </span>
                                            </td>
                                            <td>${{ number_format($offer->salary_min, 0) }} - ${{ number_format($offer->salary_max, 0) }}</td>
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
                            <i class="fas fa-info-circle"></i> Esta empresa no tiene ofertas laborales registradas.
                        </div>
                    @endif
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
                    <div class="text-center mb-4">
                        <h2 class="text-primary">{{ $company->job_offers_count }}</h2>
                        <small class="text-muted">Ofertas Laborales</small>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <small class="text-muted">Creado</small>
                        <p class="mb-0">{{ $company->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Última actualización</small>
                        <p class="mb-0">{{ $company->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt"></i> Acciones Rápidas
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.host-companies.toggle-status', $company->id) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-{{ $company->is_active ? 'warning' : 'success' }} btn-block">
                            <i class="fas fa-{{ $company->is_active ? 'ban' : 'check' }}"></i>
                            {{ $company->is_active ? 'Desactivar' : 'Activar' }} Empresa
                        </button>
                    </form>
                    <a href="{{ route('admin.host-companies.edit', $company->id) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Editar Información
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
