@php
    $visa = $tabData['visa'];
    $sections = $tabData['sections'];
    $entries = collect($tabData['entries']);
    $participantEntries = $entries->filter(fn ($e) => $e['uploaded_by'] !== 'staff' && $e['section'] !== 'c6')->values();
    $staffEntries = $entries->filter(fn ($e) => $e['uploaded_by'] === 'staff' && $e['section'] !== 'c6')->values();
    $c6Entries = $entries->filter(fn ($e) => $e['section'] === 'c6')->values();
    $visaStage = $definition->stage('visa');
    $visaGroup = ['key' => 'visa', 'label' => 'Visa J1', 'stage_key' => 'visa', 'unlock_gate_key' => null];
    $flightInfo = $visa->flight_info ?? []; $outboundLegs = $flightInfo['outbound_legs'] ?? []; $returnLegs = $flightInfo['return_legs'] ?? [];
@endphp

@if($visaStage && $definition->stageIndex($process->current_stage_key) < $definition->stageIndex('visa') && $process->status === 'active')
<div class="alert alert-info py-2 px-3 mb-3"><i class="fas fa-info-circle me-1"></i><small><strong>Pendiente:</strong> el participante aún no llegó a la etapa de Visa J1.</small></div>
@endif

<form id="visaProcessForm" method="POST" action="{{ route('admin.program.visa.update', [$program->slug, $process->id]) }}">@csrf @method('PUT')</form>

@if(in_array('c1', $sections))
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-envelope text-primary me-1"></i> C1. Aplicación de Visa</h6></div>
    <div class="card-body"><div class="list-group">
        @foreach(['visa_email_sent' => 'Correo de visa enviado al participante', 'consular_fee_paid' => 'Pago de tarifa consular', 'appointment_scheduled' => 'Agendamiento de cita', 'documents_sent_for_appointment' => 'Envío de documentos para cita de visa'] as $field => $label)
        <label class="list-group-item d-flex align-items-center"><input class="form-check-input me-3" type="checkbox" form="visaProcessForm" name="{{ $field }}" value="1" {{ $visa->$field ? 'checked' : '' }}><span>{{ $label }}</span></label>
        @endforeach
    </div></div>
</div>
@if($participantEntries->isNotEmpty())
    @include('admin.program-process.tabs.partials._documents', ['group' => ['key' => 'visa', 'label' => 'Visa J1 · documentos del participante', 'stage_key' => 'visa', 'unlock_gate_key' => null], 'groupEntries' => $participantEntries])
@endif
@endif

@if(in_array('c3', $sections))
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-file-upload text-primary me-1"></i> C2/C3. Documentos IE y chequeo</h6></div>
    <div class="card-body">
        <div class="list-group mb-3"><label class="list-group-item d-flex align-items-center bg-warning bg-opacity-10"><input class="form-check-input me-3" type="checkbox" form="visaProcessForm" name="document_check_completed" value="1" {{ $visa->document_check_completed ? 'checked' : '' }}><span class="small fw-bold">Chequeo de documentos realizado con el participante</span>@if($visa->document_check_completed_at)<small class="ms-auto text-success">{{ $visa->document_check_completed_at->format('d/m/Y H:i') }}</small>@endif</label></div>
    </div>
</div>
@if($staffEntries->isNotEmpty())
    @include('admin.program-process.tabs.partials._documents', ['group' => ['key' => 'visa_staff', 'label' => 'Visa J1 · documentos IE (DS-160, DS-2019, SEVIS…)', 'stage_key' => 'visa', 'unlock_gate_key' => null], 'groupEntries' => $staffEntries])
@endif
@endif

@if(in_array('c2', $sections))
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-calendar-alt text-primary me-1"></i> Cita de Visa</h6></div>
    <div class="card-body"><div class="row g-3">
        <div class="col-md-4"><label class="form-label small">Fecha</label><input type="date" form="visaProcessForm" name="appointment_date" class="form-control form-control-sm" value="{{ $visa->appointment_date?->format('Y-m-d') }}"></div>
        <div class="col-md-4"><label class="form-label small">Hora</label><input type="time" form="visaProcessForm" name="appointment_time" class="form-control form-control-sm" value="{{ $visa->appointment_time ? substr((string) $visa->appointment_time, 0, 5) : '' }}"></div>
        <div class="col-md-4"><label class="form-label small">Embajada</label><input type="text" form="visaProcessForm" name="embassy" class="form-control form-control-sm" value="{{ $visa->embassy }}" placeholder="Embajada USA Asunción"></div>
    </div></div>
</div>
@endif

