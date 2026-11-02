{{-- Duración del programa: cantidad + unidad. Uso: @include('admin.partials._program_duration', ['program' => $program ?? null]) --}}
@php
    $durationValue = old('duration', $program?->duration);
    $durationUnit = old('duration_unit', $program?->duration ? $program->duration_unit : 'months');
@endphp
<div class="mb-3">
    <label for="duration" class="form-label">Duración</label>
    <div class="input-group">
        <input type="number"
               class="form-control @error('duration') is-invalid @enderror"
               id="duration"
               name="duration"
               value="{{ is_numeric($durationValue) ? $durationValue : '' }}"
               min="1">
        <select class="form-select @error('duration_unit') is-invalid @enderror" id="duration_unit" name="duration_unit" style="max-width: 9rem;">
            @foreach (\App\Models\Program::DURATION_UNITS as $unit => [$singular, $plural])
                <option value="{{ $unit }}" @selected($durationUnit === $unit)>{{ ucfirst($plural) }}</option>
            @endforeach
        </select>
        @error('duration')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="form-text">
        Se sugiere automáticamente al elegir las fechas; podés ajustarla. En la app se muestra en años y meses.
        <span id="duration_preview" class="fw-semibold"></span>
    </div>
</div>

<script>
(function () {
    const units = @json(collect(\App\Models\Program::DURATION_UNITS)->map(fn ($l) => $l));
    const valueInput = document.getElementById('duration');
    const unitSelect = document.getElementById('duration_unit');
    const preview = document.getElementById('duration_preview');
    const plural = (n, u) => n + ' ' + units[u][n === 1 ? 0 : 1];

    // Misma regla que Program::formatDuration()
    function label(value, unit) {
        value = parseInt(value, 10);
        if (!value || value <= 0) return '';
        if (unit === 'years') return plural(value, 'years');
        let months = value;
        if (unit !== 'months') {
            const days = unit === 'weeks' ? value * 7 : value;
            if (days < 28) return plural(value, unit);
            months = Math.max(1, Math.round(days / 30.4375));
        }
        const years = Math.floor(months / 12);
        months = months % 12;
        return [years ? plural(years, 'years') : null, months ? plural(months, 'months') : null]
            .filter(Boolean).join(' y ');
    }

    function refreshPreview() {
        const text = label(valueInput.value, unitSelect.value);
        preview.textContent = text ? '→ ' + text : '';
    }

    // Sugiere cantidad + unidad a partir de las fechas de inicio y fin
    function suggestFromDates() {
        const start = document.getElementById('start_date')?.value;
        const end = document.getElementById('end_date')?.value;
        if (!start || !end) return;
        const days = Math.round((new Date(end) - new Date(start)) / 86400000);
        if (days <= 0) return;
        const months = Math.round(days / 30.4375);
        let value = days, unit = 'days';
        if (months >= 1 && Math.abs(days - months * 30.4375) <= 4) {
            [value, unit] = months % 12 === 0 ? [months / 12, 'years'] : [months, 'months'];
        } else if (days % 7 === 0) {
            [value, unit] = [days / 7, 'weeks'];
        }
        valueInput.value = value;
        unitSelect.value = unit;
        refreshPreview();
    }

    valueInput.addEventListener('input', refreshPreview);
    unitSelect.addEventListener('change', refreshPreview);
    document.getElementById('start_date')?.addEventListener('change', suggestFromDates);
    document.getElementById('end_date')?.addEventListener('change', suggestFromDates);
    refreshPreview();
})();
</script>
