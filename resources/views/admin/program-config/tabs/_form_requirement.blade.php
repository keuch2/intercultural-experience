@php $rid = $r?->id ?? 'new'; @endphp
<div class="row g-2">
    <div class="col-md-6"><label class="form-label small mb-0">Clave *</label><input name="key" class="form-control form-control-sm" value="{{ old('key', $r?->key) }}" placeholder="passport" required pattern="[a-z][a-z0-9_]{1,59}"></div>
    <div class="col-md-6"><label class="form-label small mb-0">Etiqueta *</label><input name="label" class="form-control form-control-sm" value="{{ old('label', $r?->label) }}" placeholder="Pasaporte" required></div>
    <div class="col-md-6"><label class="form-label small mb-0">Etapa *</label>
        <select name="stage_key" class="form-select form-select-sm" required>@foreach($stages as $st)<option value="{{ $st->key }}" {{ old('stage_key', $r?->stage_key) === $st->key ? 'selected' : '' }}>{{ $st->label }} ({{ $st->key }})</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label small mb-0">Grupo (tab) opcional</label><input name="group_key" class="form-control form-control-sm" value="{{ old('group_key', $r?->group_key) }}" placeholder="= etapa si vacío"></div>
    <div class="col-md-4"><label class="form-label small mb-0">Mín. archivos</label><input name="min_count" type="number" min="1" max="50" class="form-control form-control-sm" value="{{ old('min_count', $r?->min_count ?? 1) }}"></div>
    <div class="col-md-4"><label class="form-label small mb-0">Sube</label>
        <select name="uploaded_by" class="form-select form-select-sm"><option value="participant" {{ old('uploaded_by', $r?->uploaded_by ?? 'participant') === 'participant' ? 'selected' : '' }}>Participante</option><option value="staff" {{ old('uploaded_by', $r?->uploaded_by) === 'staff' ? 'selected' : '' }}>Equipo IE</option></select></div>
    <div class="col-md-4"><label class="form-label small mb-0">Orden</label><input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ old('sort_order', $r?->sort_order) }}"></div>
    <div class="col-md-6"><label class="form-label small mb-0">Desbloquea con gate de pago</label>
        <select name="unlock_gate_key" class="form-select form-select-sm"><option value="">— ninguno —</option>@foreach($gates as $gate)<option value="{{ $gate->key }}" {{ old('unlock_gate_key', $r?->unlock_gate_key) === $gate->key ? 'selected' : '' }}>{{ $gate->label }}</option>@endforeach</select></div>
    <div class="col-md-6"><label class="form-label small mb-0">Sección (opcional)</label><input name="section" class="form-control form-control-sm" value="{{ old('section', $r?->section) }}" placeholder="c6"></div>
    <div class="col-12"><label class="form-label small mb-0">Descripción / ayuda</label><input name="description" class="form-control form-control-sm" value="{{ old('description', $r?->description) }}"></div>
    <div class="col-12">
        <div class="form-check form-check-inline"><input type="hidden" name="is_required" value="0"><input class="form-check-input" type="checkbox" name="is_required" value="1" id="rq-{{ $rid }}" {{ ($r === null || $r->is_required) ? 'checked' : '' }}><label class="form-check-label small" for="rq-{{ $rid }}">Requerido</label></div>
        <div class="form-check form-check-inline"><input type="hidden" name="allow_multiple" value="0"><input class="form-check-input" type="checkbox" name="allow_multiple" value="1" id="am-{{ $rid }}" {{ $r?->allow_multiple ? 'checked' : '' }}><label class="form-check-label small" for="am-{{ $rid }}">Permite varios</label></div>
        <div class="form-check form-check-inline"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="ac-{{ $rid }}" {{ ($r === null || $r->is_active) ? 'checked' : '' }}><label class="form-check-label small" for="ac-{{ $rid }}">Activo</label></div>
    </div>
</div>
