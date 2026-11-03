{{-- Duración del programa: texto libre informativo. Uso: @include('admin.partials._program_duration', ['program' => $program ?? null]) --}}
<div class="mb-3">
    <label for="duration" class="form-label">Duración</label>
    <input type="text"
           class="form-control @error('duration') is-invalid @enderror"
           id="duration"
           name="duration"
           value="{{ old('duration', $program?->duration) }}"
           maxlength="100"
           placeholder="Ej.: 3 meses, 4 a 6 meses, 6 meses a 1 año, Variable">
    <div class="form-text">Solo informativo: se muestra tal cual en la app.</div>
    @error('duration')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
