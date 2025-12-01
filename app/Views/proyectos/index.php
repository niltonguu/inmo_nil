<?php // app/Views/proyectos/index.php ?>
<div class="container-fluid py-3">

  <!-- LISTA DE PROYECTOS -->
  <div class="row">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5>Proyectos</h5>
        <button id="btnNewProyecto" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-lg"></i> Nuevo proyecto
        </button>
      </div>

      <table id="tblProyectos" class="table table-striped table-hover table-sm align-middle" style="width:100%">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Código</th>
            <th>Nombre</th>
            <th>Ubigeo</th>
            <th>Precio m² base</th>
            <th>Estado legal</th>
            <th>Estado</th>
            <th>Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

  <!-- PANEL DETALLE DEL PROYECTO SELECCIONADO -->
  <div class="row mt-3" id="panelDetalleProyecto" style="display:none;">
    <div class="col-12">

      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 id="lblProyectoTitulo">Proyecto seleccionado</h6>
        <button id="btnCollapseDetalleProyecto" class="btn btn-outline-secondary btn-sm">
          Ocultar detalle
        </button>
      </div>

      <ul class="nav nav-tabs" id="tabsProyecto">
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabDatosProyecto">
            Datos generales
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabEtapas">
            Etapas
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabManzanas">
            Manzanas
          </button>
        </li>
      </ul>

      <div class="tab-content border border-top-0 p-3">

        <!-- DATOS DEL PROYECTO -->
        <div class="tab-pane fade show active" id="tabDatosProyecto">
          <form id="formProyectoDetalle">
            <input type="hidden" name="id" id="detalle_id">

            <div class="row g-2">
              <div class="col-md-3">
                <label class="form-label">Código</label>
                <input type="text" name="codigo" id="detalle_codigo" class="form-control">
              </div>
              <div class="col-md-5">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" id="detalle_nombre" class="form-control">
              </div>
              <div class="col-md-4">
                <label class="form-label">Ubigeo</label>
                <select name="id_ubigeo" id="detalle_id_ubigeo" class="form-select"></select>
              </div>

              <div class="col-md-3">
                <label class="form-label">Latitud</label>
                <input type="text" name="latitud" id="detalle_latitud" class="form-control" placeholder="-5.1234567">
              </div>
              <div class="col-md-3">
                <label class="form-label">Longitud</label>
                <input type="text" name="longitud" id="detalle_longitud" class="form-control" placeholder="-80.1234567">
              </div>
              <div class="col-md-2">
                <label class="form-label">Zoom mapa</label>
                <input type="number" name="zoom_mapa" id="detalle_zoom_mapa" class="form-control" min="5" max="20">
              </div>

              <div class="col-md-2">
                <label class="form-label">Precio m² base</label>
                <input type="number" step="0.01" name="precio_m2_base" id="detalle_precio_m2_base" class="form-control">
              </div>

              <div class="col-md-2">
                <label class="form-label">Factor mínimo (%)</label>
                <input type="number" step="0.01" name="factor_min_pct" id="detalle_factor_min_pct" class="form-control">
              </div>

              <div class="col-md-2">
                <label class="form-label">Factor máximo (%)</label>
                <input type="number" step="0.01" name="factor_max_pct" id="detalle_factor_max_pct" class="form-control">
              </div>

              <div class="col-md-2">
                <label class="form-label">Estado legal</label>
                <select name="estado_legal" id="detalle_estado_legal" class="form-select">
                  <option value="EN_TRAMITE">En trámite</option>
                  <option value="HABILITADO">Habilitado</option>
                  <option value="INSCRITO">Inscrito</option>
                </select>
              </div>

              <div class="col-md-2">
                <label class="form-label">Estado</label>
                <select name="estado" id="detalle_estado" class="form-select">
                  <option value="ACTIVO">Activo</option>
                  <option value="INACTIVO">Inactivo</option>
                </select>
              </div>
            </div>

            <div class="mt-3 text-end">
              <button type="button" id="btnSaveProyectoDetalle" class="btn btn-primary btn-sm">
                Guardar cambios
              </button>
            </div>
          </form>
        </div>

        <!-- ETAPAS -->
        <div class="tab-pane fade" id="tabEtapas">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Etapas del proyecto</h6>
            <div>
              <button id="btnGenerarEtapas" class="btn btn-outline-secondary btn-sm me-2">
                <i class="bi bi-magic"></i> Generar etapas
              </button>
              <button id="btnNewEtapa" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Nueva etapa
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table id="tblEtapas" class="table table-sm table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>#</th>
                  <th>Nombre</th>
                  <th>Número</th>
                  <th>Habilitada venta</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="tblEtapasBody">
                <!-- filas dinámicas -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- MANZANAS -->
        <div class="tab-pane fade" id="tabManzanas">
          <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="d-flex align-items-center">
              <span class="me-2">Etapa:</span>
              <select id="filtroEtapaManzana" class="form-select form-select-sm" style="width:auto; min-width:180px;"></select>
            </div>
            <div>
              <button id="btnGenerarManzanas" class="btn btn-outline-secondary btn-sm me-2">
                <i class="bi bi-magic"></i> Generar manzanas
              </button>
              <button id="btnNewManzana" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Nueva manzana
              </button>
            </div>
          </div>

          <div class="table-responsive">
            <table id="tblManzanas" class="table table-sm table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Etapa</th>
                  <th>Manzana</th>
                  <th>Descripción</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody id="tblManzanasBody">
                <!-- filas dinámicas -->
              </tbody>
            </table>
          </div>
        </div>

      </div>

    </div>
  </div>

</div>

<?php
// Modales separados para mantener orden
require __DIR__ . '/modals/proyecto.php';
require __DIR__ . '/modals/etapa.php';
require __DIR__ . '/modals/manzana.php';
require __DIR__ . '/modals/generar_etapas.php';
require __DIR__ . '/modals/generar_manzanas.php';
?>


