@extends('layouts.admin')

@section('title', $banner->exists ? 'Editar banner' : 'Nuevo banner')

@section('content')
@php
    $toLocal = fn ($d) => $d?->format('Y-m-d\TH:i');
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">{{ $banner->exists ? 'Editar banner' : 'Nuevo banner' }}</h1>
        <a href="{{ route('admin.app-banners.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Volver</a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" enctype="multipart/form-data"
          action="{{ $banner->exists ? route('admin.app-banners.update', $banner) : route('admin.app-banners.store') }}">
        @csrf
        @if($banner->exists) @method('PUT') @endif

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Contenido</h5></div>
                    <div class="card-body">
                        <label class="form-label">Tipo de banner</label>
                        <div class="mb-3">
                            @foreach(\App\Models\AppBanner::TYPES as $value => $label)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="type" id="type_{{ $value }}" value="{{ $value }}" @checked(old('type', $banner->type) === $value)>
                                    <label class="form-check-label" for="type_{{ $value }}">{{ $label }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div data-show-type="image" class="mb-3">
                            <label class="form-label">Imagen @if(! $banner->image_path)*@endif</label>
                            <input type="file" name="image" id="bannerImage" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <small class="text-muted">JPG, PNG o WEBP de hasta 4 MB. Proporción <strong>2:1</strong> (por ejemplo 1200 × 600 px): la app la recorta a ese formato.
                                @if($banner->image_path) Dejalo vacío para conservar la imagen actual.@endif</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Título <span data-show-type="text">*</span></label>
                            <input type="text" name="title" id="bannerTitle" class="form-control" maxlength="120" value="{{ old('title', $banner->title) }}">
                            <small class="text-muted" data-show-type="image">En los banners de imagen el título no se muestra; sirve para identificarlo y para accesibilidad.</small>
                        </div>

                        <div data-show-type="text">
                            <div class="mb-3">
                                <label class="form-label">Texto</label>
                                <textarea name="subtitle" id="bannerSubtitle" class="form-control" rows="2" maxlength="300">{{ old('subtitle', $banner->subtitle) }}</textarea>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Etiqueta (opcional)</label>
                                    <input type="text" name="badge" id="bannerBadge" class="form-control" maxlength="40" placeholder="POSTULACIÓN ABIERTA" value="{{ old('badge', $banner->badge) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Color de fondo</label>
                                    <input type="color" name="background_color" id="bannerColor" class="form-control form-control-color w-100" value="{{ old('background_color', $banner->background_color) }}">
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="background_color" value="{{ old('background_color', $banner->background_color) }}" data-show-type="image" id="bannerColorHidden">
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Al tocar el banner</h5></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Acción</label>
                                <select name="link_type" id="linkType" class="form-select">
                                    @foreach(\App\Models\AppBanner::LINK_TYPES as $value => $label)
                                        <option value="{{ $value }}" @selected(old('link_type', $banner->link_type) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6" data-show-link="program,url">
                                <label class="form-label">Texto del botón (opcional)</label>
                                <input type="text" name="button_label" id="bannerButton" class="form-control" maxlength="40" placeholder="Conocé más" value="{{ old('button_label', $banner->button_label) }}">
                            </div>
                            <div class="col-12" data-show-link="program">
                                <label class="form-label">Programa</label>
                                <select name="link_program_id" class="form-select">
                                    <option value="">Elegí un programa…</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" @selected((int) old('link_program_id', $banner->link_program_id) === $program->id)>{{ $program->name }}{{ $program->is_available_in_app ? '' : ' (no se postula desde la app)' }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Abre la ficha del programa en la app.</small>
                            </div>
                            <div class="col-12" data-show-link="url">
                                <label class="form-label">Enlace web</label>
                                <input type="url" name="link_url" class="form-control" maxlength="500" placeholder="https://ie.org.py/…" value="{{ old('link_url', $banner->link_url) }}">
                                <small class="text-muted">Se abre en el navegador del celular.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Vista previa</h5></div>
                    <div class="card-body">
                        <div id="previewImage" data-show-type="image" class="rounded overflow-hidden bg-light d-flex align-items-center justify-content-center text-muted" style="aspect-ratio: 2 / 1;">
                            @if($banner->image_url)
                                <img src="{{ $banner->image_url }}" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <span class="small">Elegí una imagen</span>
                            @endif
                        </div>
                        <div id="previewText" data-show-type="text" class="rounded p-3 text-white d-flex flex-column justify-content-center" style="aspect-ratio: 2 / 1;">
                            <div id="pvBadge" class="fw-bold mb-1" style="font-size: .7rem; letter-spacing: .05em; opacity: .9;"></div>
                            <div id="pvTitle" class="fw-bold fs-5 lh-sm"></div>
                            <div id="pvSubtitle" class="small mt-1" style="opacity: .92;"></div>
                            <div id="pvButton" class="fw-semibold mt-2 small"></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white"><h5 class="mb-0">Publicación</h5></div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Se muestra a</label>
                            <select name="audience" id="audience" class="form-select">
                                @foreach(\App\Models\AppBanner::AUDIENCES as $value => $label)
                                    <option value="{{ $value }}" @selected(old('audience', $banner->audience) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3" id="programFilter">
                            <label class="form-label">Solo postulantes de (opcional)</label>
                            <select name="program_id" class="form-select">
                                <option value="">Todos los participantes</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" @selected((int) old('program_id', $banner->program_id) === $program->id)>{{ $program->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Aplica solo a participantes con sesión iniciada.</small>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label">Desde (opcional)</label>
                                <input type="datetime-local" name="starts_at" class="form-control" value="{{ old('starts_at', $toLocal($banner->starts_at)) }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Hasta (opcional)</label>
                                <input type="datetime-local" name="ends_at" class="form-control" value="{{ old('ends_at', $toLocal($banner->ends_at)) }}">
                            </div>
                        </div>
                        <div class="row g-3 align-items-end">
                            <div class="col-6">
                                <label class="form-label">Orden</label>
                                <input type="number" name="sort_order" class="form-control" min="0" max="9999" value="{{ old('sort_order', $banner->sort_order) }}">
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch mb-2">
                                    <input type="hidden" name="is_active" value="0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="isActive" value="1" @checked(old('is_active', $banner->is_active))>
                                    <label class="form-check-label" for="isActive">Activo</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> Guardar banner</button>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    const $ = (sel) => document.querySelector(sel);
    const typeValue = () => document.querySelector('input[name="type"]:checked')?.value || 'text';

    function refresh() {
        const type = typeValue();
        document.querySelectorAll('[data-show-type]').forEach((el) => {
            const show = el.dataset.showType === type;
            el.style.display = show ? '' : 'none';
            el.querySelectorAll('input, textarea, select').forEach((i) => { if (i.type !== 'radio') i.disabled = !show; });
            if (el.tagName === 'INPUT') el.disabled = !show;
        });
        const link = $('#linkType').value;
        document.querySelectorAll('[data-show-link]').forEach((el) => {
            el.style.display = el.dataset.showLink.split(',').includes(link) ? '' : 'none';
        });
        $('#programFilter').style.display = $('#audience').value === 'public' ? 'none' : '';

        $('#previewText').style.background = $('#bannerColor').value;
        $('#pvBadge').textContent = $('#bannerBadge').value;
        $('#pvTitle').textContent = $('#bannerTitle').value || 'Título del banner';
        $('#pvSubtitle').textContent = $('#bannerSubtitle').value;
        $('#pvButton').textContent = link !== 'none' ? (($('#bannerButton').value || 'Conocé más') + ' →') : '';
    }

    document.querySelectorAll('input, select, textarea').forEach((el) => el.addEventListener('input', refresh));
    document.querySelectorAll('input[name="type"], #linkType, #audience').forEach((el) => el.addEventListener('change', refresh));

    $('#bannerImage').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        $('#previewImage').innerHTML = `<img src="${url}" alt="" style="width:100%;height:100%;object-fit:cover;">`;
    });

    refresh();
})();
</script>
@endsection
