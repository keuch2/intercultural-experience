{{-- Modal compartido "Eliminar postulación". Los botones que lo abren llevan data-* con la acción y los conteos:
     data-bs-toggle="modal" data-bs-target="#deleteApplicationModal" data-action="{{ route(...) }}"
     data-program="..." data-docs="n" data-pay-verified="n" data-pay-pending="n" data-assignment="0|1" --}}
<div class="modal fade" id="deleteApplicationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteApplicationForm" method="POST" action="">
                @csrf @method('DELETE')
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-trash me-1"></i> Eliminar postulación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2">Vas a eliminar definitivamente la postulación a <strong data-field="program"></strong>. El participante <strong>no se borra</strong> y podrá volver a postular a este programa.</p>
                    <p class="mb-1 small text-muted">Se eliminan junto con la postulación:</p>
                    <ul class="small mb-3">
                        <li>Proceso y etapas del programa</li>
                        <li><span data-field="docs">0</span> documento(s) y sus archivos</li>
                        <li><span data-field="pay-pending">0</span> pago(s) sin verificar</li>
                        <li data-row="assignment" hidden>La oferta laboral asignada (se libera el cupo)</li>
                    </ul>
                    <div class="alert alert-warning py-2" data-row="verified" hidden>
                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" name="confirm_payments" value="1" id="confirmPaymentsDelete">
                            <label class="form-check-label" for="confirmPaymentsDelete">Entiendo que se eliminan <strong data-field="pay-verified">0</strong> pago(s) <strong>verificado(s)</strong> con su historial.</label>
                        </div>
                    </div>
                    <p class="small text-danger mb-0"><i class="fas fa-exclamation-triangle me-1"></i> Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-1"></i> Eliminar postulación</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('deleteApplicationModal');
    if (!modal) return;
    modal.addEventListener('show.bs.modal', function (event) {
        var btn = event.relatedTarget; if (!btn) return;
        var d = btn.dataset;
        modal.querySelector('#deleteApplicationForm').action = d.action || '';
        modal.querySelector('[data-field="program"]').textContent = d.program || 'este programa';
        modal.querySelector('[data-field="docs"]').textContent = d.docs || '0';
        modal.querySelector('[data-field="pay-pending"]').textContent = d.payPending || '0';
        modal.querySelector('[data-field="pay-verified"]').textContent = d.payVerified || '0';
        modal.querySelector('[data-row="assignment"]').hidden = d.assignment !== '1';
        var verified = parseInt(d.payVerified || '0', 10) > 0;
        var row = modal.querySelector('[data-row="verified"]'); row.hidden = !verified;
        var cb = modal.querySelector('#confirmPaymentsDelete'); cb.checked = false; cb.required = verified;
    });
});
</script>
