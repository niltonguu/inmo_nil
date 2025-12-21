<?php // app/Views/lotes/modals/factores_proyecto.php ?>
<div class="modal fade" id="modalFactoresProyecto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Factores de precio del proyecto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">

        <div class="d-flex justify-content-between align-items-center mb-2">
          <div>
            <small class="text-muted">
              Proyecto: <span id="lblFactoresProyectoNombre">[selecciona proyecto]</span>
            </small>
          </div>

          <div class="d-flex gap-2">
            <!-- NUEVO: botón de ayuda con ejemplos -->
            <button id="btnEjemplosFactores" type="button" class="btn btn-outline-info btn-sm">
              <i class="bi bi-lightbulb"></i> Ver ejemplos
            </button>

            <button id="btnNewFactorProyecto" class="btn btn-primary btn-sm">
              <i class="bi bi-plus-lg"></i> Nuevo factor
            </button>
          </div>
        </div>

        <div class="table-responsive">
          <table id="tblFactoresProyecto" class="table table-sm table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Categoría</th>
                <th>Nombre</th>
                <th>Valor (%)</th>
                <th>Activo</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <!-- dinámico -->
            </tbody>
          </table>
        </div>

        <small class="text-muted">
          Los factores aquí definidos aparecerán como checkboxes en cada lote de este proyecto.
        </small>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>

<!-- Modal interno para crear/editar un factor -->
<div class="modal fade" id="modalFactorProyectoForm" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formFactorProyecto"
          class="modal-content"
          method="post"
          action="index.php?c=lotes&a=factores_save"
          onsubmit="return false;">

      <div class="modal-header">
        <h5 class="modal-title" id="modalFactorProyectoLabel">Nuevo factor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="factor_id">
        <input type="hidden" name="id_proyecto" id="factor_id_proyecto">

        <div class="mb-2">
          <label class="form-label">Nombre del factor</label>
          <input type="text" name="nombre" id="factor_nombre" class="form-control" required>
        </div>

        <div class="mb-2">
          <label class="form-label">Categoría</label>
          <select name="cat_factor" id="factor_cat_factor" class="form-select" required>
            <option value="GLOBAL">GLOBAL</option>
            <option value="UBICACION">UBICACION</option>
            <option value="VISTA">VISTA</option>
            <option value="PLUSVALIA">PLUSVALIA</option>
            <option value="RESTRICCION">RESTRICCION</option>
            <option value="ETAPA_PROYECTO">ETAPA_PROYECTO</option>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">Valor (%)</label>
          <input type="number" step="0.01" name="valor_pct" id="factor_valor_pct" class="form-control" required>
          <small class="text-muted">Ejemplo: 10 = +10%, -25 = -25%</small>
        </div>

        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" name="activo" id="factor_activo" value="1" checked>
          <label class="form-check-label" for="factor_activo">
            Activo
          </label>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="btnSaveFactorProyecto" class="btn btn-primary btn-sm">Guardar</button>
      </div>

    </form>
  </div>
</div>
