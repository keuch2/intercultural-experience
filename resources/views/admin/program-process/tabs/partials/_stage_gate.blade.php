{{-- Gate de avance de etapa. Requiere $tabData['stage'], ['isCurrent'], ['isPast'], ['blockingReasons'] --}}
@php $stage = $tabData['stage']; $isCurrent = $tabData['isCurrent']; $isPast = $tabData['isPast']; @endphp
{{-- Gate de avance --}}
@if($process->status === 'active')
    @if($isPast)
        <div class="mt-3 p-3 rounded bg-success bg-opacity-10 border border-success"><i class="fas fa-check-circle text-success me-2"></i><strong>Etapa "{{ $stage->label }}" completada.</strong> El proceso se encuentra en: <span class="badge bg-primary">{{ $definition->stage($process->current_stage_key)?->label }}</span></div>
    @elseif($isCurrent)
        @php $reasons = $tabData['blockingReasons']; $next = $definition->nextStage($stage->key); @endphp
        <div class="mt-3 p-3 rounded {{ empty($reasons) ? 'bg-success bg-opacity-10 border border-success' : 'bg-warning bg-opacity-10 border border-warning' }}">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    @if(empty($reasons))<i class="fas fa-check-circle text-success me-2"></i><strong>Requisitos completos.</strong> Se puede avanzar a "{{ $next?->label }}".
                    @else<i class="fas fa-lock text-warning me-2"></i><strong>Pendiente para avanzar:</strong> {{ implode(' ', $reasons) }}@endif
                </div>
                @if($next)
                <form method="POST" action="{{ route('admin.program.stage.advance', [$program->slug, $process->id]) }}" class="d-flex gap-2">@csrf
                    @if(!empty($reasons))<input type="hidden" name="force" value="1">@endif
                    <button type="submit" class="btn btn-sm {{ empty($reasons) ? 'btn-success' : 'btn-outline-warning' }}" @if(!empty($reasons)) onclick="return confirm('Hay condiciones pendientes. ¿Forzar el avance a {{ $next->label }}?')" @endif>
                        {{ empty($reasons) ? 'Avanzar a' : 'Forzar avance a' }} {{ $next->label }} <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
    @endif
@endif