@if(in_array('c4', $sections))
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-gavel text-primary me-1"></i> C4. Resultado de la entrevista</h6></div>
    <div class="card-body"><div class="row g-3">
        <div class="col-md-4"><label class="form-label small">Resultado</label><select name="interview_result" form="visaProcessForm" class="form-select form-select-sm">@foreach(['pending' => 'Pendiente', 'approved' => 'Aprobada', 'denied' => 'Denegada', 'administrative_process' => 'Proceso administrativo'] as $v => $l)<option value="{{ $v }}" {{ ($visa->interview_result ?? 'pending') === $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
        <div class="col-md-8"><label class="form-label small">Notas</label><input type="text" name="interview_result_notes" form="visaProcessForm" class="form-control form-control-sm" value="{{ $visa->interview_result_notes }}"></div>
    </div></div>
</div>
@endif

@if(in_array('c5', $sections))
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-plane-departure text-primary me-1"></i> C5. Información de viaje</h6></div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-4"><label class="form-label small">Fecha/hora salida</label><input type="datetime-local" form="visaProcessForm" name="departure_datetime" class="form-control form-control-sm" value="{{ $visa->departure_datetime?->format('Y-m-d\TH:i') }}"></div>
            <div class="col-md-4"><label class="form-label small">Fecha/hora llegada USA</label><input type="datetime-local" form="visaProcessForm" name="arrival_usa_datetime" class="form-control form-control-sm" value="{{ $visa->arrival_usa_datetime?->format('Y-m-d\TH:i') }}"></div>
        </div>
        @foreach(['outbound' => ['Viaje de ida', $outboundLegs], 'return' => ['Viaje de regreso', $returnLegs]] as $prefix => [$title, $legs])
        <h6 class="small fw-bold text-muted mb-2"><i class="fas fa-plane me-1"></i> {{ $title }}</h6>
        <div id="{{ $prefix }}-legs-container">
            @foreach($legs as $i => $leg)
            <div class="border rounded p-2 mb-2 {{ $prefix }}-leg"><div class="row g-2 align-items-end">
                <div class="col-md-2"><label class="form-label small">Tramo {{ $i + 1 }}</label><input type="text" name="{{ $prefix }}_legs[{{ $i }}][origin]" form="visaProcessForm" class="form-control form-control-sm" value="{{ $leg['origin'] ?? '' }}" placeholder="Origen"></div>
                <div class="col-md-2"><label class="form-label small">&nbsp;</label><input type="text" name="{{ $prefix }}_legs[{{ $i }}][destination]" form="visaProcessForm" class="form-control form-control-sm" value="{{ $leg['destination'] ?? '' }}" placeholder="Destino"></div>
                <div class="col-md-2"><label class="form-label small">Aerolínea</label><input type="text" name="{{ $prefix }}_legs[{{ $i }}][airline]" form="visaProcessForm" class="form-control form-control-sm" value="{{ $leg['airline'] ?? '' }}"></div>
                <div class="col-md-2"><label class="form-label small">Vuelo #</label><input type="text" name="{{ $prefix }}_legs[{{ $i }}][flight_number]" form="visaProcessForm" class="form-control form-control-sm" value="{{ $leg['flight_number'] ?? '' }}"></div>
                <div class="col-md-3"><label class="form-label small">Salida</label><input type="datetime-local" name="{{ $prefix }}_legs[{{ $i }}][departure]" form="visaProcessForm" class="form-control form-control-sm" value="{{ $leg['departure'] ?? '' }}"></div>
                <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-leg"><i class="fas fa-times"></i></button></div>
            </div></div>
            @endforeach
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary mb-3 add-leg" data-prefix="{{ $prefix }}"><i class="fas fa-plus me-1"></i> Agregar tramo</button>
        @endforeach
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.add-leg').forEach(btn => btn.addEventListener('click', function () {
        const prefix = this.dataset.prefix, container = document.getElementById(prefix + '-legs-container');
        const idx = container.querySelectorAll('.' + prefix + '-leg').length;
        const div = document.createElement('div'); div.className = 'border rounded p-2 mb-2 ' + prefix + '-leg';
        div.innerHTML = `<div class="row g-2 align-items-end">
            <div class="col-md-2"><label class="form-label small">Tramo ${idx + 1}</label><input type="text" name="${prefix}_legs[${idx}][origin]" form="visaProcessForm" class="form-control form-control-sm" placeholder="Origen"></div>
            <div class="col-md-2"><label class="form-label small">&nbsp;</label><input type="text" name="${prefix}_legs[${idx}][destination]" form="visaProcessForm" class="form-control form-control-sm" placeholder="Destino"></div>
            <div class="col-md-2"><label class="form-label small">Aerolínea</label><input type="text" name="${prefix}_legs[${idx}][airline]" form="visaProcessForm" class="form-control form-control-sm"></div>
            <div class="col-md-2"><label class="form-label small">Vuelo #</label><input type="text" name="${prefix}_legs[${idx}][flight_number]" form="visaProcessForm" class="form-control form-control-sm"></div>
            <div class="col-md-3"><label class="form-label small">Salida</label><input type="datetime-local" name="${prefix}_legs[${idx}][departure]" form="visaProcessForm" class="form-control form-control-sm"></div>
            <div class="col-md-1 text-end"><button type="button" class="btn btn-sm btn-outline-danger remove-leg"><i class="fas fa-times"></i></button></div></div>`;
        container.appendChild(div);
    }));
    document.addEventListener('click', e => { const b = e.target.closest('.remove-leg'); if (b) b.closest('.outbound-leg, .return-leg')?.remove(); });
});
</script>
@endif

