<?php // app/Views/dashboard/index.php ?>
<?php
$fullname = $currentUser['fullname'] ?? 'Usuario';
$role     = $currentUser['role'] ?? 'visita';

$summary = $summary ?? [
    'total_personas'     => 0,
    'personas_asignadas' => 0,
    'total_usuarios'     => 0,
    'online_usuarios'    => 0,
    'prospectos'         => 0,
    'separados'          => 0,
    'vendidos'           => 0,
    'problemas'          => 0,
];

$usersActivity = $usersActivity ?? [];
?>

<div class="container py-4">

  <!-- Saludo -->
  <div class="p-4 mb-4 rounded-3 border bg-white shadow-sm">
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between">
      <div>
        <h4 class="mb-1">
          Hola, <?= htmlspecialchars($fullname) ?> 👋
        </h4>
        <p class="text-muted mb-0 small">
          Rol: <span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($role) ?></span>
        </p>
        <p class="text-muted mb-0 small mt-2">
          Resumen de personas, etiquetas y actividad de usuarios.
        </p>
      </div>
      <div class="mt-3 mt-md-0 text-md-end">
        <div class="mb-2">
          <span class="badge rounded-pill bg-success-subtle text-success border">
            <i class="bi bi-circle-fill me-1"></i>
            <?= (int)$summary['online_usuarios'] ?> usuarios online
          </span>
        </div>
        <a href="index.php?c=personas&a=index" class="btn btn-sm btn-primary">
          <i class="bi bi-people-fill me-1"></i> Ir a Personas
        </a>
      </div>
    </div>
  </div>

  <!-- Resumen superior (personas y usuarios) -->
  <div class="row g-3 mb-3">
    <div class="col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-muted small text-uppercase">Total personas</span>
            <i class="bi bi-person-lines-fill fs-4 text-primary"></i>
          </div>
          <h3 class="mb-0"><?= (int)$summary['total_personas'] ?></h3>
          <p class="text-muted small mb-0">Registradas en el sistema</p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-muted small text-uppercase">Personas asignadas</span>
            <i class="bi bi-person-check-fill fs-4 text-success"></i>
          </div>
          <h3 class="mb-0"><?= (int)$summary['personas_asignadas'] ?></h3>
          <p class="text-muted small mb-0">Con responsable asignado</p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-muted small text-uppercase">Usuarios</span>
            <i class="bi bi-person-badge-fill fs-4 text-info"></i>
          </div>
          <h3 class="mb-0"><?= (int)$summary['total_usuarios'] ?></h3>
          <p class="text-muted small mb-0">Con acceso a la plataforma</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Resumen etiquetas (Prospectos, Separados, Vendidos, Problemas) -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-muted small text-uppercase">Prospectos</span>
            <i class="bi bi-bullseye fs-4 text-primary"></i>
          </div>
          <h4 class="mb-0"><?= (int)$summary['prospectos'] ?></h4>
          <p class="text-muted small mb-0">Leads con interés</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-muted small text-uppercase">Separados</span>
            <i class="bi bi-flag fs-4 text-warning"></i>
          </div>
          <h4 class="mb-0"><?= (int)$summary['separados'] ?></h4>
          <p class="text-muted small mb-0">Reservas o separaciones</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-muted small text-uppercase">Vendidos</span>
            <i class="bi bi-check2-circle fs-4 text-success"></i>
          </div>
          <h4 class="mb-0"><?= (int)$summary['vendidos'] ?></h4>
          <p class="text-muted small mb-0">Operaciones cerradas</p>
        </div>
      </div>
    </div>

    <div class="col-md-3">
      <div class="card shadow-sm border-0 h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="text-muted small text-uppercase">Problemas</span>
            <i class="bi bi-exclamation-triangle fs-4 text-danger"></i>
          </div>
          <h4 class="mb-0"><?= (int)$summary['problemas'] ?></h4>
          <p class="text-muted small mb-0">Casos con incidencias</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Actividad por usuario -->
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h6 class="mb-0">Actividad por usuario</h6>
    <span class="text-muted small">
      Conexiones, asignaciones, actividad y notas
    </span>
  </div>

  <?php if (empty($usersActivity)): ?>
    <div class="alert alert-light border text-muted small">
      No hay usuarios registrados aún o no se encontró actividad.
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($usersActivity as $u): ?>
        <div class="col-md-4 col-lg-3">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body d-flex flex-column">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                  <h6 class="mb-0">
                    <i class="bi bi-person-circle me-1"></i>
                    <?= htmlspecialchars($u['fullname']) ?>
                  </h6>
                  <span class="badge bg-light text-dark border small text-uppercase">
                    <?= htmlspecialchars($u['role']) ?>
                  </span>
                </div>
              </div>

              <div class="mt-2">

                <!-- Conexiones -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-muted small">Veces que se conectó</span>
                  <span class="fw-semibold">
                    <?= (int)$u['login_count'] ?>
                  </span>
                </div>
                <div class="progress mb-2" style="height: 4px;">
                  <?php
                    $logins = (int)$u['login_count'];
                    $w1     = max(5, min(100, ($logins / 50) * 100));
                  ?>
                  <div class="progress-bar" role="progressbar"
                       style="width: <?= $w1 ?>%;"></div>
                </div>

                <!-- Personas asignadas -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-muted small">Personas asignadas</span>
                  <span class="fw-semibold">
                    <?= (int)$u['personas_asignadas'] ?>
                  </span>
                </div>
                <div class="progress mb-2" style="height: 4px;">
                  <?php
                    $asig = (int)$u['personas_asignadas'];
                    $w2   = max(5, min(100, ($asig / 50) * 100));
                  ?>
                  <div class="progress-bar bg-success" role="progressbar"
                       style="width: <?= $w2 ?>%;"></div>
                </div>

                <!-- Actividad teléfono -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-muted small">Actividad teléfono (clicks)</span>
                  <span class="fw-semibold">
                    <?= (int)$u['clicks'] ?>
                  </span>
                </div>
                <div class="progress mb-2" style="height: 4px;">
                  <?php
                    $clk = (int)$u['clicks'];
                    $w3  = max(5, min(100, ($clk / 50) * 100));
                  ?>
                  <div class="progress-bar bg-info" role="progressbar"
                       style="width: <?= $w3 ?>%;"></div>
                </div>

                <!-- Notas registradas -->
                <div class="d-flex justify-content-between align-items-center mb-1">
                  <span class="text-muted small">Notas registradas</span>
                  <span class="fw-semibold">
                    <?= (int)$u['notas'] ?>
                  </span>
                </div>
                <div class="progress" style="height: 4px;">
                  <?php
                    $nt = (int)$u['notas'];
                    $w4 = max(5, min(100, ($nt / 50) * 100));
                  ?>
                  <div class="progress-bar bg-warning" role="progressbar"
                       style="width: <?= $w4 ?>%;"></div>
                </div>

              </div>

              <div class="mt-3 text-end">
                <a href="index.php?c=personas&a=index" class="btn btn-outline-secondary btn-sm">
                  <i class="bi bi-arrow-right-short"></i> Ver personas
                </a>
              </div>

            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div>
