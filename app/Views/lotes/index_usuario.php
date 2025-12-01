<?php
// app/Views/lotes/index_usuario.php
?>
<div class="container-fluid py-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Lotes - Panel del vendedor</h4>
  </div>

  <!-- Filtros -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-3">
          <label for="filtroProyectoUsr" class="form-label">Proyecto</label>
          <select id="filtroProyectoUsr" class="form-select form-select-sm">
            <option value="">Todos</option>
          </select>
        </div>
        <div class="col-md-3">
          <label for="filtroEtapaUsr" class="form-label">Etapa</label>
          <select id="filtroEtapaUsr" class="form-select form-select-sm">
            <option value="">Todas</option>
          </select>
        </div>
        <div class="col-md-3">
          <label for="filtroManzanaUsr" class="form-label">Manzana</label>
          <select id="filtroManzanaUsr" class="form-select form-select-sm">
            <option value="">Todas</option>
          </select>
        </div>
        <div class="col-md-3">
          <label for="filtroEstadoUsr" class="form-label">Estado lote</label>
          <select id="filtroEstadoUsr" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="DISPONIBLE">DISPONIBLE</option>
            <option value="RESERVADO">RESERVADO</option>
            <option value="SEPARADO">SEPARADO</option>
            <option value="VENDIDO">VENDIDO</option>
          </select>
        </div>
      </div>
      <div class="mt-2 small text-muted">
        Solo se mostrarán lotes <strong>HABILITADOS</strong> para venta, y asociados al usuario logueado según reglas del backend.
      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card">
    <div class="card-body">
      <table id="tablaLotesUsr" class="table table-striped table-bordered table-hover w-100">
        <thead>
          <tr>
            <th>Proyecto</th>
            <th>Etapa</th>
            <th>Manzana</th>
            <th>Lote</th>
            <th>Área (m²)</th>
            <th>Precio final</th>
            <th>Estado</th>
            <th>Cliente</th>
            <th>Acciones</th>
          </tr>
        </thead>
      </table>
    </div>
  </div>

</div>
