{{-- Tab: Aplicación (B) --}}
@php
    $proc = $tabData['process'] ?? $process ?? null;
    $p1Docs = $tabData['documents_p1'] ?? collect();
    $p2Docs = $tabData['documents_p2'] ?? collect();
    $p1DocDefs = \App\Models\AuPairDocument::documentTypesForStage('application_payment1');
    $p2DocDefs = \App\Models\AuPairDocument::documentTypesForStage('application_payment2');
@endphp

@if(!($stages['_meta']['admission_docs_approved'] ?? false))
<div class="alert alert-info alert-sm py-2 px-3 mb-3">
    <i class="fas fa-info-circle me-1"></i>
    <small><strong>Pendiente:</strong> El participante aún no ha completado los documentos obligatorios de Admisión.</small>
</div>
@endif

{{-- B1: Test de Inglés --}}
@php
    $engTests = $tabData['englishTests'] ?? collect();
    $engRemaining = $tabData['remainingAttempts'] ?? 3;
    $bestTest = $engTests->sortByDesc('final_score')->first();
@endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-language text-info me-2"></i> B1. Test de Inglés
        </h5>
        @if($engRemaining > 0)
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addEnglishTestModal">
            <i class="fas fa-plus me-1"></i> Registrar Evaluación
        </button>
        @endif
    </div>
    <div class="card-body">
        <div class="alert {{ $bestTest && $bestTest->meetsMinimumLevel() ? 'alert-success' : 'alert-info' }} py-2 px-3 mb-3">
            <small>
                <i class="fas fa-info-circle me-1"></i>
                <strong>Nivel mínimo requerido: B1</strong> &middot;
                Se incluyen 3 evaluaciones sin costo adicional &middot;
                Intentos restantes: <strong>{{ $engRemaining }}</strong>
                @if($bestTest)
                    &middot; Mejor nivel: <span class="badge bg-{{ $bestTest->cefr_color }}">{{ $bestTest->cefr_level }}</span> ({{ $bestTest->final_score }} pts)
                @endif
            </small>
        </div>

        @if($engTests->count() > 0)
        <div class="table-responsive mb-3">
            <table class="table table-sm table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Evaluador</th>
                        <th>Examen</th>
                        <th class="text-center">Oral</th>
                        <th class="text-center">Listening</th>
                        <th class="text-center">Reading</th>
                        <th class="text-center">Final</th>
                        <th class="text-center">Nivel</th>
                        <th class="text-center">PDF</th>
                        <th class="text-center">Enviado</th>
                        <th>Fecha</th>
                        <th style="width:60px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($engTests as $test)
                    <tr>
                        <td>{{ $test->attempt_number }}</td>
                        <td>{{ $test->evaluator_name ?? '-' }}</td>
                        <td>{{ $test->exam_name ?? '-' }}</td>
                        <td class="text-center">{{ $test->oral_score ?? '-' }}</td>
                        <td class="text-center">{{ $test->listening_score ?? '-' }}</td>
                        <td class="text-center">{{ $test->reading_score ?? '-' }}</td>
                        <td class="text-center fw-bold">{{ $test->final_score }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $test->cefr_color }}">{{ $test->cefr_level }}</span>
                        </td>
                        <td class="text-center">
                            @if($test->test_pdf_path)
                                <a href="{{ route('admin.aupair.profiles.download-english-pdf', [$user->id, $test->id]) }}" class="text-primary" title="Descargar PDF"><i class="fas fa-file-pdf"></i></a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($test->results_sent_to_applicant)
                                <i class="fas fa-check text-success" title="Enviado {{ $test->results_sent_at ? $test->results_sent_at->format('d/m/Y') : '' }}"></i>
                            @else
                                <i class="fas fa-times text-danger"></i>
                            @endif
                        </td>
                        <td><small>{{ $test->created_at->format('d/m/Y') }}</small></td>
                        <td>
                            <form method="POST" action="{{ route('admin.aupair.profiles.delete-english-test', [$user->id, $test->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar esta evaluación?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-secondary py-0" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @if($test->observations)
                    <tr>
                        <td colspan="12" class="py-1 px-3 bg-light"><small class="text-muted"><i class="fas fa-comment me-1"></i>{{ $test->observations }}</small></td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-muted mb-3"><i class="fas fa-info-circle"></i> Aún no se han registrado evaluaciones de inglés.</p>
        @endif
    </div>
