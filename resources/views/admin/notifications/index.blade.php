@extends('layouts.admin')

@section('title', 'Gestión de Notificaciones')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4">Gestión de Notificaciones</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Notificaciones</li>
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
    
    <!-- Resumen de Notificaciones -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total de Notificaciones</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalNotifications) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Notificaciones Leídas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($readNotifications) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Notificaciones No Leídas</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($unreadNotifications) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell-slash fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Nueva Notificación -->
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <i class="fas fa-plus-circle me-1"></i>
                        Nueva Notificación
                    </div>
                    <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNewNotification" aria-expanded="false" aria-controls="collapseNewNotification">
                        Mostrar/Ocultar
                    </button>
                </div>
                <div class="collapse" id="collapseNewNotification">
                    <div class="card-body">
                        <form action="{{ route('admin.notifications.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Título</label>
                                        <input type="text" class="form-control" id="title" name="title" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Categoría</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Seleccionar categoría</option>
                                            <option value="info">Información</option>
                                            <option value="warning">Advertencia</option>
                                            <option value="success">Éxito</option>
                                            <option value="error">Error</option>
                                            <option value="update">Actualización</option>
                                            <option value="promotion">Promoción</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Mensaje</label>
                                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="user_ids" class="form-label">Destinatarios</label>
                                <select class="form-select" id="user_ids" name="user_ids[]" multiple required>
                                    <option value="">Seleccionar usuarios</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                                <div class="form-text">Mantén presionada la tecla Ctrl (o Command en Mac) para seleccionar múltiples usuarios.</div>
                            </div>
                            <button type="submit" class="btn btn-primary">Enviar Notificación</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Filtros -->
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-filter me-1"></i>
                    Filtros
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.notifications.index') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="search" class="form-label">Buscar</label>
                                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Título, mensaje, usuario...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Categoría</label>
                                    <select class="form-select" id="category" name="category">
                                        <option value="">Todas las categorías</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                                @switch($category)
                                                    @case('info')
                                                        Información
                                                        @break
                                                    @case('warning')
                                                        Advertencia
                                                        @break
                                                    @case('success')
                                                        Éxito
                                                        @break
                                                    @case('error')
                                                        Error
                                                        @break
                                                    @case('update')
                                                        Actualización
                                                        @break
                                                    @case('promotion')
                                                        Promoción
                                                        @break
                                                    @default
                                                        {{ ucfirst($category) }}
                                                @endswitch
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="is_read" class="form-label">Estado</label>
                                    <select class="form-select" id="is_read" name="is_read">
                                        <option value="">Todos los estados</option>
                                        <option value="1" {{ request('is_read') === '1' ? 'selected' : '' }}>Leídas</option>
                                        <option value="0" {{ request('is_read') === '0' ? 'selected' : '' }}>No leídas</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Filtrar</button>
                                <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">Limpiar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Tabla de Notificaciones -->
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Listado de Notificaciones
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Usuario</th>
                                    <th>Título</th>
                                    <th>Categoría</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                    <tr class="{{ $notification->is_read ? '' : 'table-warning' }}">
                                        <td>{{ $notification->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $notification->user_id) }}">
                                                {{ $notification->user->name }}
                                            </a>
                                        </td>
                                        <td>{{ $notification->title }}</td>
                                        <td>
                                            @switch($notification->category)
                                                @case('info')
                                                    <span class="badge bg-info">Información</span>
                                                    @break
                                                @case('warning')
                                                    <span class="badge bg-warning">Advertencia</span>
                                                    @break
                                                @case('success')
                                                    <span class="badge bg-success">Éxito</span>
                                                    @break
                                                @case('error')
                                                    <span class="badge bg-danger">Error</span>
                                                    @break
                                                @case('update')
                                                    <span class="badge bg-primary">Actualización</span>
                                                    @break
                                                @case('promotion')
                                                    <span class="badge bg-secondary">Promoción</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $notification->category }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($notification->is_read)
                                                <span class="badge bg-success">Leída</span>
                                            @else
                                                <span class="badge bg-warning">No leída</span>
                                            @endif
                                        </td>
                                        <td>{{ $notification->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#notificationModal{{ $notification->id }}">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal para ver la notificación -->
                                    <div class="modal fade" id="notificationModal{{ $notification->id }}" tabindex="-1" aria-labelledby="notificationModalLabel{{ $notification->id }}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="notificationModalLabel{{ $notification->id }}">{{ $notification->title }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Usuario:</strong> {{ $notification->user->name }}</p>
                                                    <p><strong>Categoría:</strong> 
                                                        @switch($notification->category)
                                                            @case('info')
                                                                Información
                                                                @break
                                                            @case('warning')
                                                                Advertencia
                                                                @break
                                                            @case('success')
                                                                Éxito
                                                                @break
                                                            @case('error')
                                                                Error
                                                                @break
                                                            @case('update')
                                                                Actualización
                                                                @break
                                                            @case('promotion')
                                                                Promoción
                                                                @break
                                                            @default
                                                                {{ $notification->category }}
                                                        @endswitch
                                                    </p>
                                                    <p><strong>Fecha:</strong> {{ $notification->created_at->format('d/m/Y H:i') }}</p>
                                                    <p><strong>Estado:</strong> {{ $notification->is_read ? 'Leída' : 'No leída' }}</p>
                                                    <hr>
                                                    <p>{{ $notification->message }}</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No hay notificaciones disponibles.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="d-flex justify-content-center mt-3">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar select múltiple para usuarios
        const userSelect = document.getElementById('user_ids');
        if (userSelect) {
            // Aquí podrías agregar una librería como Select2 para mejorar la experiencia
            // Por ahora, solo usamos el select múltiple nativo
        }
    });
</script>
@endsection
