<?php // app/Views/main.php ?>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user'])) {
    header('Location: index.php?c=auth&a=loginForm');
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>PUBLICIDAD APP</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- CSS globales -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

  <style>
    body {
      background-color: #f5f6fa;
    }
    .navbar-brand {
      font-weight: bold;
      letter-spacing: .5px;
    }


  </style>

</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php?c=dashboard&a=index">PUBLICIDAD</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link" href="index.php?c=dashboard&a=index">Dashboard</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?c=personas&a=index">Personas</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?c=proyectos&a=index">Proyectos</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?c=lotes&a=index">Lotes</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php?c=clientes&a=index">Clientes</a>
        </li>

        <!-- aquí puedes agregar más módulos -->
      </ul>
    </div>


    <div class="d-flex align-items-center">
      <span class="text-white me-3">
        <i class="bi bi-person-circle"></i>
        <?= htmlspecialchars($_SESSION['user']['fullname'] ?? '') ?>
      </span>
      <a class="btn btn-sm btn-outline-light" href="index.php?c=auth&a=logout">
        <i class="bi bi-box-arrow-right"></i> Salir
      </a>
    </div>
  </div>
</nav>

<div class="container-fluid py-3">
<?php
// 👉 AQUÍ se pinta el módulo (dashboard, personas, etc.)
if (isset($content_view) && file_exists($content_view)) {
    require $content_view;
} else {
    echo '<div class="alert alert-warning">Contenido no encontrado.</div>';
}
?>
</div>

<!-- JS globales -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
  // Ping para usuarios online
  setInterval(function(){
    $.get('index.php?c=api&a=ping');
  }, 60000); // cada 60 segundos
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  window.APP = window.APP || {};
  APP.currentUser = {
    id: <?= (int)($_SESSION['user']['id'] ?? 0) ?>,
    role: "<?= htmlspecialchars($_SESSION['user']['role'] ?? 'visita', ENT_QUOTES) ?>",
    fullname: "<?= htmlspecialchars($_SESSION['user']['fullname'] ?? '', ENT_QUOTES) ?>"
  };
</script>

<!-- JS de módulos -->
<script src="public/assets/js/app.js"></script>
<script src="public/assets/js/persona.js"></script>

<script src="public/assets/js/proyectos.js"></script>
<script src="public/assets/js/clientes.js"></script>

<script src="public/assets/js/lotes_global.js"></script>
<?php if ($view === 'lotes/index_admin'): ?>
  <script src="public/assets/js/lotes_admin.js"></script>
<?php elseif ($view === 'lotes/index_usuario'): ?>
  <script src="public/assets/js/lotes_usuario.js"></script>
<?php endif; ?>

</body>
</html>
