<?php
// app/Views/lotes/index_admin.php
?>
<div class="container-fluid py-3">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Lotes - Administración</h4>
    <button type="button" class="btn btn-sm btn-primary" id="btnAbrirMasivo">
      <i class="bi bi-grid-3x3-gap"></i> Crear lotes masivos
    </button>
  </div>

  <!-- Filtros -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-3">
          <label for="filtroProyecto" class="form-label">Proyecto</label>
          <select id="filtroProyecto" class="form-select form-select-sm">
            <option value="">Todos</option>
          </select>
        </div>
        <div class="col-md-3">
          <label for="filtroEtapa" class="form-label">Etapa</label>
          <select id="filtroEtapa" class="form-select form-select-sm">
            <option value="">Todas</option>
          </select>
        </div>
        <div class="col-md-3">
          <label for="filtroManzana" class="form-label">Manzana</label>
          <select id="filtroManzana" class="form-select form-select-sm">
            <option value="">Todas</option>
          </select>
        </div>
        <div class="col-md-3">
          <label for="filtroEstado" class="form-label">Estado lote</label>
          <select id="filtroEstado" class="form-select form-select-sm">
            <option value="">Todos</option>
            <option value="DISPONIBLE">DISPONIBLE</option>
            <option value="RESERVADO">RESERVADO</option>
            <option value="SEPARADO">SEPARADO</option>
            <option value="VENDIDO">VENDIDO</option>
            <option value="BLOQUEADO">BLOQUEADO</option>
          </select>
        </div>
      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="card">
    <div class="card-body">
      <table id="tablaLotes" class="table table-striped table-bordered table-hover w-100">
        <thead>
          <tr>
            <th>Proyecto</th>
            <th>Etapa</th>
            <th>Manzana</th>
            <th>Lote</th>
            <th>Área (m²)</th>
            <th>Precio base</th>
            <th>Factor (%)</th>
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

<!-- ==========================
     MODAL: Lote (alta / edición)
     ========================== -->
<div class="modal fade" id="modalLote" tabindex="-1" aria-labelledby="modalLoteLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="formLote">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLoteLabel">Lote</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="id">

          <div class="row g-3 mb-2">
            <div class="col-md-4">
              <label class="form-label">Proyecto</label>
              <select name="id_proyecto" class="form-select form-select-sm" required></select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Etapa</label>
              <select name="id_etapa" class="form-select form-select-sm" required></select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Manzana</label>
              <select name="id_manzana" class="form-select form-select-sm" required></select>
            </div>
          </div>

          <div class="row g-3 mb-2">
            <div class="col-md-3">
              <label class="form-label">Número de lote</label>
              <input type="number" name="numero" class="form-control form-control-sm" required min="1">
            </div>
            <div class="col-md-3">
              <label class="form-label">Código</label>
              <input type="text" name="codigo" class="form-control form-control-sm" placeholder="Ej: L-1">
            </div>
            <div class="col-md-3">
              <label class="form-label">Área (m²)</label>
              <input type="number" step="0.01" name="area_m2" class="form-control form-control-sm" required min="0.01">
            </div>
            <div class="col-md-3">
              <label class="form-label">Precio m²</label>
              <input type="number" step="0.01" name="precio_m2" class="form-control form-control-sm" placeholder="Opcional">
            </div>
          </div>

          <div class="row g-3 mb-2">
            <div class="col-md-3">
              <label class="form-label">Frente (m)</label>
              <input type="number" step="0.01" name="frente_m" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
              <label class="form-label">Fondo (m)</label>
              <input type="number" step="0.01" name="fondo_m" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
              <label class="form-label">Lado izquierdo (m)</label>
              <input type="number" step="0.01" name="lado_izq_m" class="form-control form-control-sm">
            </div>
            <div class="col-md-3">
              <label class="form-label">Lado derecho (m)</label>
              <input type="number" step="0.01" name="lado_der_m" class="form-control form-control-sm">
            </div>
          </div>

          <div class="row g-3 mb-2">
            <div class="col-md-6">
              <label class="form-label">Estado comercial</label>
              <select name="estado_comercial" class="form-select form-select-sm">
                <option value="HABILITADO">HABILITADO</option>
                <option value="DESHABILITADO">DESHABILITADO</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Estado del lote</label>
              <select name="estado_lote" class="form-select form-select-sm">
                <option value="DISPONIBLE">DISPONIBLE</option>
                <option value="RESERVADO">RESERVADO</option>
                <option value="SEPARADO">SEPARADO</option>
                <option value="VENDIDO">VENDIDO</option>
                <option value="BLOQUEADO">BLOQUEADO</option>
              </select>
            </div>
          </div>

          <div class="alert alert-info small mt-3 mb-0">
            <strong>Nota:</strong> El cálculo de <em>precio_base</em>, <em>factor_pct_total</em> y
            <em>precio_final</em> se realizará en el backend (modelo), en base al proyecto, área y factores
            configurados.
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ========================================
     MODAL: Creación MASIVA de lotes
     ======================================== -->
