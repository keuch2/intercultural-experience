@php $tests = $tabData['englishTests']; $remaining = $tabData['englishRemaining']; $best = $tabData['englishBest']; $min = $definition->minEnglishLevel(); $max = $definition->maxEnglishAttempts(); @endphp
<div class="card shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fas fa-language text-info me-2"></i> Test de inglés</h5>
        @if($remaining > 0)<button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addEnglishTestModal"><i class="fas fa-plus me-1"></i> Registrar evaluación</button>@endif
    </div>
    <div class="card-body">
        <div class="alert {{ $best && \App\Models\ProgramEnglishTest::levelMeets($best, $min) ? 'alert-success' : 'alert-info' }} py-2 px-3 mb-3"><small><i class="fas fa-info-circle me-1"></i><strong>Nivel mínimo: {{ $min }}</strong> &middot; Intentos: {{ $tests->count() }}/{{ $max }} &middot; Restantes: <strong>{{ $remaining }}</strong>@if($best) &middot; Mejor nivel: <span class="badge bg-{{ \App\Models\ProgramEnglishTest::levelMeets($best, $min) ? 'success' : 'warning text-dark' }}">{{ $best }}</span>@endif</small></div>
        @if($tests->count())
        <div class="table-responsive"><table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light"><tr><th>#</th><th>Evaluador</th><th>Examen</th><th class="text-center">Oral</th><th class="text-center">Listening</th><th class="text-center">Reading</th><th class="text-center">Final</th><th class="text-center">Nivel</th><th class="text-center">PDF</th><th class="text-center">Enviado</th><th>Fecha</th><th></th></tr></thead>
            <tbody>
            @foreach($tests as $test)
            <tr>
                <td>{{ $test->attempt_number }}</td><td>{{ $test->evaluator_name ?? '-' }}</td><td>{{ $test->exam_name ?? '-' }}</td>
                <td class="text-center">@if($test->oral_score)<span class="badge bg-{{ $test->oral_score === 'Excellent' ? 'success' : ($test->oral_score === 'Great' ? 'info' : 'warning text-dark') }}">{{ $test->oral_score }}</span>@else - @endif</td>
                <td class="text-center">{{ $test->listening_score ?? '-' }}</td><td class="text-center">{{ $test->reading_score ?? '-' }}</td><td class="text-center fw-bold">{{ $test->final_score }}</td>
                <td class="text-center"><span class="badge bg-{{ \App\Models\ProgramEnglishTest::levelMeets($test->cefr_level, $min) ? 'success' : 'warning text-dark' }}">{{ $test->cefr_level }}</span></td>
                <td class="text-center">@if($test->test_pdf_path)<a href="{{ route('admin.program.english.pdf', [$program->slug, $process->id, $test->id]) }}"><i class="fas fa-file-pdf text-danger"></i></a>@else -@endif</td>
                <td class="text-center">{!! $test->results_sent_to_applicant ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-muted"></i>' !!}</td>
                <td><small>{{ $test->created_at->format('d/m/Y') }}</small></td>
                <td><form method="POST" action="{{ route('admin.program.english.delete', [$program->slug, $process->id, $test->id]) }}" onsubmit="return confirm('¿Eliminar esta evaluación?')">@csrf @method('DELETE')<input type="hidden" name="redirect_tab" value="{{ $activeTab }}"><button class="btn btn-sm btn-outline-secondary py-0"><i class="fas fa-trash"></i></button></form></td>
            </tr>
            @if($test->observations)<tr><td colspan="12" class="py-1 px-3 bg-light"><small class="text-muted"><i class="fas fa-comment me-1"></i>{{ $test->observations }}</small></td></tr>@endif
            @endforeach
            </tbody>
        </table></div>
        @else
        <p class="text-muted mb-0"><i class="fas fa-info-circle"></i> Aún no se registraron evaluaciones de inglés.</p>
        @endif
    </div>
</div>

@if($remaining > 0)
<div class="modal fade" id="addEnglishTestModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
    <form method="POST" action="{{ route('admin.program.english.store', [$program->slug, $process->id]) }}" enctype="multipart/form-data">@csrf<input type="hidden" name="redirect_tab" value="{{ $activeTab }}">
        <div class="modal-header"><h6 class="modal-title"><i class="fas fa-language me-2"></i> Registrar evaluación (intento {{ $tests->count() + 1 }} de {{ $max }})</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><div class="row g-3">
            <div class="col-md-6"><label class="form-label small">Evaluador *</label><input type="text" name="evaluator_name" class="form-control form-control-sm" required></div>
            <div class="col-md-6"><label class="form-label small">Examen *</label><input type="text" name="exam_name" class="form-control form-control-sm" required placeholder="EF SET, iTEP, TOEFL…"></div>
            <div class="col-md-3"><label class="form-label small">Oral</label><select name="oral_score" class="form-select form-select-sm"><option value="">--</option><option>Good</option><option>Great</option><option>Excellent</option></select></div>
            <div class="col-md-3"><label class="form-label small">Listening</label><input type="number" name="listening_score" class="form-control form-control-sm" min="0" max="100"></div>
            <div class="col-md-3"><label class="form-label small">Reading</label><input type="number" name="reading_score" class="form-control form-control-sm" min="0" max="100"></div>
            <div class="col-md-3"><label class="form-label small">Puntaje final *</label><input type="number" name="final_score" class="form-control form-control-sm" min="0" max="100" required><small class="text-muted">CEFR se calcula automáticamente</small></div>
            <div class="col-md-8"><label class="form-label small">PDF del resultado</label><input type="file" name="test_pdf" class="form-control form-control-sm" accept=".pdf"></div>
            <div class="col-md-4 d-flex align-items-end"><div class="form-check"><input class="form-check-input" type="checkbox" name="results_sent_to_applicant" value="1" id="resultsSent"><label class="form-check-label small" for="resultsSent">Resultados enviados al participante</label></div></div>
            <div class="col-12"><label class="form-label small">Observaciones</label><textarea name="observations" class="form-control form-control-sm" rows="2"></textarea></div>
        </div></div>
        <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-save me-1"></i> Registrar</button></div>
    </form>
</div></div></div>
@endif
