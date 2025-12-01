<?php // app/Views/personas/modals/nota.php ?>
<div class="modal fade" id="modalNota"
     data-bs-backdrop="static"
     data-bs-keyboard="false"
     tabindex="-1"
     aria-labelledby="modalNotaLabel"
     aria-hidden="true">

  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalNotaLabel">Notas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">

        <input type="hidden" id="id_persona_nota">

        <div class="mb-2">
          <label class="form-label">Nueva nota</label>
          <textarea id="nota_texto" class="form-control" rows="3"></textarea>
        </div>

        <hr>

        <div id="historialNotas"
             style="max-height: 250px; overflow-y: auto;">
        </div>

      </div>

      <div class="modal-footer">
        <button type="button"
                class="btn btn-secondary btn-sm"
                data-bs-dismiss="modal">
          Cerrar
        </button>

        <button type="button"
                id="btnSaveNota"
                class="btn btn-primary btn-sm">
          Guardar nota
        </button>
      </div>

    </div>
  </div>

</div>