</div>

{{-- Add English Test Modal --}}
@if($engRemaining > 0)
<div class="modal fade" id="addEnglishTestModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.profiles.store-english-test', $user->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title"><i class="fas fa-language me-2"></i> Registrar Evaluación de Inglés (Intento {{ $engTests->count() + 1 }} de 3)</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small">Evaluador <span class="text-danger">*</span></label>
                            <input type="text" name="evaluator_name" class="form-control form-control-sm" required placeholder="Nombre del evaluador">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small">Nombre del Examen <span class="text-danger">*</span></label>
                            <input type="text" name="exam_name" class="form-control form-control-sm" required placeholder="Ej: EF SET, iTEP, TOEFL...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Oral</label>
                            <input type="number" name="oral_score" class="form-control form-control-sm" min="0" max="100" placeholder="0-100">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Listening</label>
                            <input type="number" name="listening_score" class="form-control form-control-sm" min="0" max="100" placeholder="0-100">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Reading</label>
                            <input type="number" name="reading_score" class="form-control form-control-sm" min="0" max="100" placeholder="0-100">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">Puntaje Final <span class="text-danger">*</span></label>
                            <input type="number" name="final_score" class="form-control form-control-sm" min="0" max="100" required placeholder="0-100">
                            <small class="text-muted">El nivel CEFR se calcula automáticamente</small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small">PDF del Resultado</label>
                            <input type="file" name="test_pdf" class="form-control form-control-sm" accept=".pdf">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="results_sent_to_applicant" value="1" id="resultsSent">
                                <label class="form-check-label small" for="resultsSent">Resultados enviados al participante</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small">Observaciones</label>
                            <textarea name="observations" class="form-control form-control-sm" rows="2" placeholder="Notas adicionales..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Registrar Evaluación</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

