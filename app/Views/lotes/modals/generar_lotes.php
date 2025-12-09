<?php
// app/Views/lotes/modals/generar_lotes.php
?>
<div class="modal fade" id="modalGenerarLotes"
     data-bs-backdrop="static"
     data-bs-keyboard="false"
     tabindex="-1"
     aria-labelledby="modalGenerarLotesLabel"
     aria-hidden="true">
  <div class="modal-dialog">
    <form id="formGenerarLotes" class="modal-content" onsubmit="return false;">
      <div class="modal-header">
        <h5 class="modal-title" id="modalGenerarLotesLabel">Generar lotes por rango</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id_proyecto" id="gen_id_proyecto">
        <input type="hidden" name="id_etapa" id="gen_id_etapa">
        <input type="hidden" name="id_manzana" id="gen_id_manzana">

        <div class="mb-2">
          <label class="form-label">Contexto</label>
          <div class="form-control form-control-sm bg-light" id="gen_contexto" readonly></div>
        </div>

        <div class="row g-2">
          <div class="col-6 mb-2">
            <label class="form-label">N° desde</label>
            <input type="number" name="numero_desde" id="gen_desde" class="form-control form-control-sm">
          </div>
          <div class="col-6 mb-2">
            <label class="form-label">N° hasta</label>
            <input type="number" name="numero_hasta" id="gen_hasta" class="form-control form-control-sm">
          </div>
        </div>

        <div class="mb-2">
          <label class="form-label">Área base (m²) opcional</label>
          <input type="number" step="0.01" name="area_m2_default" id="gen_area_m2" class="form-control form-control-sm">
        </div>

        <p class="small text-muted mb-0">
          Se crearán lotes consecutivos en la manzana seleccionada.
        </p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="btnGenerarLotesConfirm" class="btn btn-primary btn-sm">Generar</button>
      </div>
    </form>
  </div>
</div>
