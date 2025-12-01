<?php // app/Views/lotes/modals/cambiar_estado.php ?>
<div class="modal fade" id="modalEstadoLote" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalEstadoLoteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="formEstadoLote">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEstadoLoteLabel">Cambio de estado del lote</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id_lote" id="estadoIdLote">

          <div class="alert alert-info small mb-3">
            <strong>Nota:</strong>
            <ul class="mb-0">
              <li>El cambio de estado se registrará en el historial del lote.</li>
              <li>Si el estado es diferente de <strong>DISPONIBLE</strong>, el lote debe estar asociado a un cliente.</li>
              <li>Flujo para vendedores: <code>DISPONIBLE → RESERVADO → SEPARADO → VENDIDO</code>.</li>
            </ul>
          </div>

          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Nuevo estado</label>
              <select name="estado_nuevo" id="estadoNuevo" class="form-select" required>
                <option value="">— Seleccione —</option>
                <option value="DISPONIBLE">DISPONIBLE</option>
                <option value="RESERVADO">RESERVADO</option>
                <option value="SEPARADO">SEPARADO</option>
                <option value="VENDIDO">VENDIDO</option>
                <option value="BLOQUEADO">BLOQUEADO</option>
              </select>
            </div>

            <div class="col-md-8">
              <label class="form-label">Cliente asociado</label>
              <select name="id_cliente" id="estadoIdCliente" class="form-select">
                <!-- AJAX -->
              </select>
              <div class="form-text">
                Obligatorio para estados: <strong>RESERVADO / SEPARADO / VENDIDO</strong>.
              </div>
            </div>

            <div class="col-12">
              <label class="form-label">Motivo</label>
              <textarea name="motivo" id="estadoMotivo" class="form-control" rows="3" placeholder="Motivo del cambio…"></textarea>
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Guardar cambio</button>
        </div>
      </form>
    </div>
  </div>
</div>
