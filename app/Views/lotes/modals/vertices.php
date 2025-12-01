<?php // app/Views/lotes/modals/vertices.php ?>
<div class="modal fade" id="modalVerticesLote" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="formVerticesLote"
          class="modal-content"
          method="post"
          action="index.php?c=lotes&a=vertices_save"
          onsubmit="return false;">

      <div class="modal-header">
        <h5 class="modal-title">Editar vértices del lote</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id_lote" id="vertices_id_lote">

        <p class="small text-muted">
          Los vértices deben ingresarse en <strong>sentido horario (clockwise)</strong>.
          El orden se respeta según la columna <strong>#</strong>.
        </p>

        <div class="table-responsive mb-2">
          <table id="tblVerticesEditor" class="table table-sm table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:60px;">#</th>
                <th>Latitud</th>
                <th>Longitud</th>
                <th style="width:80px;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- filas dinámicas: inputs orden/lat/lng -->
            </tbody>
          </table>
        </div>

        <div class="d-flex justify-content-between align-items-center">
          <button type="button" id="btnAddVerticeFila" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Agregar vértice
          </button>
          <small class="text-muted">
            Define al menos 3 vértices para formar el polígono.
          </small>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="btnSaveVerticesLote" class="btn btn-primary btn-sm">Guardar vértices</button>
      </div>

    </form>
  </div>
</div>
