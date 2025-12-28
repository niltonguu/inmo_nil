<?php // app/Views/lotes/modals/documentos_lotes.php ?>
<?php // MODAL: Documentos del lote ?>
<div class="modal fade" id="modalLoteDocumentos" tabindex="-1" aria-labelledby="modalLoteDocumentosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="formLoteDocumento">

        <div class="modal-header">
          <h5 class="modal-title" id="modalLoteDocumentosLabel">Documentos del lote</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">

          <!-- Alerta de felicitación -->
          <div id="alert-reserva"></div>

          <!-- Hidden: ID de lote -->
          <input type="hidden" name="id_lote" id="doc_id_lote">

          <!-- Hidden: titulo (tu controlador lo recibe) -->
          <input type="hidden" name="titulo" id="doc_titulo" value="">

          <!-- Hidden: plantilla (ID en BD) -->
          <input type="hidden" name="plantilla" id="doc_plantilla" value="">

          <!-- Info básica del lote y cliente -->
          <div class="border rounded p-2 mb-3 bg-light small">
            <div>
              <strong>Lote:</strong>
              <span id="doc_lbl_lote">—</span>
            </div>
            <div>
              <strong>Proyecto:</strong>
              <span id="doc_lbl_proyecto">—</span>
            </div>
            <div>
              <strong>Cliente:</strong>
              <span id="doc_lbl_cliente">—</span>
            </div>
            <div>
              <strong>Estado del lote:</strong>
              <span id="doc_lbl_estado">—</span>
            </div>
          </div>

          <div class="row">
            <!-- 1) Tipo de persona -->
            <div class="col-md-6 mb-3">
              <label for="doc_tipo_persona" class="form-label">Tipo de persona</label>
              <select class="form-select form-select-sm" name="tipo_persona" id="doc_tipo_persona" required>
                <option value="">-- Seleccione --</option>
                <option value="PN">Persona Natural</option>
                <option value="PN_CONYUGE">Persona Natural con Cónyuge</option>
                <option value="PJ">Persona Jurídica</option>
              </select>
              <div class="form-text">
                Selecciona el tipo de persona para cargar la plantilla correcta.
              </div>
            </div>

            <!-- 2) Tipo de documento -->
            <div class="col-md-6 mb-3">
              <label for="doc_tipo_documento" class="form-label">Tipo de documento a generar</label>
              <select class="form-select form-select-sm" name="tipo_documento" id="doc_tipo_documento" required disabled>
                <option value="">-- Seleccione --</option>
                <option value="RESERVA">Contrato de RESERVA</option>
                <option value="SEPARACION">Contrato de SEPARACIÓN</option>
                <option value="COMPRAVENTA">Contrato de COMPRAVENTA</option>
                <option value="ANULACION">Documento de ANULACIÓN</option>
              </select>
              <div class="form-text">
                Primero selecciona el tipo de persona para habilitar este selector.
              </div>
            </div>
          </div>


          <!-- Contenedor dinámico de campos según el tipo de documento -->
          <div id="doc_campos_container">
            <div class="alert alert-info small mb-0">
              Selecciona tipo de persona y tipo de documento para mostrar los campos necesarios.
            </div>
          </div>

          <!-- Panel resultado (si lo usas en tu JS) -->
          <div id="doc_result_container" class="d-none mt-3">
            <div class="border rounded p-2 bg-light">
              <div class="fw-semibold small mb-2">Documento generado</div>
              <div class="d-flex flex-wrap gap-2">
                <a id="doc_result_view" class="btn btn-sm btn-outline-primary" href="#" target="_blank" rel="noopener">Ver</a>
                <a id="doc_result_html" class="btn btn-sm btn-outline-secondary" href="#">HTML</a>
                <a id="doc_result_pdf" class="btn btn-sm btn-outline-secondary" href="#">PDF</a>
              </div>
            </div>
          </div>

          <!-- Tabla docs (si la incluyes en el modal completo en otra parte) -->
          <div class="mt-3">
            <div class="fw-semibold small mb-2">Historial de documentos</div>
            <div class="table-responsive">
              <table class="table table-sm table-striped align-middle mb-0">
                <thead>
                  <tr class="small text-muted">
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Título</th>
                    <th class="text-center">Estado</th>
                    <th>Usuario</th>
                    <th class="text-end">Acciones</th>
                  </tr>
                </thead>
                <tbody id="doc_docs_body">
                  <tr>
                    <td colspan="6" class="text-center text-muted small py-3">
                      Selecciona un lote para ver sus documentos.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-sm btn-primary" id="btnGenerarDocumento">
            Generar documento
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
