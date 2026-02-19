@extends('layouts.admin')

@section('title', 'Configuraciones del Sistema')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-cogs"></i> Configuraciones del Sistema
                        </h3>
                        <div class="btn-group">
                            <a href="{{ route('admin.settings.index') }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Configuración General
                            </a>
                            <form action="{{ route('admin.settings.clearCache') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary">
                                    <i class="fas fa-sync"></i> Limpiar Cache
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Configuraciones por Grupo -->
    <div class="row">
        @foreach($settings as $group => $groupSettings)
            <div class="col-lg-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            @switch($group)
                                @case('general')
                                    <i class="fas fa-cogs text-primary"></i> Configuración General
                                    @break
                                @case('whatsapp')
                                    <i class="fab fa-whatsapp text-success"></i> WhatsApp
                                    @break
                                @case('contact')
                                    <i class="fas fa-address-book text-info"></i> Contacto
                                    @break
                                @case('notifications')
                                    <i class="fas fa-bell text-warning"></i> Notificaciones
                                    @break
                                @default
                                    <i class="fas fa-folder text-secondary"></i> {{ ucfirst($group) }}
                            @endswitch
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($groupSettings as $setting)
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-6">
                                    <strong>{{ ucfirst(str_replace('_', ' ', $setting->key)) }}</strong>
                                    @if($setting->description)
                                        <br><small class="text-muted">{{ $setting->description }}</small>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    @if($setting->type === 'boolean')
                                        <span class="badge {{ $setting->value ? 'bg-success' : 'bg-danger' }}">
                                            {{ $setting->value ? 'Habilitado' : 'Deshabilitado' }}
                                        </span>
                                    @else
                                        <code class="small">{{ Str::limit($setting->value ?? 'No configurado', 30) }}</code>
                                    @endif
                                    
                                    @if($setting->is_public)
                                        <span class="badge bg-info ms-1" title="Visible en la API pública">
                                            <i class="fas fa-eye"></i> Público
                                        </span>
                                    @endif
                                </div>
                            </div>
                            @if(!$loop->last)
                                <hr>
                            @endif
                        @endforeach
                        
                        <!-- Botón de edición específico por grupo -->
                        <div class="mt-3">
                            <a href="{{ route('admin.settings.index') }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Información del Sistema -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle"></i> Información del Sistema
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Laravel:</strong><br>
                            <code>{{ app()->version() }}</code>
                        </div>
                        <div class="col-md-3">
                            <strong>PHP:</strong><br>
                            <code>{{ PHP_VERSION }}</code>
                        </div>
                        <div class="col-md-3">
                            <strong>Entorno:</strong><br>
                            <span class="badge {{ app()->environment() === 'production' ? 'bg-success' : 'bg-warning' }}">
                                {{ ucfirst(app()->environment()) }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Configuraciones:</strong><br>
                            <span class="badge bg-info">{{ $settings->flatten()->count() }} total</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar toast si se limpió el cache
    @if(session('success') && str_contains(session('success'), 'cache'))
        setTimeout(() => {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
    @endif
});
</script>
@endpush 