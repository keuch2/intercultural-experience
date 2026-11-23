@extends('layouts.admin')

@section('title', 'Notificaciones push')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Notificaciones push</h1>
            <p class="text-muted mb-0 small">Todos los avisos quedan siempre en la sección Avisos de la app. Acá se decide cuáles llegan además como notificación push al celular.</p>
        </div>
        <a href="{{ route('admin.notification-campaigns.index') }}" class="btn btn-outline-secondary"><i class="fas fa-bullhorn me-1"></i> Avisos especiales</a>
    </div>


    <div class="alert alert-light border small">
        <strong>Push activa:</strong> el aviso llega como notificación push (si el participante tiene la app y no silenció la categoría).<br>
        <strong>Obligatorio:</strong> llega aunque el participante haya silenciado esa categoría en Perfil &gt; Notificaciones.
    </div>

    <form method="POST" action="{{ route('admin.notification-types.update') }}">
        @csrf @method('PUT')
        <div class="row g-4">
            @foreach($groups as $category => $types)
                <div class="col-lg-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">{{ \App\Support\NotificationCatalog::CATEGORIES[$category]['label'] }}</h5>
                            <small class="text-muted">{{ \App\Support\NotificationCatalog::CATEGORIES[$category]['description'] }}</small>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light"><tr><th class="ps-3">Aviso</th><th class="text-center" style="width: 110px">Push activa</th><th class="text-center" style="width: 110px">Obligatorio</th></tr></thead>
                                <tbody>
                                    @foreach($types as $type)
                                        <tr>
                                            <td class="ps-3">{{ \App\Support\NotificationCatalog::label($type) }}</td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="push[{{ $type }}]" value="1" @checked($settings[$type]['push_enabled'])>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-inline-block">
                                                    <input class="form-check-input" type="checkbox" role="switch" name="mandatory[{{ $type }}]" value="1" @checked($settings[$type]['mandatory'])>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-4">
            <button class="btn btn-primary"><i class="fas fa-save me-1"></i> Guardar configuración</button>
        </div>
    </form>
</div>
@endsection
