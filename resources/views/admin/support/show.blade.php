@extends('layouts.admin')

@section('title', 'Detalle de Ticket de Soporte')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Detalle de Ticket de Soporte</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.support.index') }}">Tickets de Soporte</a></li>
        <li class="breadcrumb-item active">Ticket #{{ $ticket->id }}</li>
    </ol>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="row">
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-1"></i>
                    Información del Ticket
                </div>
                <div class="card-body">
                    <h5 class="card-title">{{ $ticket->subject }}</h5>
                    <div class="mb-3">
                        <span class="badge bg-{{ $ticket->status == 'open' ? 'warning' : ($ticket->status == 'answered' ? 'info' : 'secondary') }}">
                            {{ $ticket->status == 'open' ? 'Abierto' : ($ticket->status == 'answered' ? 'Respondido' : 'Cerrado') }}
                        </span>
                        <span class="badge bg-{{ $ticket->priority == 'low' ? 'success' : ($ticket->priority == 'medium' ? 'warning' : 'danger') }}">
                            Prioridad {{ $ticket->priority == 'low' ? 'Baja' : ($ticket->priority == 'medium' ? 'Media' : 'Alta') }}
                        </span>
                    </div>
                    
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item">
                            <strong>ID:</strong> #{{ $ticket->id }}
                        </li>
                        <li class="list-group-item">
                            <strong>Usuario:</strong> {{ $ticket->user->name }}
                        </li>
                        <li class="list-group-item">
                            <strong>Email:</strong> {{ $ticket->user->email }}
                        </li>
                        <li class="list-group-item">
                            <strong>Fecha de Creación:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}
                        </li>
                        <li class="list-group-item">
                            <strong>Última Actualización:</strong> {{ $ticket->updated_at->format('d/m/Y H:i') }}
                        </li>
                        @if($ticket->closed_at)
                        <li class="list-group-item">
                            <strong>Fecha de Cierre:</strong> {{ $ticket->closed_at ? $ticket->closed_at->format('d/m/Y H:i') : 'N/A' }}
                        </li>
                        @endif
                        @if($ticket->assigned_to)
                        <li class="list-group-item">
                            <strong>Asignado a:</strong> {{ $ticket->assignedTo->name }}
                        </li>
                        @endif
                    </ul>
                    
                    <div class="d-grid gap-2">
                        @if($ticket->status != 'closed')
                            <form action="{{ route('admin.support.close', $ticket->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times-circle me-1"></i> Cerrar Ticket
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.support.reopen', $ticket->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-warning btn-block">
                                    <i class="fas fa-redo me-1"></i> Reabrir Ticket
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-cog me-1"></i>
                    Acciones
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.support.changePriority', $ticket->id) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PUT')
                        <div class="input-group">
                            <select class="form-select" name="priority" id="priority">
                                <option value="low" {{ $ticket->priority == 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ $ticket->priority == 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ $ticket->priority == 'high' ? 'selected' : '' }}>Alta</option>
                            </select>
                            <button class="btn btn-outline-primary" type="submit">Cambiar Prioridad</button>
                        </div>
                    </form>
                    
                    <form action="{{ route('admin.support.assign', $ticket->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="input-group">
                            <select class="form-select" name="assigned_to" id="assigned_to">
                                <option value="">Sin asignar</option>
                                @foreach($admins as $admin)
                                    <option value="{{ $admin->id }}" {{ $ticket->assigned_to == $admin->id ? 'selected' : '' }}>
                                        {{ $admin->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button class="btn btn-outline-primary" type="submit">Asignar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-comment me-1"></i>
                    Mensaje Original
                </div>
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <img class="rounded-circle" src="https://via.placeholder.com/50" alt="User Avatar">
                        </div>
                        <div class="ms-3">
                            <div class="fw-bold">{{ $ticket->user->name }}</div>
                            <div class="text-muted small mb-2">{{ $ticket->created_at->format('d/m/Y H:i') }}</div>
                            <div class="ticket-message">
                                {!! nl2br(e($ticket->message)) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-comments me-1"></i>
                    Respuestas
                </div>
                <div class="card-body">
                    @forelse($replies as $reply)
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <img class="rounded-circle" src="https://via.placeholder.com/50" alt="User Avatar">
                            </div>
                            <div class="ms-3">
                                <div class="fw-bold">{{ $reply->user->name }}</div>
                                <div class="text-muted small mb-2">{{ $reply->created_at->format('d/m/Y H:i') }}</div>
                                <div class="ticket-reply">
                                    {!! nl2br(e($reply->message)) !!}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <p class="text-muted">No hay respuestas todavía.</p>
                        </div>
                    @endforelse
                    
                    @if($ticket->status != 'closed')
                        <hr class="my-4">
                        <h5>Responder</h5>
                        <form action="{{ route('admin.support.reply', $ticket->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea class="form-control" id="message" name="message" rows="5" placeholder="Escribe tu respuesta aquí..." required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Enviar Respuesta
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            
            @if($ticket->user->supportTickets->count() > 1)
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-history me-1"></i>
                        Otros Tickets del Usuario
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Asunto</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ticket->user->supportTickets->where('id', '!=', $ticket->id)->take(5) as $otherTicket)
                                        <tr>
                                            <td>{{ $otherTicket->id }}</td>
                                            <td>{{ Str::limit($otherTicket->subject, 30) }}</td>
                                            <td>
                                                @if($otherTicket->status == 'open')
                                                    <span class="badge bg-warning">Abierto</span>
                                                @elseif($otherTicket->status == 'answered')
                                                    <span class="badge bg-info">Respondido</span>
                                                @elseif($otherTicket->status == 'closed')
                                                    <span class="badge bg-secondary">Cerrado</span>
                                                @endif
                                            </td>
                                            <td>{{ $otherTicket->created_at->format('d/m/Y') }}</td>
                                            <td>
                                                <a href="{{ route('admin.support.show', $otherTicket->id) }}" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Código JavaScript adicional si es necesario
    });
</script>
@endsection
