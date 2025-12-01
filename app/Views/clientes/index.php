<?php // app/Views/clientes/index.php ?>
<div class="container-fluid py-3">

  <div class="row">
    <div class="col-12">

      <div class="d-flex justify-content-between align-items-center mb-2">
        <h5>Clientes</h5>
        <button id="btnNewCliente" class="btn btn-primary btn-sm">
          <i class="bi bi-plus-lg"></i> Nuevo cliente
        </button>
      </div>

      <div class="card">
        <div class="card-body">

          <div class="row g-2 mb-3">
            <div class="col-md-3">
              <label class="form-label mb-1">Documento</label>
              <input type="text" id="filtroDocumento" class="form-control form-control-sm" placeholder="DNI / RUC / CE">
            </div>
            <div class="col-md-3">
              <label class="form-label mb-1">Tipo cliente</label>
              <select id="filtroTipoCliente" class="form-select form-select-sm">
                <option value="">-- Todos --</option>
                <option value="PROSPECTO">Prospecto</option>
                <option value="CLIENTE">Cliente</option>
                <option value="INVERSIONISTA">Inversionista</option>
                <option value="REFERIDO">Referido</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label mb-1">Nivel interés</label>
              <select id="filtroNivelInteres" class="form-select form-select-sm">
                <option value="">-- Todos --</option>
                <option value="BAJO">Bajo</option>
                <option value="MEDIO">Medio</option>
                <option value="ALTO">Alto</option>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label mb-1">Estado</label>
              <select id="filtroEstado" class="form-select form-select-sm">
                <option value="">-- Todos --</option>
                <option value="ACTIVO">Activo</option>
                <option value="INACTIVO">Inactivo</option>
                <option value="BLOQUEADO">Bloqueado</option>
              </select>
            </div>
          </div>

          <div class="table-responsive">
            <table id="tblClientes" class="table table-striped table-hover table-sm align-middle" style="width:100%">
              <thead class="table-light">
              <tr>
                <th>ID</th>
                <th>Documento</th>
                <th>Cliente</th>
                <th>Tipo cliente</th>
                <th>Nivel interés</th>
                <th>Teléfono</th>
                <th>Estado</th>
                <th>Responsable</th>
                <th>Acciones</th>
              </tr>
              </thead>
            </table>
          </div>

        </div>
      </div>

    </div>
  </div>

</div>

<?php
require __DIR__ . '/modals/cliente.php';
require __DIR__ . '/modals/copropietarios.php';
?>