<div class="modal fade" id="modalLotesMasivos" tabindex="-1" aria-labelledby="modalLotesMasivosLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-md modal-dialog-scrollable">
    <div class="modal-content">
      <form id="formLotesMasivos">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLotesMasivosLabel">Creación masiva de lotes</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">

          <div class="alert alert-warning small">
            <strong>Importante:</strong> Esta función creará varios lotes en la misma manzana, numerados
            secuencialmente (desde / hasta). Úsalo solo al iniciar el proyecto.
          </div>

          <div class="mb-2">
            <label class="form-label">Proyecto</label>
            <select name="id_proyecto" class="form-select form-select-sm" required></select>
          </div>

          <div class="mb-2">
            <label class="form-label">Etapa (opcional)</label>
            <select name="id_etapa" class="form-select form-select-sm"></select>
          </div>

          <div class="mb-2">
            <label class="form-label">Manzana</label>
            <select name="id_manzana" class="form-select form-select-sm" required></select>
          </div>

          <hr class="my-2">

          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="form-label">Número desde</label>
              <input type="number" name="numero_desde" class="form-control form-control-sm" required min="1">
            </div>
            <div class="col-6">
              <label class="form-label">Número hasta</label>
              <input type="number" name="numero_hasta" class="form-control form-control-sm" required min="1">
            </div>
          </div>

          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="form-label">Área (m²) por defecto</label>
              <input type="number" step="0.01" name="area_m2_default" class="form-control form-control-sm">
            </div>
            <div class="col-6">
              <label class="form-label">Precio m² por defecto</label>
              <input type="number" step="0.01" name="precio_m2_default" class="form-control form-control-sm">
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-sm btn-primary">Crear lotes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php // app/Views/lotes/modals/factores_lote.php ?>
<div class="modal fade" id="modalLoteFactores" tabindex="-1" aria-labelledby="modalLoteFactoresLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="formLoteFactores">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLoteFactoresLabel">
            <span id="lblLoteTituloFactores">Factores del lote</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">

          <!-- Hidden para lógica -->
          <input type="hidden" id="fact_id_lote" name="id_lote">
          <input type="hidden" id="fact_id_proyecto">
          <input type="hidden" id="fact_factor_min">
          <input type="hidden" id="fact_factor_max">
          <input type="hidden" id="fact_precio_base">

          <div class="alert alert-info py-2 mb-3">
            <div><strong>Rango permitido de factores del proyecto:</strong>
              <span id="lblFactorRango">—</span>
            </div>
            <div class="mt-1">
              <strong>Factor total aplicado al lote:</strong>
              <span id="lblFactorTotal" class="badge bg-secondary">0.00%</span>
            </div>
          </div>

          <div class="table-responsive mb-3">
            <table class="table table-sm table-striped align-middle" id="tblFactoresLote">
              <thead>
                <tr>
                  <th style="width:40px;" class="text-center">✔</th>
                  <th>Categoría</th>
                  <th>Nombre (código)</th>
                  <th class="text-end">Valor (%)</th>
                </tr>
              </thead>
              <tbody id="tblFactoresLoteBody">
                <tr>
                  <td colspan="4" class="text-muted text-center small">
                    Selecciona un lote para ver sus factores.
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="row">
            <div class="col-md-4 mb-2">
              <div class="border rounded p-2 small">
                <div class="text-muted">Precio base</div>
                <div id="lblPrecioBase" class="fw-bold">S/ 0.00</div>
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <div class="border rounded p-2 small">
                <div class="text-muted">Precio final (preview)</div>
                <div id="lblPrecioFinal" class="fw-bold">S/ 0.00</div>
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <div class="border rounded p-2 small">
                <div class="text-muted">Nota</div>
                <div class="small text-muted">
                  El cálculo final se consolida en el backend al guardar.
                </div>
              </div>
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-primary">
            Guardar factores del lote
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ==========================
     MODAL: Vértices del lote
     ========================== -->
