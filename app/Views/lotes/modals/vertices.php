<?php
// app/Views/lotes/modals/vertices.php
?>
<div class="modal fade" id="modalVerticesLote" tabindex="-1" aria-labelledby="modalVerticesLoteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalVerticesLoteLabel">
          Vértices del lote
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="formVerticesLote">

        <input type="hidden" name="id_lote" id="vert_id_lote">

        <div class="modal-body">

          <div class="alert alert-info small">
            <strong>Instrucciones:</strong>
            ingresa los vértices en <strong>sentido horario</strong>,
            con al menos <strong>3 puntos válidos</strong>.
          </div>

          <div class="row mb-2">
            <div class="col">
              <span class="fw-semibold">Lote:</span>
              <span id="vert_lbl_lote"></span>
            </div>
          </div>

          <div class="table-responsive border rounded">
            <table class="table table-sm mb-0" id="tblVerticesLote">
              <thead class="table-light">
                <tr>
                  <th style="width: 70px;">#</th>
                  <th style="width: 160px;">Latitud</th>
                  <th style="width: 160px;">Longitud</th>
                  <th style="width: 80px;"></th>
                </tr>
              </thead>
              <tbody id="tblVerticesBody">
                <!-- filas dinámicas -->
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-between mt-2">
            <div>
              <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAddVerticeRow">
                + Agregar vértice
              </button>
            </div>
            <div>
              <button type="button" class="btn btn-outline-danger btn-sm" id="btnClearVertices">
                Eliminar todos
              </button>
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cerrar
          </button>
          <button type="submit" class="btn btn-primary">
            Guardar vértices
          </button>
        </div>

      </form>

    </div>
  </div>
</div>
