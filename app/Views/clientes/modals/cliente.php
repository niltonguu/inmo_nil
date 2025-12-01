<?php // app/Views/clientes/modals/cliente.php ?>
<div class="modal fade" id="modalCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="formCliente" class="modal-content" method="post" action="index.php?c=clientes&a=save">

      <div class="modal-header">
        <h5 class="modal-title" id="modalClienteLabel">Nuevo cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" name="id" id="cli_id">
        <input type="hidden" name="id_user" id="cli_id_user">

        <div class="row g-2">

          <div class="col-md-3">
            <label class="form-label mb-1">Tipo persona</label>
            <select name="tipo_persona" id="cli_tipo_persona" class="form-select form-select-sm">
              <option value="1">Natural</option>
              <option value="2">Jurídica</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Tipo documento</label>
            <select name="tipo_documento" id="cli_tipo_documento" class="form-select form-select-sm">
              <option value="1">DNI</option>
              <option value="2">CE</option>
              <option value="3">Pasaporte</option>
              <option value="4">RUC</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">N° documento</label>
            <input type="text" name="numero_documento" id="cli_numero_documento" class="form-control form-control-sm" required>
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Estado civil</label>
            <select name="estado_civil" id="cli_estado_civil" class="form-select form-select-sm">
              <option value="">-- Seleccione --</option>
              <option value="SOLTERO">Soltero</option>
              <option value="CASADO">Casado</option>
              <option value="DIVORCIADO">Divorciado</option>
              <option value="VIUDO">Viudo</option>
              <option value="CONVIVIENTE">Conviviente</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label mb-1">Nombres</label>
            <input type="text" name="nombres" id="cli_nombres" class="form-control form-control-sm">
          </div>

          <div class="col-md-4">
            <label class="form-label mb-1">Apellidos</label>
            <input type="text" name="apellidos" id="cli_apellidos" class="form-control form-control-sm">
          </div>

          <div class="col-md-4">
            <label class="form-label mb-1">Razón social</label>
            <input type="text" name="razon_social" id="cli_razon_social" class="form-control form-control-sm">
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Fecha nacimiento</label>
            <input type="date" name="fecha_nacimiento" id="cli_fecha_nacimiento" class="form-control form-control-sm">
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Teléfono</label>
            <input type="text" name="telefono" id="cli_telefono" class="form-control form-control-sm">
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Teléfono alt.</label>
            <input type="text" name="telefono_alt" id="cli_telefono_alt" class="form-control form-control-sm">
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Email</label>
            <input type="email" name="email" id="cli_email" class="form-control form-control-sm">
          </div>

          <div class="col-md-4">
            <label class="form-label mb-1">Ubigeo</label>
            <select name="id_ubigeo" id="cli_id_ubigeo" class="form-select form-select-sm"></select>
          </div>

          <div class="col-md-8">
            <label class="form-label mb-1">Dirección</label>
            <input type="text" name="direccion" id="cli_direccion" class="form-control form-control-sm">
          </div>

          <div class="col-md-12">
            <label class="form-label mb-1">Referencia dirección</label>
            <input type="text" name="referencia_direccion" id="cli_referencia_direccion" class="form-control form-control-sm">
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Tipo cliente</label>
            <select name="tipo_cliente" id="cli_tipo_cliente" class="form-select form-select-sm">
              <option value="PROSPECTO">Prospecto</option>
              <option value="CLIENTE">Cliente</option>
              <option value="INVERSIONISTA">Inversionista</option>
              <option value="REFERIDO">Referido</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Nivel interés</label>
            <select name="nivel_interes" id="cli_nivel_interes" class="form-select form-select-sm">
              <option value="BAJO">Bajo</option>
              <option value="MEDIO" selected>Medio</option>
              <option value="ALTO">Alto</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Medio contacto</label>
            <select name="medio_contacto_preferido" id="cli_medio_contacto" class="form-select form-select-sm">
              <option value="WHATSAPP">WhatsApp</option>
              <option value="TELEFONO">Teléfono</option>
              <option value="EMAIL">Email</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Origen</label>
            <input type="text" name="origen" id="cli_origen" class="form-control form-control-sm" placeholder="Facebook, Referido, etc.">
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Estado</label>
            <select name="estado" id="cli_estado" class="form-select form-select-sm">
              <option value="ACTIVO">Activo</option>
              <option value="INACTIVO">Inactivo</option>
              <option value="BLOQUEADO">Bloqueado</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Responsable</label>
            <select name="id_user_responsable" id="cli_id_user_responsable" class="form-select form-select-sm">
              <!-- llenar por JS si quieres -->
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label mb-1">Último contacto</label>
            <input type="datetime-local" name="fecha_ultimo_contacto" id="cli_fecha_ultimo_contacto" class="form-control form-control-sm">
          </div>

          <div class="col-md-12">
            <label class="form-label mb-1">Observaciones</label>
            <textarea name="observaciones" id="cli_observaciones" rows="2" class="form-control form-control-sm"></textarea>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" id="btnSaveCliente" class="btn btn-primary btn-sm">Guardar</button>
      </div>

    </form>
  </div>
</div>
