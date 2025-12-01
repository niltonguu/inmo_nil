<?php // app/Views/lotes/modals/lote.php ?>
<div class="modal fade" id="modalLote" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="formLoteModal"
          class="modal-content"
          method="post"
          action="index.php?c=lotes&a=save"
          onsubmit="return false;">

      <div class="modal-header">
        <h5 class="modal-title" id="modalLoteLabel">Nuevo lote</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="lote_id">

        <div class="row g-2">

          <!-- Proyecto / Etapa / Manzana -->
          <div class="col-md-4">
            <label for="lote_id_proyecto" class="form-label">Proyecto</label>
            <select name="id_proyecto" id="lote_id_proyecto" class="form-select" required></select>
          </div>

          <div class="col-md-4">
            <label for="lote_id_etapa" class="form-label">Etapa</label>
            <select name="id_etapa" id="lote_id_etapa" class="form-select" required></select>
          </div>

          <div class="col-md-4">
            <label for="lote_id_manzana" class="form-label">Manzana</label>
            <select name="id_manzana" id="lote_id_manzana" class="form-select" required></select>
          </div>

          <!-- Identificación / área / precio -->
          <div class="col-md-3">
            <label for="lote_numero" class="form-label">N° lote</label>
            <input type="number" name="numero" id="lote_numero" class="form-control" required>
          </div>

          <div class="col-md-3">
            <label for="lote_codigo" class="form-label">Código lote</label>
            <input type="text" name="codigo" id="lote_codigo" class="form-control" placeholder="Opcional">
          </div>

          <div class="col-md-3">
            <label for="lote_area_m2" class="form-label">Área (m²)</label>
            <input type="number" step="0.01" name="area_m2" id="lote_area_m2" class="form-control" required>
          </div>

          <div class="col-md-3">
            <label for="lote_precio_m2" class="form-label">Precio m² (lote)</label>
            <input type="number" step="0.01" name="precio_m2" id="lote_precio_m2" class="form-control">
            <small class="text-muted">
              Si se deja vacío, usa el precio base del proyecto.
            </small>
          </div>

          <!-- Dimensiones -->
          <div class="col-md-3">
            <label for="lote_frente_m" class="form-label">Frente (m)</label>
            <input type="number" step="0.01" name="frente_m" id="lote_frente_m" class="form-control">
          </div>

          <div class="col-md-3">
            <label for="lote_fondo_m" class="form-label">Fondo (m)</label>
            <input type="number" step="0.01" name="fondo_m" id="lote_fondo_m" class="form-control">
          </div>

          <div class="col-md-3">
            <label for="lote_lado_izq_m" class="form-label">Lado izq. (m)</label>
            <input type="number" step="0.01" name="lado_izq_m" id="lote_lado_izq_m" class="form-control">
          </div>

          <div class="col-md-3">
            <label for="lote_lado_der_m" class="form-label">Lado der. (m)</label>
            <input type="number" step="0.01" name="lado_der_m" id="lote_lado_der_m" class="form-control">
          </div>

          <!-- Estados / cliente -->
          <div class="col-md-4">
            <label for="lote_estado_comercial" class="form-label">Estado comercial</label>
            <select name="estado_comercial" id="lote_estado_comercial" class="form-select">
              <option value="HABILITADO">Habilitado</option>
              <option value="DESHABILITADO">Deshabilitado</option>
            </select>
          </div>

          <div class="col-md-4">
            <label for="lote_estado_lote" class="form-label">Estado del lote</label>
            <select name="estado_lote" id="lote_estado_lote" class="form-select">
              <option value="DISPONIBLE">Disponible</option>
              <option value="RESERVADO">Reservado</option>
              <option value="SEPARADO">Separado</option>
              <option value="VENDIDO">Vendido</option>
              <option value="BLOQUEADO">Bloqueado</option>
            </select>
          </div>

          <div class="col-md-4">
            <label for="lote_id_cliente" class="form-label">Cliente asociado</label>
            <select name="id_cliente" id="lote_id_cliente" class="form-select">
              <option value="">-- Sin cliente --</option>
            </select>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="btnSaveLoteModal" class="btn btn-primary btn-sm">Guardar</button>
      </div>

    </form>
  </div>
</div>
