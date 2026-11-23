@extends('layouts.admin')

@section('title', 'Banners de la app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h1 class="h3 mb-1">Banners de la app</h1>
            <p class="text-muted mb-0 small">Se muestran como carrusel en el Inicio de la app, en el orden indicado. Los cambios se ven al abrir o refrescar la app.</p>
        </div>
        <a href="{{ route('admin.app-banners.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nuevo banner</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($banners->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-images fa-2x mb-2"></i>
                    <p class="mb-0">Todavía no hay banners. Creá el primero con "Nuevo banner".</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px">Orden</th>
                                <th style="width: 220px">Vista previa</th>
                                <th>Contenido</th>
                                <th>Se muestra a</th>
                                <th>Vigencia</th>
                                <th>Estado</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($banners as $banner)
                                @php
                                    $statusMap = [
                                        'live' => ['success', 'Publicado'], 'scheduled' => ['info', 'Programado'],
                                        'expired' => ['secondary', 'Vencido'], 'inactive' => ['light text-dark border', 'Inactivo'],
                                    ];
                                    [$statusClass, $statusLabel] = $statusMap[$banner->schedule_status];
                                @endphp
                                <tr>
                                    <td class="text-center fw-semibold">{{ $banner->sort_order }}</td>
                                    <td>
                                        @if($banner->type === 'image' && $banner->image_url)
                                            <img src="{{ $banner->image_url }}" alt="{{ $banner->title }}" class="rounded" style="width: 200px; aspect-ratio: 2 / 1; object-fit: cover;">
                                        @else
                                            <div class="rounded p-2 text-white small" style="width: 200px; aspect-ratio: 2 / 1; background: {{ $banner->background_color }}; overflow: hidden;">
                                                @if($banner->badge)<div class="fw-bold" style="font-size: .65rem; opacity: .85;">{{ $banner->badge }}</div>@endif
                                                <div class="fw-bold">{{ $banner->title }}</div>
                                                <div style="font-size: .7rem; opacity: .9;">{{ \Illuminate\Support\Str::limit($banner->subtitle, 70) }}</div>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $banner->title ?: '(sin título)' }}</div>
                                        <div class="small text-muted">
                                            {{ \App\Models\AppBanner::TYPES[$banner->type] ?? $banner->type }}
                                            @if($banner->link_type === 'program' && $banner->linkProgram) · Lleva a: {{ $banner->linkProgram->name }}
                                            @elseif($banner->link_type === 'url') · Enlace: {{ \Illuminate\Support\Str::limit($banner->link_url, 40) }}
                                            @endif
                                        </div>
                                    </td>
                                    <td class="small">
                                        {{ \App\Models\AppBanner::AUDIENCES[$banner->audience] ?? $banner->audience }}
                                        @if($banner->program)<br><span class="text-muted">Postulantes de {{ $banner->program->name }}</span>@endif
                                    </td>
                                    <td class="small">
                                        @if($banner->starts_at || $banner->ends_at)
                                            {{ $banner->starts_at?->format('d/m/Y H:i') ?? 'Desde ya' }}<br>
                                            <span class="text-muted">hasta {{ $banner->ends_at?->format('d/m/Y H:i') ?? 'sin fin' }}</span>
                                        @else
                                            <span class="text-muted">Siempre</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('admin.app-banners.edit', $banner) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.app-banners.toggle', $banner) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary" title="{{ $banner->is_active ? 'Desactivar' : 'Activar' }}"><i class="fas {{ $banner->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i></button>
                                        </form>
                                        <form action="{{ route('admin.app-banners.destroy', $banner) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este banner?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
