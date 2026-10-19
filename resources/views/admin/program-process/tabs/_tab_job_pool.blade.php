@php $access = $tabData['access']; $enabled = (bool) ($access['enabled'] ?? false); $assignment = $tabData['assignment']; $offers = $tabData['offers']; $history = $tabData['history']; $events = $tabData['events']; @endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center"><h5 class="card-title mb-0"><i class="fas fa-briefcase text-primary me-2"></i> Pool de Ofertas Laborales</h5>
        <a href="{{ route('admin.program.job-pool.index', $program->slug) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-list me-1"></i> Ver todas las ofertas</a></div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.program.module-access.update', [$program->slug, $process->id]) }}" class="row g-2 align-items-center mb-3">@csrf @method('PUT')<input type="hidden" name="module" value="job_pool">
            <div class="col-auto"><span class="badge {{ $enabled ? 'bg-success' : 'bg-warning text-dark' }} fs-6"><i class="fas fa-{{ $enabled ? 'unlock' : 'lock' }} me-1"></i>{{ $enabled ? 'Acceso habilitado' : 'Acceso no habilitado' }}</span>@if(!$enabled && ($access['disabled_reason'] ?? null) === 'offer_selected')<small class="text-muted ms-2">deshabilitado automáticamente al elegir oferta</small>@endif @if($enabled && !empty($access['enabled_at']))<small class="text-muted ms-2">desde {{ \Carbon\Carbon::parse($access['enabled_at'])->format('d/m/Y') }}</small>@endif</div>
            <div class="col-auto"><div class="form-check form-switch mb-0"><input type="hidden" name="allow_reselect" value="0"><input class="form-check-input" type="checkbox" name="allow_reselect" value="1" id="allowReselect" {{ !empty($access['allow_reselect']) ? 'checked' : '' }}><label class="form-check-label small" for="allowReselect">Autorizar cambio de oferta</label></div></div>
            <div class="col-auto ms-auto">
                <input type="hidden" name="enabled" value="{{ $enabled ? 0 : 1 }}">
                <button class="btn btn-sm {{ $enabled ? 'btn-outline-danger' : 'btn-success' }}">{!! $enabled ? '<i class="fas fa-ban me-1"></i> Deshabilitar acceso' : '<i class="fas fa-check me-1"></i> Habilitar acceso al Pool' !!}</button>
            </div>
        </form>
        <p class="text-muted small mb-0"><i class="fas fa-info-circle me-1"></i> Solo los participantes habilitados por IE ven las ofertas en la app. Al seleccionar una se descuenta un cupo y el acceso se deshabilita solo. Para un cambio de oferta, habilitá el acceso con "Autorizar cambio de oferta": al elegir la nueva, la anterior se libera automáticamente.</p>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h5 class="card-title mb-0"><i class="fas fa-user-check text-success me-2"></i> Oferta asignada</h5></div>
    <div class="card-body">
        @if($assignment)
        @php $o = $assignment->offer; @endphp
        <div class="border rounded p-3 border-success bg-success bg-opacity-10">
            <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                <div><h5 class="mb-1"><a href="{{ route('admin.program.job-pool.show', [$program->slug, $o->id]) }}" class="text-decoration-none">{{ $o->display_name }}</a></h5>@if($o->job_title)<div class="fw-semibold">{{ $o->employer_name }}</div>@endif<div class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>{{ $o->city }}, {{ $o->state }}</div><small class="text-muted">Seleccionada {{ $assignment->selected_at->format('d/m/Y H:i') }} · {{ $assignment->assignedBy ? 'asignada por '.$assignment->assignedBy->name : 'auto-selección desde la app' }}</small></div>
                <div class="d-flex gap-1">@if($o->hasPdf())<a href="{{ route('admin.program.job-pool.pdf', [$program->slug, $o->id]) }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf me-1"></i> PDF</a>@endif
                    <form method="POST" action="{{ route('admin.program.job-pool.release', [$program->slug, $o->id, $assignment->id]) }}" class="d-flex gap-1" onsubmit="return confirm('¿Liberar la asignación? El cupo vuelve a la oferta y el participante es notificado.')">@csrf<input type="text" name="reason" class="form-control form-control-sm" placeholder="Motivo" style="max-width:180px"><button class="btn btn-sm btn-outline-warning text-nowrap"><i class="fas fa-unlink me-1"></i> Liberar</button></form>
                    <button type="button" class="btn btn-sm btn-outline-danger text-nowrap" data-bs-toggle="modal" data-bs-target="#cancelJobModal"><i class="fas fa-ban me-1"></i> Trabajo cancelado</button></div>
            </div>
        </div>
        <div class="modal fade" id="cancelJobModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
            <form method="POST" action="{{ route('admin.program.job-pool.cancel-job', [$program->slug, $o->id, $assignment->id]) }}">@csrf
                <div class="modal-header"><h6 class="modal-title"><i class="fas fa-ban text-danger me-1"></i> Trabajo cancelado por el empleador</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p class="small text-muted">Se libera la asignación a <strong>{{ $o->display_name }}</strong>, se rehabilita el acceso del participante al Pool para que elija otra oferta y se le notifica.</p>
                    <label class="form-label small">Motivo / detalle</label>
                    <input type="text" name="reason" class="form-control form-control-sm mb-3" maxlength="500" placeholder="Ej.: el hotel canceló la temporada">
                    <label class="form-label small">¿Qué hacer con la oferta? *</label>
                    <div class="form-check"><input class="form-check-input" type="radio" name="offer_action" value="keep" id="oaKeep" checked><label class="form-check-label small" for="oaKeep">Mantenerla activa (el cupo vuelve a estar disponible)</label></div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="offer_action" value="pause" id="oaPause"><label class="form-check-label small" for="oaPause">Suspenderla (deja de verse en la app; se puede reactivar)</label></div>
                    <div class="form-check"><input class="form-check-input" type="radio" name="offer_action" value="delete" id="oaDelete"><label class="form-check-label small" for="oaDelete">Eliminarla (si tiene otros participantes asignados, queda suspendida)</label></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button class="btn btn-sm btn-danger"><i class="fas fa-ban me-1"></i> Registrar trabajo cancelado</button></div>
            </form>
        </div></div></div>
        @else
        <p class="text-muted mb-3"><i class="fas fa-info-circle"></i> El participante aún no seleccionó una oferta.</p>
        @if($offers->isNotEmpty())
        <h6 class="small text-muted text-uppercase">Ofertas disponibles (asignación manual por IE)</h6>
        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Empleador</th><th>Ubicación</th><th class="text-center">Cupos</th><th></th></tr></thead>
            <tbody>@foreach($offers as $o)<tr><td><strong>{{ $o->display_name }}</strong>@if($o->job_title)<div class="small text-muted">{{ $o->employer_name }}</div>@endif @if($o->requirements)<div class="small text-muted fst-italic">{{ Str::limit($o->requirements, 90) }}</div>@endif @if($o->application_deadline)<div class="small text-muted">hasta {{ $o->application_deadline->format('d/m/Y') }}</div>@endif</td><td>{{ $o->city }}, {{ $o->state }}</td><td class="text-center"><span class="badge bg-success">{{ $o->positions_available }}</span></td>
                <td class="text-end"><form method="POST" action="{{ route('admin.program.job-pool.assign', [$program->slug, $o->id]) }}" onsubmit="return confirm('¿Asignar {{ $o->display_name }} a este participante?')">@csrf<input type="hidden" name="process_id" value="{{ $process->id }}"><button class="btn btn-sm btn-outline-primary py-0"><i class="fas fa-user-plus me-1"></i> Asignar</button></form></td></tr>@endforeach</tbody>
        </table></div>
        @else<p class="text-muted small mb-0">No hay ofertas activas con cupo en este momento.</p>@endif
        @endif
    </div>
</div>

@if($history->where('status', '!=', 'active')->isNotEmpty() || $events->isNotEmpty())
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-history text-muted me-1"></i> Historial del participante en el Pool</h6></div>
    <div class="card-body">
        @foreach($events as $ev)
        <div class="border-bottom py-2 small d-flex justify-content-between"><div><strong>{{ $ev->label }}</strong> @if($ev->offer)— {{ $ev->offer->headline }} ({{ $ev->offer->city }})@endif @if(!empty($ev->payload['reason']))<span class="text-muted">· {{ $ev->payload['reason'] }}</span>@endif</div><span class="text-muted text-nowrap">{{ $ev->created_at->format('d/m/Y H:i') }} · {{ $ev->actor?->name ?? ucfirst($ev->actor_type) }}</span></div>
        @endforeach
    </div>
</div>
@endif

@if(!empty($tabData['stage']))
    @include('admin.program-process.tabs.partials._stage_gate')
@endif
