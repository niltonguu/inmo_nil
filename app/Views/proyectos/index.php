<?php // app/Views/proyectos/index.php ?>
<div class="container-fluid">

  <!-- Encabezado -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Proyectos</h1>
    <button type="button" class="btn btn-primary btn-sm" id="btnNewProyecto">
      Nuevo proyecto
    </button>
  </div>

  <!-- Tabla de proyectos -->
  <div class="card mb-3">
    <div class="card-body">
      <table id="tblProyectos" class="table table-sm table-striped table-hover align-middle mb-0">
        <thead>
          <tr>
            <th style="width:5%;">ID</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Ubigeo</th>
            <th style="width:10%;">Precio m² base</th>
            <th style="width:10%;">Estado legal</th>
            <th style="width:8%;">Estado</th>
            <th style="width:12%;">Acciones</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <!-- Detalle del proyecto (se muestra al seleccionar una fila) -->
  <div id="panelDetalleProyecto" class="card" style="display:none;">
    <div class="card-header d-flex justify-content-between align-items-center">
      <div>
        <h6 class="mb-0" id="lblProyectoTitulo">Proyecto seleccionado</h6>
      </div>
      <button type="button" class="btn btn-outline-secondary btn-sm" id="btnCollapseDetalleProyecto">
        Ocultar detalle
      </button>
    </div>

    <div class="card-body">
      <!-- Tabs -->
      <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="tab-datos-tab" data-bs-toggle="tab"
                  data-bs-target="#tab-datos" type="button" role="tab">
            Datos generales
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-etapas-tab" data-bs-toggle="tab"
                  data-bs-target="#tab-etapas" type="button" role="tab">
            Etapas
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="tab-manzanas-tab" data-bs-toggle="tab"
                  data-bs-target="#tab-manzanas" type="button" role="tab">
            Manzanas
          </button>
        </li>
      </ul>

      <div class="tab-content">
        <!-- TAB: Datos generales -->
        <div class="tab-pane fade show active" id="tab-datos" role="tabpanel" aria-labelledby="tab-datos-tab">

          <form id="formProyectoDetalle" class="row g-3">
            <input type="hidden" id="detalle_id" name="id">

            <div class="col-md-3">
              <label for="detalle_codigo" class="form-label form-label-sm">Código</label>
              <input type="text" class="form-control form-control-sm" id="detalle_codigo" name="codigo">
            </div>

            <div class="col-md-5">
              <label for="detalle_nombre" class="form-label form-label-sm">Nombre</label>
              <input type="text" class="form-control form-control-sm" id="detalle_nombre" name="nombre">
            </div>

            <div class="col-md-4">
              <label for="detalle_id_ubigeo" class="form-label form-label-sm">Ubigeo</label>
              <select id="detalle_id_ubigeo" name="id_ubigeo" class="form-select form-select-sm">
                <option value="">-- Seleccione --</option>
              </select>
            </div>

            <div class="col-md-3">
              <label for="detalle_latitud" class="form-label form-label-sm">Latitud</label>
              <input type="text" class="form-control form-control-sm" id="detalle_latitud" name="latitud">
            </div>

            <div class="col-md-3">
              <label for="detalle_longitud" class="form-label form-label-sm">Longitud</label>
              <input type="text" class="form-control form-control-sm" id="detalle_longitud" name="longitud">
            </div>

            <div class="col-md-2">
              <label for="detalle_zoom_mapa" class="form-label form-label-sm">Zoom mapa</label>
              <input type="number" class="form-control form-control-sm" id="detalle_zoom_mapa"
                     name="zoom_mapa" min="8" max="22">
            </div>

            <div class="col-md-4">
              <label for="detalle_precio_m2_base" class="form-label form-label-sm">Precio m² base</label>
              <input type="number" step="0.01" class="form-control form-control-sm"
                     id="detalle_precio_m2_base" name="precio_m2_base">
            </div>

            <div class="col-md-3">
              <label for="detalle_factor_min_pct" class="form-label form-label-sm">Factor mínimo (%)</label>
              <input type="number" class="form-control form-control-sm"
                     id="detalle_factor_min_pct" name="factor_min_pct">
            </div>

            <div class="col-md-3">
              <label for="detalle_factor_max_pct" class="form-label form-label-sm">Factor máximo (%)</label>
              <input type="number" class="form-control form-control-sm"
                     id="detalle_factor_max_pct" name="factor_max_pct">
            </div>

            <div class="col-md-3">
              <label for="detalle_estado_legal" class="form-label form-label-sm">Estado legal</label>
              <select id="detalle_estado_legal" name="estado_legal"
                      class="form-select form-select-sm">
                <option value="EN_TRAMITE">En trámite</option>
                <option value="HABILITADO">Habilitado</option>
                <option value="INSCRITO">Inscrito</option>
              </select>
            </div>

            <div class="col-md-3">
              <label for="detalle_estado" class="form-label form-label-sm">Estado</label>
              <select id="detalle_estado" name="estado"
                      class="form-select form-select-sm">
                <option value="ACTIVO">Activo</option>
                <option value="INACTIVO">Inactivo</option>
              </select>
            </div>

            <div class="col-12 d-flex justify-content-end mt-2">
              <button type="button" id="btnSaveProyectoDetalle" class="btn btn-success btn-sm">
                Guardar cambios
              </button>
            </div>
          </form>

        </div>

        <!-- TAB: Etapas -->
        <div class="tab-pane fade" id="tab-etapas" role="tabpanel" aria-labelledby="tab-etapas-tab">

          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Etapas del proyecto</h6>
            <div class="btn-group btn-group-sm">
              <!-- BOTÓN MÁGICO -->
              <button type="button" class="btn btn-outline-secondary" id="btnGenerarEtapas">
                Generar etapas
              </button>
              <button type="button" class="btn btn-primary" id="btnNewEtapa">
                Nueva etapa
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table id="tblEtapas" class="table table-sm table-striped table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th style="width:5%;">#</th>
                  <th>Nombre</th>
                  <th style="width:10%;">Número</th>
                  <th style="width:10%;">Habilitada venta</th>
                  <th style="width:15%;">Acciones</th>
                </tr>
              </thead>
              <tbody id="tblEtapasBody"></tbody>
            </table>
          </div>

        </div>

        <!-- TAB: Manzanas -->
        <div class="tab-pane fade" id="tab-manzanas" role="tabpanel" aria-labelledby="tab-manzanas-tab">

          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center gap-2">
              <h6 class="mb-0">Manzanas</h6>
              <div>
                <label for="filtroEtapaManzana" class="form-label form-label-sm mb-0 me-1">
                  Etapa:
                </label>
                <select id="filtroEtapaManzana" class="form-select form-select-sm d-inline-block"
                        style="width:auto; min-width:180px;">
                  <option value="">-- Todas --</option>
                </select>
              </div>
            </div>

            <div class="btn-group btn-group-sm">
              <!-- BOTÓN MÁGICO -->
              <button type="button" class="btn btn-outline-secondary" id="btnGenerarManzanas">
                Generar manzanas
              </button>
              <button type="button" class="btn btn-primary" id="btnNewManzana">
                Nueva manzana
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table id="tblManzanas" class="table table-sm table-striped table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Etapa</th>
                  <th>Manzana</th>
                  <th>Descripción</th>
                  <th style="width:15%;">Acciones</th>
                </tr>
              </thead>
              <tbody id="tblManzanasBody"></tbody>
            </table>
          </div>

        </div>

      </div> <!-- /.tab-content -->
    </div> <!-- /.card-body -->
  </div> <!-- /#panelDetalleProyecto -->

</div>

<?php
// Modales relacionadas al módulo de proyectos
include __DIR__ . '/modals/proyecto.php';
include __DIR__ . '/modals/etapa.php';
include __DIR__ . '/modals/generar_etapas.php';
include __DIR__ . '/modals/manzana.php';
include __DIR__ . '/modals/generar_manzanas.php';
include __DIR__ . '/modals/factores.php';
?>
