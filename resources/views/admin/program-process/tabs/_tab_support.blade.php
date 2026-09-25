@php
    $logs = $tabData['logs'];
    $types = $tabData['types'];
    $labels = ['arrival_followup' => 'Seguimiento de llegada', 'monthly_followup' => 'Seguimiento mensual', 'program_followup' => 'Seguimiento durante el programa', 'incident' => 'Incidente', 'employer_change' => 'Cambio de empleador', 'experience_evaluation' => 'Evaluación de experiencia', 'final_evaluation' => 'Evaluación final', 'participant_report' => 'Reportes del participante'];
    $icons = ['arrival_followup' => 'fa-plane-arrival text-info', 'monthly_followup' => 'fa-calendar-check text-primary', 'program_followup' => 'fa-calendar-check text-primary', 'incident' => 'fa-exclamation-triangle text-danger', 'employer_change' => 'fa-exchange-alt text-warning', 'experience_evaluation' => 'fa-star text-warning', 'final_evaluation' => 'fa-star text-success', 'participant_report' => 'fa-comment-dots text-warning'];
    $staffTypes = array_values(array_diff($types, ['participant_report']));
    $sevColor = ['low' => 'secondary', 'medium' => 'info', 'high' => 'warning text-dark', 'critical' => 'danger'];
@endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-headset text-primary me-2"></i> Support</h5>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSupportLogModal"><i class="fas fa-plus me-1"></i> Nuevo registro</button>
    </div>
    <div class="card-body"><div class="row g-3 text-center">
        @foreach($types as $type)<div class="col"><div class="border rounded p-2"><div class="h5 mb-0 fw-bold">{{ $logs->where('log_type', $type)->count() }}</div><small class="text-muted">{{ $labels[$type] ?? Str::headline($type) }}</small></div></div>@endforeach
    </div></div>
</div>

@foreach($types as $type)
@php $typeLogs = $logs->where('log_type', $type); @endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas {{ $icons[$type] ?? 'fa-clipboard text-secondary' }} me-1"></i> {{ $labels[$type] ?? Str::headline($type) }}</h6></div>
    <div class="card-body">
        @forelse($typeLogs as $log)
        <div class="border rounded p-3 mb-2 {{ $log->severity === 'critical' ? 'border-danger' : ($log->severity === 'high' ? 'border-warning' : '') }}">
            <div class="d-flex justify-content-between align-items-start">
                <div><strong>{{ $log->title }}</strong>@if($log->follow_up_number)<span class="badge bg-primary ms-1">#{{ $log->follow_up_number }}</span>@endif @if($log->severity)<span class="badge bg-{{ $sevColor[$log->severity] ?? 'secondary' }} ms-1">{{ ucfirst($log->severity) }}</span>@endif<small class="text-muted ms-2">{{ $log->log_date->format('d/m/Y') }}</small>@if($log->loggedBy)<small class="text-muted"> — {{ $log->loggedBy->name }}</small>@endif @if($log->source === 'participant')<span class="badge bg-warning text-dark ms-1"><i class="fas fa-mobile-alt me-1"></i>Reportado por el participante</span>@endif</div>
                <form method="POST" action="{{ route('admin.program.support.delete', [$program->slug, $process->id, $log->id]) }}" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-trash"></i></button></form>
            </div>
            @if($log->description)<p class="mb-0 mt-1 small">{{ $log->description }}</p>@endif
            @if($log->resolution)<p class="mb-0 mt-1 small text-success"><i class="fas fa-check-circle me-1"></i><strong>Resolución:</strong> {{ $log->resolution }}</p>@endif
        </div>
        @empty
        <p class="text-muted small mb-0"><i class="fas fa-info-circle"></i> Sin registros.</p>
        @endforelse
    </div>
</div>
@endforeach

<div class="modal fade" id="addSupportLogModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="{{ route('admin.program.support.store', [$program->slug, $process->id]) }}">@csrf
        <div class="modal-header"><h6 class="modal-title"><i class="fas fa-plus me-1"></i> Nuevo registro de seguimiento</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label small">Tipo *</label><select name="log_type" class="form-select form-select-sm" required id="supportLogType">@foreach($staffTypes as $type)<option value="{{ $type }}">{{ $labels[$type] ?? Str::headline($type) }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label small">Fecha *</label><input type="date" name="log_date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}"></div>
            <div class="col-12"><label class="form-label small">Título *</label><input type="text" name="title" class="form-control form-control-sm" required></div>
            <div class="col-md-6"><label class="form-label small"># Seguimiento</label><input type="number" name="follow_up_number" class="form-control form-control-sm" min="1"></div>
            <div class="col-md-6"><label class="form-label small">Severidad</label><select name="severity" class="form-select form-select-sm"><option value="">--</option><option value="low">Baja</option><option value="medium">Media</option><option value="high">Alta</option><option value="critical">Crítica</option></select></div>
            <div class="col-12"><label class="form-label small">Descripción</label><textarea name="description" class="form-control form-control-sm" rows="3"></textarea></div>
            <div class="col-12"><label class="form-label small">Resolución (si aplica)</label><textarea name="resolution" class="form-control form-control-sm" rows="2"></textarea></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Guardar</button></div>
    </form>
</div></div></div>

@if(!empty($tabData['stage']))
    @include('admin.program-process.tabs.partials._stage_gate', ['prominent' => true])
    @if($definition->nextStage($tabData['stage']->key)?->is_terminal)
        @include('admin.program-process.tabs.partials._finalization')
    @endif
@endif
