@php $xid = $x?->id ?? 'new'; @endphp
<div class="row g-2">
    <div class="col-md-8"><label class="form-label small mb-0">Título *</label><input name="title" class="form-control form-control-sm" value="{{ old('title', $x?->title) }}" required></div>
    <div class="col-md-4"><label class="form-label small mb-0">Tipo</label>
        <select name="file_type" class="form-select form-select-sm">@foreach(['PDF','DOC','VIDEO','LINK'] as $t)<option value="{{ $t }}" {{ old('file_type', $x?->file_type ?? 'PDF') === $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach</select></div>
    <div class="col-12"><label class="form-label small mb-0">Descripción</label><input name="description" class="form-control form-control-sm" value="{{ old('description', $x?->description) }}"></div>
    <div class="col-md-8"><label class="form-label small mb-0">Archivo (PDF/DOC/imagen/video, máx. 50MB)</label><input type="file" name="file" class="form-control form-control-sm"></div>
    <div class="col-md-4"><label class="form-label small mb-0">Orden</label><input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ old('sort_order', $x?->sort_order) }}"></div>
    <div class="col-12"><label class="form-label small mb-0">URL externa (para tipo LINK o video)</label><input name="external_url" type="url" class="form-control form-control-sm" value="{{ old('external_url', $x?->external_url) }}"></div>
    <div class="col-12"><div class="form-check"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="xac-{{ $xid }}" {{ ($x === null || $x->is_active) ? 'checked' : '' }}><label class="form-check-label small" for="xac-{{ $xid }}">Activo</label></div></div>
</div>
