@php $placement = $tabData['placement']; $assignment = $tabData['assignment']; $o = $assignment?->offer; $sponsors = $tabData['sponsors']; $entries = $tabData['entries']; $docsOk = $tabData['documentsComplete']; @endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center"><h5 class="card-title mb-0"><i class="fas fa-building-user text-primary me-2"></i> Job Placement</h5><span class="badge bg-{{ ['completed' => 'success', 'ds_shipped' => 'info', 'documents_complete' => 'primary', 'in_progress' => 'warning text-dark', 'cancelled' => 'danger'][$placement->status] ?? 'secondary' }} fs-6">{{ $placement->status_label }}</span></div>
    <div class="card-body">
        @if($o)
        <div class="border rounded p-3 mb-3 bg-light">
            <div class="row g-3">
                <div class="col-md-4"><small class="text-muted d-block">Puesto / Empleador</small><strong>{{ $o->display_name }}</strong>@if($o->job_title)<div class="small">{{ $o->employer_name }}</div>@endif</div>
                <div class="col-md-3"><small class="text-muted d-block">Estado / Ciudad</small>{{ $o->state }} / {{ $o->city }}</div>
                <div class="col-md-3"><small class="text-muted d-block">Fecha de aceptación</small>{{ $placement->acceptance_date?->format('d/m/Y') ?? $assignment->selected_at->format('d/m/Y') }}</div>
                <div class="col-md-2 text-end">@if($o->hasPdf())<a href="{{ route('admin.program.job-pool.pdf', [$program->slug, $o->id]) }}" class="btn btn-sm btn-outline-danger"><i class="fas fa-file-pdf me-1"></i> PDF</a>@endif</div>
            </div>
        </div>
        @else
        <div class="alert alert-warning py-2 px-3 mb-3"><i class="fas fa-exclamation-triangle me-1"></i><small>El participante aún no tiene una oferta asignada. Asignala desde la pestaña <strong>Pool de Ofertas</strong>.</small></div>
        @endif

        <form method="POST" action="{{ route('admin.program.placement.update', [$program->slug, $process->id]) }}">@csrf @method('PUT')
            <h6 class="small fw-bold text-muted text-uppercase">Programa y Sponsor</h6>
            <div class="row g-3 mb-3">
                <div class="col-md-4"><label class="form-label small">Sponsor</label><select name="sponsor_id" class="form-select form-select-sm"><option value="">-- Seleccionar --</option>@foreach($sponsors as $sp)<option value="{{ $sp->id }}" {{ $placement->sponsor_id == $sp->id ? 'selected' : '' }}>{{ $sp->name }} ({{ $sp->code }})</option>@endforeach</select></div>
                <div class="col-md-3"><label class="form-label small">Fecha de aceptación</label><input type="date" name="acceptance_date" class="form-control form-control-sm" value="{{ $placement->acceptance_date?->format('Y-m-d') }}"></div>
                <div class="col-md-2"><label class="form-label small">Inicio programa</label><input type="date" name="program_start_date" class="form-control form-control-sm" value="{{ $placement->program_start_date?->format('Y-m-d') }}"></div>
                <div class="col-md-2"><label class="form-label small">Fin programa</label><input type="date" name="program_end_date" class="form-control form-control-sm" value="{{ $placement->program_end_date?->format('Y-m-d') }}"></div>
                <div class="col-md-1 d-flex align-items-end"><div class="form-check"><input type="hidden" name="terms_accepted" value="0"><input class="form-check-input" type="checkbox" name="terms_accepted" value="1" id="terms" {{ $placement->terms_accepted_at ? 'checked' : '' }}><label class="form-check-label small" for="terms">T&C</label></div></div>
            </div>
            <h6 class="small fw-bold text-muted text-uppercase">SEVIS y DS-2019 <small class="fw-normal text-muted">(se completan cuando la documentación del Sponsor está aprobada)</small></h6>
            <div class="row g-3 mb-3">
                <div class="col-md-3"><label class="form-label small">Número SEVIS</label><input type="text" name="sevis_number" class="form-control form-control-sm" value="{{ $placement->sevis_number }}" placeholder="N0012345678"></div>
                <div class="col-md-3"><label class="form-label small">Número DS-2019</label><input type="text" name="ds2019_number" class="form-control form-control-sm" value="{{ $placement->ds2019_number }}"></div>
                <div class="col-md-2"><label class="form-label small">Courier</label><input type="text" name="ds_tracking_carrier" class="form-control form-control-sm" value="{{ $placement->ds_tracking_carrier }}" placeholder="DHL / FedEx"></div>
                <div class="col-md-2"><label class="form-label small">Tracking envío DS</label><input type="text" name="ds_tracking_number" class="form-control form-control-sm" value="{{ $placement->ds_tracking_number }}"></div>
                <div class="col-md-2"><label class="form-label small">Fecha de recepción</label><input type="date" name="ds_received_at" class="form-control form-control-sm" value="{{ $placement->ds_received_at?->format('Y-m-d') }}"></div>
            </div>
            <div class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label small">Estado del placement <small class="text-muted">(vacío = automático)</small></label><select name="status" class="form-select form-select-sm"><option value="">Automático</option>@foreach(\App\Models\JobPlacement::STATUSES as $k => $l)<option value="{{ $k }}" {{ $placement->status === $k ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
                <div class="col-md-7"><label class="form-label small">Notas</label><input type="text" name="notes" class="form-control form-control-sm" value="{{ $placement->notes }}"></div>
                <div class="col-md-2"><button class="btn btn-sm btn-primary w-100"><i class="fas fa-save me-1"></i> Guardar</button></div>
            </div>
        </form>
    </div>
</div>

@include('admin.program-process.tabs.partials._documents', ['group' => ['key' => 'placement', 'label' => 'Documentos según Sponsor (carga IE)', 'stage_key' => 'placement', 'unlock_gate_key' => null], 'groupEntries' => $entries])

<div class="alert {{ $docsOk && $placement->sevis_number && $placement->ds2019_number ? 'alert-success' : 'alert-info' }} py-2 px-3"><small><i class="fas fa-{{ $docsOk ? 'check-circle' : 'info-circle' }} me-1"></i>Placement completo cuando: documentos del Sponsor aprobados {!! $docsOk ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!} · SEVIS {!! $placement->sevis_number ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!} · DS-2019 {!! $placement->ds2019_number ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}</small></div>

@if(!empty($tabData['stage']))
    @include('admin.program-process.tabs.partials._stage_gate')
@endif
