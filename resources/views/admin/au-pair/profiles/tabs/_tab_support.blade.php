{{-- Tab: Support (D) --}}
@php
    $logs = $tabData['supportLogs'] ?? collect();
    $arrivalLogs = $logs->where('log_type', 'arrival_followup');
    $monthlyLogs = $logs->where('log_type', 'monthly_followup');
    $incidents = $logs->where('log_type', 'incident');
    $evaluations = $logs->where('log_type', 'experience_evaluation');
@endphp

@if(!in_array($stages['_meta']['current_stage'] ?? '', ['support', 'completed']))
<div class="alert alert-info alert-sm py-2 px-3 mb-3">
    <i class="fas fa-info-circle me-1"></i>
    <small><strong>Pendiente:</strong> El participante aún no se encuentra en etapa de Support (post-arribo).</small>
</div>
@endif

{{-- Summary --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-headset text-primary me-2"></i> D. Support</h5>
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addSupportLogModal">
            <i class="fas fa-plus me-1"></i> Nuevo Registro
        </button>
    </div>
    <div class="card-body">
        <div class="row g-3 text-center mb-0">
            <div class="col"><div class="border rounded p-2"><div class="h5 mb-0 fw-bold text-info">{{ $arrivalLogs->count() }}</div><small class="text-muted">Llegada</small></div></div>
            <div class="col"><div class="border rounded p-2"><div class="h5 mb-0 fw-bold text-primary">{{ $monthlyLogs->count() }}</div><small class="text-muted">Mensuales</small></div></div>
            <div class="col"><div class="border rounded p-2"><div class="h5 mb-0 fw-bold text-danger">{{ $incidents->count() }}</div><small class="text-muted">Incidentes</small></div></div>
            <div class="col"><div class="border rounded p-2"><div class="h5 mb-0 fw-bold text-success">{{ $evaluations->count() }}</div><small class="text-muted">Evaluaciones</small></div></div>
        </div>
    </div>
</div>

{{-- Seguimiento de Llegada --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-plane-arrival text-info me-1"></i> Seguimiento de Llegada</h6></div>
    <div class="card-body">
        @if($arrivalLogs->count() > 0)
        @foreach($arrivalLogs as $log)
        <div class="border rounded p-3 mb-2">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>{{ $log->title }}</strong>
                    <small class="text-muted ms-2">{{ $log->log_date->format('d/m/Y') }}</small>
                    @if($log->logger) <small class="text-muted">— {{ $log->logger->name }}</small> @endif
                </div>
                <form method="POST" action="{{ route('admin.aupair.profiles.delete-support-log', [$user->id, $log->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-trash"></i></button></form>
            </div>
            @if($log->description)<p class="mb-0 mt-1 small text-muted">{{ $log->description }}</p>@endif
        </div>
        @endforeach
        @else
        <p class="text-muted small mb-0"><i class="fas fa-info-circle"></i> Sin registros de llegada.</p>
        @endif
    </div>
</div>

{{-- Seguimientos Mensuales --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-calendar-check text-primary me-1"></i> Seguimientos Mensuales</h6></div>
    <div class="card-body">
        @if($monthlyLogs->count() > 0)
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                    <tr><th>#</th><th>Título</th><th>Fecha</th><th>Registrado por</th><th></th></tr>
                </thead>
                <tbody>
                @foreach($monthlyLogs->sortBy('follow_up_number') as $log)
                <tr>
                    <td><span class="badge bg-primary">{{ $log->follow_up_number ?? '-' }}</span></td>
                    <td>{{ $log->title }} @if($log->description)<br><small class="text-muted">{{ Str::limit($log->description, 80) }}</small>@endif</td>
                    <td><small>{{ $log->log_date->format('d/m/Y') }}</small></td>
                    <td><small>{{ $log->logger->name ?? '-' }}</small></td>
                    <td><form method="POST" action="{{ route('admin.aupair.profiles.delete-support-log', [$user->id, $log->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-trash"></i></button></form></td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted small mb-0"><i class="fas fa-info-circle"></i> No hay seguimientos mensuales registrados.</p>
        @endif
    </div>
</div>

{{-- Incidentes --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-exclamation-triangle text-danger me-1"></i> Incidentes</h6></div>
    <div class="card-body">
        @if($incidents->count() > 0)
        @foreach($incidents as $log)
        <div class="border rounded p-3 mb-2 {{ $log->severity === 'critical' ? 'border-danger' : ($log->severity === 'high' ? 'border-warning' : '') }}">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <strong>{{ $log->title }}</strong>
                    @if($log->severity) <span class="badge bg-{{ $log->severity_color }} ms-1">{{ $log->severity_label }}</span> @endif
                    <small class="text-muted ms-2">{{ $log->log_date->format('d/m/Y') }}</small>
                </div>
                <form method="POST" action="{{ route('admin.aupair.profiles.delete-support-log', [$user->id, $log->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-trash"></i></button></form>
            </div>
            @if($log->description)<p class="mb-0 mt-1 small">{{ $log->description }}</p>@endif
            @if($log->resolution)<p class="mb-0 mt-1 small text-success"><i class="fas fa-check-circle me-1"></i><strong>Resolución:</strong> {{ $log->resolution }}</p>@endif
        </div>
        @endforeach
        @else
        <p class="text-muted small mb-0"><i class="fas fa-info-circle"></i> No hay incidentes registrados.</p>
        @endif
    </div>
</div>

{{-- Evaluaciones de Experiencia --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-star text-warning me-1"></i> Evaluación de Experiencia</h6></div>
    <div class="card-body">
        @if($evaluations->count() > 0)
        @foreach($evaluations as $log)
        <div class="border rounded p-3 mb-2">
            <div class="d-flex justify-content-between align-items-start">
                <div><strong>{{ $log->title }}</strong> <small class="text-muted ms-2">{{ $log->log_date->format('d/m/Y') }}</small></div>
                <form method="POST" action="{{ route('admin.aupair.profiles.delete-support-log', [$user->id, $log->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-trash"></i></button></form>
            </div>
            @if($log->description)<p class="mb-0 mt-1 small">{{ $log->description }}</p>@endif
        </div>
        @endforeach
        @else
        <p class="text-muted small mb-0"><i class="fas fa-info-circle"></i> Sin evaluaciones registradas.</p>
        @endif
    </div>
</div>

@endif

{{-- Add Support Log Modal --}}
<div class="modal fade" id="addSupportLogModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.profiles.store-support-log', $user->id) }}">
                @csrf
                <div class="modal-header"><h6 class="modal-title"><i class="fas fa-plus me-1"></i> Nuevo Registro de Seguimiento</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Tipo <span class="text-danger">*</span></label>
                            <select name="log_type" class="form-select form-select-sm" required id="supportLogType">
                                <option value="arrival_followup">Seguimiento de Llegada</option>
                                <option value="monthly_followup">Seguimiento Mensual</option>
                                <option value="incident">Incidente</option>
                                <option value="experience_evaluation">Evaluación de Experiencia</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Fecha <span class="text-danger">*</span></label>
                            <input type="date" name="log_date" class="form-control form-control-sm" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Título <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control form-control-sm" required placeholder="Título del registro">
                        </div>
                        <div class="col-md-6" id="followUpNumberField">
                            <label class="form-label small"># Seguimiento</label>
                            <input type="number" name="follow_up_number" class="form-control form-control-sm" min="1" placeholder="Ej: 1, 2, 3...">
                        </div>
                        <div class="col-md-6" id="severityField" style="display:none;">
                            <label class="form-label small">Severidad</label>
                            <select name="severity" class="form-select form-select-sm">
                                <option value="">-- Seleccionar --</option>
                                <option value="low">Baja</option>
                                <option value="medium">Media</option>
                                <option value="high">Alta</option>
                                <option value="critical">Crítica</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Descripción</label>
                            <textarea name="description" class="form-control form-control-sm" rows="3" placeholder="Detalles..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('supportLogType')?.addEventListener('change', function() {
    document.getElementById('followUpNumberField').style.display = this.value === 'monthly_followup' ? '' : 'none';
    document.getElementById('severityField').style.display = this.value === 'incident' ? '' : 'none';
});
</script>
