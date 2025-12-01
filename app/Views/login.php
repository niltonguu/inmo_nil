<?php
// app/Views/login.php
if (isset($_SESSION['user'])) {
    header('Location: index.php?c=dashboard&a=index'); exit;
}
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Publicidad - Login</title>

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

  <style>
    body{background:#f5f7fa}
    .card{border-radius:10px}
  </style>
</head>
<body>
  <div class="container d-flex align-items-center justify-content-center" style="min-height:100vh">
    <div class="card p-4 shadow-sm" style="width:360px">
      <h4 class="mb-3 text-center">PUBLICIDAD APP</h4>

      <div class="mb-2"><input id="username" class="form-control" placeholder="Usuario" autofocus></div>
      <div class="mb-3"><input id="password" type="password" class="form-control" placeholder="Contraseña"></div>
      <button id="btnLogin" class="btn btn-primary w-100">Ingresar</button>
    </div>
  </div>

<script>
$('#btnLogin').on('click', function(){
    let u = $('#username').val().trim();
    let p = $('#password').val().trim();
    if(!u || !p){ Swal.fire('Error','Completa usuario y contraseña','error'); return; }

    $.post('index.php?c=auth&a=login', { username: u, password: p }, function(resp){
        // resp debería venir en JSON
        let data;
        try { data = (typeof resp === 'object') ? resp : JSON.parse(resp); } catch(e){ console.error(resp); Swal.fire('Error','Respuesta inválida del servidor','error'); return; }

        if(data.ok){
            Swal.fire({ icon:'success', title:'Bienvenido', timer:700, showConfirmButton:false }).then(()=> {
                window.location = 'index.php?c=dashboard&a=index';
            });
        } else {
            Swal.fire('Error', data.msg || 'Credenciales inválidas','error');
        }
    }).fail(function(xhr){
        Swal.fire('Error','Error en la petición: ' + xhr.status,'error');
    });
});

// Permitir Enter para enviar
$('#password, #username').on('keypress', function(e){ if(e.which===13) $('#btnLogin').click(); });
</script>
</body>
</html>
