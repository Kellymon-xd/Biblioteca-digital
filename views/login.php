<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Acceso Administrativo – Biblioteca Digital</title>
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/app.css">
</head>

<body class="bg-primary bg-gradient min-vh-100 d-flex align-items-center justify-content-center">
  <div class="card shadow-lg" style="width:100%;max-width:420px;">
    <div class="card-body p-5">
      <div class="text-center mb-4">
        <i class="bi bi-book-half text-primary" style="font-size:3rem;"></i>
        <h4 class="mt-2 fw-bold">Biblioteca Digital</h4>
        <p class="text-muted small">Panel Administrativo</p>
      </div>

      <?php foreach (ErrorHandler::obtenerMensajes() as $tipo => $lista): ?>
        <?php foreach ($lista as $msg): ?>
          <div class="alert alert-<?= $tipo ?> py-2"><?= Sanitizador::html($msg) ?></div>
        <?php endforeach; ?>
      <?php endforeach; ?>

      <form method="POST" action="<?= BASE_URL ?>/index.php?mod=auth&accion=login" novalidate id="formLogin">
        <?= CsrfToken::campoOculto() ?>
        <div class="mb-3">
          <label class="form-label fw-semibold">Usuario</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="username" class="form-control" placeholder="usuario" required
              autocomplete="username" value="<?= Sanitizador::html($_POST['username'] ?? '') ?>">
          </div>
        </div>
        <div class="mb-4">
          <label class="form-label fw-semibold">Contraseña</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="inputPass" class="form-control" placeholder="••••••••••••"
              required autocomplete="current-password" minlength="8" maxlength="12">
            <button type="button" class="btn btn-outline-secondary" onclick="togglePass()">
              <i class="bi bi-eye" id="eyeIcon"></i>
            </button>
          </div>
          <div class="form-text">Entre 8 y 12 caracteres.</div>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
          <i class="bi bi-box-arrow-in-right me-2"></i>Ingresar
        </button>
      </form>
      <hr>
      <div class="text-center">
        <a href="<?= BASE_URL ?>/index.php?mod=portal&accion=login" class="text-decoration-none small">
          <i class="bi bi-person-badge me-1"></i>Portal Estudiantil
        </a>
        &nbsp;|&nbsp;
        <a href="<?= BASE_URL ?>/index.php?mod=portal" class="text-decoration-none small">
          <i class="bi bi-house me-1"></i>Página pública
        </a>
      </div>
    </div>
  </div>
  <script src="<?= BASE_URL ?>/assets/js/app.js"></script>
  <script>
    function togglePass() {
      const i = document.getElementById('inputPass');
      const eye = document.getElementById('eyeIcon');
      i.type = i.type === 'password' ? 'text' : 'password';
      eye.className = i.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    }
    // Prevenir historial del navegador en páginas protegidas
    history.replaceState(null, '', location.href);
  </script>
</body>

</html>