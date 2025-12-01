<?php // app/Views/clientes/modals/copropietarios.php ?>
<div class="modal fade" id="modalCopropietarios" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">
          Copropietarios de <span id="coprop_cliente_nombre" class="fw-bold"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">

        <input type="hidden" id="coprop_id_cliente">

        <div class="table-responsive mb-3">
          <table id="tblCopropietarios" class="table table-sm table-hover align-middle">
            <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Documento</th>
              <th>Nombre</th>
              <th>Parentesco</th>
              <th>%</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>

        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center py-2">
            <span class="fw-semibold">
              <i class="bi bi-people"></i> Formulario de copropietario
            </span>
            <button type="button" id="btnCopropNuevo" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-plus-lg"></i> Nuevo
            </button>
          </div>

          <div class="card-body bg-light">
            <form id="formCopropietario">
              <input type="hidden" name="id" id="coprop_id">
              <input type="hidden" name="id_cliente" id="coprop_form_id_cliente">

              <div class="row g-2">

                <div class="col-md-3">
                  <label class="form-label mb-1">Tipo persona</label>
                  <select name="tipo_persona" id="coprop_tipo_persona" class="form-select form-select-sm">
                    <option value="1">Natural</option>
                    <option value="2">Jurídica</option>
                  </select>
                </div>

                <div class="col-md-3">
                  <label class="form-label mb-1">Tipo documento</label>
                  <select name="tipo_documento" id="coprop_tipo_documento" class="form-select form-select-sm">
                    <option value="1">DNI</option>
                    <option value="2">CE</option>
                    <option value="3">Pasaporte</option>
                    <option value="4">RUC</option>
                  </select>
                </div>

                <div class="col-md-3">
                  <label class="form-label mb-1">N° documento</label>
                  <input type="text" name="numero_documento" id="coprop_numero_documento" class="form-control form-control-sm">
                </div>

                <div class="col-md-3">
                  <label class="form-label mb-1">Parentesco</label>
                  <select name="parentesco" id="coprop_parentesco" class="form-select form-select-sm">
                    <option value="">-- Seleccione --</option>
                    <option value="CONYUGE">Cónyuge</option>
                    <option value="HIJO">Hijo</option>
                    <option value="PADRE">Padre</option>
                    <option value="MADRE">Madre</option>
                    <option value="HERMANO">Hermano</option>
                    <option value="OTRO">Otro</option>
                  </select>
                </div>

                <div class="col-md-4">
                  <label class="form-label mb-1">Nombres</label>
                  <input type="text" name="nombres" id="coprop_nombres" class="form-control form-control-sm">
                </div>

                <div class="col-md-4">
                  <label class="form-label mb-1">Apellidos</label>
                  <input type="text" name="apellidos" id="coprop_apellidos" class="form-control form-control-sm">
                </div>

                <div class="col-md-4">
                  <label class="form-label mb-1">Fecha nacimiento</label>
                  <input type="date" name="fecha_nacimiento" id="coprop_fecha_nacimiento" class="form-control form-control-sm">
                </div>

                <div class="col-md-3">
                  <label class="form-label mb-1">Teléfono</label>
                  <input type="text" name="telefono" id="coprop_telefono" class="form-control form-control-sm">
                </div>

                <div class="col-md-3">
                  <label class="form-label mb-1">Email</label>
                  <input type="email" name="email" id="coprop_email" class="form-control form-control-sm">
                </div>

                <div class="col-md-4">
                  <label class="form-label mb-1">Dirección</label>
                  <input type="text" name="direccion" id="coprop_direccion" class="form-control form-control-sm">
                </div>

                <div class="col-md-2">
                  <label class="form-label mb-1">% participación</label>
                  <input type="number" step="0.01" name="porcentaje_participacion" id="coprop_porcentaje" class="form-control form-control-sm" placeholder="50.00">
                </div>

                <div class="col-md-2">
                  <label class="form-label mb-1">Estado</label>
                  <select name="estado" id="coprop_estado" class="form-select form-select-sm">
                    <option value="ACTIVO">Activo</option>
                    <option value="INACTIVO">Inactivo</option>
                  </select>
                </div>

                <div class="col-md-8">
                  <label class="form-label mb-1">Observaciones</label>
                  <input type="text" name="observaciones" id="coprop_observaciones" class="form-control form-control-sm">
                </div>

                <div class="col-md-4 d-flex align-items-end">
                  <button type="submit" id="btnSaveCoprop" class="btn btn-primary btn-sm w-100">
                    Guardar copropietario
                  </button>
                </div>

              </div>
            </form>
          </div>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>
