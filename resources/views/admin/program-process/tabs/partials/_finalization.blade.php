{{-- Finalización del programa: solo se muestra en la última etapa del workflow (la anterior a la terminal).
     Requiere $redirectTab (tab al que volver tras registrar). --}}
@php $redirectTab = $redirectTab ?? ($tabData['stage']->key ?? 'support'); @endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-flag-checkered text-primary me-1"></i> Finalización del programa</h6></div>
    <div class="card-body">
        <p class="text-muted small"><i class="fas fa-info-circle me-1"></i> Registrá acá el cierre cuando el participante <strong>termine el programa</strong>. El proceso pasa a "Completado".</p>
        @if($process->finalization_result)
        <div class="alert alert-{{ $process->finalization_result === 'success' ? 'success' : ($process->finalization_result === 'not_success' ? 'danger' : 'warning') }} mb-3">
            <strong>{{ ['success' => 'Finalizó con éxito', 'not_success' => 'No finalizó con éxito', 'status_change' => 'Cambio de estatus', 'other' => 'Otro', 'cancelled' => 'Cancelado'][$process->finalization_result] ?? $process->finalization_result }}</strong>
            @if($process->finalization_date)<small class="ms-2">{{ $process->finalization_date->format('d/m/Y') }}</small>@endif
            @if($process->finalization_reason)<p class="mb-0 mt-2 small">{{ $process->finalization_reason }}</p>@endif
        </div>
        @endif
        <form method="POST" action="{{ route('admin.program.finalization.update', [$program->slug, $process->id]) }}">@csrf @method('PUT')<input type="hidden" name="redirect_tab" value="{{ $redirectTab }}">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label small">Resultado *</label><select name="finalization_result" class="form-select form-select-sm" required><option value="">-- Seleccionar --</option>@foreach(['success' => 'Finalizó con éxito', 'not_success' => 'No finalizó con éxito', 'status_change' => 'Cambio de estatus', 'other' => 'Otro'] as $v => $l)<option value="{{ $v }}" {{ $process->finalization_result === $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label small">Fecha</label><input type="date" name="finalization_date" class="form-control form-control-sm" value="{{ $process->finalization_date?->format('Y-m-d') }}"></div>
                <div class="col-12"><label class="form-label small">Motivo / observaciones</label><textarea name="finalization_reason" class="form-control form-control-sm" rows="3">{{ $process->finalization_reason }}</textarea></div>
            </div>
            <button type="submit" class="btn btn-sm btn-primary mt-3" onclick="return confirm('¿Registrar la finalización? El proceso pasará a Completado.')"><i class="fas fa-flag-checkered me-1"></i> Registrar finalización</button>
        </form>
    </div>
</div>
