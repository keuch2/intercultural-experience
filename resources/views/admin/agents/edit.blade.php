@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-edit me-2"></i>Editar Agente
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('admin.agents.show', $agent->id) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-edit me-2"></i>Datos del Agente
                </h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.agents.update', $agent->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $agent->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $agent->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Teléfono <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $agent->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="country" class="form-label">País <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                   id="country" name="country" value="{{ old('country', $agent->country) }}" required>
                            @error('country')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="city" class="form-label">Ciudad</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" value="{{ old('city', $agent->city) }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="nationality" class="form-label">Nacionalidad</label>
                            <input type="text" class="form-control @error('nationality') is-invalid @enderror" 
                                   id="nationality" name="nationality" value="{{ old('nationality', $agent->nationality) }}">
                            @error('nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        La contraseña no se modifica desde aquí. Usa el botón "Resetear Contraseña" para cambiarla.
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="{{ route('admin.agents.show', $agent->id) }}" class="btn btn-secondary me-md-2">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Actualizar Agente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header py-3 bg-info text-white">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-info-circle me-2"></i>Información del Agente
                </h6>
            </div>
            <div class="card-body">
                <p><strong>ID:</strong> {{ $agent->id }}</p>
                <p><strong>Participantes:</strong> {{ $agent->created_participants_count }}</p>
                <p><strong>Fecha de registro:</strong> {{ $agent->created_at->format('d/m/Y') }}</p>
                <p><strong>Última actualización:</strong> {{ $agent->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="card shadow mt-3">
            <div class="card-header py-3 bg-warning text-dark">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-exclamation-triangle me-2"></i>Acciones
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <form action="{{ route('admin.agents.reset-password', $agent->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-info w-100">
                            <i class="fas fa-key me-2"></i>Resetear Contraseña
                        </button>
                    </form>

                    @if($agent->created_participants_count == 0)
                        <form action="{{ route('admin.agents.destroy', $agent->id) }}" method="POST" 
                              onsubmit="return confirm('¿Estás seguro de eliminar este agente?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="fas fa-trash me-2"></i>Eliminar Agente
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-danger w-100" disabled>
                            <i class="fas fa-trash me-2"></i>No se puede eliminar (tiene participantes)
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
