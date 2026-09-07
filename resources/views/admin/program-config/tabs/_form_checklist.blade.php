@php $cid = $c?->id ?? 'new'; @endphp
<div class="row g-2">
    <div class="col-md-6"><label class="form-label small mb-0">Clave *</label><input name="key" class="form-control form-control-sm" value="{{ old('key', $c?->key) }}" placeholder="welcome_email_sent" required pattern="[a-z][a-z0-9_]{1,49}"></div>
    <div class="col-md-6"><label class="form-label small mb-0">Etiqueta *</label><input name="label" class="form-control form-control-sm" value="{{ old('label', $c?->label) }}" placeholder="Correo de bienvenida enviado" required></div>
    <div class="col-md-5"><label class="form-label small mb-0">Etapa</label>
        <select name="stage_key" class="form-select form-select-sm"><option value="">— cualquiera —</option>@foreach($stages as $st)<option value="{{ $st->key }}" {{ old('stage_key', $c?->stage_key) === $st->key ? 'selected' : '' }}>{{ $st->label }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label small mb-0">Tipo</label>
        <select name="item_type" class="form-select form-select-sm"><option value="boolean" {{ old('item_type', $c?->item_type ?? 'boolean') === 'boolean' ? 'selected' : '' }}>Sí / No</option><option value="file" {{ old('item_type', $c?->item_type) === 'file' ? 'selected' : '' }}>Con archivo (p.ej. contrato)</option></select></div>
    <div class="col-md-3"><label class="form-label small mb-0">Orden</label><input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ old('sort_order', $c?->sort_order) }}"></div>
    <div class="col-12">
        <div class="form-check form-check-inline"><input type="hidden" name="required_for_advance" value="0"><input class="form-check-input" type="checkbox" name="required_for_advance" value="1" id="rfa-{{ $cid }}" {{ $c?->required_for_advance ? 'checked' : '' }}><label class="form-check-label small" for="rfa-{{ $cid }}">Requerido para avanzar de etapa</label></div>
        <div class="form-check form-check-inline"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="cac-{{ $cid }}" {{ ($c === null || $c->is_active) ? 'checked' : '' }}><label class="form-check-label small" for="cac-{{ $cid }}">Activo</label></div>
    </div>
</div>
