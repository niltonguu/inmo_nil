<?php
// app/Views/lotes/modals/documentos.php
?>
<div class="modal fade" id="modalLoteDocumentos" tabindex="-1" aria-labelledby="modalLoteDocumentosLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalLoteDocumentosLabel">Documentos del lote</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">

        <!-- Header info -->
        <div class="row g-2 mb-2">
          <div class="col-md-3">
            <div class="small text-muted">Lote</div>
            <div class="fw-semibold" id="doc_lbl_lote">—</div>
          </div>
          <div class="col-md-4">
            <div class="small text-muted">Proyecto</div>
            <div class="fw-semibold" id="doc_lbl_proyecto">—</div>
          </div>
          <div class="col-md-3">
            <div class="small text-muted">Cliente</div>
            <div class="fw-semibold" id="doc_lbl_cliente">—</div>
          </div>
          <div class="col-md-2">
            <div class="small text-muted">Estado del lote</div>
            <div class="fw-semibold" id="doc_lbl_estado">—</div>
          </div>
        </div>

        <hr class="my-3">

        <!-- Form generar documento -->
        <form id="formLoteDocumento">
          <input type="hidden" name="id_lote" id="doc_id_lote">

          <div class="row g-2 align-items-end">
            <div class="col-md-3">
              <label class="form-label form-label-sm">Tipo de documento</label>
              <select class="form-select form-select-sm" id="doc_tipo_documento" name="tipo_documento" required>
                <option value="">-- Seleccione --</option>
                <option value="RESERVA">RESERVA</option>
                <option value="SEPARACION">SEPARACION</option>
                <option value="COMPRAVENTA">COMPRAVENTA</option>
                <option value="ANULACION">ANULACION</option>
              </select>
              <div class="form-text small">
                (Luego filtraremos opciones según el estado del lote)
              </div>
            </div>

            <div class="col-md-4">
              <label class="form-label form-label-sm">Título</label>
              <input type="text" class="form-control form-control-sm" name="titulo" id="doc_titulo" placeholder="Documento RESERVA / SEPARACION / ...">
            </div>

            <div class="col-md-3">
              <label class="form-label form-label-sm">Plantilla</label>
              <?php
                // Lista de plantillas disponibles (app/Templates)
                $tplDir = __DIR__ . '/../../Templates';
                $tpls = [];
                if (is_dir($tplDir)) {
                  foreach (glob($tplDir . '/*.html') as $f) $tpls[] = basename($f);
                }
              ?>
              <input type="text"
                     class="form-control form-control-sm"
                     name="plantilla"
                     id="doc_plantilla"
                     list="doc_plantilla_list"
                     placeholder="Ej: contrato_reserva_natural.html (opcional)">
              <datalist id="doc_plantilla_list">
                <?php foreach ($tpls as $t): ?>
                  <option value="<?= htmlspecialchars($t, ENT_QUOTES) ?>"></option>
                <?php endforeach; ?>
              </datalist>
              <div class="form-text small">
                Si lo dejas vacío, el sistema elige una plantilla por defecto según el tipo.
              </div>
            </div>

            <div class="col-md-2 d-grid">
              <button type="submit" class="btn btn-primary btn-sm" id="btnGenerarDocumento">
                Generar documento
              </button>
            </div>
          </div>

          <!-- Campos dinámicos por tipo -->
          <div class="mt-2" id="doc_campos_container">
            <div class="alert alert-info small mb-0">
              Selecciona un tipo de documento para mostrar los campos necesarios.
            </div>
          </div>

          <!-- Resultado inmediato del documento generado (se mostrará por JS) -->
          <div class="mt-3 d-none" id="doc_result_container">
            <div class="alert alert-success d-flex flex-column gap-2 mb-0">
              <div class="fw-semibold">
                Documento generado ✅
              </div>
              <div class="small">
                Ya puedes abrirlo o descargarlo:
              </div>
              <div class="d-flex flex-wrap gap-2">
                <a href="#" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary" id="doc_result_view">Ver</a>
                <a href="#" class="btn btn-sm btn-outline-secondary" id="doc_result_html">Descargar HTML</a>
                <a href="#" class="btn btn-sm btn-outline-secondary" id="doc_result_pdf">Descargar PDF</a>
              </div>
              <div class="small text-muted">
                Si lo vuelves a generar, este panel se actualiza con la última versión.
              </div>
            </div>
          </div>

        </form>

        <hr class="my-3">

        <!-- Tabla documentos generados -->
        <div class="table-responsive border rounded">
          <table class="table table-sm table-striped align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width:140px">Fecha</th>
                <th style="width:120px">Tipo</th>
                <th>Título</th>
                <th style="width:90px" class="text-center">Vigencia</th>
                <th style="width:160px">Usuario</th>
                <th style="width:220px" class="text-end">Acciones</th>
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

        <div class="small text-muted mt-2">
          Nota: El documento se genera inmediatamente. Si deseas un PDF “bonito” (con formato legal exacto),
          luego lo elevamos con un motor PDF (dompdf / wkhtmltopdf).
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>
