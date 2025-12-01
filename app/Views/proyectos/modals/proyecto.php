<?php // app/Views/proyectos/modals/proyecto.php ?>
<div class="modal fade" id="modalProyecto" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="formProyectoModal" class="modal-content" method="post" action="index.php?c=proyectos&a=save" onsubmit="return false;">

      <div class="modal-header">
        <h5 class="modal-title" id="modalProyectoLabel">Nuevo proyecto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="proy_id">

        <div class="row g-2">
          <div class="col-md-3">
            <label class="form-label">Código</label>
            <input type="text" name="codigo" id="proy_codigo" class="form-control" required>
          </div>
          <div class="col-md-5">
            <label class="form-label">Nombre</label>
            <input type="text" name="nombre" id="proy_nombre" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Ubigeo</label>
            <select name="id_ubigeo" id="proy_id_ubigeo" class="form-select" required></select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Latitud</label>
            <input type="text" name="latitud" id="proy_latitud" class="form-control" placeholder="-5.1234567">
          </div>
          <div class="col-md-3">
            <label class="form-label">Longitud</label>
            <input type="text" name="longitud" id="proy_longitud" class="form-control" placeholder="-80.1234567">
          </div>
          <div class="col-md-2">
            <label class="form-label">Zoom mapa</label>
            <input type="number" name="zoom_mapa" id="proy_zoom_mapa" class="form-control" min="5" max="20">
          </div>

          <div class="col-md-2">
            <label class="form-label">Precio m² base</label>
            <input type="number" step="0.01" name="precio_m2_base" id="proy_precio_m2_base" class="form-control" value="0">
          </div>

          <div class="col-md-2">
            <label class="form-label">Factor mínimo (%)</label>
            <input type="number" step="0.01" name="factor_min_pct" id="proy_factor_min_pct" class="form-control" value="-40">
          </div>

          <div class="col-md-2">
            <label class="form-label">Factor máximo (%)</label>
            <input type="number" step="0.01" name="factor_max_pct" id="proy_factor_max_pct" class="form-control" value="50">
          </div>

          <div class="col-md-2">
            <label class="form-label">Estado legal</label>
            <select name="estado_legal" id="proy_estado_legal" class="form-select">
              <option value="EN_TRAMITE">En trámite</option>
              <option value="HABILITADO">Habilitado</option>
              <option value="INSCRITO">Inscrito</option>
            </select>
          </div>

          <div class="col-md-2">
            <label class="form-label">Estado</label>
            <select name="estado" id="proy_estado" class="form-select">
              <option value="ACTIVO">Activo</option>
              <option value="INACTIVO">Inactivo</option>
            </select>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="btnSaveProyectoModal" class="btn btn-primary btn-sm">Guardar</button>
      </div>

    </form>
  </div>
</div>
