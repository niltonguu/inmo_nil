<?php
// app/Views/lotes/index.php
?>
<div class="container py-4">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h5>Lotes</h5>
    <div class="btn-group btn-group-sm">
      <button id="btnGenerarLotes" class="btn btn-outline-secondary">
        <i class="bi bi-magic"></i> Generar lotes por rango
      </button>
      <button id="btnNewLote" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nuevo lote
      </button>
    </div>
  </div>

  <!-- Filtros -->
  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-md-4">
          <label class="form-label mb-1">Proyecto</label>
          <select id="filtroProyecto" class="form-select form-select-sm">
            <option value="">-- Seleccione --</option>
            <?php if (!empty($proyectos)): ?>
              <?php foreach ($proyectos as $p): ?>
                <option value="<?= (int)$p['id'] ?>">
                  <?= htmlspecialchars($p['nombre'] . ' (' . $p['codigo'] . ')', ENT_QUOTES) ?>
                </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label mb-1">Etapa</label>
          <select id="filtroEtapa" class="form-select form-select-sm" disabled>
            <option value="">-- Todas --</option>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label mb-1">Manzana</label>
          <select id="filtroManzana" class="form-select form-select-sm" disabled>
            <option value="">-- Todas --</option>
          </select>
        </div>

        <div class="col-md-2 d-grid">
          <button id="btnFiltrarLotes" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-search"></i> Filtrar
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Contenedor de cards -->
  <div id="lotesContainer" class="row g-2">
    <!-- Se llena por JS -->
  </div>

</div>

<?php require __DIR__ . '/modals/lote.php'; ?>
<?php require __DIR__ . '/modals/generar_lotes.php'; ?>
