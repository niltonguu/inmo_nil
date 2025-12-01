<?php // app/Views/proyectos/modals/etapa.php ?>
<div class="modal fade" id="modalEtapa" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formEtapa" class="modal-content" method="post" action="index.php?c=proyectos&a=etapas_save" onsubmit="return false;">

      <div class="modal-header">
        <h5 class="modal-title" id="modalEtapaLabel">Nueva etapa</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="etapa_id">
        <input type="hidden" name="id_proyecto" id="etapa_id_proyecto">

        <div class="mb-2">
          <label class="form-label">Nombre</label>
          <input type="text" name="nombre" id="etapa_nombre" class="form-control" required>
        </div>

        <div class="mb-2">
          <label class="form-label">Número (opcional)</label>
          <input type="number" name="numero" id="etapa_numero" class="form-control">
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="habilitada_venta" id="etapa_habilitada_venta" value="1">
          <label class="form-check-label" for="etapa_habilitada_venta">
            Habilitar venta
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="btnSaveEtapa" class="btn btn-primary btn-sm">Guardar</button>
      </div>

    </form>
  </div>
</div>
