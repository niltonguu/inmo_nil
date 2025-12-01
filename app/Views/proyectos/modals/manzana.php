<?php // app/Views/proyectos/modals/manzana.php ?>
<div class="modal fade" id="modalManzana" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formManzana" class="modal-content" method="post" action="index.php?c=proyectos&a=manzanas_save" onsubmit="return false;">

      <div class="modal-header">
        <h5 class="modal-title" id="modalManzanaLabel">Nueva manzana</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="manzana_id">

        <div class="mb-2">
          <label class="form-label">Etapa</label>
          <select name="id_etapa" id="manzana_id_etapa" class="form-select" required></select>
        </div>

        <div class="mb-2">
          <label class="form-label">Código (ej. A, B, C)</label>
          <input type="text" name="codigo" id="manzana_codigo" class="form-control" required>
        </div>

        <div class="mb-2">
          <label class="form-label">Descripción</label>
          <input type="text" name="descripcion" id="manzana_descripcion" class="form-control">
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="btnSaveManzana" class="btn btn-primary btn-sm">Guardar</button>
      </div>

    </form>
  </div>
</div>
