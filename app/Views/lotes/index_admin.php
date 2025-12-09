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


<?php // app/Views/lotes/modals/factores_lote.php (puedes separarlo si quieres) ?>
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
