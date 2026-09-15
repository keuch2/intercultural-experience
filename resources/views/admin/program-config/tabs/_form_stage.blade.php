@php $g = $s?->guards ?? []; $sid = $s?->id ?? 'new'; @endphp
<div class="row g-2">
    <div class="col-md-4"><label class="form-label small mb-0">Clave *</label><input name="key" class="form-control form-control-sm" value="{{ old('key', $s?->key) }}" placeholder="admission" required pattern="[a-z][a-z0-9_]{1,49}"></div>
    <div class="col-md-5"><label class="form-label small mb-0">Etiqueta *</label><input name="label" class="form-control form-control-sm" value="{{ old('label', $s?->label) }}" placeholder="Admisión" required></div>
    <div class="col-md-3"><label class="form-label small mb-0">Orden</label><input name="sort_order" type="number" min="0" class="form-control form-control-sm" value="{{ old('sort_order', $s?->sort_order) }}"></div>
    <div class="col-md-6"><label class="form-label small mb-0">Pantalla móvil (opcional)</label><input name="mobile_screen" class="form-control form-control-sm" value="{{ old('mobile_screen', $s?->mobile_screen) }}" placeholder="JobPool, ProgramVisa, ProgramSupport…"></div>
    <div class="col-md-6"><label class="form-label small mb-0">Descripción</label><input name="description" class="form-control form-control-sm" value="{{ old('description', $s?->description) }}"></div>
    <div class="col-12">
        <div class="form-check form-check-inline"><input type="hidden" name="is_terminal" value="0"><input class="form-check-input" type="checkbox" name="is_terminal" value="1" id="term-{{ $sid }}" {{ $s?->is_terminal ? 'checked' : '' }}><label class="form-check-label small" for="term-{{ $sid }}">Etapa terminal</label></div>
    </div>
    <div class="col-12"><hr class="my-1"><span class="small fw-semibold">Condiciones para avanzar (guards)</span></div>
    <div class="col-md-6">
        <div class="form-check"><input type="hidden" name="guards[manual_only]" value="0"><input class="form-check-input" type="checkbox" name="guards[manual_only]" value="1" id="mo-{{ $sid }}" {{ !empty($g['manual_only']) ? 'checked' : '' }}><label class="form-check-label small" for="mo-{{ $sid }}">Solo manual (sin condiciones automáticas)</label></div>
        <div class="form-check"><input type="hidden" name="guards[require_docs_approved]" value="0"><input class="form-check-input" type="checkbox" name="guards[require_docs_approved]" value="1" id="rd-{{ $sid }}" {{ ($s === null || !empty($g['require_docs_approved'])) ? 'checked' : '' }}><label class="form-check-label small" for="rd-{{ $sid }}">Documentos requeridos aprobados</label></div>
        <div class="form-check"><input type="hidden" name="guards[require_english_min_level]" value="0"><input class="form-check-input" type="checkbox" name="guards[require_english_min_level]" value="1" id="re-{{ $sid }}" {{ !empty($g['require_english_min_level']) ? 'checked' : '' }}><label class="form-check-label small" for="re-{{ $sid }}">Nivel mínimo de inglés</label></div>
        <div class="form-check"><input type="hidden" name="guards[require_job_assignment]" value="0"><input class="form-check-input" type="checkbox" name="guards[require_job_assignment]" value="1" id="rj-{{ $sid }}" {{ !empty($g['require_job_assignment']) ? 'checked' : '' }}><label class="form-check-label small" for="rj-{{ $sid }}">Oferta laboral asignada</label></div>
        <div class="form-check"><input type="hidden" name="guards[require_placement_complete]" value="0"><input class="form-check-input" type="checkbox" name="guards[require_placement_complete]" value="1" id="rp-{{ $sid }}" {{ !empty($g['require_placement_complete']) ? 'checked' : '' }}><label class="form-check-label small" for="rp-{{ $sid }}">Job Placement completo</label></div>
        <div class="form-check"><input type="hidden" name="guards[require_visa_approved]" value="0"><input class="form-check-input" type="checkbox" name="guards[require_visa_approved]" value="1" id="rv-{{ $sid }}" {{ !empty($g['require_visa_approved']) ? 'checked' : '' }}><label class="form-check-label small" for="rv-{{ $sid }}">Visa aprobada (resultado de la entrevista consular)</label></div>
    </div>
    <div class="col-md-6">
        <label class="form-label small mb-0">Gates de pago verificados</label>
        <select name="guards[require_gates][]" class="form-select form-select-sm" multiple size="3">
            @foreach($gateOptions ?? $gates as $gate)<option value="{{ $gate->key }}" {{ in_array($gate->key, $g['require_gates'] ?? []) ? 'selected' : '' }}>{{ $gate->label }} ({{ $gate->key }})</option>@endforeach
        </select>
        <label class="form-label small mb-0 mt-1">Ítems de checklist completos</label>
        <select name="guards[require_checklist][]" class="form-select form-select-sm" multiple size="3">
            @foreach($checkOptions ?? $checklist as $item)<option value="{{ $item->key }}" {{ in_array($item->key, $g['require_checklist'] ?? []) ? 'selected' : '' }}>{{ $item->label }} ({{ $item->key }})</option>@endforeach
        </select>
    </div>
</div>
