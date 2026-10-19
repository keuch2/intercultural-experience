{{-- Modal único para "Marcar como solucionado"; los botones llevan data-action y data-title. --}}
<div class="modal fade" id="resolveSupportReportModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <form method="POST" action="#" id="resolveSupportReportForm">@csrf
        <div class="modal-header"><h6 class="modal-title"><i class="fas fa-check-circle text-success me-1"></i> Marcar como solucionado</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <p class="small text-muted mb-2" id="resolveSupportReportTitle"></p>
            <label class="form-label small">¿Qué acciones se tomaron y cómo se resolvió? *</label>
            <textarea name="resolution" class="form-control form-control-sm" rows="4" required maxlength="2000"></textarea>
            <small class="text-muted">El participante recibe este mensaje como aviso en la app.</small>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i> Marcar como solucionado</button></div>
    </form>
</div></div></div>
@once
@push('scripts')
<script>
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-resolve-report]');
    if (!btn) return;
    document.getElementById('resolveSupportReportForm').action = btn.dataset.action;
    document.getElementById('resolveSupportReportTitle').textContent = btn.dataset.title || '';
});
</script>
@endpush
@endonce