{{-- B2: Documentos habilitados por pagos --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-folder-open text-info me-2"></i> B2. Documentos (habilitados por pagos)
        </h5>
    </div>
    <div class="card-body">

        {{-- Documentos 1er Pago --}}
        <div class="mb-4">
            <div class="d-flex align-items-center mb-2">
                <h6 class="mb-0 me-2">Documentos - 1er Pago (Inscripción)</h6>
                @if($stages['_meta']['payment1_verified'])
                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Pago verificado</span>
                @else
                    <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle"></i> Pago pendiente</span>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th class="text-center" style="width: 80px;">Req.</th>
                            <th class="text-center" style="width: 110px;">Estado</th>
                            <th style="width: 250px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($p1DocDefs as $docKey => $docDef)
                        @php $doc = $p1Docs->where('document_type', $docKey)->first(); @endphp
                        <tr>
                            <td>
                                <i class="fas fa-file text-muted me-1"></i>
                                {{ $docDef['label'] }}
                                @if(isset($docDef['min_count']) && $docDef['min_count'] > 1)
                                    <small class="text-muted">(mín. {{ $docDef['min_count'] }})</small>
                                @endif
                                @if($doc && $doc->rejection_reason)
                                    <br><small class="text-danger"><i class="fas fa-comment-alt"></i> {{ $doc->rejection_reason }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $docDef['required'] ? 'danger' : 'secondary' }}">{{ $docDef['required'] ? 'Sí' : 'No' }}</span>
                            </td>
                            <td class="text-center">
                                @if($doc)
                                    <span class="badge bg-{{ $doc->status_color }}">{{ $doc->status_label }}</span>
                                @else
                                    <span class="badge bg-light text-dark">Sin subir</span>
                                @endif
                            </td>
                            <td>
                                @if($doc)
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a href="{{ route('admin.aupair.profiles.download-doc', [$user->id, $doc->id]) }}" class="btn btn-sm btn-outline-primary py-0" title="Descargar"><i class="fas fa-download"></i></a>
                                        @if($doc->status !== 'approved')
                                            <form method="POST" action="{{ route('admin.aupair.profiles.review-doc', [$user->id, $doc->id]) }}" class="d-inline">@csrf @method('PUT')<input type="hidden" name="action" value="approve"><button type="submit" class="btn btn-sm btn-outline-success py-0"><i class="fas fa-check"></i></button></form>
                                            <button class="btn btn-sm btn-outline-danger py-0" data-bs-toggle="modal" data-bs-target="#rejectP1Modal{{ $doc->id }}"><i class="fas fa-times"></i></button>
                                        @endif
                                        <form method="POST" action="{{ route('admin.aupair.profiles.delete-doc', [$user->id, $doc->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-trash"></i></button></form>
                                    </div>
                                @else
                                    <button class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal" data-bs-target="#uploadP1Modal{{ $docKey }}"><i class="fas fa-upload me-1"></i>Subir</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Aviso contrato --}}
            <div class="alert {{ ($proc && $proc->contract_signed) ? 'alert-success' : 'alert-danger' }} py-2 px-3 mb-0">
                <small>
                    @if($proc && $proc->contract_signed)
                        <i class="fas fa-check-circle me-1"></i>
                        <strong>Contrato firmado</strong> el {{ $proc->contract_signed_at ? $proc->contract_signed_at->format('d/m/Y H:i') : '-' }}
                        @if($proc->contractConfirmedBy) por {{ $proc->contractConfirmedBy->name }} @endif
                    @else
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        <strong>Importante:</strong> Para continuar, es necesario contactar a IE para la firma del contrato.
                        No se puede avanzar hasta que IE confirme que el contrato fue firmado.
                    @endif
                </small>
            </div>
        </div>

        <hr>

        {{-- Documentos 2do Pago --}}
        <div class="mb-4">
            <div class="d-flex align-items-center mb-2">
                <h6 class="mb-0 me-2">Documentos - 2do Pago (Programa)</h6>
                @if($stages['_meta']['payment2_verified'])
                    <span class="badge bg-success"><i class="fas fa-check-circle"></i> Pago verificado</span>
                @else
                    <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-circle"></i> Pago pendiente</span>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Documento</th>
                            <th class="text-center" style="width: 80px;">Req.</th>
                            <th class="text-center" style="width: 110px;">Estado</th>
                            <th style="width: 250px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($p2DocDefs as $docKey => $docDef)
                        @php $doc = $p2Docs->where('document_type', $docKey)->first(); @endphp
                        <tr>
                            <td>
                                <i class="fas fa-file text-muted me-1"></i>
                                {{ $docDef['label'] }}
                                @if(isset($docDef['min_count']) && $docDef['min_count'] > 1)
                                    <small class="text-muted">(mín. {{ $docDef['min_count'] }})</small>
                                @endif
                                @if($doc && $doc->rejection_reason)
                                    <br><small class="text-danger"><i class="fas fa-comment-alt"></i> {{ $doc->rejection_reason }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $docDef['required'] ? 'danger' : 'secondary' }}">{{ $docDef['required'] ? 'Sí' : 'No' }}</span>
                            </td>
                            <td class="text-center">
                                @if($doc)
                                    <span class="badge bg-{{ $doc->status_color }}">{{ $doc->status_label }}</span>
                                @else
                                    <span class="badge bg-light text-dark">Sin subir</span>
                                @endif
                            </td>
                            <td>
                                @if($doc)
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a href="{{ route('admin.aupair.profiles.download-doc', [$user->id, $doc->id]) }}" class="btn btn-sm btn-outline-primary py-0"><i class="fas fa-download"></i></a>
                                        @if($doc->status !== 'approved')
                                            <form method="POST" action="{{ route('admin.aupair.profiles.review-doc', [$user->id, $doc->id]) }}" class="d-inline">@csrf @method('PUT')<input type="hidden" name="action" value="approve"><button type="submit" class="btn btn-sm btn-outline-success py-0"><i class="fas fa-check"></i></button></form>
                                            <button class="btn btn-sm btn-outline-danger py-0" data-bs-toggle="modal" data-bs-target="#rejectP2Modal{{ $doc->id }}"><i class="fas fa-times"></i></button>
                                        @endif
                                        <form method="POST" action="{{ route('admin.aupair.profiles.delete-doc', [$user->id, $doc->id]) }}" class="d-inline" onsubmit="return confirm('¿Eliminar?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-trash"></i></button></form>
                                    </div>
                                @else
                                    <button class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal" data-bs-target="#uploadP2Modal{{ $docKey }}"><i class="fas fa-upload me-1"></i>Subir</button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- B3: Gestión de Documentos (staff) --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-clipboard-check text-info me-2"></i> B3. Resumen de Documentos
        </h5>
    </div>
    <div class="card-body">
        @php
            $allDocs = collect();
            if ($proc) $allDocs = $proc->documents()->orderBy('sort_order')->get();
            $totalDocs = $allDocs->count();
            $approvedDocs = $allDocs->where('status', 'approved')->count();
            $pendingDocs = $allDocs->where('status', 'pending')->count();
            $rejectedDocs = $allDocs->where('status', 'rejected')->count();
        @endphp
        <div class="row g-3 text-center">
            <div class="col">
                <div class="border rounded p-2">
                    <div class="h4 mb-0 fw-bold text-primary">{{ $totalDocs }}</div>
                    <small class="text-muted">Total subidos</small>
                </div>
            </div>
            <div class="col">
                <div class="border rounded p-2">
                    <div class="h4 mb-0 fw-bold text-success">{{ $approvedDocs }}</div>
                    <small class="text-muted">Aprobados</small>
                </div>
            </div>
            <div class="col">
                <div class="border rounded p-2">
                    <div class="h4 mb-0 fw-bold text-warning">{{ $pendingDocs }}</div>
                    <small class="text-muted">Pendientes</small>
                </div>
            </div>
            <div class="col">
                <div class="border rounded p-2">
                    <div class="h4 mb-0 fw-bold text-danger">{{ $rejectedDocs }}</div>
                    <small class="text-muted">Rechazados</small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- B4: Checklist de Proceso --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-tasks text-info me-2"></i> B4. Checklist de Proceso
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.aupair.profiles.update-checklist', $user->id) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="list-group mb-3">
                <label class="list-group-item d-flex align-items-center">
                    <input class="form-check-input me-3" type="checkbox" name="welcome_email_sent" value="1"
                        {{ $proc && $proc->welcome_email_sent ? 'checked' : '' }}>
                    <span>Correo de bienvenida enviado</span>
                </label>
                <label class="list-group-item d-flex align-items-center">
                    <input class="form-check-input me-3" type="checkbox" name="interview_process_email_sent" value="1"
                        {{ $proc && $proc->interview_process_email_sent ? 'checked' : '' }}>
                    <span>Correo de inicio de proceso de entrevistas con familias</span>
                </label>
                <label class="list-group-item d-flex align-items-center">
                    <input class="form-check-input me-3" type="checkbox" name="all_docs_and_payments_complete" value="1"
                        {{ $proc && $proc->all_docs_and_payments_complete ? 'checked' : '' }}>
                    <span>Todos los documentos completos y pagos realizados</span>
                    @if(!$stages['_meta']['payment1_verified'] || !$stages['_meta']['payment2_verified'])
                        <span class="badge bg-danger ms-auto">Pago pendiente</span>
                    @endif
                </label>
                <label class="list-group-item d-flex align-items-center">
                    <input class="form-check-input me-3" type="checkbox" name="itep_completed" value="1"
                        {{ $proc && $proc->itep_completed ? 'checked' : '' }}>
                    <span>ITEP (examen obligatorio - perfil online y verificado)</span>
                </label>
                <label class="list-group-item d-flex align-items-center {{ ($proc && $proc->contract_signed) ? 'list-group-item-success' : 'list-group-item-warning' }}">
                    <input class="form-check-input me-3" type="checkbox" name="contract_signed" value="1"
                        {{ $proc && $proc->contract_signed ? 'checked' : '' }}>
                    <span>Contrato firmado con IE</span>
                    @if($proc && $proc->contract_signed)
                        <small class="ms-auto text-success"><i class="fas fa-check"></i> {{ $proc->contract_signed_at ? $proc->contract_signed_at->format('d/m/Y') : '' }}</small>
                    @endif
                </label>
            </div>

            {{-- Contrato firmado como archivo físico --}}
            <div class="mt-3 p-3 border rounded bg-light">
                <h6 class="small fw-bold text-muted mb-2"><i class="fas fa-file-contract me-1"></i> Archivo del Contrato Firmado</h6>
                @if($proc && $proc->contract_file_path)
                    <div class="d-flex align-items-center">
                        <a href="{{ Storage::url($proc->contract_file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                            <i class="fas fa-file-pdf me-1"></i> {{ $proc->contract_original_filename ?? 'Ver Contrato' }}
                        </a>
                        <small class="text-success"><i class="fas fa-check-circle"></i> Archivo cargado</small>
                    </div>
                @else
                    <small class="text-muted d-block mb-2">No se ha cargado el archivo del contrato firmado.</small>
                @endif
                <div class="mt-2">
                    <input type="file" name="contract_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png">
                    <small class="text-muted">Suba el contrato firmado escaneado (PDF o imagen)</small>
                </div>
            </div>

            <button type="submit" class="btn btn-sm btn-primary mt-3">
                <i class="fas fa-save me-1"></i> Guardar Checklist
            </button>
        </form>
    </div>
