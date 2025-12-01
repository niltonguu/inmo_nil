<?php // app/Views/personas/index.php ?>
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Personas</h5>
    <button id="btnNew" class="btn btn-primary btn-sm">
      <i class="bi bi-plus-lg"></i> Nuevo
    </button>
  </div>
<div class="mb-3">
    <button class="btn btn-secondary btn-sm filter" data-etiqueta="">Todos</button>
    <button class="btn btn-secondary btn-sm filter" data-etiqueta="SIN_RESPUESTA">Sin respuesta</button>
    <button class="btn btn-info btn-sm filter" data-etiqueta="CONTACTADO">Contactado</button>
    <button class="btn btn-primary btn-sm filter" data-etiqueta="PROSPECTO">Prospecto</button>
    <button class="btn btn-warning btn-sm filter" data-etiqueta="SEPARADO">Separado</button>
    <button class="btn btn-success btn-sm filter" data-etiqueta="VENDIDO">Vendido</button>
    <button class="btn btn-danger btn-sm filter" data-etiqueta="PROBLEMAS">Problemas</button>
</div>

  <table id="tblPersonas" class="table table-striped table-hover table-sm align-middle" style="width:100%">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Nombres</th>
        <th>Apellidos</th>
        <th>Teléfono</th>
        <th>Ubigeo</th>
        <th>Estado</th>
        <th>Etiqueta</th>
        <th>Asignado</th>
        <th>Última nota</th>
        <th>Acciones</th>
      </tr>
    </thead>
  </table>
</div>

<?php require __DIR__ . '/modals/persona.php'; ?>
<?php require __DIR__ . '/modals/nota.php'; ?>
