<?php // app/Views/proyectos/modals/generar_etapas.php ?>
<div class="modal fade" id="modalGenerarEtapas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formGenerarEtapas" class="modal-content" method="post" action="index.php?c=proyectos&a=etapas_generate" onsubmit="return false;">

      <div class="modal-header">
        <h5 class="modal-title">Generar etapas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id_proyecto" id="gen_etapas_id_proyecto">

        <div class="mb-2">
          <label class="form-label">Cantidad de etapas</label>
          <input type="number" name="cantidad" id="gen_etapas_cantidad" class="form-control" min="1" value="3">
        </div>

        <p class="small text-muted mb-0">
          Se crearán etapas con nombres “Etapa 1”, “Etapa 2”, etc.
        </p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="btnGenerateEtapas" class="btn btn-primary btn-sm">Generar</button>
      </div>

    </form>
  </div>
</div>