</div>

{{-- Upload Modals - Payment 1 --}}
@foreach($p1DocDefs as $docKey => $docDef)
@if(!$p1Docs->where('document_type', $docKey)->first() || ($docDef['min_count'] ?? 1) > 1)
<div class="modal fade" id="uploadP1Modal{{ $docKey }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.profiles.upload-doc', $user->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="document_type" value="{{ $docKey }}">
                <input type="hidden" name="stage" value="application_payment1">
                <div class="modal-header"><h6 class="modal-title">Subir: {{ $docDef['label'] }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    @if(($docDef['min_count'] ?? 1) > 1)
                        <div class="alert alert-info py-2 px-3 mb-3"><small><i class="fas fa-info-circle me-1"></i> Se requieren mínimo <strong>{{ $docDef['min_count'] }}</strong> archivos. Puede seleccionar varios a la vez.</small></div>
                        <div class="mb-3"><label class="form-label small">Archivos <span class="text-danger">*</span></label><input type="file" name="files[]" class="form-control form-control-sm" required multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.mp4,.mov"><small class="text-muted">Máx. 1GB por archivo. Seleccione múltiples archivos.</small></div>
                    @else
                        <div class="mb-3"><label class="form-label small">Archivo <span class="text-danger">*</span></label><input type="file" name="file" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.mp4,.mov"><small class="text-muted">Máx. 1GB</small></div>
                    @endif
                    <div class="mb-0"><label class="form-label small">Notas</label><input type="text" name="notes" class="form-control form-control-sm"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i>Subir</button></div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

{{-- Upload Modals - Payment 2 --}}
@foreach($p2DocDefs as $docKey => $docDef)
@if(!$p2Docs->where('document_type', $docKey)->first() || ($docDef['min_count'] ?? 1) > 1)
<div class="modal fade" id="uploadP2Modal{{ $docKey }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.aupair.profiles.upload-doc', $user->id) }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="document_type" value="{{ $docKey }}">
                <input type="hidden" name="stage" value="application_payment2">
                <div class="modal-header"><h6 class="modal-title">Subir: {{ $docDef['label'] }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    @if(($docDef['min_count'] ?? 1) > 1)
                        <div class="alert alert-info py-2 px-3 mb-3"><small><i class="fas fa-info-circle me-1"></i> Se requieren mínimo <strong>{{ $docDef['min_count'] }}</strong> archivos. Puede seleccionar varios a la vez.</small></div>
                        <div class="mb-3"><label class="form-label small">Archivos <span class="text-danger">*</span></label><input type="file" name="files[]" class="form-control form-control-sm" required multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.mp4,.mov"><small class="text-muted">Máx. 1GB por archivo. Seleccione múltiples archivos.</small></div>
                    @else
                        <div class="mb-3"><label class="form-label small">Archivo <span class="text-danger">*</span></label><input type="file" name="file" class="form-control form-control-sm" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.mp4,.mov"><small class="text-muted">Máx. 1GB</small></div>
                    @endif
                    <div class="mb-0"><label class="form-label small">Notas</label><input type="text" name="notes" class="form-control form-control-sm"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-upload me-1"></i>Subir</button></div>
            </form>
        </div>
    </div>
</div>
@endif
@endforeach

{{-- Reject Modals (P1 + P2) --}}
@foreach($p1Docs->merge($p2Docs)->where('status', '!=', 'approved') as $doc)
<div class="modal fade" id="rejectP1Modal{{ $doc->id }}" tabindex="-1">
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
<div class="modal fade" id="rejectP2Modal{{ $doc->id }}" tabindex="-1">
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
