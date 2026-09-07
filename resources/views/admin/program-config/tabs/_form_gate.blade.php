@php $gid = $g?->id ?? 'new'; @endphp
<div class="row g-2">
    <div class="col-md-5"><label class="form-label small mb-0">Clave *</label><input name="key" class="form-control form-control-sm" value="{{ old('key', $g?->key) }}" placeholder="inscription" required pattern="[a-z][a-z0-9_]{1,49}"></div>
    <div class="col-md-7"><label class="form-label small mb-0">Etiqueta *</label><input name="label" class="form-control form-control-sm" value="{{ old('label', $g?->label) }}" placeholder="Pago de inscripción" required></div>
    <div class="col-md-5"><label class="form-label small mb-0">Monto</label><input name="amount" type="number" step="0.01" min="0" class="form-control form-control-sm" value="{{ old('amount', $g?->amount) }}"></div>
    <div class="col-md-4"><label class="form-label small mb-0">Moneda</label>
        <select name="currency_id" class="form-select form-select-sm"><option value="">—</option>@foreach($currencies as $cur)<option value="{{ $cur->id }}" {{ (string) old('currency_id', $g?->currency_id) === (string) $cur->id ? 'selected' : '' }}>{{ $cur->code }}</option>@endforeach</select></div>
    <div class="col-md-3"><label class="form-label small mb-0">Orden</label><input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ old('sort_order', $g?->sort_order) }}"></div>
    <div class="col-12"><label class="form-label small mb-0">Texto a detectar en el concepto del pago (opcional)</label><input name="concept_match" class="form-control form-control-sm" value="{{ old('concept_match', $g?->concept_match) }}" placeholder="inscripci"></div>
    <div class="col-12">
        <div class="form-check form-check-inline"><input type="hidden" name="auto_verify_from_payments" value="0"><input class="form-check-input" type="checkbox" name="auto_verify_from_payments" value="1" id="av-{{ $gid }}" {{ $g?->auto_verify_from_payments ? 'checked' : '' }}><label class="form-check-label small" for="av-{{ $gid }}">Verificar automáticamente al verificar un pago con ese concepto</label></div>
        <div class="form-check form-check-inline"><input type="hidden" name="is_active" value="0"><input class="form-check-input" type="checkbox" name="is_active" value="1" id="gac-{{ $gid }}" {{ ($g === null || $g->is_active) ? 'checked' : '' }}><label class="form-check-label small" for="gac-{{ $gid }}">Activo</label></div>
    </div>
</div>
