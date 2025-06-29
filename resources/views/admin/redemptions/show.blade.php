@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Detalles del Canje #{{ $redemption->id }}</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ url('/admin/redemptions') }}" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver a la lista
        </a>
    </div>
</div>

<div class="row">
    <!-- Redemption Status Card -->
    <div class="col-lg-4 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Estado del Canje</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    @switch($redemption->status)
                        @case('pending')
                            <div class="display-4 text-warning mb-2"><i class="fas fa-clock"></i></div>
                            <h4 class="text-warning">Pendiente</h4>
                            <p class="text-muted">El canje está esperando aprobación.</p>
                            @break
                        @case('approved')
                            <div class="display-4 text-success mb-2"><i class="fas fa-check-circle"></i></div>
                            <h4 class="text-success">Aprobado</h4>
                            <p class="text-muted">El canje ha sido aprobado.</p>
                            @break
                        @case('rejected')
                            <div class="display-4 text-danger mb-2"><i class="fas fa-times-circle"></i></div>
                            <h4 class="text-danger">Rechazado</h4>
                            <p class="text-muted">El canje ha sido rechazado.</p>
                            @break
                        @default
                            <div class="display-4 text-secondary mb-2"><i class="fas fa-question-circle"></i></div>
                            <h4 class="text-secondary">{{ $redemption->status }}</h4>
                    @endswitch
                </div>
                
                <div class="text-center">
                    <p><strong>Fecha de Solicitud:</strong> {{ $redemption->requested_at }}</p>
                    @if($redemption->resolved_at)
                        <p><strong>Fecha de Resolución:</strong> {{ $redemption->resolved_at }}</p>
                    @endif
                </div>
                
                <hr>
                
                <div class="d-grid gap-2">
                    @if($redemption->status == 'pending')
                        <form action="{{ url('/admin/redemptions/'.$redemption->id.'/approve') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-check me-1"></i> Aprobar Canje
                            </button>
                        </form>
                        <form action="{{ url('/admin/redemptions/'.$redemption->id.'/reject') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-times me-1"></i> Rechazar Canje
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Information -->
    <div class="col-lg-8 mb-4">
        <div class="card shadow h-100">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Información del Usuario</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p class="mb-1 text-muted small">Nombre Completo</p>
                        <p class="mb-0 font-weight-bold">{{ $redemption->user->name }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1 text-muted small">Correo Electrónico</p>
                        <p class="mb-0 font-weight-bold">{{ $redemption->user->email }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1 text-muted small">Teléfono</p>
                        <p class="mb-0 font-weight-bold">{{ $redemption->user->phone ?? 'No especificado' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p class="mb-1 text-muted small">País</p>
                        <p class="mb-0 font-weight-bold">{{ $redemption->user->country ?? 'No especificado' }}</p>
                    </div>
                    <div class="col-12 mb-3">
                        <p class="mb-1 text-muted small">Dirección</p>
                        <p class="mb-0 font-weight-bold">{{ $redemption->user->address ?? 'No especificada' }}</p>
                    </div>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="font-weight-bold mb-0">Recompensa Canjeada</h6>
                    <a href="{{ url('/admin/rewards/'.$redemption->reward_id) }}" class="btn btn-sm btn-outline-primary">
                        Ver Recompensa
                    </a>
                </div>
                
                <div class="card bg-light mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="card-title">{{ $redemption->reward->name }}</h5>
                                <h6 class="card-subtitle mb-2 text-muted">Costo: {{ $redemption->reward->cost }} puntos</h6>
                                <p class="card-text">{{ Str::limit($redemption->reward->description, 150) }}</p>
                            </div>
                            <div class="col-md-4 text-center">
                                @if($redemption->reward->image)
                                    <img src="{{ asset('storage/rewards/'.$redemption->reward->image) }}" alt="{{ $redemption->reward->name }}" class="img-fluid rounded" style="max-height: 100px;">
                                @else
                                    <div class="display-4 text-muted"><i class="fas fa-gift"></i></div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-1"></i> El usuario tenía <strong>{{ $userPoints ?? 'N/A' }}</strong> puntos antes de solicitar este canje.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delivery Information -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Información de Entrega</h6>
    </div>
    <div class="card-body">
        @if($redemption->status == 'approved')
            <form action="{{ url('/admin/redemptions/'.$redemption->id.'/delivery') }}" method="POST">
                @csrf
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="tracking_number" class="form-label">Número de Seguimiento</label>
                        <input type="text" class="form-control @error('tracking_number') is-invalid @enderror" id="tracking_number" name="tracking_number" value="{{ old('tracking_number', $redemption->tracking_number ?? '') }}">
                        @error('tracking_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="carrier" class="form-label">Empresa de Envío</label>
                        <input type="text" class="form-control @error('carrier') is-invalid @enderror" id="carrier" name="carrier" value="{{ old('carrier', $redemption->carrier ?? '') }}">
                        @error('carrier')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="delivery_notes" class="form-label">Notas de Entrega</label>
                    <textarea class="form-control @error('delivery_notes') is-invalid @enderror" id="delivery_notes" name="delivery_notes" rows="3">{{ old('delivery_notes', $redemption->delivery_notes ?? '') }}</textarea>
                    @error('delivery_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Guardar Información de Entrega
                    </button>
                </div>
            </form>
        @elseif($redemption->status == 'rejected')
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-1"></i> Este canje ha sido rechazado. No se requiere información de entrega.
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-1"></i> Debe aprobar el canje primero para registrar la información de entrega.
            </div>
        @endif
    </div>
</div>

<!-- Notes and Comments -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Notas y Comentarios</h6>
    </div>
    <div class="card-body">
        <form action="{{ url('/admin/redemptions/'.$redemption->id.'/notes') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="note" class="form-label">Agregar Nota</label>
                <textarea class="form-control" id="note" name="note" rows="3" placeholder="Escriba una nota o comentario sobre este canje..."></textarea>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Guardar Nota
                </button>
            </div>
        </form>
        
        <hr>
        
        <div class="notes-container">
            @if(isset($notes) && count($notes) > 0)
                @foreach($notes as $note)
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $note->user->name }}</strong>
                                <span class="text-muted small">{{ $note->created_at }}</span>
                            </div>
                            @if(auth()->id() == $note->user_id)
                                <form action="{{ url('/admin/redemptions/notes/'.$note->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div class="card-body">
                            <p class="card-text">{{ $note->content }}</p>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-1"></i> No hay notas o comentarios para este canje.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
