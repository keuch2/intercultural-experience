@extends('layouts.admin')

@section('title', 'Detalle de Oferta Laboral')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $offer->position }}</h1>
            <p class="text-muted">{{ $offer->job_offer_id }}</p>
        </div>
        <div>
            <a href="{{ route('admin.job-offers.edit', $offer->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Editar
            </a>
            <a href="{{ route('admin.job-offers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <!-- Información General -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información General</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Sponsor:</strong><br>
                            {{ $offer->sponsor->name }} ({{ $offer->sponsor->code }})
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Empresa Host:</strong><br>
                            {{ $offer->hostCompany->name }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <strong>Descripción:</strong><br>
                            {{ $offer->description ?? 'Sin descripción' }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Ubicación:</strong><br>
                            <i class="fas fa-map-marker-alt"></i> {{ $offer->city }}, {{ $offer->state }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Estado:</strong><br>
                            @if($offer->status == 'available')
                                <span class="badge badge-success">Disponible</span>
                            @elseif($offer->status == 'full')
                                <span class="badge badge-warning">Lleno</span>
                            @else
                                <span class="badge badge-danger">Cancelado</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalles del Trabajo -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detalles del Trabajo</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <strong>Salario:</strong><br>
                            ${{ number_format($offer->salary_min, 2) }} - ${{ number_format($offer->salary_max, 2) }}/hr
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>Horas/Semana:</strong><br>
                            {{ $offer->hours_per_week }} horas
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>Vivienda:</strong><br>
                            @if($offer->housing_type == 'provided')
                                Provista
                            @elseif($offer->housing_type == 'assisted')
                                Asistida
                            @else
                                No Provista
                            @endif
                            @if($offer->housing_cost)
                                (${{ number_format($offer->housing_cost, 2) }}/mes)
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <strong>Fechas:</strong><br>
                            {{ \Carbon\Carbon::parse($offer->start_date)->format('d/m/Y') }} - 
                            {{ \Carbon\Carbon::parse($offer->end_date)->format('d/m/Y') }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>Nivel de Inglés:</strong><br>
                            {{ $offer->required_english_level }}
                        </div>
                        <div class="col-md-4 mb-3">
                            <strong>Género:</strong><br>
                            @if($offer->required_gender == 'any')
                                Cualquiera
                            @elseif($offer->required_gender == 'male')
                                Masculino
                            @else
                                Femenino
                            @endif
                        </div>
                    </div>
                    @if($offer->requirements)
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <strong>Requisitos Adicionales:</strong><br>
                                {{ $offer->requirements }}
                            </div>
                        </div>
                    @endif
                    @if($offer->benefits)
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <strong>Beneficios:</strong><br>
                                {{ $offer->benefits }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Reservas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Reservas ({{ $offer->reservations->count() }})</h6>
                </div>
                <div class="card-body">
                    @if($offer->reservations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Participante</th>
                                        <th>Email</th>
                                        <th>Fecha Reserva</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($offer->reservations as $reservation)
                                        <tr>
                                            <td>{{ $reservation->user->name }}</td>
                                            <td>{{ $reservation->user->email }}</td>
                                            <td>{{ $reservation->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($reservation->status == 'pending')
                                                    <span class="badge badge-warning">Pendiente</span>
                                                @elseif($reservation->status == 'confirmed')
                                                    <span class="badge badge-success">Confirmada</span>
                                                @else
                                                    <span class="badge badge-danger">Cancelada</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No hay reservas para esta oferta.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Estadísticas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Estadísticas</h6>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h6 class="text-muted">Cupos</h6>
                        <div class="progress mb-2">
                            @php
                                $percentage = ($offer->total_slots > 0) 
                                    ? (($offer->total_slots - $offer->available_slots) / $offer->total_slots) * 100 
                                    : 0;
                            @endphp
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ $percentage }}%" 
                                 aria-valuenow="{{ $percentage }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ round($percentage) }}%
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $offer->total_slots - $offer->available_slots }} ocupados de {{ $offer->total_slots }} totales
                        </small>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted">Disponibles</h6>
                        <h3 class="text-primary">{{ $offer->available_slots }}</h3>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted">Reservas Totales</h6>
                        <h3 class="text-info">{{ $offer->reservations->count() }}</h3>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted">Reservas Confirmadas</h6>
                        <h3 class="text-success">{{ $offer->reservations()->where('status', 'confirmed')->count() }}</h3>
                    </div>
                </div>
            </div>

            <!-- Acciones Rápidas -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Acciones Rápidas</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.job-offers.toggle-status', $offer->id) }}" method="POST" class="mb-2">
                        @csrf
                        @if($offer->status == 'available')
                            <button type="submit" class="btn btn-warning btn-block">
                                <i class="fas fa-ban"></i> Cancelar Oferta
                            </button>
                        @else
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-check"></i> Activar Oferta
                            </button>
                        @endif
                    </form>
                    
                    <a href="{{ route('admin.job-offers.edit', $offer->id) }}" class="btn btn-primary btn-block">
                        <i class="fas fa-edit"></i> Editar Oferta
                    </a>
                </div>
            </div>

            <!-- Información Adicional -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Información Adicional</h6>
                </div>
                <div class="card-body">
                    <small class="text-muted">Creada:</small><br>
                    {{ $offer->created_at->format('d/m/Y H:i') }}<br><br>
                    
                    <small class="text-muted">Última actualización:</small><br>
                    {{ $offer->updated_at->format('d/m/Y H:i') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