@if(in_array('c6', $sections))
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-chalkboard-teacher text-primary me-1"></i> C6. Orientación pre-partida</h6></div>
    <div class="card-body">
        <div class="row g-3 align-items-end mb-2">
            <div class="col-md-4"><label class="form-label small">Fecha programada</label><input type="date" form="visaProcessForm" name="pre_departure_orientation_date" class="form-control form-control-sm" value="{{ $visa->pre_departure_orientation_date?->format('Y-m-d') }}"></div>
            <div class="col-md-4"><div class="form-check"><input class="form-check-input" type="checkbox" form="visaProcessForm" name="pre_departure_orientation_completed" value="1" id="preDepOri" {{ $visa->pre_departure_orientation_completed ? 'checked' : '' }}><label class="form-check-label small" for="preDepOri">Se realizó la orientación</label></div></div>
        </div>
    </div>
</div>
@if($c6Entries->isNotEmpty())
    @include('admin.program-process.tabs.partials._documents', ['group' => ['key' => 'visa_c6', 'label' => 'Orientación pre-partida', 'stage_key' => 'visa', 'unlock_gate_key' => null], 'groupEntries' => $c6Entries])
@endif
@endif

<div class="mb-4"><button type="submit" form="visaProcessForm" class="btn btn-primary"><i class="fas fa-save me-1"></i> Guardar proceso de visa</button></div>

{{-- Finalización --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-flag-checkered text-primary me-1"></i> Finalización del programa</h6></div>
    <div class="card-body">
        @if($process->finalization_result)
        <div class="alert alert-{{ $process->finalization_result === 'success' ? 'success' : ($process->finalization_result === 'not_success' ? 'danger' : 'warning') }} mb-3">
            <strong>{{ ['success' => 'Finalizó con éxito', 'not_success' => 'No finalizó con éxito', 'status_change' => 'Cambio de estatus', 'other' => 'Otro', 'cancelled' => 'Cancelado'][$process->finalization_result] ?? $process->finalization_result }}</strong>
            @if($process->finalization_date)<small class="ms-2">{{ $process->finalization_date->format('d/m/Y') }}</small>@endif
            @if($process->finalization_reason)<p class="mb-0 mt-2 small">{{ $process->finalization_reason }}</p>@endif
        </div>
        @endif
        <form method="POST" action="{{ route('admin.program.finalization.update', [$program->slug, $process->id]) }}">@csrf @method('PUT')<input type="hidden" name="redirect_tab" value="visa">
            <div class="row g-3">
                <div class="col-md-4"><label class="form-label small">Resultado *</label><select name="finalization_result" class="form-select form-select-sm" required><option value="">-- Seleccionar --</option>@foreach(['success' => 'Finalizó con éxito', 'not_success' => 'No finalizó con éxito', 'status_change' => 'Cambio de estatus', 'other' => 'Otro'] as $v => $l)<option value="{{ $v }}" {{ $process->finalization_result === $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label small">Fecha</label><input type="date" name="finalization_date" class="form-control form-control-sm" value="{{ $process->finalization_date?->format('Y-m-d') }}"></div>
                <div class="col-12"><label class="form-label small">Motivo / observaciones</label><textarea name="finalization_reason" class="form-control form-control-sm" rows="3">{{ $process->finalization_reason }}</textarea></div>
            </div>
            <button type="submit" class="btn btn-sm btn-primary mt-3" onclick="return confirm('¿Registrar la finalización? El proceso pasará a Completado.')"><i class="fas fa-flag-checkered me-1"></i> Registrar finalización</button>
        </form>
    </div>
</div>
