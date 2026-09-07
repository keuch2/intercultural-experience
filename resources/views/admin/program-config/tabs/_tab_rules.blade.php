@php $rules = $program->rules ?? []; @endphp
<div class="card shadow-sm" style="max-width: 820px">
    <div class="card-header"><strong>Reglas del programa</strong></div>
    <div class="card-body">
        <form action="{{ route('admin.program-config.rules.update', $program->id) }}" method="POST">@csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label">Nivel mínimo de inglés</label>
                    <select name="min_english_level" class="form-select">@foreach(['A1','A2','B1','B2','C1','C2'] as $lvl)<option value="{{ $lvl }}" {{ ($rules['min_english_level'] ?? 'B1') === $lvl ? 'selected' : '' }}>{{ $lvl }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label">Máx. intentos de test</label><input type="number" name="max_english_attempts" min="1" max="10" class="form-control" value="{{ $rules['max_english_attempts'] ?? 3 }}"></div>
                <div class="col-md-4"><label class="form-label d-block">Pool de ofertas</label>
                    <div class="form-check form-switch"><input type="hidden" name="job_pool_allow_reselect" value="0"><input class="form-check-input" type="checkbox" name="job_pool_allow_reselect" value="1" id="jpr" {{ !empty($rules['job_pool_allow_reselect']) ? 'checked' : '' }}><label class="form-check-label" for="jpr">Permitir re-selección por defecto</label></div></div>
                <div class="col-md-8"><label class="form-label">Tipos de registro de Support (claves separadas por coma)</label>
                    <input name="support_log_types" class="form-control" value="{{ implode(', ', $rules['support_log_types'] ?? ['arrival_followup','program_followup','incident','final_evaluation']) }}">
                    <div class="form-text">Ej.: arrival_followup, program_followup, incident, employer_change, final_evaluation</div></div>
                <div class="col-md-4"><label class="form-label d-block">Secciones de visa visibles</label>
                    @foreach(['c1'=>'C1 Aplicación','c2'=>'C2 Cita','c3'=>'C3 Docs IE','c4'=>'C4 Resultado','c5'=>'C5 Viaje','c6'=>'C6 Orientación'] as $k => $lbl)
                    <div class="form-check"><input class="form-check-input" type="checkbox" name="visa_sections[]" value="{{ $k }}" id="vs-{{ $k }}" {{ in_array($k, $rules['visa_sections'] ?? ['c1','c2','c3','c4','c5','c6']) ? 'checked' : '' }}><label class="form-check-label small" for="vs-{{ $k }}">{{ $lbl }}</label></div>
                    @endforeach
                </div>
            </div>
            <button class="btn btn-primary mt-3"><i class="fas fa-save"></i> Guardar reglas</button>
        </form>
    </div>
</div>
