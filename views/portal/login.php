<?php
$tituloPagina = 'Portal Estudiantil';
require_once SRC_PATH . '/views/layout/header.php';
?>

<div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center py-4"
     style="background: linear-gradient(135deg, #f8f9fa 0%, #e9f2ff 100%);">
  <div class="card shadow-lg border-0" style="width:100%;max-width:420px;">
    <div class="card-body p-5">

      <div class="text-center mb-4">
        <i class="bi bi-book-half text-primary" style="font-size:3rem;"></i>
        <h4 class="mt-2 fw-bold">Biblioteca Digital</h4>
        <p class="text-muted small mb-0">Portal Estudiantil</p>
      </div>

      <?php require_once SRC_PATH . '/views/layout/flash.php'; ?>

      <form method="POST" action="<?= BASE_URL ?>/index.php?mod=portal&accion=login" novalidate id="formLoginPortal">
        <?= CsrfToken::campoOculto() ?>

        <div class="mb-3">
          <label class="form-label fw-semibold">CIP o correo</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
            <input
              type="text"
              name="identificador"
              class="form-control"
              placeholder="8-888-888 o user@correo.com"
              required
              autocomplete="username"
              value="<?= Sanitizador::html($_POST['identificador'] ?? '') ?>"
            >
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Contraseña</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input
              type="password"
              name="password"
              id="inputPass"
              class="form-control"
              placeholder="••••••••••••"
              required
              autocomplete="current-password"
              minlength="8"
              maxlength="12"
            >
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
        <a href="<?= BASE_URL ?>/index.php?mod=auth&accion=login" class="text-decoration-none small">
          <i class="bi bi-shield-lock me-1"></i>Panel Administrativo
        </a>
        &nbsp;|&nbsp;
        <a href="<?= BASE_URL ?>/index.php?mod=portal" class="text-decoration-none small">
          <i class="bi bi-house me-1"></i>Página pública
        </a>
      </div>

    </div>
  </div>
</div>

<script>
function togglePass() {
  const i = document.getElementById('inputPass');
  const eye = document.getElementById('eyeIcon');
  i.type = i.type === 'password' ? 'text' : 'password';
  eye.className = i.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

history.replaceState(null, '', location.href);
</script>

<?php require_once SRC_PATH . '/views/layout/footer.php'; ?>