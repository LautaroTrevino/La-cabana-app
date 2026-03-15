{{--
    Componente: modal de confirmación con contraseña de admin.
    Se incluye UNA vez por vista. Los botones de borrar lo activan con:
      data-bs-toggle="modal" data-bs-target="#adminConfirmModal"
      data-form-id="id-del-form-a-enviar"
      data-label="Descripción de lo que se va a borrar"
--}}
@if(Auth::user()->role === 'administrativo')
<div class="modal fade" id="adminConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0" style="background: #7c3aed;">
                <h5 class="modal-title fw-bold text-white">
                    <i class="bi bi-shield-lock-fill me-2"></i>Acción requiere autorización
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body px-4 py-3">
                <p class="text-muted mb-1">Estás intentando eliminar:</p>
                <p class="fw-bold fs-6 mb-3" id="adminConfirmLabel"></p>
                <div class="alert alert-warning border-0 py-2 small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    Esta acción requiere la <strong>contraseña del administrador</strong>.
                </div>
                <label class="form-label fw-bold">Contraseña del administrador</label>
                <input type="password" id="adminPasswordInput" class="form-control" placeholder="Ingresá la contraseña" autocomplete="off">
                <div id="adminPasswordError" class="text-danger small mt-2" style="display:none;">
                    <i class="bi bi-x-circle me-1"></i> Contraseña incorrecta. Intentá de nuevo.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" id="adminConfirmBtn" class="btn fw-bold text-white" style="background:#7c3aed;">
                    <i class="bi bi-shield-check me-1"></i> Confirmar y Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    let targetFormId = null;

    document.addEventListener('DOMContentLoaded', function () {
        const modal     = document.getElementById('adminConfirmModal');
        const input     = document.getElementById('adminPasswordInput');
        const errorMsg  = document.getElementById('adminPasswordError');
        const label     = document.getElementById('adminConfirmLabel');
        const confirmBtn= document.getElementById('adminConfirmBtn');

        if (!modal) return;

        modal.addEventListener('show.bs.modal', function (e) {
            const trigger = e.relatedTarget;
            targetFormId  = trigger ? trigger.dataset.formId : null;
            label.textContent = trigger ? (trigger.dataset.label || '¿Continuar?') : '';
            input.value   = '';
            errorMsg.style.display = 'none';
            confirmBtn.disabled    = false;
        });

        confirmBtn.addEventListener('click', async function () {
            const password = input.value.trim();
            if (!password) { input.focus(); return; }

            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Verificando...';

            try {
                const res = await fetch('{{ route("admin.verifyPassword") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ password }),
                });

                if (res.ok) {
                    const form = document.getElementById(targetFormId);
                    if (form) form.submit();
                } else {
                    errorMsg.style.display = 'block';
                    input.value = '';
                    input.focus();
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Confirmar y Eliminar';
                }
            } catch {
                errorMsg.textContent = 'Error de conexión. Intentá de nuevo.';
                errorMsg.style.display = 'block';
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="bi bi-shield-check me-1"></i> Confirmar y Eliminar';
            }
        });
    });
})();
</script>
@endif
