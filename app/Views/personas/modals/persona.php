<?php
// app/Views/personas/modals/persona.php
if (session_status() === PHP_SESSION_NONE) session_start();
$isAdmin = (($_SESSION['user']['role'] ?? '') === 'admin');
?>

<div class="modal fade" id="modalPersona"
     data-bs-backdrop="static"
     data-bs-keyboard="false"
     tabindex="-1"
     aria-labelledby="modalPersonaLabel"
     aria-hidden="true">

  <div class="modal-dialog modal-lg" role="document">

    <form id="formPersona" class="modal-content" method="post" action="index.php?c=personas&a=save" onsubmit="return false;">


      <div class="modal-header">
        <h5 class="modal-title" id="modalPersonaLabel">Persona</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">

        <input type="hidden" name="id" id="id">

        <?php if ($isAdmin): ?>
        <div class="mb-2">
          <label class="form-label">Asignado a</label>
          <select name="asignado" id="asignado_modal" class="form-select"></select>
        </div>
        <?php endif; ?>

        <div class="mb-2">
          <label class="form-label">Tipo persona</label>
          <select name="tipo_persona" id="tipo_persona" class="form-select"></select>
        </div>

        <div class="mb-2">
          <label class="form-label">Tipo documento</label>
          <select name="tipo_documento" id="tipo_documento" class="form-select"></select>
        </div>

        <div class="mb-2">
          <label class="form-label">N° documento</label>
          <input type="text" name="numero_documento" id="numero_documento" class="form-control">
        </div>

        <div class="mb-2">
          <label class="form-label">Nombres</label>
          <input type="text" name="nombres" id="nombres" class="form-control">
        </div>

        <div class="mb-2">
          <label class="form-label">Apellidos</label>
          <input type="text" name="apellidos" id="apellidos" class="form-control">
        </div>

        <div class="mb-2">
          <label class="form-label">Ubigeo</label>
          <select name="id_ubigeo" id="id_ubigeo" class="form-select"></select>
        </div>

        <div class="mb-2">
          <label class="form-label">Teléfono</label>
          <input type="text" name="telefono" id="telefono" class="form-control">
        </div>

        <div class="mb-2">
          <label class="form-label">Email</label>
          <input type="email" name="email" id="email" class="form-control">
        </div>

        <div class="mb-2">
          <label class="form-label">Estado</label>
          <select name="estado" id="estado" class="form-select">
            <option value="ACTIVO">ACTIVO</option>
            <option value="INACTIVO">INACTIVO</option>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label">Etiqueta</label>
          <select name="etiqueta" id="etiqueta" class="form-select">
            <option value="NULL">Null</option>
            <option value="SIN_RESPUESTA">Sin respuesta</option>
            <option value="CONTACTADO">Contactado</option>
            <option value="PROSPECTO">Prospecto</option>
            <option value="SEPARADO">Separado</option>
            <option value="VENDIDO">Vendido</option>
            <option value="PROBLEMAS">Problemas</option>
          </select>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="btnSavePersona" class="btn btn-primary btn-sm">Guardar</button>
      </div>

    </form>

  </div>
</div>
