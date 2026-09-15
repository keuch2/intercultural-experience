{{-- Gate de avance de etapa. Requiere $tabData['stage'], ['isCurrent'], ['isPast'], ['blockingReasons'].
     $prominent = true lo muestra como card "Siguiente etapa" (para etapas largas como Visa). --}}
@php
    $stage = $tabData['stage']; $isCurrent = $tabData['isCurrent']; $isPast = $tabData['isPast'];
    $prominent = $prominent ?? false;
@endphp
@if($process->status === 'active')
    @if($isPast)
        <div class="mt-3 p-3 rounded bg-success bg-opacity-10 border border-success"><i class="fas fa-check-circle text-success me-2"></i><strong>Etapa "{{ $stage->label }}" completada.</strong> El proceso se encuentra en: <span class="badge bg-primary">{{ $definition->stage($process->current_stage_key)?->label }}</span></div>
    @elseif($isCurrent)
        @php
            $reasons = $tabData['blockingReasons']; $next = $definition->nextStage($stage->key);
            $automatic = $stage->hasAutomaticGuards();
            $tone = empty($reasons) ? ($automatic ? 'success' : 'secondary') : 'warning';
        @endphp
        @if($prominent)
        <div class="card shadow-sm mb-4 border-{{ $tone }}">
            <div class="card-header bg-{{ $tone }} bg-opacity-10"><h6 class="mb-0"><i class="fas fa-arrow-circle-right text-{{ $tone }} me-1"></i> Siguiente etapa: {{ $next?->label ?? '—' }}</h6></div>
            <div class="card-body">
                @if(empty($reasons))
                    @if($automatic)<p class="mb-3"><i class="fas fa-check-circle text-success me-1"></i> <strong>Requisitos completos.</strong> El participante puede pasar a "{{ $next?->label }}".</p>
                    @else<p class="mb-3 text-muted"><i class="fas fa-hand-pointer me-1"></i> Etapa manual: pasá al participante a "{{ $next?->label }}" cuando corresponda.</p>@endif
                @else
                    <p class="mb-2"><i class="fas fa-lock text-warning me-1"></i> <strong>Pendiente para avanzar a "{{ $next?->label }}":</strong></p>
                    <ul class="mb-3">@foreach($reasons as $r)<li>{{ $r }}</li>@endforeach</ul>
                @endif
                @if($next)
                <form method="POST" action="{{ route('admin.program.stage.advance', [$program->slug, $process->id]) }}">@csrf
                    @if(!empty($reasons))<input type="hidden" name="force" value="1">@endif
                    <button type="submit" class="btn {{ empty($reasons) ? 'btn-success' : 'btn-outline-warning' }}" @if(!empty($reasons)) onclick="return confirm('Hay condiciones pendientes. ¿Forzar el avance a {{ $next->label }}?')" @endif>
                        {{ empty($reasons) ? 'Avanzar a' : 'Forzar avance a' }} {{ $next->label }} <i class="fas fa-arrow-right ms-1"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @else
        <div class="mt-3 p-3 rounded bg-{{ $tone }} bg-opacity-10 border border-{{ $tone }}">
            <div class="d-flex align-items-center justify-content-between gap-3">
                <div>
                    @if(empty($reasons) && $automatic)<i class="fas fa-check-circle text-success me-2"></i><strong>Requisitos completos.</strong> Se puede avanzar a "{{ $next?->label }}".
                    @elseif(empty($reasons))<i class="fas fa-hand-pointer text-secondary me-2"></i><strong>Etapa manual.</strong> Avanzar a "{{ $next?->label }}" cuando corresponda.
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
@endif
