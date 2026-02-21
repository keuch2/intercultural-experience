{{-- Tab: Match / Visa J1 (C) --}}
@php
    $visa = $tabData['visaProcess'] ?? null;
    $matchesExt = $tabData['matchesExtended'] ?? collect();
    $visaDocs = $tabData['visaDocs'] ?? collect();
    $visaDocDefs = \App\Models\AuPairDocument::documentTypesForStage('visa');
    $activeMatch = $matchesExt->where('is_active', true)->first();
@endphp

@if(!in_array($stages['_meta']['current_stage'] ?? '', ['match_visa', 'support', 'completed']))
<div class="alert alert-info alert-sm py-2 px-3 mb-3">
    <i class="fas fa-info-circle me-1"></i>
    <small><strong>Pendiente:</strong> El participante aún no ha completado la etapa de Aplicación.</small>
</div>
@endif

{{-- Visa Process Form --}}
<form method="POST" action="{{ route('admin.aupair.profiles.update-visa', $user->id) }}">
@csrf @method('PUT')

{{-- C1: Aplicación Visa --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-envelope text-primary me-1"></i> C1. Aplicación de Visa</h6></div>
    <div class="card-body">
        <div class="list-group mb-3">
            <label class="list-group-item d-flex align-items-center">
                <input class="form-check-input me-3" type="checkbox" name="visa_email_sent" value="1" {{ $visa && $visa->visa_email_sent ? 'checked' : '' }}>
                <span>Correo de visa enviado al participante</span>
            </label>
            <label class="list-group-item d-flex align-items-center">
                <input class="form-check-input me-3" type="checkbox" name="consular_fee_paid" value="1" {{ $visa && $visa->consular_fee_paid ? 'checked' : '' }}>
                <span>Pago de tarifa consular</span>
            </label>
            <label class="list-group-item d-flex align-items-center">
                <input class="form-check-input me-3" type="checkbox" name="appointment_scheduled" value="1" {{ $visa && $visa->appointment_scheduled ? 'checked' : '' }}>
                <span>Agendamiento de cita</span>
            </label>
            <label class="list-group-item d-flex align-items-center">
                <input class="form-check-input me-3" type="checkbox" name="documents_sent_for_appointment" value="1" {{ $visa && $visa->documents_sent_for_appointment ? 'checked' : '' }}>
                <span>Envío de documentos para cita de visa</span>
            </label>
        </div>
    </div>
</div>

{{-- C1.1: Documentos del Participante para Visa --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-file-alt text-primary me-1"></i> C1.1. Documentos del Participante (Visa)</h6></div>
    <div class="card-body">
        <p class="text-muted small mb-3">Documentos que el participante debe cargar para el proceso de visa. También pueden ser cargados por el staff de IE.</p>
        @php $participantVisaDocs = collect($visaDocDefs)->filter(fn($d) => !isset($d['uploaded_by']) || $d['uploaded_by'] !== 'staff'); @endphp
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                    <tr><th>Documento</th><th class="text-center" style="width:80px;">Req.</th><th class="text-center" style="width:110px;">Estado</th><th style="width:250px;">Acciones</th></tr>
                </thead>
                <tbody>
                @foreach($participantVisaDocs as $docKey => $docDef)
                @php $doc = $visaDocs->where('document_type', $docKey)->first(); @endphp
                <tr>
                    <td>
                        <i class="fas fa-file text-muted me-1"></i> {{ $docDef['label'] }}
                        @if($doc && $doc->original_filename)<br><small class="text-muted"><i class="fas fa-paperclip"></i> {{ $doc->original_filename }} ({{ $doc->file_size_formatted }})</small>@endif
                        @if($doc && $doc->rejection_reason)<br><small class="text-danger"><i class="fas fa-comment-alt"></i> {{ $doc->rejection_reason }}</small>@endif
                    </td>
                    <td class="text-center"><span class="badge bg-{{ $docDef['required'] ? 'danger' : 'secondary' }}">{{ $docDef['required'] ? 'Sí' : 'No' }}</span></td>
                    <td class="text-center">
                        @if($doc)<span class="badge bg-{{ $doc->status_color }}">{{ $doc->status_label }}</span>@else<span class="badge bg-light text-dark">Sin subir</span>@endif
                    </td>
                    <td>
                        @if($doc)
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('admin.aupair.profiles.download-doc', [$user->id, $doc->id]) }}" class="btn btn-sm btn-outline-primary py-0"><i class="fas fa-download"></i></a>
                            @if($doc->status !== 'approved')
                                <form method="POST" action="{{ route('admin.aupair.profiles.review-doc', [$user->id, $doc->id]) }}" class="d-inline">@csrf @method('PUT')<input type="hidden" name="action" value="approve"><button type="submit" class="btn btn-sm btn-outline-success py-0"><i class="fas fa-check"></i></button></form>
                                <button class="btn btn-sm btn-outline-danger py-0" data-bs-toggle="modal" data-bs-target="#rejectVisaParticipantDoc{{ $doc->id }}"><i class="fas fa-times"></i></button>
                            @endif
                            <form method="POST" action="{{ route('admin.aupair.profiles.delete-doc', [$user->id, $doc->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-trash"></i></button></form>
                        </div>
                        @else
                        <button class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal" data-bs-target="#uploadVisaParticipant{{ $docKey }}"><i class="fas fa-upload me-1"></i>Subir</button>
                        @endif
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- C2: Cita de Visa --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-calendar-alt text-primary me-1"></i> C2. Cita de Visa</h6></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small">Fecha</label>
                <input type="date" name="appointment_date" class="form-control form-control-sm" value="{{ $visa && $visa->appointment_date ? $visa->appointment_date->format('Y-m-d') : '' }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Hora</label>
                <input type="time" name="appointment_time" class="form-control form-control-sm" value="{{ $visa->appointment_time ?? '' }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Embajada</label>
                <input type="text" name="embassy" class="form-control form-control-sm" value="{{ $visa->embassy ?? '' }}" placeholder="Ej: Embajada USA Asunción">
            </div>
        </div>
    </div>
</div>

{{-- C3: Documentos IE --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-file-upload text-primary me-1"></i> C3. Documentos IE (descargables)</h6></div>
    <div class="card-body">
        <div class="list-group mb-3">
            <label class="list-group-item d-flex align-items-center bg-warning bg-opacity-10">
                <input class="form-check-input me-3" type="checkbox" name="document_check_completed" value="1" {{ $visa && $visa->document_check_completed ? 'checked' : '' }}>
                <span class="small fw-bold">Chequeo de documentos realizado con el participante</span>
                @if($visa && $visa->document_check_completed_at)
                    <small class="ms-auto text-success">{{ $visa->document_check_completed_at->format('d/m/Y H:i') }}</small>
                @endif
            </label>
        </div>
        @php $staffDocDefs = collect($visaDocDefs)->filter(fn($d) => isset($d['uploaded_by']) && $d['uploaded_by'] === 'staff'); @endphp
        @foreach($staffDocDefs as $docKey => $docDef)
        @php $doc = $visaDocs->where('document_type', $docKey)->first(); @endphp
        <div class="border rounded p-2 mb-2 d-flex align-items-center justify-content-between">
            <span class="small"><i class="fas fa-file-pdf text-danger me-1"></i> {{ $docDef['label'] }}</span>
            <div class="d-flex gap-1 align-items-center">
                @if($doc)
                    <a href="{{ route('admin.aupair.profiles.download-doc', [$user->id, $doc->id]) }}" class="btn btn-sm btn-outline-primary py-0"><i class="fas fa-download"></i></a>
                    <span class="badge bg-{{ $doc->status_color }}">{{ $doc->status_label }}</span>
                @else
                    <span class="badge bg-light text-dark me-1">Sin cargar</span>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal" data-bs-target="#uploadVisaDoc{{ $docKey }}"><i class="fas fa-upload"></i></button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- C4: Resultado Entrevista --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-gavel text-primary me-1"></i> C4. Resultado Entrevista</h6></div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small">Resultado</label>
                <select name="interview_result" class="form-select form-select-sm">
                    @foreach(['pending' => 'Pendiente', 'approved' => 'Aprobada', 'denied' => 'Denegada', 'administrative_process' => 'Proceso Administrativo'] as $val => $lbl)
                        <option value="{{ $val }}" {{ ($visa->interview_result ?? 'pending') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label small">Notas</label>
                <input type="text" name="interview_result_notes" class="form-control form-control-sm" value="{{ $visa->interview_result_notes ?? '' }}" placeholder="Observaciones...">
            </div>
        </div>
    </div>
</div>

{{-- C5: Información de Viaje --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-plane-departure text-primary me-1"></i> C5. Información de Viaje</h6></div>
    <div class="card-body">
        @php $flightInfo = $visa->flight_info ?? []; @endphp
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small">Fecha/hora salida</label>
                <input type="datetime-local" name="departure_datetime" class="form-control form-control-sm" value="{{ $visa && $visa->departure_datetime ? $visa->departure_datetime->format('Y-m-d\TH:i') : '' }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Fecha/hora llegada USA</label>
                <input type="datetime-local" name="arrival_usa_datetime" class="form-control form-control-sm" value="{{ $visa && $visa->arrival_usa_datetime ? $visa->arrival_usa_datetime->format('Y-m-d\TH:i') : '' }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Aerolínea</label>
                <input type="text" name="flight_airline" class="form-control form-control-sm" value="{{ $flightInfo['airline'] ?? '' }}" placeholder="Ej: American Airlines">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Número de Vuelo</label>
                <input type="text" name="flight_number" class="form-control form-control-sm" value="{{ $flightInfo['flight_number'] ?? '' }}" placeholder="Ej: AA 1234">
            </div>
            <div class="col-md-8">
                <label class="form-label small">Escalas y Horarios</label>
                <input type="text" name="flight_layovers" class="form-control form-control-sm" value="{{ $flightInfo['layovers'] ?? '' }}" placeholder="Ej: Miami (2h escala) → Dallas (1h30 escala)">
            </div>
        </div>
    </div>
</div>

{{-- C6: Orientación Pre-partida --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-chalkboard-teacher text-primary me-1"></i> C6. Orientación Pre-partida</h6></div>
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Fecha programada</label>
                <input type="date" name="pre_departure_orientation_date" class="form-control form-control-sm" value="{{ $visa && $visa->pre_departure_orientation_date ? $visa->pre_departure_orientation_date->format('Y-m-d') : '' }}">
            </div>
            <div class="col-md-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="pre_departure_orientation_completed" value="1" id="preDepOri" {{ $visa && $visa->pre_departure_orientation_completed ? 'checked' : '' }}>
                    <label class="form-check-label small" for="preDepOri">Se realizó la orientación</label>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Save Visa Form Button --}}
<div class="mb-4">
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save me-1"></i> Guardar Proceso de Visa
    </button>
</div>
</form>

{{-- C7: Matches --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-heart text-danger me-1"></i> C7. Matches</h6>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addMatchModal">
            <i class="fas fa-plus me-1"></i> Agregar Match
        </button>
    </div>
    <div class="card-body">
        @if($matchesExt->count() > 0)
        @foreach($matchesExt as $m)
        <div class="border rounded p-3 mb-3 {{ $m->is_active ? 'border-success' : '' }}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <span class="badge bg-{{ $m->match_type_color }}">{{ $m->match_type_label }}</span>
                    @if($m->is_active) <span class="badge bg-success">Activo</span> @else <span class="badge bg-secondary">Finalizado</span> @endif
                    @if($m->match_date) <small class="text-muted ms-2">{{ $m->match_date->format('d/m/Y') }}</small> @endif
                </div>
                <form method="POST" action="{{ route('admin.aupair.profiles.delete-match', [$user->id, $m->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este match?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-trash"></i></button>
                </form>
            </div>
            <div class="row g-2">
                <div class="col-md-3"><small class="text-muted">Estado:</small> <strong>{{ $m->host_state ?? '-' }}</strong></div>
                <div class="col-md-3"><small class="text-muted">Ciudad:</small> <strong>{{ $m->host_city ?? '-' }}</strong></div>
                <div class="col-md-3"><small class="text-muted">Mom:</small> {{ $m->host_mom_name ?? '-' }}</div>
                <div class="col-md-3"><small class="text-muted">Dad:</small> {{ $m->host_dad_name ?? '-' }}</div>
                <div class="col-md-4"><small class="text-muted">Email:</small> {{ $m->host_email ?? '-' }}</div>
                <div class="col-md-4"><small class="text-muted">Tel:</small> {{ $m->host_phone ?? '-' }}</div>
                @if($m->host_address)<div class="col-md-4"><small class="text-muted">Dir:</small> {{ $m->host_address }}</div>@endif
                @if(!$m->is_active && $m->end_reason)<div class="col-12"><small class="text-danger"><i class="fas fa-comment"></i> {{ $m->end_reason }}</small></div>@endif
            </div>
        </div>
        @endforeach
        @else
        <p class="text-muted small mb-0"><i class="fas fa-info-circle"></i> Aún no se ha registrado un match.</p>
        @endif
    </div>
</div>

{{-- C8: Finalización --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-flag-checkered text-primary me-1"></i> C8. Finalización del Programa</h6></div>
    <div class="card-body">
        @if($process && $process->finalization_result)
        <div class="alert alert-{{ $process->finalization_result === 'success' ? 'success' : ($process->finalization_result === 'not_success' ? 'danger' : 'warning') }} mb-3">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>
                        @switch($process->finalization_result)
                            @case('success') <i class="fas fa-check-circle me-1"></i> Finalizó con éxito @break
                            @case('not_success') <i class="fas fa-times-circle me-1"></i> No finalizó con éxito @break
                            @case('status_change') <i class="fas fa-exchange-alt me-1"></i> Cambio de estatus @break
                            @case('other') <i class="fas fa-info-circle me-1"></i> Otro @break
                        @endswitch
                    </strong>
                    @if($process->finalization_date)
                        <small class="ms-2">{{ $process->finalization_date->format('d/m/Y') }}</small>
                    @endif
                </div>
            </div>
            @if($process->finalization_reason)
                <p class="mb-0 mt-2 small">{{ $process->finalization_reason }}</p>
            @endif
        </div>
        @endif

        <form method="POST" action="{{ route('admin.aupair.profiles.update-finalization', $user->id) }}">
            @csrf @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">Resultado <span class="text-danger">*</span></label>
                    <select name="finalization_result" class="form-select form-select-sm" required>
                        <option value="">-- Seleccionar --</option>
                        <option value="success" {{ ($process->finalization_result ?? '') === 'success' ? 'selected' : '' }}>Finalizó con éxito</option>
                        <option value="not_success" {{ ($process->finalization_result ?? '') === 'not_success' ? 'selected' : '' }}>No finalizó con éxito</option>
                        <option value="status_change" {{ ($process->finalization_result ?? '') === 'status_change' ? 'selected' : '' }}>Cambio de estatus</option>
                        <option value="other" {{ ($process->finalization_result ?? '') === 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Fecha de Finalización</label>
                    <input type="date" name="finalization_date" class="form-control form-control-sm" value="{{ $process && $process->finalization_date ? $process->finalization_date->format('Y-m-d') : '' }}">
                </div>
                <div class="col-12">
                    <label class="form-label small">Motivo / Observaciones</label>
                    <textarea name="finalization_reason" class="form-control form-control-sm" rows="3" placeholder="Explique el motivo en caso de no finalización, cambio de estatus u otro...">{{ $process->finalization_reason ?? '' }}</textarea>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('¿Está seguro de registrar la finalización? Esto marcará el proceso como completado.')">
                    <i class="fas fa-flag-checkered me-1"></i> Registrar Finalización
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Add Match Modal --}}
<div class="modal fade" id="addMatchModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.profiles.store-match', $user->id) }}">
                @csrf
                <div class="modal-header"><h6 class="modal-title">Agregar Match</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small">Tipo <span class="text-danger">*</span></label>
                            <select name="match_type" class="form-select form-select-sm" required>
                                <option value="initial">Match Inicial</option>
                                <option value="rematch">Rematch</option>
                                <option value="extension">Extensión</option>
                            </select>
                        </div>
                        <div class="col-md-4"><label class="form-label small">Fecha</label><input type="date" name="match_date" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><label class="form-label small">Estado</label><input type="text" name="host_state" class="form-control form-control-sm"></div>
                        <div class="col-md-4"><label class="form-label small">Ciudad</label><input type="text" name="host_city" class="form-control form-control-sm"></div>
                        <div class="col-md-8"><label class="form-label small">Dirección</label><input type="text" name="host_address" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Nombre Mom</label><input type="text" name="host_mom_name" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Nombre Dad</label><input type="text" name="host_dad_name" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Email</label><input type="email" name="host_email" class="form-control form-control-sm"></div>
                        <div class="col-md-6"><label class="form-label small">Teléfono</label><input type="text" name="host_phone" class="form-control form-control-sm"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Guardar Match</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Visa Document Upload Modals --}}
@php $staffDocDefs = collect($visaDocDefs)->filter(fn($d) => isset($d['uploaded_by']) && $d['uploaded_by'] === 'staff'); @endphp
@foreach($staffDocDefs as $docKey => $docDef)
@if(!$visaDocs->where('document_type', $docKey)->first())
<div class="modal fade" id="uploadVisaDoc{{ $docKey }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.profiles.upload-doc', $user->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="document_type" value="{{ $docKey }}">
                <input type="hidden" name="stage" value="visa">
                <div class="modal-header"><h6 class="modal-title">Subir: {{ $docDef['label'] }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label small">Archivo <span class="text-danger">*</span></label><input type="file" name="file" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.mp4,.mov"><small class="text-muted">Máx. 1GB</small></div>
                    <div class="mb-0"><label class="form-label small">Notas</label><input type="text" name="notes" class="form-control form-control-sm"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i>Subir</button></div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

{{-- Participant Visa Document Upload Modals --}}
@php $participantVisaDocDefs = collect($visaDocDefs)->filter(fn($d) => !isset($d['uploaded_by']) || $d['uploaded_by'] !== 'staff'); @endphp
@foreach($participantVisaDocDefs as $docKey => $docDef)
@if(!$visaDocs->where('document_type', $docKey)->first())
<div class="modal fade" id="uploadVisaParticipant{{ $docKey }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.profiles.upload-doc', $user->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="document_type" value="{{ $docKey }}">
                <input type="hidden" name="stage" value="visa">
                <div class="modal-header"><h6 class="modal-title">Subir: {{ $docDef['label'] }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label small">Archivo <span class="text-danger">*</span></label><input type="file" name="file" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.mp4,.mov"><small class="text-muted">Máx. 1GB</small></div>
                    <div class="mb-0"><label class="form-label small">Notas</label><input type="text" name="notes" class="form-control form-control-sm"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i>Subir</button></div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

{{-- Participant Visa Document Reject Modals --}}
@foreach($visaDocs->where('status', '!=', 'approved') as $doc)
<div class="modal fade" id="rejectVisaParticipantDoc{{ $doc->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.profiles.review-doc', [$user->id, $doc->id]) }}">
                @csrf @method('PUT')
                <input type="hidden" name="action" value="reject">
                <div class="modal-header"><h6 class="modal-title">Rechazar Documento</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><div class="mb-0"><label class="form-label small">Motivo del rechazo <span class="text-danger">*</span></label><textarea name="rejection_reason" class="form-control form-control-sm" rows="3" required></textarea></div></div>
                <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times me-1"></i>Rechazar</button></div>
            </form>
        </div>
    </div>
</div>
@endforeach