<div class="modal fade" id="modalVerticesLote" tabindex="-1" aria-labelledby="modalVerticesLoteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalVerticesLoteLabel">
          Vértices del lote
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <form id="formVerticesLote">

        <input type="hidden" name="id_lote" id="vert_id_lote">

        <div class="modal-body">

          <div class="alert alert-info small">
            <strong>Instrucciones:</strong>
            ingresa los vértices en <strong>sentido horario</strong>,
            con al menos <strong>3 puntos válidos</strong>.
          </div>

          <div class="row mb-2">
            <div class="col">
              <span class="fw-semibold">Lote:</span>
              <span id="vert_lbl_lote"></span>
            </div>
          </div>

          <div class="table-responsive border rounded">
            <table class="table table-sm mb-0" id="tblVerticesLote">
              <thead class="table-light">
                <tr>
                  <th style="width: 70px;">#</th>
                  <th style="width: 160px;">Latitud</th>
                  <th style="width: 160px;">Longitud</th>
                  <th style="width: 80px;"></th>
                </tr>
              </thead>
              <tbody id="tblVerticesBody">
                <!-- filas dinámicas -->
              </tbody>
            </table>
          </div>

          <div class="d-flex justify-content-between mt-2">
            <div>
              <button type="button" class="btn btn-outline-secondary btn-sm" id="btnAddVerticeRow">
                + Agregar vértice
              </button>
            </div>
            <div>
              <button type="button" class="btn btn-outline-danger btn-sm" id="btnClearVertices">
                Eliminar todos
              </button>
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cerrar
          </button>
          <button type="submit" class="btn btn-primary">
            Guardar vértices
          </button>
        </div>

      </form>

    </div>
  </div>
</div>
<!-- ==========================
     MODAL: Historial del lote
     ========================== -->
<div class="modal fade" id="modalHistorialLote" tabindex="-1" aria-labelledby="modalHistorialLoteLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="modalHistorialLoteLabel">
          Historial del lote
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">

        <div class="mb-2">
          <span class="fw-semibold">Lote:</span>
          <span id="hist_lbl_lote" class="ms-1"></span>
        </div>

        <div class="table-responsive border rounded">
          <table class="table table-sm mb-0" id="tblHistorialLote">
            <thead class="table-light">
              <tr>
                <th style="width: 150px;">Fecha / hora</th>
                <th style="width: 140px;">Usuario</th>
                <th style="width: 160px;">Estado</th>
                <th>Cliente</th>
                <th style="width: 220px;">Motivo</th>
              </tr>
            </thead>
            <tbody id="tblHistorialBody">
              <tr>
                <td colspan="5" class="text-muted text-center small">
                  Selecciona un lote para ver su historial.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
          Cerrar
        </button>
      </div>

    </div>
  </div>
</div>

<?php // MODAL: Documentos del lote ?>
<div class="modal fade" id="modalLoteDocumentos" tabindex="-1" aria-labelledby="modalLoteDocumentosLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="formLoteDocumento">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLoteDocumentosLabel">
            Documentos del lote
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">

          <!-- Alerta de felicitación -->
          <div id="alert-reserva"></div>

          <!-- Hidden: ID de lote -->
          <input type="hidden" name="id_lote" id="doc_id_lote">

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
              <span id="doc_lbl_estado_lote">—</span>
            </div>
          </div>

          <!-- Tipo de documento -->
          <div class="mb-3">
            <label for="doc_tipo_documento" class="form-label">Tipo de documento a generar</label>
            <select class="form-select form-select-sm" name="tipo_documento" id="doc_tipo_documento" required>
              <option value="">-- Seleccione --</option>
              <option value="RESERVA">Contrato de RESERVA</option>
              <option value="SEPARACION">Contrato de SEPARACIÓN</option>
              <option value="COMPRAVENTA">Contrato de COMPRAVENTA</option>
              <option value="ANULACION">Documento de ANULACIÓN</option>
            </select>
            <div class="form-text">
              Las opciones se podrán filtrar según el estado actual del lote en una siguiente iteración.
            </div>
          </div>

          <!-- Contenedor dinámico de campos según el tipo de documento -->
          <div id="doc_campos_container">
            <div class="alert alert-info small mb-0">
              Selecciona un tipo de documento para mostrar los campos necesarios.
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
            Cerrar
          </button>
          <button type="submit" class="btn btn-sm btn-primary">
            Generar documento
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
<?php //require __DIR__ . '/modals/documentos_lote.php'; ?>
<?php require __DIR__ . '/modals/documentos.php'; ?>

