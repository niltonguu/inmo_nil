<?php
// app/Views/proyectos/modals/factores.php
?>
<div class="modal fade" id="modalFactoresProyecto" tabindex="-1" aria-labelledby="modalFactoresProyectoLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalFactoresProyectoLabel">
          Factores del proyecto: <span id="fp_nombreProyecto" class="fw-bold"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="fp_idProyecto">

        <div class="row">
          <!-- Lista de factores -->
          <div class="col-md-7 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0">Factores configurados</h6>
              <button type="button" class="btn btn-sm btn-outline-secondary" id="fp_btnEjemplos">
                Ver ejemplos de factores
              </button>
            </div>

            <div class="table-responsive border rounded">
              <table class="table table-sm table-striped mb-0" id="fp_tablaFactores">
                <thead class="table-light">
                  <tr>
                    <th>Categoría</th>
                    <th>Nombre</th>
                    <th class="text-end">Valor %</th>
                    <th class="text-center">Activo</th>
                    <th class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- JS -->
                </tbody>
              </table>
            </div>
          </div>

          <!-- Formulario -->
          <div class="col-md-5 mb-3">
            <h6 id="fp_formTitulo">Nuevo factor</h6>

            <form id="fp_formFactor" autocomplete="off">
              <input type="hidden" id="fp_idFactor" name="id">

              <div class="mb-2">
                <label for="fp_catFactor" class="form-label mb-1">Categoría</label>
                <select id="fp_catFactor" name="cat_factor" class="form-select form-select-sm" required>
                  <option value="">Seleccione...</option>
                  <option value="GLOBAL">GLOBAL</option>
                  <option value="UBICACION">UBICACIÓN</option>
                  <option value="VISTA">VISTA</option>
                  <option value="PLUSVALIA">PLUSVALÍA</option>
                  <option value="RESTRICCION">RESTRICCIÓN</option>
                  <option value="GLOBALES">GLOBALES</option>
                  <option value="ETAPA_PROYECTO">ETAPA PROYECTO</option>
                  <option value="ESPECIALES">ESPECIALES</option>
                </select>
              </div>

              <div class="mb-2">
                <label for="fp_codigo" class="form-label mb-1">Código del factor</label>
                <input type="text" id="fp_codigo" name="codigo" class="form-control form-control-sm" required>
              </div>


              <div class="mb-2">
                <label for="fp_nombre" class="form-label mb-1">Nombre del factor</label>
                <input type="text" id="fp_nombre" name="nombre" class="form-control form-control-sm" required>
              </div>

              <div class="mb-2">
                <label for="fp_valorPct" class="form-label mb-1">
                  Valor porcentual <small class="text-muted">(ej: 10 = +10%, 25 = +25%)</small>
                </label>
                <input type="number" step="0.01" id="fp_valorPct" name="valor_pct" class="form-control form-control-sm" required>
              </div>

              <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" id="fp_activo" name="activo" checked>
                <label class="form-check-label" for="fp_activo">Activo</label>
              </div>

              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary">
                  Guardar factor
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="fp_btnLimpiar">
                  Limpiar
                </button>
              </div>
            </form>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal de ejemplos de factores (usa factores.json) -->
<div class="modal fade" id="modalFactoresEjemplos" tabindex="-1" aria-labelledby="modalFactoresEjemplosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalFactoresEjemplosLabel">Ejemplos de factores</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted">
          Estos son solo ejemplos. Haz clic en un factor para rellenar el formulario del proyecto.
        </p>
        <div id="fp_ejemplosContainer">
          <!-- JS desde factores.json -->
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
